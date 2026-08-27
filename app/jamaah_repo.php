<?php

declare(strict_types=1);

function jamaah_find(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM jamaah WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function jamaah_search(string $q, int $limit = 50): array
{
    $q = trim($q);
    if ($q === '') {
        $stmt = db()->query('SELECT id, id_jamaah, nomor_pendaftaran, nama_lengkap, nik, hp, email, status FROM jamaah ORDER BY id DESC LIMIT 50');
        return $stmt->fetchAll();
    }

    $like = '%' . $q . '%';
    $stmt = db()->prepare('
        SELECT id, id_jamaah, nomor_pendaftaran, nama_lengkap, nik, hp, email, status
        FROM jamaah
        WHERE
          nama_lengkap LIKE :like OR
          id_jamaah LIKE :like OR
          nomor_pendaftaran LIKE :like OR
          nik LIKE :like OR
          hp LIKE :like
        ORDER BY id DESC
        LIMIT :limit
    ');
    $stmt->bindValue('like', $like, PDO::PARAM_STR);
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function jamaah_update(int $id, array $data): void
{
    $stmt = db()->prepare('
        UPDATE jamaah SET
          nama_lengkap = :nama_lengkap,
          nama_bapak_kandung = :nama_bapak_kandung,
          nik = :nik,
          nomor_kk = :nomor_kk,
          tempat_lahir = :tempat_lahir,
          tanggal_lahir = :tanggal_lahir,
          jenis_kelamin = :jenis_kelamin,
          status_pernikahan = :status_pernikahan,
          pendidikan = :pendidikan,
          pekerjaan = :pekerjaan,
          alamat_lengkap = :alamat_lengkap,
          hp = :hp,
          email = :email,
          status = :status
        WHERE id = :id
        LIMIT 1
    ');
    $stmt->execute([
        'id' => $id,
        'nama_lengkap' => (string)$data['nama_lengkap'],
        'nama_bapak_kandung' => (string)$data['nama_bapak_kandung'],
        'nik' => (string)$data['nik'],
        'nomor_kk' => (string)$data['nomor_kk'],
        'tempat_lahir' => (string)$data['tempat_lahir'],
        'tanggal_lahir' => (string)$data['tanggal_lahir'],
        'jenis_kelamin' => (string)$data['jenis_kelamin'],
        'status_pernikahan' => (string)$data['status_pernikahan'],
        'pendidikan' => (string)$data['pendidikan'],
        'pekerjaan' => (string)$data['pekerjaan'],
        'alamat_lengkap' => (string)$data['alamat_lengkap'],
        'hp' => (string)$data['hp'],
        'email' => $data['email'] !== '' ? (string)$data['email'] : null,
        'status' => (string)$data['status'],
    ]);
}
