<?php

namespace App\Libraries;

/**
 * Instrumen "Pola Penggunaan Internet" (18 butir, skala 0–5, skor total 0–90).
 * Sumber: bahan/Pola Penggunaan Internet.docx. Butir dipertahankan persis seperti instrumen.
 */
class PolaInternet
{
    public const SKALA = [
        0 => 'Tidak Pernah',
        1 => 'Jarang',
        2 => 'Kadang-kadang',
        3 => 'Sering',
        4 => 'Sangat Sering',
        5 => 'Selalu',
    ];

    public const BUTIR = [
        1  => 'Seberapa sering Anda menemukan diri Anda bermain internet lebih lama dari yang Anda rencanakan?',
        2  => 'Seberapa sering Anda mengabaikan pekerjaan rumah untuk menghabiskan lebih banyak waktu dengan internet?',
        3  => 'Seberapa sering Anda memiliki kesenangan dengan internet dibandingkan dengan kedekatan bersama keluarga, teman, atau orang terdekat Anda?',
        4  => 'Seberapa sering Anda menjalin pertemanan baru dengan sesama orang yang menggunakan internet?',
        5  => 'Seberapa sering nilai-nilai atau tugas sekolah Anda memburuk akibat bermain di internet?',
        6  => 'Seberapa sering prestasi sekolah atau tugas Anda memburuk akibat internet?',
        7  => 'Seberapa sering Anda menutupi atau merahasiakan ketika seseorang bertanya apa yang Anda lakukan saat bermain internet?',
        8  => 'Seberapa sering Anda menutupi pikiran yang mengganggu dengan pikiran yang menyenangkan mengenai internet?',
        9  => 'Seberapa sering Anda mendapatkan diri Anda merencanakan kapan akan bermain internet lagi?',
        10 => 'Seberapa sering Anda merasa takut bahwa hidup tanpa internet akan membosankan, kosong, dan hilang kegembiraan?',
        11 => 'Seberapa sering Anda marah, berteriak, atau bertingkah jika seseorang mengganggu Anda saat bermain internet?',
        12 => 'Seberapa sering Anda begadang karena online bermain internet hingga larut malam?',
        13 => 'Seberapa sering Anda merasa terus menerus memikirkan internet ketika sedang offline, atau berkhayal seolah-olah sedang online?',
        14 => 'Seberapa sering Anda mengatakan "sebentar lagi" saat bermain internet?',
        15 => 'Seberapa sering Anda berusaha mengurangi waktu yang Anda habiskan untuk bermain internet dan kemudian gagal?',
        16 => 'Seberapa sering Anda berusaha menyembunyikan berapa lama sebenarnya Anda menggunakan waktu untuk bermain internet?',
        17 => 'Seberapa sering Anda memilih menggunakan waktu lebih lama untuk bermain internet daripada pergi bersama dengan orang lain?',
        18 => 'Seberapa sering Anda merasa depresi, labil atau gugup saat offline dan akan hilang ketika kembali bermain internet?',
    ];

    public const SKOR_MAKS = 90;

    /** Kategori: [min, max, label, rekomendasi, warna]. Label kategori TIDAK ditampilkan ke mahasiswa. */
    public const KATEGORI = [
        'normal' => [0, 27, 'Normal', 'Penggunaan internet Anda masih relatif terkendali. Pertahankan kebiasaan yang sudah baik dengan menyeimbangkan aktivitas daring, belajar atau bekerja, istirahat, dan interaksi sosial.', '#2d9b6e'],
        'ringan' => [28, 44, 'Ringan', 'Anda mulai menunjukkan beberapa kebiasaan penggunaan internet yang perlu diperhatikan. Cobalah mengenali aktivitas yang membuat Anda sulit berhenti, mengatur waktu penggunaan, dan mengurangi notifikasi yang tidak penting.', '#1a5f7a'],
        'sedang' => [45, 71, 'Sedang', 'Penggunaan internet Anda mungkin mulai menyulitkan pengelolaan waktu atau mengganggu aktivitas sehari-hari. Cobalah membuat jadwal penggunaan yang lebih teratur, menjaga waktu tidur, dan menyediakan waktu untuk aktivitas di luar internet. Jika kesulitan berlanjut, pertimbangkan berdiskusi dengan konselor atau psikolog.', '#d48f00'],
        'berat'  => [72, 90, 'Berat', 'Jawaban Anda menunjukkan pola penggunaan internet yang perlu mendapat perhatian lebih lanjut. Anda dapat mulai meminta bantuan orang tepercaya dan sebaiknya konsultasi dengan psikolog, terutama jika penggunaan internet mengganggu belajar, tidur, hubungan sosial, atau tanggung jawab sehari-hari.', '#dc3545'],
    ];

    public const CATATAN = 'Hasil ini membantu Anda mengenali pola penggunaan internet, bukan untuk menilai diri Anda sebagai baik atau buruk. Skor yang lebih tinggi tidak otomatis berarti Anda mengalami gangguan klinis. Perhatikan juga dampak penggunaan internet terhadap kehidupan sehari-hari. Jika mengalami kesulitan, perubahan dapat dimulai secara bertahap dengan dukungan yang sesuai.';

    public static function kategori(int $skor): string
    {
        foreach (self::KATEGORI as $kode => [$min, $max]) {
            if ($skor >= $min && $skor <= $max) return $kode;
        }
        return 'berat';
    }

    public static function labelKategori(string $kode): string
    {
        return self::KATEGORI[$kode][2] ?? $kode;
    }

    public static function rekomendasi(string $kode): string
    {
        return self::KATEGORI[$kode][3] ?? '';
    }

    public static function warna(string $kode): string
    {
        return self::KATEGORI[$kode][4] ?? '#1a5f7a';
    }
}
