<?php

namespace App\Libraries;

use App\Libraries\InventoriMasalahUmum as IMU;
use CodeIgniter\Database\BaseBuilder;

/**
 * Kriteria safety flag: skrining keselamatan (tabel janji_safety_screening)
 * ditambah butir pikiran bunuh diri pada asesmen awal IMU.
 * Satu sumber untuk query dashboard/daftar janji dan banner di halaman detail.
 */
class SafetyFlag
{
    /** kolom => [nilai pemicu flag, keterangan] */
    public const KRITERIA = [
        'pikiran_mengakhiri_hidup' => ['ya',    'Mahasiswa memiliki pikiran mengakhiri hidup'],
        'pernah_selfharm'          => ['ya',    'Mahasiswa pernah melakukan self-harm'],
        'pikiran_mengganggu'       => ['ya',    'Pikiran mengakhiri hidup terasa mengganggu / sulit dikendalikan'],
        'merasa_aman'              => ['tidak', 'Mahasiswa merasa tidak aman dengan dirinya sendiri'],
    ];

    public const KETERANGAN_IMU = 'Asesmen awal: ada pikiran atau kekhawatiran tentang bunuh diri';

    /**
     * Tambahkan kondisi (OR) safety flag ke query builder.
     * $prefix  : prefix kolom skrining keselamatan, contoh 'jss.'
     * $janjiId : kolom id janji pada query, untuk mencocokkan asesmen IMU
     */
    public static function where(BaseBuilder $builder, string $prefix = '', string $janjiId = 'janji_id'): BaseBuilder
    {
        $builder->groupStart();
        foreach (self::KRITERIA as $kolom => [$nilai]) {
            $builder->orWhere($prefix . $kolom, $nilai);
        }
        $builder->orWhereIn($janjiId, static fn (BaseBuilder $q) => $q
            ->select('imu.janji_id')
            ->from('imu_assessments imu')
            ->join('imu_jawaban imj', 'imj.assessment_id = imu.id')
            ->where('imu.deleted_at', null)
            ->where('imj.nomor_item', IMU::BUTIR_BUNUH_DIRI)
            ->where('imj.jawaban >=', IMU::AMBANG_BUNUH_DIRI));

        return $builder->groupEnd();
    }

    /**
     * Daftar keterangan flag yang aktif (kosong = tidak ada flag).
     * $imu: hasil ImuAssessmentModel::byJanji() (berisi 'jawaban'), boleh null.
     */
    public static function alasan(?array $safety, ?array $imu = null): array
    {
        $alasan = [];
        foreach (self::KRITERIA as $kolom => [$nilai, $keterangan]) {
            if (($safety[$kolom] ?? null) === $nilai) $alasan[] = $keterangan;
        }
        if ((int) ($imu['jawaban'][IMU::BUTIR_BUNUH_DIRI] ?? 0) >= IMU::AMBANG_BUNUH_DIRI) {
            $alasan[] = self::KETERANGAN_IMU;
        }
        return $alasan;
    }
}
