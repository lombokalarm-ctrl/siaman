<?php

declare(strict_types=1);

function auth_tables_ready(): bool
{
    try {
        $stmt = db()->query("
            SELECT COUNT(*) AS c
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME IN ('users', 'roles')
        ");
        $row = $stmt ? $stmt->fetch() : null;
        return $row ? ((int)$row['c'] >= 2) : false;
    } catch (Throwable $e) {
        return false;
    }
}

function auth_ensure_roles(): void
{
    if (!auth_tables_ready()) {
        return;
    }
    try {
        $stmt = db()->prepare('INSERT IGNORE INTO roles (name) VALUES (:name)');
        foreach (['admin', 'staff', 'keuangan'] as $name) {
            $stmt->execute(['name' => $name]);
        }
    } catch (Throwable $e) {
    }
}

function auth_bootstrap(): void
{
    if (!auth_tables_ready()) {
        return;
    }

    auth_ensure_roles();

    $pdo = db();
    try {
        $stmt = $pdo->query('SELECT COUNT(*) AS c FROM users');
        $row = $stmt ? $stmt->fetch() : null;
        $count = $row ? (int)$row['c'] : 0;
    } catch (Throwable $e) {
        return;
    }

    if ($count > 0) {
        return;
    }

    $pdo->beginTransaction();
    try {
        $pdo->prepare('INSERT IGNORE INTO roles (name) VALUES (:name)')->execute(['name' => 'admin']);
    } catch (Throwable $e) {
    }

    $stmt = $pdo->prepare('SELECT id FROM roles WHERE name = :name LIMIT 1');
    $stmt->execute(['name' => 'admin']);
    $role = $stmt->fetch();
    $roleId = $role ? (int)$role['id'] : 0;
    if ($roleId <= 0) {
        $pdo->rollBack();
        return;
    }

    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    if (!is_string($hash) || $hash === '') {
        $pdo->rollBack();
        return;
    }

    try {
        $pdo->prepare('
            INSERT INTO users (username, password_hash, role_id, status, must_change_password)
            VALUES (:username, :password_hash, :role_id, :status, :must_change_password)
        ')->execute([
            'username' => 'admin',
            'password_hash' => $hash,
            'role_id' => $roleId,
            'status' => 'aktif',
            'must_change_password' => 1,
        ]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
    }
}

function auth_user(): ?array
{
    if (!isset($_SESSION['auth']) || !is_array($_SESSION['auth'])) {
        return null;
    }
    $a = $_SESSION['auth'];
    if (!isset($a['id'], $a['username'], $a['role'])) {
        return null;
    }
    return [
        'id' => (int)$a['id'],
        'username' => (string)$a['username'],
        'role' => (string)$a['role'],
        'must_change_password' => (int)($a['must_change_password'] ?? 0),
    ];
}

function auth_is_logged_in(): bool
{
    return auth_user() !== null;
}

function auth_is_admin(): bool
{
    $u = auth_user();
    return $u !== null && $u['role'] === 'admin';
}

function auth_is_staff(): bool
{
    $u = auth_user();
    return $u !== null && $u['role'] === 'staff';
}

function auth_is_admin_or_staff(): bool
{
    $u = auth_user();
    if ($u === null) {
        return false;
    }
    return $u['role'] === 'admin' || $u['role'] === 'staff';
}

function auth_role(): string
{
    $u = auth_user();
    return $u ? (string)$u['role'] : '';
}

function auth_can_access_page(string $pageKey): bool
{
    if ($pageKey === 'login') {
        return true;
    }
    if (!auth_is_logged_in()) {
        return false;
    }
    if (auth_is_admin()) {
        return true;
    }
    if (auth_is_staff()) {
        return !in_array($pageKey, ['invoice_edit', 'jamaah_unassigned'], true);
    }
    if (auth_is_admin_or_staff()) {
        return true;
    }

    $role = auth_role();
    if ($role === 'keuangan') {
        return in_array($pageKey, [
            'dashboard',
            'invoice',
            'invoice_detail',
            'invoice_print',
            'invoice_pdf',
            'kuitansi',
            'kuitansi_detail',
            'kuitansi_print',
            'kuitansi_pdf',
            'rekap_pembayaran',
            'rekap_piutang',
        ], true);
    }

    return false;
}

function auth_can_do_action(string $action): bool
{
    if ($action === 'auth.login') {
        return true;
    }
    if (!auth_is_logged_in()) {
        return false;
    }
    if ($action === 'auth.logout') {
        return true;
    }
    if (auth_is_admin()) {
        return true;
    }
    if (auth_is_staff()) {
        return !in_array($action, ['invoice.delete', 'invoice.update'], true);
    }

    $role = auth_role();
    if ($role === 'keuangan') {
        return in_array($action, [
            'payment.create',
        ], true);
    }

    return false;
}

function auth_require(): void
{
    if (auth_is_logged_in()) {
        return;
    }
    $next = (string)($_SERVER['REQUEST_URI'] ?? '');
    $to = app_url('/?page=login');
    if ($next !== '') {
        $to .= '&next=' . rawurlencode($next);
    }
    redirect($to);
}

function auth_require_admin(): void
{
    auth_require();
    if (!auth_is_admin()) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

function auth_require_admin_or_staff(): void
{
    auth_require();
    if (!auth_is_admin_or_staff()) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

function auth_login_attempt(string $username, string $password): bool
{
    if (!auth_tables_ready()) {
        return false;
    }

    $username = strtolower(trim($username));
    $password = (string)$password;
    if ($username === '' || $password === '') {
        return false;
    }

    try {
        $stmt = db()->prepare('
            SELECT u.*, r.name AS role_name
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            WHERE u.username = :username
            LIMIT 1
        ');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
    } catch (Throwable $e) {
        return false;
    }

    if (!$user) {
        return false;
    }
    if ((string)$user['status'] !== 'aktif') {
        return false;
    }
    if (!password_verify($password, (string)$user['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['auth'] = [
        'id' => (int)$user['id'],
        'username' => (string)$user['username'],
        'role' => (string)$user['role_name'],
        'must_change_password' => (int)($user['must_change_password'] ?? 0),
    ];
    return true;
}

function auth_logout(): void
{
    unset($_SESSION['auth']);
    session_regenerate_id(true);
}
