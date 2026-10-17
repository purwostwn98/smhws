<?php

namespace App\Libraries;

/**
 * Deskripsi hasil DASS-21 (interpretasi per subskala + rekomendasi).
 * Dipakai oleh halaman hasil skrining publik dan PDF-nya.
 */
class DassDeskripsi
{
    private const KAT_ORDER = ['normal' => 0, 'ringan' => 1, 'sedang' => 2, 'berat' => 3, 'sangat_berat' => 4];

    private const INTERPRETASI = [
            'depresi' => [
              'normal'       => 'Kamu mengalami perubahan suasana hati, namun masih dalam batas wajar.',
              'ringan'       => 'Kamu tampaknya mulai mengalami sedikit penurunan suasana hati, namun masih tergolong ringan.',
              'sedang'       => 'Kamu tampaknya cukup sering mengalami penurunan suasana hati, dan ini mulai mempengaruhi aktivitas harianmu.',
              'berat'        => 'Kamu tampaknya sedang mengalami penurunan suasana hati yang lumayan besar, sehingga aktivitas harianmu agak terganggu.',
              'sangat_berat' => 'Kamu tampaknya sedang mengalami penurunan suasana hati yang besar dan sudah mengganggu aktivitas harianmu.',
            ],
            'anxiety' => [
              'normal'       => 'Kamu merasa cemas namun masih dalam batas wajar.',
              'ringan'       => 'Kamu tampaknya mulai mengalami peningkatan rasa cemas, namun masih tergolong ringan.',
              'sedang'       => 'Kamu tampaknya cukup sering mengalami kecemasan yang mulai mempengaruhi aktivitas harianmu.',
              'berat'        => 'Kamu tampaknya sedang mengalami kecemasan yang cukup tinggi, dan mengganggu aktivitas harianmu.',
              'sangat_berat' => 'Kamu tampaknya sedang mengalami rasa cemas yang tinggi, dan ini sudah mengganggu aktivitas harianmu.',
            ],
            'stress' => [
              'normal'       => 'Kamu mengalami sedikit tekanan, namun masih berada dalam tahap wajar.',
              'ringan'       => 'Kamu tampaknya mulai merasakan peningkatan tekanan dan ketegangan, namun masih tergolong ringan.',
              'sedang'       => 'Kamu tampaknya cukup sering merasakan tekanan dan ketegangan yang mulai mempengaruhi aktivitas harianmu.',
              'berat'        => 'Kamu tampaknya sedang mengalami tekanan dan ketegangan yang cukup tinggi, dan ini mengganggu aktivitas harianmu.',
              'sangat_berat' => 'Kamu tampaknya dalam kondisi tertekan dan ketegangan yang tinggi, dan ini sudah mengganggu aktivitas harianmu.',
            ],
          ];

    private const REKOMENDASI = [
            0 => "Kondisimu saat ini tampaknya cukup baik. Usahakan untuk menjaganya, misalnya dengan pola hidup sehat, seperti tidur yang cukup, makan bergizi, berolahraga, menjaga hubungan baik dengan keluarga, teman, dosen, dll., dan meluangkan waktu untuk melakukan aktivitas positif yang kamu sukai. Jangan ragu untuk mencari bantuan jika suatu saat kamu merasa membutuhkan.",
            1 => "Kondisimu saat ini mungkin sedikit kurang bagus. Cobalah untuk mengenali hal apa yang mungkin mempengaruhi suasana perasaanmu, luangkan waktu untuk beristirahat, lakukan aktivitas positif yang bisa membuatmu lebih nyaman, dan berbicaralah kepada orang-orang di sekitarmu yang bisa kamu percaya terkait hal-hal yang sedang mengganggu suasana perasaanmu.",
            2 => "Kondisi yang kamu rasakan saat ini tampaknya mulai mempengaruhi aktivitas harianmu. Akan sangat baik jika kamu mencari bantuan dari teman, keluarga, dosen, atau psikolog untuk berbagi cerita dan memperoleh dukungan supaya kondisimu lebih baik.",
            3 => "Kondisi yang kamu rasakan saat ini tampaknya sudah cukup mengganggu keseharianmu. Kamu sebaiknya tidak menghadapinya sendirian. Pertimbangkan untuk berbicara dengan psikolog atau mengunjungi layanan kesehatan mental agar kamu mendapatkan bantuan yang sesuai.",
            4 => "Kondisi yang kamu rasakan tampaknya sudah mempengaruhi kesejahteraan psikologis dan aktivitas harianmu. Kamu sangat disarankan untuk segera berbicara dengan psikolog atau mengunjungi layanan kesehatan mental guna mendapatkan bantuan profesional. Kamu butuh bantuan agar tidak menghadapi kondisi ini sendirian.",
          ];

    private const REKOMENDASI_META = [
            0 => ['icon' => 'tabler-mood-happy',   'color' => '#2d9b6e', 'bg' => 'rgba(45,155,110,.07)',  'border' => '#2d9b6e'],
            1 => ['icon' => 'tabler-mood-smile',   'color' => '#1a5f7a', 'bg' => 'rgba(26,95,122,.07)',   'border' => '#1a5f7a'],
            2 => ['icon' => 'tabler-mood-neutral', 'color' => '#d48f00', 'bg' => 'rgba(240,165,0,.09)',   'border' => '#f0a500'],
            3 => ['icon' => 'tabler-mood-sad',     'color' => '#c8600a', 'bg' => 'rgba(200,96,10,.08)',   'border' => '#e07a20'],
            4 => ['icon' => 'tabler-mood-cry',     'color' => '#dc3545', 'bg' => 'rgba(220,53,69,.08)',   'border' => '#dc3545'],
          ];

    /**
     * @param array $hasil baris skrining (kategori_* dan skor_*)
     */
    public static function susun(array $hasil): array
    {
        $sub = [
            ['label' => 'Depresi',   'key' => 'depresi', 'icon' => 'tabler-mood-sad',           'color' => '#1a5f7a'],
            ['label' => 'Kecemasan', 'key' => 'anxiety', 'icon' => 'tabler-heart-rate-monitor', 'color' => '#f0a500'],
            ['label' => 'Stres',     'key' => 'stress',  'icon' => 'tabler-bolt',               'color' => '#2d9b6e'],
        ];

        $maxLevel = 0;
        foreach ($sub as &$s) {
            $s['skor']      = $hasil['skor_' . $s['key']];
            $s['kategori']  = $hasil['kategori_' . $s['key']];
            $s['interpretasi'] = self::INTERPRETASI[$s['key']][$s['kategori']] ?? '';
            $maxLevel = max($maxLevel, self::KAT_ORDER[$s['kategori']] ?? 0);
        }
        unset($s);

        return [
            'subskala'    => $sub,
            'rekomendasi' => self::REKOMENDASI[$maxLevel],
            'meta'        => self::REKOMENDASI_META[$maxLevel],
        ];
    }
}
