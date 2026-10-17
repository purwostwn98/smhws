<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Daftar jenis assessment pada menu Mental Health Check.
 * Tambahkan entri baru di sini (dan route-nya) untuk assessment berikutnya.
 */
class Assessment extends BaseConfig
{
    /**
     * @var array<string, array{nama:string, singkatan:string, deskripsi:string, icon:string, color:string, jumlah_item:string, durasi:string, url:string, aktif:bool}>
     */
    public array $jenis = [
        'dass' => [
            'nama'        => 'Depression Anxiety Stress Scales',
            'singkatan'   => 'DASS-21',
            'deskripsi'   => 'Mengukur tingkat depresi, kecemasan, dan stres yang kamu rasakan dalam 1 minggu terakhir.',
            'icon'        => 'tabler-brain',
            'color'       => '#1a5f7a',
            'jumlah_item' => '21 pernyataan',
            'durasi'      => '± 5 menit',
            'url'         => 'skrining/dass',
            'aktif'       => true,
        ],
    ];
}
