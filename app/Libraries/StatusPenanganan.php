<?php

namespace App\Libraries;

/**
 * Status penanganan klien yang diisi psikolog pada hasil konseling
 * (checklist rekomendasi → "E. Status Penanganan Klien Saat Ini", disimpan di hasil_konseling.rekomendasi['status']).
 */
class StatusPenanganan
{
    /** nilai checklist => [label, keterangan untuk mahasiswa, ikon, warna] */
    public const STATUS = [
        'Selesai' => [
            'Selesai Penanganan',
            'Proses konselingmu telah selesai. Jika suatu saat kamu membutuhkan bantuan lagi, kamu dapat mendaftar konseling kembali.',
            'tabler-circle-check', '#2d9b6e',
        ],
        'Monitoring' => [
            'Monitoring',
            'Psikolog akan memantau perkembangan kondisimu. Terus terapkan saran yang diberikan, dan hubungi SMHWS bila kondisimu memburuk.',
            'tabler-eye-check', '#1a5f7a',
        ],
        'Follow-up' => [
            'Follow-up / Konseling Lanjutan',
            'Psikolog menyarankan kamu mengikuti sesi konseling lanjutan. Silakan daftar konseling kembali untuk melanjutkan prosesnya.',
            'tabler-calendar-repeat', '#d48f00',
        ],
        'Rujukan' => [
            'Dirujuk',
            'Psikolog merujukmu ke layanan profesional lain agar kamu mendapat penanganan yang lebih sesuai. Unduh surat rujukan dan bawa saat datang ke instansi tujuan.',
            'tabler-arrow-forward-up', '#dc3545',
        ],
        'Terminasi atas kesepakatan bersama' => [
            'Terminasi atas Kesepakatan Bersama',
            'Konseling diakhiri atas kesepakatan bersama antara kamu dan psikolog. Kamu dapat mendaftar konseling kembali kapan pun kamu membutuhkan.',
            'tabler-heart-handshake', '#696cff',
        ],
    ];

    /** Nilai status dari baris hasil_konseling (null bila belum diisi / tidak dikenal). */
    public static function dariHasil(?array $hasil): ?string
    {
        $rek    = json_decode($hasil['rekomendasi'] ?? '', true) ?: [];
        $status = $rek['status'] ?? null;
        return is_string($status) && isset(self::STATUS[$status]) ? $status : null;
    }

    /** [nilai => label] untuk rekap/dashboard. */
    public static function label(): array
    {
        return array_map(static fn ($s) => $s[0], self::STATUS);
    }
}
