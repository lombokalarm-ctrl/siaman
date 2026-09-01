<?php

declare(strict_types=1);

function jamaah_find(int $id): ?array
{
    $stmt = db()->prepare('
        SELECT
          j.*,
          p.nama AS paket_nama
        FROM jamaah j
        LEFT JOIN paket p ON p.id = j.paket_id
        WHERE j.id = :id
        LIMIT 1
    ');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function jamaah_search(string $q, int $limit = 50, ?int $paketId = null): array
{
    $q = trim($q);
    $paketId = $paketId !== null && $paketId > 0 ? (int)$paketId : null;
    if ($q === '') {
        if ($paketId !== null) {
            $stmt = db()->prepare('
                SELECT
                  j.id, j.id_jamaah, j.nomor_pendaftaran, j.nama_lengkap, j.nik, j.hp, j.email, j.status,
                  j.paket_id, p.nama AS paket_nama
                FROM jamaah j
                LEFT JOIN paket p ON p.id = j.paket_id
                WHERE j.paket_id = :paket_id
                ORDER BY j.id DESC
                LIMIT :limit
            ');
            $stmt->bindValue('paket_id', $paketId, PDO::PARAM_INT);
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }

        $stmt = db()->prepare('
            SELECT
              j.id, j.id_jamaah, j.nomor_pendaftaran, j.nama_lengkap, j.nik, j.hp, j.email, j.status,
              j.paket_id, p.nama AS paket_nama
            FROM jamaah j
            LEFT JOIN paket p ON p.id = j.paket_id
            ORDER BY j.id DESC
            LIMIT :limit
        ');
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    $like = '%' . $q . '%';
    $sql = '
        SELECT
          j.id, j.id_jamaah, j.nomor_pendaftaran, j.nama_lengkap, j.nik, j.hp, j.email, j.status,
          j.paket_id, p.nama AS paket_nama
        FROM jamaah j
        LEFT JOIN paket p ON p.id = j.paket_id
        WHERE
          (j.nama_lengkap LIKE :like OR
           j.id_jamaah LIKE :like OR
           j.nomor_pendaftaran LIKE :like OR
           j.nik LIKE :like OR
           j.hp LIKE :like)
    ';
    if ($paketId !== null) {
        $sql .= ' AND j.paket_id = :paket_id ';
    }
    $sql .= ' ORDER BY j.id DESC LIMIT :limit ';
    $stmt = db()->prepare($sql);
    $stmt->bindValue('like', $like, PDO::PARAM_STR);
    if ($paketId !== null) {
        $stmt->bindValue('paket_id', $paketId, PDO::PARAM_INT);
    }
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function jamaah_search_unassigned(string $q, int $limit = 500): array
{
    $q = trim($q);
    if ($q === '') {
        $stmt = db()->prepare('
            SELECT
              j.id, j.id_jamaah, j.nomor_pendaftaran, j.nama_lengkap, j.nik, j.hp, j.email, j.status,
              j.paket_id, p.nama AS paket_nama
            FROM jamaah j
            LEFT JOIN paket p ON p.id = j.paket_id
            WHERE j.paket_id IS NULL
            ORDER BY j.id DESC
            LIMIT :limit
        ');
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    $like = '%' . $q . '%';
    $stmt = db()->prepare('
        SELECT
          j.id, j.id_jamaah, j.nomor_pendaftaran, j.nama_lengkap, j.nik, j.hp, j.email, j.status,
          j.paket_id, p.nama AS paket_nama
        FROM jamaah j
        LEFT JOIN paket p ON p.id = j.paket_id
        WHERE j.paket_id IS NULL AND (
          j.nama_lengkap LIKE :like OR
          j.id_jamaah LIKE :like OR
          j.nomor_pendaftaran LIKE :like OR
          j.nik LIKE :like OR
          j.hp LIKE :like
        )
        ORDER BY j.id DESC
        LIMIT :limit
    ');
    $stmt->bindValue('like', $like, PDO::PARAM_STR);
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function jamaah_manifest_by_paket(int $paketId): array
{
    if ($paketId <= 0) {
        return [];
    }
    $stmt = db()->prepare('
        SELECT
          j.nama_lengkap,
          j.nama_bapak_kandung,
          j.jenis_kelamin,
          j.tanggal_lahir,
          j.nik,
          j.passport_no,
          j.passport_expire_date
        FROM jamaah j
        WHERE j.paket_id = :paket_id
        ORDER BY j.nama_lengkap ASC
    ');
    $stmt->execute(['paket_id' => $paketId]);
    return $stmt->fetchAll();
}

function jamaah_update(int $id, array $data): void
{
    $stmt = db()->prepare('
        UPDATE jamaah SET
          paket_id = :paket_id,
          nama_lengkap = :nama_lengkap,
          nama_bapak_kandung = :nama_bapak_kandung,
          nik = :nik,
          passport_no = :passport_no,
          passport_expire_date = :passport_expire_date,
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
        'paket_id' => (int)$data['paket_id'],
        'nama_lengkap' => (string)$data['nama_lengkap'],
        'nama_bapak_kandung' => (string)$data['nama_bapak_kandung'],
        'nik' => (string)$data['nik'],
        'passport_no' => $data['passport_no'] !== '' ? (string)$data['passport_no'] : null,
        'passport_expire_date' => $data['passport_expire_date'] !== '' ? (string)$data['passport_expire_date'] : null,
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
