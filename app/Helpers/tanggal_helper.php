<?php

if (! function_exists('tanggal_id')) {
    /** "2026-10-16 09:30:00" → "16 Oktober 2026" (atau "16 Oktober 2026, 09.30 WIB" bila $denganJam). */
    function tanggal_id(?string $datetime, bool $denganJam = false): string
    {
        if (! $datetime) return '';
        $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $t = strtotime($datetime);
        $s = date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t);
        return $denganJam ? $s . ', ' . date('H.i', $t) . ' WIB' : $s;
    }
}

if (! function_exists('rentang_tanggal_id')) {
    /** Rentang kegiatan: satu hari → "16 Oktober 2026, 09.00 – 12.00 WIB"; beda hari → "16 Okt ... – 17 Okt ...". */
    function rentang_tanggal_id(string $mulai, ?string $selesai): string
    {
        if (! $selesai || $selesai === $mulai) return tanggal_id($mulai, true);
        if (date('Y-m-d', strtotime($mulai)) === date('Y-m-d', strtotime($selesai))) {
            return tanggal_id($mulai) . ', ' . date('H.i', strtotime($mulai)) . ' – ' . date('H.i', strtotime($selesai)) . ' WIB';
        }
        return tanggal_id($mulai, true) . ' – ' . tanggal_id($selesai, true);
    }
}

if (! function_exists('wa_link')) {
    /** Ambil nomor telepon dari teks kontak dan ubah ke tautan wa.me; null bila tidak ada nomor. */
    function wa_link(?string $kontak): ?string
    {
        if (! $kontak || ! preg_match('/(\+?\d[\d\s\-]{7,}\d)/', $kontak, $m)) return null;
        $n = preg_replace('/\D/', '', $m[1]);
        if (str_starts_with($n, '0')) $n = '62' . substr($n, 1);
        return 'https://wa.me/' . $n;
    }
}

if (! function_exists('biaya_label')) {
    function biaya_label(array $row): string
    {
        return $row['biaya_tipe'] === 'berbayar'
            ? 'Rp ' . number_format((int) $row['biaya_nominal'], 0, ',', '.')
            : 'Gratis';
    }
}
