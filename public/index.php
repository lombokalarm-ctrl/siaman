<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/../app/helpers.php';
require __DIR__ . '/../app/db.php';
require __DIR__ . '/../app/csrf.php';
require __DIR__ . '/../app/auth.php';
require __DIR__ . '/../app/settings_repo.php';
require __DIR__ . '/../app/numbering.php';
require __DIR__ . '/../app/terbilang.php';
require __DIR__ . '/../app/pdf.php';
require __DIR__ . '/../app/bank_account_repo.php';
require __DIR__ . '/../app/jamaah_repo.php';
require __DIR__ . '/../app/client_repo.php';
require __DIR__ . '/../app/paket_repo.php';
require __DIR__ . '/../app/invoice_repo.php';
require __DIR__ . '/../app/role_repo.php';
require __DIR__ . '/../app/user_repo.php';

$pages = [
    'dashboard' => ['title' => 'Dashboard', 'view' => __DIR__ . '/views/pages/dashboard.php'],
    'jamaah' => ['title' => 'Jamaah', 'view' => __DIR__ . '/views/pages/jamaah_list.php'],
    'jamaah_import' => ['title' => 'Import Jamaah', 'view' => __DIR__ . '/views/pages/jamaah_import.php'],
    'login' => ['title' => 'Login', 'view' => __DIR__ . '/views/pages/login.php'],
    'clients' => ['title' => 'Klien', 'view' => __DIR__ . '/views/pages/client_list.php'],
    'client_create' => ['title' => 'Tambah Klien', 'view' => __DIR__ . '/views/pages/client_form.php'],
    'client_edit' => ['title' => 'Edit Klien', 'view' => __DIR__ . '/views/pages/client_edit.php'],
    'users' => ['title' => 'Users', 'view' => __DIR__ . '/views/pages/users.php'],
    'user_create' => ['title' => 'Tambah User', 'view' => __DIR__ . '/views/pages/user_form.php'],
    'user_edit' => ['title' => 'Edit User', 'view' => __DIR__ . '/views/pages/user_edit.php'],
    'jamaah_detail' => ['title' => 'Detail Jamaah', 'view' => __DIR__ . '/views/pages/jamaah_detail.php'],
    'jamaah_edit' => ['title' => 'Edit Jamaah', 'view' => __DIR__ . '/views/pages/jamaah_edit.php'],
    'paket' => ['title' => 'Paket Umroh', 'view' => __DIR__ . '/views/pages/paket_list.php'],
    'paket_create' => ['title' => 'Tambah Paket', 'view' => __DIR__ . '/views/pages/paket_form.php'],
    'paket_edit' => ['title' => 'Edit Paket', 'view' => __DIR__ . '/views/pages/paket_edit.php'],
    'invoice' => ['title' => 'Invoice', 'view' => __DIR__ . '/views/pages/invoice_list.php'],
    'invoice_detail' => ['title' => 'Detail Invoice', 'view' => __DIR__ . '/views/pages/invoice_detail.php'],
    'invoice_create' => ['title' => 'Buat Invoice', 'view' => __DIR__ . '/views/pages/invoice_form.php'],
    'invoice_print' => ['title' => 'Invoice', 'view' => __DIR__ . '/views/pages/invoice_print.php'],
    'invoice_pdf' => ['title' => 'Invoice PDF', 'view' => __DIR__ . '/views/pages/invoice_pdf.php'],
    'kuitansi' => ['title' => 'Kuitansi', 'view' => __DIR__ . '/views/pages/kuitansi.php'],
    'kuitansi_detail' => ['title' => 'Kuitansi', 'view' => __DIR__ . '/views/pages/kuitansi_detail.php'],
    'kuitansi_print' => ['title' => 'Kuitansi', 'view' => __DIR__ . '/views/pages/kuitansi_print.php'],
    'kuitansi_pdf' => ['title' => 'Kuitansi PDF', 'view' => __DIR__ . '/views/pages/kuitansi_pdf.php'],
    'payment_void' => ['title' => 'Void Pembayaran', 'view' => __DIR__ . '/views/pages/payment_void.php'],
    'rekap_pembayaran' => ['title' => 'Rekap Pembayaran', 'view' => __DIR__ . '/views/pages/rekap_pembayaran.php'],
    'rekap_piutang' => ['title' => 'Piutang', 'view' => __DIR__ . '/views/pages/rekap_piutang.php'],
    'settings' => ['title' => 'Pengaturan', 'view' => __DIR__ . '/views/pages/settings.php'],
    'jamaah_create' => ['title' => 'Tambah Jamaah', 'view' => __DIR__ . '/views/pages/jamaah_form.php'],
];

$pageKey = (string)($_GET['page'] ?? 'dashboard');
if (!isset($pages[$pageKey])) {
    $pageKey = 'dashboard';
}

$action = (string)($_POST['_action'] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action !== '') {
    auth_bootstrap();
    if ($action !== 'auth.login') {
        auth_require();
    }
    if (!auth_can_do_action($action)) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
    require __DIR__ . '/routes/post.php';
}

$isLoginPage = ($pageKey === 'login');
auth_bootstrap();
if (!$isLoginPage) {
    auth_require();
}
if (!auth_can_access_page($pageKey)) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

$printPages = ['invoice_print' => true, 'kuitansi_print' => true, 'invoice_pdf' => true, 'kuitansi_pdf' => true];
if (isset($printPages[$pageKey])) {
    require $pages[$pageKey]['view'];
    exit;
}

if ($isLoginPage) {
    if (auth_is_logged_in()) {
        redirect(app_url('/?page=dashboard'));
    }
    require $pages[$pageKey]['view'];
    exit;
}

$title = $pages[$pageKey]['title'];
$contentView = $pages[$pageKey]['view'];

require __DIR__ . '/views/layout.php';
