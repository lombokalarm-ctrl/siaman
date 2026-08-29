<?php

declare(strict_types=1);

function bank_accounts_all(): array
{
    try {
        $stmt = db()->query('SELECT * FROM company_bank_accounts ORDER BY id ASC');
        return $stmt ? $stmt->fetchAll() : [];
    } catch (Throwable $e) {
        return [];
    }
}

function bank_account_create(array $data): int
{
    $bankNama = trim((string)($data['bank_nama'] ?? ''));
    $noRekening = trim((string)($data['no_rekening'] ?? ''));
    $namaRekening = trim((string)($data['nama_rekening'] ?? ''));

    if ($bankNama === '') throw new RuntimeException('Nama bank wajib diisi.');
    if ($noRekening === '') throw new RuntimeException('No rekening wajib diisi.');
    if ($namaRekening === '') throw new RuntimeException('Nama rekening wajib diisi.');

    $stmt = db()->prepare('
        INSERT INTO company_bank_accounts (bank_nama, no_rekening, nama_rekening)
        VALUES (:bank_nama, :no_rekening, :nama_rekening)
    ');
    $stmt->execute([
        'bank_nama' => $bankNama,
        'no_rekening' => $noRekening,
        'nama_rekening' => $namaRekening,
    ]);
    return (int)db()->lastInsertId();
}

function bank_account_delete(int $id): void
{
    if ($id <= 0) throw new RuntimeException('ID tidak valid.');
    $stmt = db()->prepare('DELETE FROM company_bank_accounts WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
}
