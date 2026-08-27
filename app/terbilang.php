<?php

declare(strict_types=1);

function terbilang_id_int(int $n): string
{
    $n = abs($n);
    $words = [
        0 => 'nol',
        1 => 'satu',
        2 => 'dua',
        3 => 'tiga',
        4 => 'empat',
        5 => 'lima',
        6 => 'enam',
        7 => 'tujuh',
        8 => 'delapan',
        9 => 'sembilan',
        10 => 'sepuluh',
        11 => 'sebelas',
    ];

    if ($n < 12) {
        return $words[$n];
    }
    if ($n < 20) {
        return terbilang_id_int($n - 10) . ' belas';
    }
    if ($n < 100) {
        $puluh = intdiv($n, 10);
        $sisa = $n % 10;
        $out = terbilang_id_int($puluh) . ' puluh';
        if ($sisa > 0) $out .= ' ' . terbilang_id_int($sisa);
        return $out;
    }
    if ($n < 200) {
        $sisa = $n - 100;
        $out = 'seratus';
        if ($sisa > 0) $out .= ' ' . terbilang_id_int($sisa);
        return $out;
    }
    if ($n < 1000) {
        $ratus = intdiv($n, 100);
        $sisa = $n % 100;
        $out = terbilang_id_int($ratus) . ' ratus';
        if ($sisa > 0) $out .= ' ' . terbilang_id_int($sisa);
        return $out;
    }
    if ($n < 2000) {
        $sisa = $n - 1000;
        $out = 'seribu';
        if ($sisa > 0) $out .= ' ' . terbilang_id_int($sisa);
        return $out;
    }
    if ($n < 1_000_000) {
        $ribuan = intdiv($n, 1000);
        $sisa = $n % 1000;
        $out = terbilang_id_int($ribuan) . ' ribu';
        if ($sisa > 0) $out .= ' ' . terbilang_id_int($sisa);
        return $out;
    }
    if ($n < 1_000_000_000) {
        $juta = intdiv($n, 1_000_000);
        $sisa = $n % 1_000_000;
        $out = terbilang_id_int($juta) . ' juta';
        if ($sisa > 0) $out .= ' ' . terbilang_id_int($sisa);
        return $out;
    }
    if ($n < 1_000_000_000_000) {
        $miliar = intdiv($n, 1_000_000_000);
        $sisa = $n % 1_000_000_000;
        $out = terbilang_id_int($miliar) . ' miliar';
        if ($sisa > 0) $out .= ' ' . terbilang_id_int($sisa);
        return $out;
    }
    if ($n < 1_000_000_000_000_000) {
        $triliun = intdiv($n, 1_000_000_000_000);
        $sisa = $n % 1_000_000_000_000;
        $out = terbilang_id_int($triliun) . ' triliun';
        if ($sisa > 0) $out .= ' ' . terbilang_id_int($sisa);
        return $out;
    }
    return 'terlalu besar';
}

function terbilang_rupiah(string $amount): string
{
    $normalized = trim($amount);
    $normalized = str_replace([',', ' '], ['', ''], $normalized);
    if ($normalized === '') {
        return 'Nol rupiah';
    }
    $num = (float)$normalized;
    $rupiah = (int)round($num);
    $text = terbilang_id_int($rupiah);
    $text = preg_replace('/\s+/', ' ', trim((string)$text));
    $text = mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
    return $text . ' rupiah';
}

