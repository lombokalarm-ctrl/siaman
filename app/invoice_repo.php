<?php

declare(strict_types=1);

function payments_void_supported(): bool
{
    static $cached = null;
    if (is_bool($cached)) {
        return $cached;
    }
    try {
        $stmt = db()->query("
            SELECT COUNT(*) AS c
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'payments'
              AND COLUMN_NAME = 'voided_at'
        ");
        $row = $stmt ? $stmt->fetch() : null;
        $cached = $row ? ((int)$row['c'] > 0) : false;
        return $cached;
    } catch (Throwable $e) {
        $cached = false;
        return false;
    }
}

function invoice_search(string $q, int $limit = 50): array
{
    $q = trim($q);
    $paidExpr = payments_void_supported()
        ? '(SELECT COALESCE(SUM(py.amount), 0) FROM payments py WHERE py.invoice_id = i.id AND py.voided_at IS NULL)'
        : '(SELECT COALESCE(SUM(py.amount), 0) FROM payments py WHERE py.invoice_id = i.id)';
    if ($q === '') {
        $stmt = db()->prepare('
            SELECT
              i.id, i.nomor, i.tanggal, i.status, i.grand_total,
              CASE WHEN i.client_id IS NULL THEN "jamaah" ELSE "client" END AS target_type,
              COALESCE(j.nama_lengkap, c.nama_perusahaan) AS target_nama,
              p.nama AS paket_nama,
              ' . $paidExpr . ' AS paid_total
            FROM invoices i
            LEFT JOIN jamaah j ON j.id = i.jamaah_id
            LEFT JOIN clients c ON c.id = i.client_id
            LEFT JOIN paket p ON p.id = i.paket_id
            ORDER BY i.id DESC
            LIMIT :limit
        ');
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    $like = '%' . $q . '%';
    $stmt = db()->prepare('
        SELECT
          i.id, i.nomor, i.tanggal, i.status, i.grand_total,
          CASE WHEN i.client_id IS NULL THEN "jamaah" ELSE "client" END AS target_type,
          COALESCE(j.nama_lengkap, c.nama_perusahaan) AS target_nama,
          p.nama AS paket_nama,
          ' . $paidExpr . ' AS paid_total
        FROM invoices i
        LEFT JOIN jamaah j ON j.id = i.jamaah_id
        LEFT JOIN clients c ON c.id = i.client_id
        LEFT JOIN paket p ON p.id = i.paket_id
        WHERE
          i.nomor LIKE :like OR
          j.nama_lengkap LIKE :like OR
          j.id_jamaah LIKE :like OR
          j.nomor_pendaftaran LIKE :like OR
          c.nama_perusahaan LIKE :like OR
          c.nama_pic LIKE :like OR
          c.no_tlp LIKE :like OR
          c.email LIKE :like
        ORDER BY i.id DESC
        LIMIT :limit
    ');
    $stmt->bindValue('like', $like, PDO::PARAM_STR);
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function invoice_find(int $id): ?array
{
    $stmt = db()->prepare('
        SELECT
          i.*,
          CASE WHEN i.client_id IS NULL THEN "jamaah" ELSE "client" END AS target_type,
          COALESCE(j.nama_lengkap, c.nama_perusahaan) AS target_nama,
          j.nama_lengkap AS jamaah_nama,
          j.id_jamaah AS jamaah_kode,
          j.nomor_pendaftaran AS jamaah_daftar,
          j.hp AS jamaah_hp,
          c.nama_perusahaan AS client_perusahaan,
          c.nama_pic AS client_pic,
          c.no_tlp AS client_tlp,
          c.email AS client_email,
          c.alamat AS client_alamat,
          p.nama AS paket_nama
        FROM invoices i
        LEFT JOIN jamaah j ON j.id = i.jamaah_id
        LEFT JOIN clients c ON c.id = i.client_id
        LEFT JOIN paket p ON p.id = i.paket_id
        WHERE i.id = :id
        LIMIT 1
    ');
    $stmt->execute(['id' => $id]);
    $inv = $stmt->fetch();
    if (!$inv) {
        return null;
    }

    $itemsStmt = db()->prepare('SELECT * FROM invoice_items WHERE invoice_id = :id ORDER BY id ASC');
    $itemsStmt->execute(['id' => $id]);
    $items = $itemsStmt->fetchAll();

    $payStmt = db()->prepare('SELECT * FROM payments WHERE invoice_id = :id ORDER BY tanggal ASC, id ASC');
    $payStmt->execute(['id' => $id]);
    $payments = $payStmt->fetchAll();

    $paidTotal = 0.0;
    $voidSupported = payments_void_supported();
    foreach ($payments as $p) {
        if ($voidSupported && isset($p['voided_at']) && $p['voided_at']) {
            continue;
        }
        $paidTotal += (float)$p['amount'];
    }
    $grandTotal = (float)$inv['grand_total'];

    $inv['items'] = $items;
    $inv['payments'] = $payments;
    $inv['paid_total'] = $paidTotal;
    $inv['remaining_total'] = max(0, $grandTotal - $paidTotal);

    return $inv;
}

function invoice_create(array $data): int
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $now = new DateTimeImmutable('now');
        $nomor = generate_nomor_invoice($now);

        $jamaahId = (int)($data['jamaah_id'] ?? 0);
        $clientId = (int)($data['client_id'] ?? 0);
        $paketId = $data['paket_id'] ? (int)$data['paket_id'] : null;
        $tanggal = (string)$data['tanggal'];
        $notes = $data['notes'] !== '' ? (string)$data['notes'] : null;

        if (($jamaahId > 0 && $clientId > 0) || ($jamaahId <= 0 && $clientId <= 0)) {
            throw new RuntimeException('Target invoice tidak valid.');
        }
        if ($clientId > 0) {
            $paketId = null;
        }

        $items = $data['items'];
        $subtotal = 0.0;
        foreach ($items as $it) {
            $subtotal += (float)$it['total'];
        }

        $diskon = (float)$data['diskon'];
        $pajak = (float)$data['pajak'];
        $grandTotal = max(0, $subtotal - $diskon + $pajak);

        $stmt = $pdo->prepare('
            INSERT INTO invoices (nomor, jamaah_id, client_id, paket_id, tanggal, subtotal, diskon, pajak, grand_total, status, notes)
            VALUES (:nomor, :jamaah_id, :client_id, :paket_id, :tanggal, :subtotal, :diskon, :pajak, :grand_total, :status, :notes)
        ');
        $stmt->execute([
            'nomor' => $nomor,
            'jamaah_id' => $jamaahId > 0 ? $jamaahId : null,
            'client_id' => $clientId > 0 ? $clientId : null,
            'paket_id' => $paketId,
            'tanggal' => $tanggal,
            'subtotal' => $subtotal,
            'diskon' => $diskon,
            'pajak' => $pajak,
            'grand_total' => $grandTotal,
            'status' => 'unpaid',
            'notes' => $notes,
        ]);

        $invoiceId = (int)$pdo->lastInsertId();

        $itemStmt = $pdo->prepare('
            INSERT INTO invoice_items (invoice_id, label, qty, price, total)
            VALUES (:invoice_id, :label, :qty, :price, :total)
        ');
        foreach ($items as $it) {
            $itemStmt->execute([
                'invoice_id' => $invoiceId,
                'label' => (string)$it['label'],
                'qty' => (float)$it['qty'],
                'price' => (float)$it['price'],
                'total' => (float)$it['total'],
            ]);
        }

        $pdo->commit();
        return $invoiceId;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function invoice_recalc_status(int $invoiceId): void
{
    $pdo = db();

    $stmt = $pdo->prepare('SELECT grand_total, status FROM invoices WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $invoiceId]);
    $inv = $stmt->fetch();
    if (!$inv) {
        return;
    }

    if ((string)$inv['status'] === 'void') {
        return;
    }

    $grandTotal = (float)$inv['grand_total'];

    if (payments_void_supported()) {
        $sumStmt = $pdo->prepare('SELECT COALESCE(SUM(amount), 0) AS paid_total FROM payments WHERE invoice_id = :id AND voided_at IS NULL');
    } else {
        $sumStmt = $pdo->prepare('SELECT COALESCE(SUM(amount), 0) AS paid_total FROM payments WHERE invoice_id = :id');
    }
    $sumStmt->execute(['id' => $invoiceId]);
    $row = $sumStmt->fetch();
    $paidTotal = $row ? (float)$row['paid_total'] : 0.0;

    $newStatus = 'unpaid';
    if ($paidTotal <= 0) {
        $newStatus = 'unpaid';
    } elseif ($paidTotal + 0.00001 < $grandTotal) {
        $newStatus = 'partial';
    } else {
        $newStatus = 'paid';
    }

    $upd = $pdo->prepare('UPDATE invoices SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
    $upd->execute(['status' => $newStatus, 'id' => $invoiceId]);
}

function payment_create(int $invoiceId, array $data): int
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $now = new DateTimeImmutable('now');
        $nomor = generate_nomor_kuitansi($now);

        $stmt = $pdo->prepare('
            INSERT INTO payments (
              invoice_id, nomor_kuitansi, tanggal, amount, metode, reference, pengirim, outlet, sales, created_by
            ) VALUES (
              :invoice_id, :nomor_kuitansi, :tanggal, :amount, :metode, :reference, :pengirim, :outlet, :sales, :created_by
            )
        ');
        $stmt->execute([
            'invoice_id' => $invoiceId,
            'nomor_kuitansi' => $nomor,
            'tanggal' => (string)$data['tanggal'],
            'amount' => (string)$data['amount'],
            'metode' => (string)$data['metode'],
            'reference' => $data['reference'] !== '' ? (string)$data['reference'] : null,
            'pengirim' => $data['pengirim'] !== '' ? (string)$data['pengirim'] : null,
            'outlet' => $data['outlet'] !== '' ? (string)$data['outlet'] : null,
            'sales' => $data['sales'] !== '' ? (string)$data['sales'] : null,
            'created_by' => null,
        ]);

        $paymentId = (int)$pdo->lastInsertId();

        invoice_recalc_status($invoiceId);

        $pdo->commit();
        return $paymentId;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function payment_find(int $id): ?array
{
    $stmt = db()->prepare('
        SELECT
          py.*,
          i.nomor AS invoice_nomor,
          i.grand_total AS invoice_total,
          CASE WHEN i.client_id IS NULL THEN "jamaah" ELSE "client" END AS target_type,
          COALESCE(j.nama_lengkap, c.nama_perusahaan) AS target_nama,
          j.nama_lengkap AS jamaah_nama,
          j.hp AS jamaah_hp,
          c.nama_perusahaan AS client_perusahaan,
          c.nama_pic AS client_pic,
          c.no_tlp AS client_tlp,
          c.email AS client_email,
          c.alamat AS client_alamat,
          p.nama AS paket_nama
        FROM payments py
        INNER JOIN invoices i ON i.id = py.invoice_id
        LEFT JOIN jamaah j ON j.id = i.jamaah_id
        LEFT JOIN clients c ON c.id = i.client_id
        LEFT JOIN paket p ON p.id = i.paket_id
        WHERE py.id = :id
        LIMIT 1
    ');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function payment_void(int $paymentId, string $reason): ?int
{
    if (!payments_void_supported()) {
        return null;
    }
    $reason = trim($reason);
    if ($reason === '') {
        $reason = 'void';
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT id, invoice_id, voided_at FROM payments WHERE id = :id LIMIT 1 FOR UPDATE');
        $stmt->execute(['id' => $paymentId]);
        $row = $stmt->fetch();
        if (!$row) {
            $pdo->rollBack();
            return null;
        }

        $invoiceId = (int)$row['invoice_id'];
        if ($row['voided_at']) {
            $pdo->commit();
            return $invoiceId;
        }

        $upd = $pdo->prepare('
            UPDATE payments
            SET voided_at = CURRENT_TIMESTAMP, void_reason = :reason
            WHERE id = :id
        ');
        $upd->execute(['reason' => $reason, 'id' => $paymentId]);

        invoice_recalc_status($invoiceId);

        $pdo->commit();
        return $invoiceId;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function invoices_by_jamaah(int $jamaahId): array
{
    $paidExpr = payments_void_supported()
        ? '(SELECT COALESCE(SUM(py.amount), 0) FROM payments py WHERE py.invoice_id = i.id AND py.voided_at IS NULL)'
        : '(SELECT COALESCE(SUM(py.amount), 0) FROM payments py WHERE py.invoice_id = i.id)';
    $stmt = db()->prepare('
        SELECT
          i.id, i.nomor, i.status, i.grand_total, i.tanggal,
          ' . $paidExpr . ' AS paid_total
        FROM invoices i
        WHERE i.jamaah_id = :jamaah_id
        ORDER BY i.id DESC
        LIMIT 50
    ');
    $stmt->execute(['jamaah_id' => $jamaahId]);
    return $stmt->fetchAll();
}

function payments_search(string $q, int $limit = 50): array
{
    $q = trim($q);
    $selectVoid = payments_void_supported() ? ', py.voided_at' : '';
    if ($q === '') {
        $stmt = db()->prepare('
            SELECT
              py.id, py.nomor_kuitansi, py.tanggal, py.amount, py.metode' . $selectVoid . ',
              i.nomor AS invoice_nomor,
              CASE WHEN i.client_id IS NULL THEN "jamaah" ELSE "client" END AS target_type,
              COALESCE(j.nama_lengkap, c.nama_perusahaan) AS target_nama
            FROM payments py
            INNER JOIN invoices i ON i.id = py.invoice_id
            LEFT JOIN jamaah j ON j.id = i.jamaah_id
            LEFT JOIN clients c ON c.id = i.client_id
            ORDER BY py.id DESC
            LIMIT :limit
        ');
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    $like = '%' . $q . '%';
    $stmt = db()->prepare('
        SELECT
          py.id, py.nomor_kuitansi, py.tanggal, py.amount, py.metode' . $selectVoid . ',
          i.nomor AS invoice_nomor,
          CASE WHEN i.client_id IS NULL THEN "jamaah" ELSE "client" END AS target_type,
          COALESCE(j.nama_lengkap, c.nama_perusahaan) AS target_nama
        FROM payments py
        INNER JOIN invoices i ON i.id = py.invoice_id
        LEFT JOIN jamaah j ON j.id = i.jamaah_id
        LEFT JOIN clients c ON c.id = i.client_id
        WHERE
          py.nomor_kuitansi LIKE :like OR
          i.nomor LIKE :like OR
          j.nama_lengkap LIKE :like OR
          c.nama_perusahaan LIKE :like OR
          c.nama_pic LIKE :like
        ORDER BY py.id DESC
        LIMIT :limit
    ');
    $stmt->bindValue('like', $like, PDO::PARAM_STR);
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function payments_report(array $filters): array
{
    $from = trim((string)($filters['from'] ?? ''));
    $to = trim((string)($filters['to'] ?? ''));
    $paketId = (int)($filters['paket_id'] ?? 0);
    $outlet = strtolower(trim((string)($filters['outlet'] ?? '')));
    $sales = strtolower(trim((string)($filters['sales'] ?? '')));
    $metode = strtolower(trim((string)($filters['metode'] ?? '')));
    $includeVoid = (bool)($filters['include_void'] ?? false);
    $limit = (int)($filters['limit'] ?? 200);
    if ($limit <= 0 || $limit > 1000) $limit = 200;

    $where = [];
    $params = [];

    if ($from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
        $where[] = 'py.tanggal >= :from';
        $params['from'] = $from;
    }
    if ($to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
        $where[] = 'py.tanggal <= :to';
        $params['to'] = $to;
    }
    if ($outlet !== '') {
        $where[] = 'py.outlet LIKE :outlet';
        $params['outlet'] = '%' . $outlet . '%';
    }
    if ($sales !== '') {
        $where[] = 'py.sales LIKE :sales';
        $params['sales'] = '%' . $sales . '%';
    }
    if ($metode !== '') {
        $where[] = 'py.metode LIKE :metode';
        $params['metode'] = '%' . $metode . '%';
    }
    if ($paketId > 0) {
        $where[] = 'i.paket_id = :paket_id';
        $params['paket_id'] = $paketId;
    }

    if (payments_void_supported() && !$includeVoid) {
        $where[] = 'py.voided_at IS NULL';
    }

    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
    $selectVoid = payments_void_supported() ? ', py.voided_at, py.void_reason' : '';

    $sql = '
        SELECT
          py.id, py.nomor_kuitansi, py.tanggal, py.amount, py.metode,
          py.pengirim, py.outlet, py.sales, py.reference' . $selectVoid . ',
          i.id AS invoice_id, i.nomor AS invoice_nomor,
          CASE WHEN i.client_id IS NULL THEN "jamaah" ELSE "client" END AS target_type,
          COALESCE(j.nama_lengkap, c.nama_perusahaan) AS target_nama,
          p.nama AS paket_nama
        FROM payments py
        INNER JOIN invoices i ON i.id = py.invoice_id
        LEFT JOIN jamaah j ON j.id = i.jamaah_id
        LEFT JOIN clients c ON c.id = i.client_id
        LEFT JOIN paket p ON p.id = i.paket_id
        ' . $whereSql . '
        ORDER BY py.tanggal DESC, py.id DESC
        LIMIT :limit
    ';

    $stmt = db()->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, PDO::PARAM_STR);
    }
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $totalOk = 0.0;
    $totalVoid = 0.0;
    $countOk = 0;
    $countVoid = 0;
    $voidSupported = payments_void_supported();
    foreach ($rows as $r) {
        $isVoid = $voidSupported && isset($r['voided_at']) && $r['voided_at'];
        if ($isVoid) {
            $totalVoid += (float)$r['amount'];
            $countVoid++;
        } else {
            $totalOk += (float)$r['amount'];
            $countOk++;
        }
    }

    return [
        'rows' => $rows,
        'summary' => [
            'total_ok' => $totalOk,
            'total_void' => $totalVoid,
            'count_ok' => $countOk,
            'count_void' => $countVoid,
        ],
        'filters' => [
            'from' => $from,
            'to' => $to,
            'paket_id' => $paketId,
            'outlet' => $outlet,
            'sales' => $sales,
            'metode' => $metode,
            'include_void' => $includeVoid,
        ],
    ];
}

function paket_outstanding_summary(int $paketId): array
{
    $paidExpr = payments_void_supported()
        ? 'COALESCE((SELECT SUM(py.amount) FROM payments py WHERE py.invoice_id = i.id AND py.voided_at IS NULL), 0)'
        : 'COALESCE((SELECT SUM(py.amount) FROM payments py WHERE py.invoice_id = i.id), 0)';

    $where = ['i.status <> "void"'];
    $params = [];
    if ($paketId > 0) {
        $where[] = 'i.paket_id = :paket_id';
        $params['paket_id'] = $paketId;
    }
    $whereSql = 'WHERE ' . implode(' AND ', $where);

    $sql = '
        SELECT
          COUNT(*) AS invoice_count,
          COALESCE(SUM(i.grand_total), 0) AS total_tagihan,
          COALESCE(SUM(' . $paidExpr . '), 0) AS total_paid,
          COALESCE(SUM(GREATEST(0, i.grand_total - (' . $paidExpr . '))), 0) AS total_outstanding
        FROM invoices i
        ' . $whereSql . '
    ';
    $stmt = db()->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, PDO::PARAM_INT);
    }
    $stmt->execute();
    $row = $stmt->fetch() ?: ['invoice_count' => 0, 'total_tagihan' => 0, 'total_paid' => 0, 'total_outstanding' => 0];
    return [
        'invoice_count' => (int)$row['invoice_count'],
        'total_tagihan' => (float)$row['total_tagihan'],
        'total_paid' => (float)$row['total_paid'],
        'total_outstanding' => (float)$row['total_outstanding'],
    ];
}

function invoices_piutang_report(array $filters): array
{
    $q = trim((string)($filters['q'] ?? ''));
    $from = trim((string)($filters['from'] ?? ''));
    $to = trim((string)($filters['to'] ?? ''));
    $paketId = (int)($filters['paket_id'] ?? 0);
    $status = strtolower(trim((string)($filters['status'] ?? '')));
    $limit = (int)($filters['limit'] ?? 200);
    if ($limit <= 0 || $limit > 1000) $limit = 200;

    $where = [];
    $params = [];

    if ($q !== '') {
        $where[] = '(i.nomor LIKE :like OR j.nama_lengkap LIKE :like OR j.id_jamaah LIKE :like OR j.nomor_pendaftaran LIKE :like OR c.nama_perusahaan LIKE :like OR c.nama_pic LIKE :like)';
        $params['like'] = '%' . $q . '%';
    }
    if ($from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
        $where[] = 'i.tanggal >= :from';
        $params['from'] = $from;
    }
    if ($to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
        $where[] = 'i.tanggal <= :to';
        $params['to'] = $to;
    }
    if ($paketId > 0) {
        $where[] = 'i.paket_id = :paket_id';
        $params['paket_id'] = $paketId;
    }
    if ($status === 'unpaid' || $status === 'partial') {
        $where[] = 'i.status = :status';
        $params['status'] = $status;
    }

    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $paidExpr = payments_void_supported()
        ? 'COALESCE((SELECT SUM(py.amount) FROM payments py WHERE py.invoice_id = i.id AND py.voided_at IS NULL), 0)'
        : 'COALESCE((SELECT SUM(py.amount) FROM payments py WHERE py.invoice_id = i.id), 0)';

    $sql = '
        SELECT
          i.id, i.nomor, i.tanggal, i.status, i.grand_total,
          CASE WHEN i.client_id IS NULL THEN "jamaah" ELSE "client" END AS target_type,
          COALESCE(j.nama_lengkap, c.nama_perusahaan) AS target_nama,
          j.id_jamaah AS jamaah_kode,
          j.nomor_pendaftaran AS jamaah_daftar,
          c.nama_pic AS client_pic,
          c.no_tlp AS client_tlp,
          c.email AS client_email,
          p.nama AS paket_nama,
          ' . $paidExpr . ' AS paid_total,
          GREATEST(0, i.grand_total - (' . $paidExpr . ')) AS remaining_total,
          DATEDIFF(CURDATE(), i.tanggal) AS age_days
        FROM invoices i
        LEFT JOIN jamaah j ON j.id = i.jamaah_id
        LEFT JOIN clients c ON c.id = i.client_id
        LEFT JOIN paket p ON p.id = i.paket_id
        ' . $whereSql . '
        HAVING remaining_total > 0 AND i.status IN ("unpaid","partial")
        ORDER BY i.tanggal DESC, i.id DESC
        LIMIT :limit
    ';

    $stmt = db()->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, PDO::PARAM_STR);
    }
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $totalPiutang = 0.0;
    foreach ($rows as $r) {
        $totalPiutang += (float)$r['remaining_total'];
    }

    return [
        'rows' => $rows,
        'summary' => [
            'total_piutang' => $totalPiutang,
            'count' => count($rows),
        ],
        'filters' => [
            'q' => $q,
            'from' => $from,
            'to' => $to,
            'paket_id' => $paketId,
            'status' => $status,
        ],
    ];
}
