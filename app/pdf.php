<?php

declare(strict_types=1);

function dompdf_available(): bool
{
    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (!is_file($autoload)) {
        return false;
    }
    require_once $autoload;
    return class_exists('Dompdf\\Dompdf');
}

function image_data_uri_from_public_path(string $publicPath): ?string
{
    $publicPath = trim($publicPath);
    if ($publicPath === '' || $publicPath[0] !== '/') {
        return null;
    }
    $publicRoot = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public';
    $fsPath = $publicRoot . str_replace('/', DIRECTORY_SEPARATOR, $publicPath);
    if (!is_file($fsPath)) {
        return null;
    }

    $ext = strtolower((string)pathinfo($fsPath, PATHINFO_EXTENSION));
    $mime = null;
    if ($ext === 'png') $mime = 'image/png';
    if ($ext === 'jpg' || $ext === 'jpeg') $mime = 'image/jpeg';
    if ($mime === null) {
        return null;
    }
    $bytes = file_get_contents($fsPath);
    if ($bytes === false) {
        return null;
    }
    return 'data:' . $mime . ';base64,' . base64_encode($bytes);
}

function pdf_stream(string $html, string $filename, string $paper = 'A4', string $orientation = 'portrait'): never
{
    if (!dompdf_available()) {
        http_response_code(500);
        echo 'dompdf belum terpasang. Jalankan composer install.';
        exit;
    }

    $options = new Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);

    $dompdf = new Dompdf\Dompdf($options);
    $dompdf->setPaper($paper, $orientation);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->render();
    $dompdf->stream($filename, ['Attachment' => true]);
    exit;
}

