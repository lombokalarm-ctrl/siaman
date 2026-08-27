<?php

declare(strict_types=1);

function sequence_next(string $key, ?int $scopeYear = null, ?int $scopeMonth = null): int
{
    $pdo = db();
    $ownsTransaction = !$pdo->inTransaction();
    if ($ownsTransaction) {
        $pdo->beginTransaction();
    }
    try {
        $stmt = $pdo->prepare('SELECT id, last_number FROM number_sequences WHERE `key` = :key AND scope_year <=> :scope_year AND scope_month <=> :scope_month FOR UPDATE');
        $stmt->execute([
            'key' => $key,
            'scope_year' => $scopeYear,
            'scope_month' => $scopeMonth,
        ]);
        $row = $stmt->fetch();
        if ($row) {
            $next = ((int)$row['last_number']) + 1;
            $upd = $pdo->prepare('UPDATE number_sequences SET last_number = :n, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
            $upd->execute(['n' => $next, 'id' => (int)$row['id']]);
            if ($ownsTransaction) {
                $pdo->commit();
            }
            return $next;
        }

        $ins = $pdo->prepare('INSERT INTO number_sequences (`key`, scope_year, scope_month, last_number) VALUES (:key, :scope_year, :scope_month, :n)');
        $ins->execute([
            'key' => $key,
            'scope_year' => $scopeYear,
            'scope_month' => $scopeMonth,
            'n' => 1,
        ]);
        if ($ownsTransaction) {
            $pdo->commit();
        }
        return 1;
    } catch (Throwable $e) {
        if ($ownsTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function generate_id_jamaah(): string
{
    $n = sequence_next('jamaah.id', null, null);
    return 'JMH-' . str_pad((string)$n, 6, '0', STR_PAD_LEFT);
}

function generate_nomor_pendaftaran(DateTimeImmutable $now): string
{
    $fmt = settings_get_pendaftaran_format();

    $prefix = strtoupper(trim((string)$fmt['prefix']));
    $separator = (string)$fmt['separator'];
    $yearToken = (string)$fmt['year_token'];
    $seqLength = max(1, (int)$fmt['seq_length']);
    $resetPolicy = (string)$fmt['reset_policy'];

    $yearPart = '';
    if ($yearToken === '{YYYY}') {
        $yearPart = $now->format('Y');
    } elseif ($yearToken === '{YY}') {
        $yearPart = $now->format('y');
    }

    $scopeYear = null;
    $scopeMonth = null;
    if ($resetPolicy === 'yearly') {
        $scopeYear = (int)$now->format('Y');
    } elseif ($resetPolicy === 'monthly') {
        $scopeYear = (int)$now->format('Y');
        $scopeMonth = (int)$now->format('n');
    }

    $n = sequence_next('jamaah.pendaftaran', $scopeYear, $scopeMonth);
    $seqPart = str_pad((string)$n, $seqLength, '0', STR_PAD_LEFT);

    $parts = [];
    if ($prefix !== '') {
        $parts[] = $prefix;
    }
    if ($yearPart !== '') {
        $parts[] = $yearPart;
    }
    $parts[] = $seqPart;

    return implode($separator, $parts);
}

function generate_nomor_invoice(DateTimeImmutable $now): string
{
    $fmt = settings_get_invoice_format();

    $prefix = strtoupper(trim((string)$fmt['prefix']));
    $separator = (string)$fmt['separator'];
    $yearToken = (string)$fmt['year_token'];
    $seqLength = max(1, (int)$fmt['seq_length']);
    $resetPolicy = (string)$fmt['reset_policy'];

    $yearPart = '';
    if ($yearToken === '{YYYY}') {
        $yearPart = $now->format('Y');
    } elseif ($yearToken === '{YY}') {
        $yearPart = $now->format('y');
    }

    $scopeYear = null;
    $scopeMonth = null;
    if ($resetPolicy === 'yearly') {
        $scopeYear = (int)$now->format('Y');
    } elseif ($resetPolicy === 'monthly') {
        $scopeYear = (int)$now->format('Y');
        $scopeMonth = (int)$now->format('n');
    }

    $n = sequence_next('invoice.nomor', $scopeYear, $scopeMonth);
    $seqPart = str_pad((string)$n, $seqLength, '0', STR_PAD_LEFT);

    $parts = [];
    if ($prefix !== '') $parts[] = $prefix;
    if ($yearPart !== '') $parts[] = $yearPart;
    $parts[] = $seqPart;

    return implode($separator, $parts);
}

function generate_nomor_kuitansi(DateTimeImmutable $now): string
{
    $fmt = settings_get_kuitansi_format();

    $prefix = strtoupper(trim((string)$fmt['prefix']));
    $separator = (string)$fmt['separator'];
    $yearToken = (string)$fmt['year_token'];
    $seqLength = max(1, (int)$fmt['seq_length']);
    $resetPolicy = (string)$fmt['reset_policy'];

    $yearPart = '';
    if ($yearToken === '{YYYY}') {
        $yearPart = $now->format('Y');
    } elseif ($yearToken === '{YY}') {
        $yearPart = $now->format('y');
    }

    $scopeYear = null;
    $scopeMonth = null;
    if ($resetPolicy === 'yearly') {
        $scopeYear = (int)$now->format('Y');
    } elseif ($resetPolicy === 'monthly') {
        $scopeYear = (int)$now->format('Y');
        $scopeMonth = (int)$now->format('n');
    }

    $n = sequence_next('kuitansi.nomor', $scopeYear, $scopeMonth);
    $seqPart = str_pad((string)$n, $seqLength, '0', STR_PAD_LEFT);

    $parts = [];
    if ($prefix !== '') $parts[] = $prefix;
    if ($yearPart !== '') $parts[] = $yearPart;
    $parts[] = $seqPart;

    return implode($separator, $parts);
}
