<?php

declare(strict_types=1);

function roomlists_by_paket(int $paketId): array
{
    if ($paketId <= 0) {
        return [];
    }
    $stmt = db()->prepare('SELECT * FROM roomlists WHERE paket_id = :paket_id ORDER BY id DESC');
    $stmt->execute(['paket_id' => $paketId]);
    return $stmt->fetchAll();
}

function roomlist_find(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM roomlists WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function roomlist_create(int $paketId, string $hotelNama): int
{
    if ($paketId <= 0) {
        throw new RuntimeException('Paket tidak valid.');
    }
    $hotelNama = trim($hotelNama);
    if ($hotelNama === '') {
        throw new RuntimeException('Nama hotel wajib diisi.');
    }
    $stmt = db()->prepare('INSERT INTO roomlists (paket_id, hotel_nama) VALUES (:paket_id, :hotel_nama)');
    $stmt->execute(['paket_id' => $paketId, 'hotel_nama' => $hotelNama]);
    return (int)db()->lastInsertId();
}

function roomlist_update(int $id, string $hotelNama): void
{
    if ($id <= 0) {
        throw new RuntimeException('ID tidak valid.');
    }
    $hotelNama = trim($hotelNama);
    if ($hotelNama === '') {
        throw new RuntimeException('Nama hotel wajib diisi.');
    }
    $stmt = db()->prepare('UPDATE roomlists SET hotel_nama = :hotel_nama, updated_at = CURRENT_TIMESTAMP WHERE id = :id LIMIT 1');
    $stmt->execute(['hotel_nama' => $hotelNama, 'id' => $id]);
}

function rooms_by_roomlist(int $roomlistId): array
{
    if ($roomlistId <= 0) {
        return [];
    }

    $roomsStmt = db()->prepare('SELECT * FROM rooms WHERE roomlist_id = :roomlist_id ORDER BY room_no ASC');
    $roomsStmt->execute(['roomlist_id' => $roomlistId]);
    $rooms = $roomsStmt->fetchAll();
    if (!$rooms) {
        return [];
    }

    $ids = array_map(fn($r) => (int)$r['id'], $rooms);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $memStmt = db()->prepare('
        SELECT
          rm.*,
          j.nama_lengkap,
          j.jenis_kelamin
        FROM room_members rm
        INNER JOIN jamaah j ON j.id = rm.jamaah_id
        WHERE rm.room_id IN (' . $placeholders . ')
        ORDER BY rm.room_id ASC, rm.position ASC
    ');
    $memStmt->execute($ids);
    $members = $memStmt->fetchAll();

    $byRoom = [];
    foreach ($members as $m) {
        $rid = (int)$m['room_id'];
        if (!isset($byRoom[$rid])) {
            $byRoom[$rid] = [];
        }
        $byRoom[$rid][] = $m;
    }

    foreach ($rooms as &$r) {
        $rid = (int)$r['id'];
        $r['members'] = $byRoom[$rid] ?? [];
    }
    unset($r);

    return $rooms;
}

function roomlist_assigned_jamaah_ids(int $roomlistId): array
{
    if ($roomlistId <= 0) {
        return [];
    }
    $stmt = db()->prepare('
        SELECT DISTINCT rm.jamaah_id
        FROM room_members rm
        INNER JOIN rooms r ON r.id = rm.room_id
        WHERE r.roomlist_id = :roomlist_id
    ');
    $stmt->execute(['roomlist_id' => $roomlistId]);
    $rows = $stmt->fetchAll();
    return array_map(fn($r) => (int)$r['jamaah_id'], $rows ?: []);
}

function roomlist_reset_rooms(int $roomlistId): void
{
    if ($roomlistId <= 0) {
        return;
    }
    db()->prepare('DELETE FROM rooms WHERE roomlist_id = :id')->execute(['id' => $roomlistId]);
}

function room_create(int $roomlistId, int $roomNo, string $roomType, string $roomGender, int $capacity): int
{
    $roomType = strtoupper(trim($roomType));
    $roomGender = strtolower(trim($roomGender));
    if (!in_array($roomType, ['QD', 'QT', 'TR', 'DB'], true)) {
        throw new RuntimeException('Tipe kamar tidak valid.');
    }
    if (!in_array($roomGender, ['male', 'female', 'mix'], true)) {
        throw new RuntimeException('Kategori kamar tidak valid.');
    }
    if ($capacity <= 0 || $capacity > 10) {
        throw new RuntimeException('Kapasitas tidak valid.');
    }
    $code = 'R' . str_pad((string)$roomNo, 3, '0', STR_PAD_LEFT) . '-' . $roomType;
    $stmt = db()->prepare('
        INSERT INTO rooms (roomlist_id, room_no, room_type, room_gender, capacity, room_code)
        VALUES (:roomlist_id, :room_no, :room_type, :room_gender, :capacity, :room_code)
    ');
    $stmt->execute([
        'roomlist_id' => $roomlistId,
        'room_no' => $roomNo,
        'room_type' => $roomType,
        'room_gender' => $roomGender,
        'capacity' => $capacity,
        'room_code' => $code,
    ]);
    return (int)db()->lastInsertId();
}

function room_update_key(int $roomId, string $nomorKunci): void
{
    if ($roomId <= 0) {
        throw new RuntimeException('ID kamar tidak valid.');
    }
    $nomorKunci = trim($nomorKunci);
    $nomorKunci = $nomorKunci !== '' ? $nomorKunci : null;
    $stmt = db()->prepare('UPDATE rooms SET nomor_kunci = :nomor_kunci, updated_at = CURRENT_TIMESTAMP WHERE id = :id LIMIT 1');
    $stmt->execute(['nomor_kunci' => $nomorKunci, 'id' => $roomId]);
}

function room_member_add(int $roomId, int $jamaahId): void
{
    if ($roomId <= 0 || $jamaahId <= 0) {
        throw new RuntimeException('Data tidak valid.');
    }
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $roomStmt = $pdo->prepare('SELECT id, roomlist_id, room_gender, capacity FROM rooms WHERE id = :id LIMIT 1 FOR UPDATE');
        $roomStmt->execute(['id' => $roomId]);
        $room = $roomStmt->fetch();
        if (!$room) {
            throw new RuntimeException('Kamar tidak ditemukan.');
        }

        $dupStmt = $pdo->prepare('
            SELECT COUNT(*) AS c
            FROM room_members rm
            INNER JOIN rooms r ON r.id = rm.room_id
            WHERE r.roomlist_id = :roomlist_id AND rm.jamaah_id = :jamaah_id
        ');
        $dupStmt->execute(['roomlist_id' => (int)$room['roomlist_id'], 'jamaah_id' => $jamaahId]);
        $dup = $dupStmt->fetch();
        if ($dup && (int)$dup['c'] > 0) {
            throw new RuntimeException('Jamaah sudah ditempatkan di kamar lain.');
        }

        $jStmt = $pdo->prepare('SELECT id, jenis_kelamin FROM jamaah WHERE id = :id LIMIT 1');
        $jStmt->execute(['id' => $jamaahId]);
        $j = $jStmt->fetch();
        if (!$j) {
            throw new RuntimeException('Jamaah tidak ditemukan.');
        }
        $jk = strtolower((string)$j['jenis_kelamin']);

        $roomGender = (string)$room['room_gender'];
        if ($roomGender === 'male' && !in_array($jk, ['laki-laki', 'laki laki', 'l'], true)) {
            throw new RuntimeException('Kamar ini khusus laki-laki.');
        }
        if ($roomGender === 'female' && !in_array($jk, ['perempuan', 'p'], true)) {
            throw new RuntimeException('Kamar ini khusus perempuan.');
        }

        $cap = (int)$room['capacity'];
        $posStmt = $pdo->prepare('SELECT position FROM room_members WHERE room_id = :room_id ORDER BY position ASC');
        $posStmt->execute(['room_id' => $roomId]);
        $used = array_map(fn($r) => (int)$r['position'], $posStmt->fetchAll() ?: []);
        $pos = 0;
        for ($i = 1; $i <= $cap; $i++) {
            if (!in_array($i, $used, true)) {
                $pos = $i;
                break;
            }
        }
        if ($pos === 0) {
            throw new RuntimeException('Kamar sudah penuh.');
        }

        $ins = $pdo->prepare('INSERT INTO room_members (room_id, jamaah_id, position) VALUES (:room_id, :jamaah_id, :position)');
        $ins->execute(['room_id' => $roomId, 'jamaah_id' => $jamaahId, 'position' => $pos]);

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function room_member_remove(int $memberId): int
{
    if ($memberId <= 0) {
        throw new RuntimeException('ID tidak valid.');
    }
    $pdo = db();
    $stmt = $pdo->prepare('SELECT room_id FROM room_members WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $memberId]);
    $row = $stmt->fetch();
    if (!$row) {
        throw new RuntimeException('Member tidak ditemukan.');
    }
    $roomId = (int)$row['room_id'];
    $pdo->prepare('DELETE FROM room_members WHERE id = :id LIMIT 1')->execute(['id' => $memberId]);
    return $roomId;
}

function roomlist_generate_template(int $roomlistId, int $paketId, int $maleCount, int $femaleCount): void
{
    if ($roomlistId <= 0 || $paketId <= 0) {
        throw new RuntimeException('Data tidak valid.');
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        roomlist_reset_rooms($roomlistId);

        $roomNo = 0;
        foreach ([
            ['gender' => 'male', 'count' => $maleCount],
            ['gender' => 'female', 'count' => $femaleCount],
        ] as $grp) {
            $gender = (string)$grp['gender'];
            $n = (int)$grp['count'];
            if ($n <= 0) {
                continue;
            }

            $qd = 0;
            $qt = 0;
            $found = false;
            $maxQt = (int)floor($n / 5);
            for ($i = 0; $i <= $maxQt; $i++) {
                $rem = $n - (5 * $i);
                if ($rem < 0) {
                    continue;
                }
                if ($rem % 4 === 0) {
                    $qt = $i;
                    $qd = (int)($rem / 4);
                    $found = true;
                    break;
                }
            }

            $roomPlan = [];
            if ($found) {
                for ($i = 0; $i < $qd; $i++) $roomPlan[] = ['type' => 'QD', 'cap' => 4];
                for ($i = 0; $i < $qt; $i++) $roomPlan[] = ['type' => 'QT', 'cap' => 5];
            } else {
                $left = $n;
                while ($left > 0) {
                    if ($left === 1) {
                        $roomPlan[] = ['type' => 'DB', 'cap' => 2];
                        $left = 0;
                        break;
                    }
                    if ($left === 2) {
                        $roomPlan[] = ['type' => 'DB', 'cap' => 2];
                        $left = 0;
                        break;
                    }
                    if ($left === 3) {
                        $roomPlan[] = ['type' => 'TR', 'cap' => 3];
                        $left = 0;
                        break;
                    }
                    if ($left === 5) {
                        $roomPlan[] = ['type' => 'QT', 'cap' => 5];
                        $left = 0;
                        break;
                    }
                    if ($left === 6) {
                        $roomPlan[] = ['type' => 'TR', 'cap' => 3];
                        $roomPlan[] = ['type' => 'TR', 'cap' => 3];
                        $left = 0;
                        break;
                    }
                    if ($left === 7) {
                        $roomPlan[] = ['type' => 'QD', 'cap' => 4];
                        $roomPlan[] = ['type' => 'TR', 'cap' => 3];
                        $left = 0;
                        break;
                    }
                    $roomPlan[] = ['type' => 'QD', 'cap' => 4];
                    $left -= 4;
                }
            }

            foreach ($roomPlan as $plan) {
                $roomNo++;
                room_create($roomlistId, $roomNo, (string)$plan['type'], $gender, (int)$plan['cap']);
            }
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

