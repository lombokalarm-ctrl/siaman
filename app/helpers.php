<?php

declare(strict_types=1);

function h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function app_base_path(): string
{
    $script = (string)($_SERVER['SCRIPT_NAME'] ?? '');
    $dir = str_replace('\\', '/', dirname($script));
    $dir = rtrim($dir, '/');
    if ($dir === '' || $dir === '.' || $dir === '/') {
        return '';
    }
    return $dir;
}

function app_url(string $path = '/'): string
{
    $base = app_base_path();
    if ($path === '') {
        $path = '/';
    }
    if ($path[0] !== '/') {
        $path = '/' . $path;
    }
    return $base . $path;
}

function app_origin(): string
{
    $proto = strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
    $https = (string)($_SERVER['HTTPS'] ?? '');
    $isHttps = $proto === 'https' || ($https !== '' && $https !== 'off' && $https !== '0');
    $scheme = $isHttps ? 'https' : 'http';
    $host = (string)($_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost'));
    return $scheme . '://' . $host;
}

function app_absolute_url(string $path = '/'): string
{
    return app_origin() . app_url($path);
}

function redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}

function flash_set(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

function flash_get(string $key): ?string
{
    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }
    $value = (string)$_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $value;
}

function rupiah(float|string|null $value, bool $withPrefix = true): string
{
    if ($value === null || $value === '') {
        return $withPrefix ? 'Rp 0' : '0';
    }
    $n = is_string($value) ? (float)str_replace([',', ' '], ['', ''], $value) : (float)$value;
    $out = number_format($n, 0, ',', '.');
    return $withPrefix ? ('Rp ' . $out) : $out;
}
