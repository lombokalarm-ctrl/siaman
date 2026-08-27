<?php

declare(strict_types=1);

function settings_get(string $key, ?string $default = null): ?string
{
    $stmt = db()->prepare('SELECT `value` FROM settings WHERE `key` = :key LIMIT 1');
    $stmt->execute(['key' => $key]);
    $row = $stmt->fetch();
    if (!$row) {
        return $default;
    }
    return is_string($row['value']) ? $row['value'] : $default;
}

function settings_set(string $key, string $value): void
{
    $stmt = db()->prepare('INSERT INTO settings (`key`, `value`) VALUES (:key, :value) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), updated_at = CURRENT_TIMESTAMP');
    $stmt->execute([
        'key' => $key,
        'value' => $value,
    ]);
}

function settings_get_pendaftaran_format(): array
{
    return [
        'prefix' => settings_get('pendaftaran.prefix', 'REG'),
        'year_token' => settings_get('pendaftaran.year_token', '{YYYY}'),
        'separator' => settings_get('pendaftaran.separator', '-'),
        'seq_length' => (int)(settings_get('pendaftaran.seq_length', '5') ?: 5),
        'reset_policy' => settings_get('pendaftaran.reset_policy', 'yearly'),
    ];
}

function settings_get_invoice_format(): array
{
    return [
        'prefix' => settings_get('invoice.prefix', 'INV'),
        'year_token' => settings_get('invoice.year_token', '{YYYY}'),
        'separator' => settings_get('invoice.separator', '-'),
        'seq_length' => (int)(settings_get('invoice.seq_length', '5') ?: 5),
        'reset_policy' => settings_get('invoice.reset_policy', 'yearly'),
    ];
}

function settings_get_kuitansi_format(): array
{
    return [
        'prefix' => settings_get('kuitansi.prefix', 'RCT'),
        'year_token' => settings_get('kuitansi.year_token', '{YYYY}'),
        'separator' => settings_get('kuitansi.separator', '-'),
        'seq_length' => (int)(settings_get('kuitansi.seq_length', '5') ?: 5),
        'reset_policy' => settings_get('kuitansi.reset_policy', 'yearly'),
    ];
}

function settings_get_invoice_kop(): array
{
    return [
        'nama' => settings_get('kop.nama', 'Siaman'),
        'alamat' => settings_get('kop.alamat', ''),
        'kontak' => settings_get('kop.kontak', ''),
        'kota' => settings_get('kop.kota', ''),
        'penanggung_jawab' => settings_get('kop.penanggung_jawab', ''),
        'logo_path' => settings_get('kop.logo_path', ''),
    ];
}
