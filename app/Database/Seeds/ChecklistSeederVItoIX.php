<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ChecklistSeederVItoIX extends Seeder
{
    public function run(): void
    {
        $db = \Config\Database::connect();

        $sections = [
            [
                'section_key' => 'diagnosis',
                'huruf'       => 'VI',
                'label'       => 'Diagnosis Problem Normal Bermasalah (DSM-5-TR)',
                'icon'        => 'tabler-stethoscope',
                'color'       => '#e83e8c',
                'urutan'      => 5,
                'is_active'   => 1,
            ],
            [
                'section_key' => 'intervensi',
                'huruf'       => 'VII',
                'label'       => 'Intervensi yang Diberikan',
                'icon'        => 'tabler-bolt',
                'color'       => '#6f42c1',
                'urutan'      => 6,
                'is_active'   => 1,
            ],
            [
                'section_key' => 'rekomendasi',
                'huruf'       => 'VIII',
                'label'       => 'Rekomendasi',
                'icon'        => 'tabler-checklist',
                'color'       => '#20c997',
                'urutan'      => 7,
                'is_active'   => 1,
            ],
            [
                'section_key' => 'prognosis',
                'huruf'       => 'IX',
                'label'       => 'Prognosis (Kemungkinan Perkembangan Permasalahan Klien)',
                'icon'        => 'tabler-trending-up',
                'color'       => '#fd7e14',
                'urutan'      => 8,
                'is_active'   => 1,
            ],
        ];

        foreach ($sections as $sec) {
            $existing = $db->table('checklist_sections')
                ->where('section_key', $sec['section_key'])
                ->get()->getRowArray();
            if (! $existing) {
                $db->table('checklist_sections')->insert($sec);
            }
        }

        $items = [

            /* ═══════════════════════════════════════════════════
               VI. DIAGNOSIS DSM-5-TR
            ═══════════════════════════════════════════════════ */
            'diagnosis' => [
                'dsm5' => [
                    'label'    => 'Pilih semua yang relevan',
                    'urutan'   => 1,
                    'type'     => 'checkbox',
                    'items'    => [
                        'Masalah relasional',
                        'Masalah pendidikan dan pekerjaan',
                        'Masalah perumahan dan ekonomi',
                        'Masalah yang berkaitan dengan lingkungan sosial',
                        'Masalah yang berkaitan dengan tindak kriminal atau interaksi dengan sistem hukum',
                        'Kunjungan layanan kesehatan untuk konseling atau pemberian nasihat medis',
                        'Masalah psikososial, personal, dan lingkungan lainnya',
                        'Kondisi lain yang berkaitan dengan riwayat pribadi',
                        'Kekerasan dan penelantaran',
                        'Perilaku bunuh diri',
                        'Perilaku melukai diri (self-harm)',
                    ],
                ],
            ],

            /* ═══════════════════════════════════════════════════
               VII. INTERVENSI YANG DIBERIKAN
            ═══════════════════════════════════════════════════ */
            'intervensi' => [
                'fokus' => [
                    'label'  => 'A. Fokus Intervensi',
                    'urutan' => 1,
                    'type'   => 'checkbox',
                    'items'  => [
                        'Eksplorasi masalah',
                        'Dukungan emosional',
                        'Pengurangan distres psikologis',
                        'Pengembangan coping adaptif',
                        'Penyelesaian masalah',
                        'Pengambilan keputusan',
                        'Adaptasi akademik',
                        'Pengembangan keterampilan belajar',
                        'Pengembangan relasi interpersonal',
                        'Persiapan karier',
                        'Pencegahan kekambuhan',
                        'Manajemen krisis',
                        'Lainnya',
                    ],
                ],
                'pendekatan' => [
                    'label'  => 'B. Pendekatan Intervensi yang Dominan Digunakan',
                    'urutan' => 2,
                    'type'   => 'checkbox',
                    'items'  => [
                        'Konseling suportif',
                        'Person-Centered Counseling',
                        'Cognitive Behavioral Therapy (CBT)',
                        'Solution-Focused Brief Counseling (SFBC)',
                        'Problem-Solving Counseling',
                        'Motivational Interviewing (MI)',
                        'Acceptance and Commitment Therapy (ACT)',
                        'Strength-Based Counseling',
                        'Konseling berbasis spiritualitas atau religiusitas',
                        'Lainnya',
                    ],
                ],
                'teknik' => [
                    'label'  => 'C. Teknik Intervensi yang Digunakan',
                    'urutan' => 3,
                    'type'   => 'checkbox',
                    'items'  => [
                        'Active listening',
                        'Empati dan validasi emosi',
                        'Refleksi perasaan',
                        'Klarifikasi',
                        'Parafrase',
                        'Konfrontasi terapeutik',
                        'Psikoedukasi',
                        'Restrukturisasi kognitif',
                        'Identifikasi pikiran otomatis',
                        'Problem solving',
                        'Goal setting',
                        'Behavioral activation',
                        'Relaksasi',
                        'Mindfulness',
                        'Pelatihan regulasi emosi',
                        'Pelatihan komunikasi asertif',
                        'Manajemen waktu (time management)',
                        'Study skills coaching',
                        'Penguatan dukungan sosial',
                        'Eksplorasi makna dan nilai hidup',
                        'Perencanaan tindak lanjut',
                        'Rujukan',
                        'Lainnya',
                    ],
                ],
                'tahap' => [
                    'label'  => 'D. Fokus Tahap Intervensi pada Sesi Ini',
                    'urutan' => 4,
                    'type'   => 'radio',
                    'items'  => [
                        'Assessment/Intake',
                        'Intervensi awal',
                        'Intervensi lanjutan',
                        'Monitoring perkembangan',
                        'Terminasi',
                    ],
                ],
                'respons' => [
                    'label'  => 'E. Penilaian Respons Klien terhadap Sesi Ini',
                    'urutan' => 5,
                    'type'   => 'radio',
                    'items'  => [
                        'Sangat baik',
                        'Baik',
                        'Cukup',
                        'Minimal',
                        'Belum tampak perubahan',
                    ],
                ],
            ],

            /* ═══════════════════════════════════════════════════
               VIII. REKOMENDASI
            ═══════════════════════════════════════════════════ */
            'rekomendasi' => [
                'personal' => [
                    'label'  => 'A. Rekomendasi untuk Aspek Personal/Pribadi',
                    'urutan' => 1,
                    'type'   => 'checkbox',
                    'items'  => [
                        'Mengembangkan strategi coping adaptif',
                        'Meningkatkan kemampuan manajemen waktu dan prioritas',
                        'Meningkatkan kualitas tidur dan menerapkan gaya hidup sehat',
                        'Memperkuat dukungan sosial',
                        'Mengurangi penggunaan coping maladaptif',
                        'Mengikuti kegiatan pengembangan diri',
                        'Memanfaatkan layanan pendampingan akademik',
                        'Lainnya',
                    ],
                ],
                'akademik' => [
                    'label'  => 'B. Rekomendasi untuk Aspek Akademik',
                    'urutan' => 2,
                    'type'   => 'checkbox',
                    'items'  => [
                        'Konsultasi dengan dosen wali',
                        'Konsultasi dengan dosen pembimbing',
                        'Menyusun target akademik jangka pendek',
                        'Penyesuaian beban akademik sementara',
                        'Mengikuti kelompok belajar',
                        'Mengakses layanan akademik program studi',
                        'Lainnya',
                    ],
                ],
                'sosial' => [
                    'label'  => 'C. Rekomendasi untuk Aspek Sosial dan Keluarga',
                    'urutan' => 3,
                    'type'   => 'checkbox',
                    'items'  => [
                        'Meningkatkan komunikasi dengan keluarga',
                        'Memperluas jejaring pertemanan',
                        'Meningkatkan keterlibatan dalam komunitas atau organisasi',
                        'Mengidentifikasi sumber dukungan sosial yang tersedia',
                        'Lainnya',
                    ],
                ],
                'tindak_lanjut' => [
                    'label'  => 'D. Tindak Lanjut',
                    'urutan' => 4,
                    'type'   => 'radio',
                    'items'  => [
                        'Tidak diperlukan tindakan lanjutan',
                        'Konseling lanjutan',
                        'Rujukan ke Psikolog Klinis BKPP',
                        'Rujukan ke Psikolog Klinis RS UMS',
                        'Rujukan ke Psikiater FK UMS',
                        'Rujukan ke Layanan Kesehatan UMS (MMC)',
                        'Rujukan ke layanan profesional lainnya',
                        'Aktivasi prosedur penanganan krisis',
                        'Lainnya',
                    ],
                ],
                'status' => [
                    'label'  => 'E. Status Penanganan Klien Saat Ini',
                    'urutan' => 5,
                    'type'   => 'radio',
                    'items'  => [
                        'Selesai',
                        'Monitoring',
                        'Follow-up',
                        'Rujukan',
                        'Terminasi atas kesepakatan bersama',
                    ],
                ],
            ],

            /* ═══════════════════════════════════════════════════
               IX. PROGNOSIS
            ═══════════════════════════════════════════════════ */
            'prognosis' => [
                'hasil' => [
                    'label'  => 'Pilih satu',
                    'urutan' => 1,
                    'type'   => 'radio',
                    'items'  => [
                        'Baik',
                        'Cukup baik',
                        'Perlu pemantauan intensif',
                        'Memerlukan rujukan lanjutan',
                    ],
                ],
            ],
        ];

        foreach ($items as $sectionKey => $subsections) {
            $sec = $db->table('checklist_sections')
                ->where('section_key', $sectionKey)
                ->get()->getRowArray();
            if (! $sec) continue;
            $sectionId = $sec['id'];

            foreach ($subsections as $subKey => $grp) {
                $order = 1;
                foreach ($grp['items'] as $itemLabel) {
                    $exists = $db->table('checklist_items')
                        ->where('section_id', $sectionId)
                        ->where('subsection_key', $subKey)
                        ->where('item_label', $itemLabel)
                        ->get()->getRowArray();
                    if (! $exists) {
                        $db->table('checklist_items')->insert([
                            'section_id'        => $sectionId,
                            'subsection_key'    => $subKey,
                            'subsection_label'  => $grp['label'],
                            'subsection_urutan' => $grp['urutan'],
                            'item_label'        => $itemLabel,
                            'input_type'        => $grp['type'],
                            'item_urutan'       => $order++,
                            'is_active'         => 1,
                        ]);
                    }
                }
            }
        }
    }
}
