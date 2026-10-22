<?php

namespace App\Libraries;

/**
 * Instrumen "Inventori Masalah Umum" (IMU) — asesmen awal pendaftaran konseling.
 * 24 butir berskor (skala 1–5, skor total 24–120) + butir 25 "Lainnya" (tidak dihitung).
 * Sumber: bahan/INVENTORI  MASALAH UMUM.docx. Kata "pekerjaan" pada butir 9, 10, 12
 * disesuaikan menjadi "pekerjaan/kuliah/tugas kuliah".
 */
class InventoriMasalahUmum
{
    public const SKALA = [
        1 => 'Tidak sama sekali',
        2 => 'Sedikit',
        3 => 'Sedang',
        4 => 'Cukup banyak',
        5 => 'Sangat banyak',
    ];

    public const BUTIR = [
        1  => 'Merasa depresi, sedih, atau murung?',
        2  => 'Menyalahkan, mengkritik, atau mencela diri sendiri?',
        3  => 'Merasa putus asa atau merasa seperti seorang yang gagal?',
        4  => 'Ada pikiran atau kekhawatiran tentang bunuh diri?',
        5  => 'Merasa mudah tersinggung, tegang, atau gugup?',
        6  => 'Merasa takut?',
        7  => 'Mengalami serangan teror atau panik?',
        8  => 'Merasa seperti sedang “hancur” atau “tidak sanggup lagi”?',
        9  => 'Mengalami masalah pekerjaan/kuliah/tugas kuliah?',
        10 => 'Kesulitan untuk peduli terhadap pekerjaan/kuliah/tugas kuliah atau berkonsentrasi padanya?',
        11 => 'Keragu-raguan atau kekhawatiran dalam memilih karier atau jurusan?',
        12 => 'Merasa bahwa kinerja saya dalam pekerjaan/kuliah/tugas kuliah tidak sebaik yang seharusnya?',
        13 => 'Masalah dalam hubungan romantis atau seksual?',
        14 => 'Masalah keluarga?',
        15 => 'Kesulitan bergaul atau berhubungan baik dengan orang lain?',
        16 => 'Merasa kesepian atau terisolasi?',
        17 => 'Masalah kesehatan fisik?',
        18 => 'Sakit kepala, merasa akan pingsan, atau pusing?',
        19 => 'Kesulitan tidur?',
        20 => 'Masalah makan, nafsu makan, atau berat badan?',
        21 => 'Penggunaan alkohol oleh saya?',
        22 => 'Penggunaan ganja oleh saya?',
        23 => 'Seberapa banyak obat-obatan psikoaktif yang saya gunakan?',
        24 => 'Seberapa banyak obat yang diresepkan dokter yang saya gunakan?',
    ];

    /** Butir yang punya isian teks "Jika ya, apa?" */
    public const BUTIR_OBAT = 24;

    /** Butir pikiran bunuh diri — jawaban >= ambang ini memicu safety flag. */
    public const BUTIR_BUNUH_DIRI = 4;
    public const AMBANG_BUNUH_DIRI = 2;

    public const SKOR_MIN = 24;
    public const SKOR_MAKS = 120;

    /** Kategori skor total: [min, max, label, interpretasi, warna]. Ditampilkan ke mahasiswa, admin, psikolog. */
    public const KATEGORI = [
        'rendah' => [24, 56, 'Rendah', 'Mahasiswa sedang berada dalam kondisi adaptasi yang baik. Masalah yang muncul bersifat minor atau wajar dalam kehidupan kampus sehari-hari.', '#2d9b6e'],
        'sedang' => [57, 88, 'Sedang (Moderat)', 'Menunjukkan adanya distres (perasaan tertekan) yang mulai terlihat. Mahasiswa mulai kesulitan mengatasi tekanan akademik atau hubungan interpersonal. Disarankan untuk melakukan konseling agar kondisi tidak memburuk.', '#d48f00'],
        'tinggi' => [89, 120, 'Tinggi/Berat', 'Menandakan tingkat stres dan tekanan psikologis yang berat. Kondisi ini bisa sangat mengganggu aktivitas keseharian dan akademik mahasiswa. Sangat disarankan untuk segera melakukan konseling dengan psikolog.', '#dc3545'],
    ];

    /** Area masalah: kode => [butir awal, butir akhir, nama, indikasi]. Hanya untuk admin & psikolog. */
    public const AREA = [
        'emosi'     => [1, 8, 'Depresi dan Kecemasan', 'Mengindikasikan masalah kesehatan mental emosional yang membutuhkan perhatian psikologis.'],
        'akademik'  => [9, 16, 'Akademik dan Interpersonal', 'Menandakan ada hambatan lingkungan, seperti stres tugas kuliah atau konflik dengan teman/keluarga.'],
        'kesehatan' => [17, 24, 'Kesehatan dan Penyalahgunaan Zat Terlarang', 'Menandakan adanya keluhan fisik (somatisasi) atau mekanisme koping yang tidak sehat, seperti ketergantungan NAPZA.'],
    ];

    /** Skor area di atas nilai ini dianggap perlu perhatian. */
    public const AMBANG_AREA = 29;

    public static function kategori(int $skor): string
    {
        foreach (self::KATEGORI as $kode => [$min, $max]) {
            if ($skor >= $min && $skor <= $max) return $kode;
        }
        return $skor < self::SKOR_MIN ? 'rendah' : 'tinggi';
    }

    /** @param array $jawaban [nomor_item => nilai 1–5] @return array [kode_area => skor] */
    public static function skorArea(array $jawaban): array
    {
        $skor = [];
        foreach (self::AREA as $kode => [$awal, $akhir]) {
            $skor[$kode] = 0;
            for ($n = $awal; $n <= $akhir; $n++) {
                $skor[$kode] += (int) ($jawaban[$n] ?? 0);
            }
        }
        return $skor;
    }

    public static function areaTinggi(int $skor): bool
    {
        return $skor > self::AMBANG_AREA;
    }

    /** Skor area dari baris imu_assessments: [kode_area => skor]. */
    public static function skorAreaDariBaris(array $row): array
    {
        $skor = [];
        foreach (array_keys(self::AREA) as $kode) {
            $skor[$kode] = (int) ($row['skor_area_' . $kode] ?? 0);
        }
        return $skor;
    }
}
