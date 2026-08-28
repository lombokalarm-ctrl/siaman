<?php

declare(strict_types=1);

function client_find(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM clients WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function client_search(string $q, int $limit = 50): array
{
    $q = trim($q);
    if ($q === '') {
        $stmt = db()->prepare('
            SELECT id, nama_perusahaan, nama_pic, no_tlp, email, status
            FROM clients
            ORDER BY id DESC
            LIMIT :limit
        ');
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    $like = '%' . $q . '%';
    $stmt = db()->prepare('
        SELECT id, nama_perusahaan, nama_pic, no_tlp, email, status
        FROM clients
        WHERE
          nama_perusahaan LIKE :like OR
          nama_pic LIKE :like OR
          no_tlp LIKE :like OR
          email LIKE :like
        ORDER BY id DESC
        LIMIT :limit
    ');
    $stmt->bindValue('like', $like, PDO::PARAM_STR);
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function client_create(array $data): int
{
    $namaPerusahaan = trim((string)($data['nama_perusahaan'] ?? ''));
    $namaPic = trim((string)($data['nama_pic'] ?? ''));
    $alamat = trim((string)($data['alamat'] ?? ''));
    $noTlp = trim((string)($data['no_tlp'] ?? ''));
    $email = trim((string)($data['email'] ?? ''));
    $status = trim((string)($data['status'] ?? 'aktif'));

    if ($namaPerusahaan === '') throw new RuntimeException('Nama perusahaan wajib diisi.');
    if ($namaPic === '') throw new RuntimeException('Nama PIC wajib diisi.');
    if ($alamat === '') throw new RuntimeException('Alamat wajib diisi.');
    if ($noTlp === '') throw new RuntimeException('No Tlp wajib diisi.');
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Email tidak valid.');
    if (!in_array($status, ['aktif', 'nonaktif'], true)) throw new RuntimeException('Status tidak valid.');

    $stmt = db()->prepare('
        INSERT INTO clients (nama_perusahaan, nama_pic, alamat, no_tlp, email, status)
        VALUES (:nama_perusahaan, :nama_pic, :alamat, :no_tlp, :email, :status)
    ');
    $stmt->execute([
        'nama_perusahaan' => $namaPerusahaan,
        'nama_pic' => $namaPic,
        'alamat' => $alamat,
        'no_tlp' => $noTlp,
        'email' => $email,
        'status' => $status,
    ]);
    return (int)db()->lastInsertId();
}

function client_update(int $id, array $data): void
{
    $namaPerusahaan = trim((string)($data['nama_perusahaan'] ?? ''));
    $namaPic = trim((string)($data['nama_pic'] ?? ''));
    $alamat = trim((string)($data['alamat'] ?? ''));
    $noTlp = trim((string)($data['no_tlp'] ?? ''));
    $email = trim((string)($data['email'] ?? ''));
    $status = trim((string)($data['status'] ?? 'aktif'));

    if ($id <= 0) throw new RuntimeException('ID tidak valid.');
    if ($namaPerusahaan === '') throw new RuntimeException('Nama perusahaan wajib diisi.');
    if ($namaPic === '') throw new RuntimeException('Nama PIC wajib diisi.');
    if ($alamat === '') throw new RuntimeException('Alamat wajib diisi.');
    if ($noTlp === '') throw new RuntimeException('No Tlp wajib diisi.');
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Email tidak valid.');
    if (!in_array($status, ['aktif', 'nonaktif'], true)) throw new RuntimeException('Status tidak valid.');

    $stmt = db()->prepare('
        UPDATE clients
        SET
          nama_perusahaan = :nama_perusahaan,
          nama_pic = :nama_pic,
          alamat = :alamat,
          no_tlp = :no_tlp,
          email = :email,
          status = :status
        WHERE id = :id
        LIMIT 1
    ');
    $stmt->execute([
        'id' => $id,
        'nama_perusahaan' => $namaPerusahaan,
        'nama_pic' => $namaPic,
        'alamat' => $alamat,
        'no_tlp' => $noTlp,
        'email' => $email,
        'status' => $status,
    ]);
}
