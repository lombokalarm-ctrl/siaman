<?php

declare(strict_types=1);

function paket_find(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM paket WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function paket_search(string $q, int $limit = 50): array
{
    $q = trim($q);
    if ($q === '') {
        $stmt = db()->query('SELECT id, kode, nama, durasi_hari, tanggal_berangkat, harga, currency, status FROM paket ORDER BY id DESC LIMIT 50');
        return $stmt->fetchAll();
    }

    $like = '%' . $q . '%';
    $stmt = db()->prepare('
        SELECT id, kode, nama, durasi_hari, tanggal_berangkat, harga, currency, status
        FROM paket
        WHERE nama LIKE :like OR kode LIKE :like
        ORDER BY id DESC
        LIMIT :limit
    ');
    $stmt->bindValue('like', $like, PDO::PARAM_STR);
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function paket_create(array $data): int
{
    $stmt = db()->prepare('
        INSERT INTO paket (kode, nama, durasi_hari, tanggal_berangkat, harga, currency, deskripsi, status)
        VALUES (:kode, :nama, :durasi_hari, :tanggal_berangkat, :harga, :currency, :deskripsi, :status)
    ');
    $stmt->execute([
        'kode' => $data['kode'] !== '' ? (string)$data['kode'] : null,
        'nama' => (string)$data['nama'],
        'durasi_hari' => (int)$data['durasi_hari'],
        'tanggal_berangkat' => $data['tanggal_berangkat'] !== '' ? (string)$data['tanggal_berangkat'] : null,
        'harga' => (string)$data['harga'],
        'currency' => (string)$data['currency'],
        'deskripsi' => $data['deskripsi'] !== '' ? (string)$data['deskripsi'] : null,
        'status' => (string)$data['status'],
    ]);
    return (int)db()->lastInsertId();
}

function paket_update(int $id, array $data): void
{
    $stmt = db()->prepare('
        UPDATE paket SET
          kode = :kode,
          nama = :nama,
          durasi_hari = :durasi_hari,
          tanggal_berangkat = :tanggal_berangkat,
          harga = :harga,
          currency = :currency,
          deskripsi = :deskripsi,
          status = :status
        WHERE id = :id
        LIMIT 1
    ');
    $stmt->execute([
        'id' => $id,
        'kode' => $data['kode'] !== '' ? (string)$data['kode'] : null,
        'nama' => (string)$data['nama'],
        'durasi_hari' => (int)$data['durasi_hari'],
        'tanggal_berangkat' => $data['tanggal_berangkat'] !== '' ? (string)$data['tanggal_berangkat'] : null,
        'harga' => (string)$data['harga'],
        'currency' => (string)$data['currency'],
        'deskripsi' => $data['deskripsi'] !== '' ? (string)$data['deskripsi'] : null,
        'status' => (string)$data['status'],
    ]);
}

