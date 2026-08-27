<?php

declare(strict_types=1);

function users_all(): array
{
    $stmt = db()->query('
        SELECT u.*, r.name AS role_name
        FROM users u
        INNER JOIN roles r ON r.id = u.role_id
        ORDER BY u.id DESC
    ');
    return $stmt ? $stmt->fetchAll() : [];
}

function user_find(int $id): ?array
{
    $stmt = db()->prepare('
        SELECT u.*, r.name AS role_name
        FROM users u
        INNER JOIN roles r ON r.id = u.role_id
        WHERE u.id = :id
        LIMIT 1
    ');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function user_find_by_username(string $username): ?array
{
    $stmt = db()->prepare('
        SELECT u.*, r.name AS role_name
        FROM users u
        INNER JOIN roles r ON r.id = u.role_id
        WHERE u.username = :username
        LIMIT 1
    ');
    $stmt->execute(['username' => $username]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function user_create(array $data): int
{
    $username = strtolower(trim((string)($data['username'] ?? '')));
    $password = (string)($data['password'] ?? '');
    $roleId = (int)($data['role_id'] ?? 0);
    $status = (string)($data['status'] ?? 'aktif');

    if ($username === '') {
        throw new RuntimeException('Username wajib diisi.');
    }
    if ($password === '') {
        throw new RuntimeException('Password wajib diisi.');
    }
    if ($roleId <= 0) {
        throw new RuntimeException('Role wajib dipilih.');
    }
    if (!in_array($status, ['aktif', 'nonaktif'], true)) {
        throw new RuntimeException('Status tidak valid.');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    if (!is_string($hash) || $hash === '') {
        throw new RuntimeException('Gagal membuat password hash.');
    }

    $stmt = db()->prepare('
        INSERT INTO users (username, password_hash, role_id, status, must_change_password)
        VALUES (:username, :password_hash, :role_id, :status, :must_change_password)
    ');
    $stmt->execute([
        'username' => $username,
        'password_hash' => $hash,
        'role_id' => $roleId,
        'status' => $status,
        'must_change_password' => (int)($data['must_change_password'] ?? 0),
    ]);
    return (int)db()->lastInsertId();
}

function user_update(int $id, array $data): void
{
    $username = strtolower(trim((string)($data['username'] ?? '')));
    $roleId = (int)($data['role_id'] ?? 0);
    $status = (string)($data['status'] ?? 'aktif');
    $mustChange = (int)($data['must_change_password'] ?? 0);

    if ($id <= 0) {
        throw new RuntimeException('ID tidak valid.');
    }
    if ($username === '') {
        throw new RuntimeException('Username wajib diisi.');
    }
    if ($roleId <= 0) {
        throw new RuntimeException('Role wajib dipilih.');
    }
    if (!in_array($status, ['aktif', 'nonaktif'], true)) {
        throw new RuntimeException('Status tidak valid.');
    }

    $stmt = db()->prepare('
        UPDATE users
        SET username = :username, role_id = :role_id, status = :status, must_change_password = :must_change_password
        WHERE id = :id
        LIMIT 1
    ');
    $stmt->execute([
        'id' => $id,
        'username' => $username,
        'role_id' => $roleId,
        'status' => $status,
        'must_change_password' => $mustChange,
    ]);
}

function user_set_password(int $id, string $password): void
{
    $password = (string)$password;
    if ($id <= 0) {
        throw new RuntimeException('ID tidak valid.');
    }
    if ($password === '') {
        throw new RuntimeException('Password wajib diisi.');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    if (!is_string($hash) || $hash === '') {
        throw new RuntimeException('Gagal membuat password hash.');
    }

    $stmt = db()->prepare('UPDATE users SET password_hash = :password_hash, must_change_password = 0 WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id, 'password_hash' => $hash]);
}
