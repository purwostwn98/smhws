<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ChecklistSeeder extends Seeder
{
    public function run(): void
    {
        $db = \Config\Database::connect();

        $sections = [
            ['section_key' => 'stressor',   'huruf' => 'B', 'label' => 'Stressor Saat Ini (Current Stressor)',      'icon' => 'tabler-flame',           'color' => '#1a5f7a', 'urutan' => 1],
            ['section_key' => 'kerentanan', 'huruf' => 'C', 'label' => 'Faktor Kerentanan (Diathesis/Vulnerability)', 'icon' => 'tabler-alert-triangle',  'color' => '#dc3545', 'urutan' => 2],
            ['section_key' => 'protektif',  'huruf' => 'D', 'label' => 'Faktor Protektif',                            'icon' => 'tabler-shield-check',    'color' => '#2d9b6e', 'urutan' => 3],
            ['section_key' => 'koping',     'huruf' => 'E', 'label' => 'Strategi Koping yang Digunakan Klien',        'icon' => 'tabler-arrows-exchange', 'color' => '#f0a500', 'urutan' => 4],
        ];

        foreach ($sections as $sec) {
            $db->table('checklist_sections')->insert($sec);
        }

        $secIds = [];
        $rows = $db->table('checklist_sections')->get()->getResultArray();
        foreach ($rows as $r) {
            $secIds[$r['section_key']] = $r['id'];
        }

        $items = [

            /* ── B. STRESSOR ─────────────────────────────────────────────── */
            ['section_key' => 'stressor', 'subsection_key' => 'akademik', 'subsection_label' => '1. Akademik', 'subsection_urutan' => 1, 'items' => [
                'Beban tugas kuliah yang tinggi',
                'Tuntutan IPK atau prestasi akademik',
                'Kesulitan mengikuti ritme perkuliahan',
                'Kesulitan menyelesaikan skripsi/tugas akhir',
                'Ketidakjelasan arah studi atau minat akademik',
                'Kegagalan akademik (nilai rendah, mengulang mata kuliah)',
                'Kesulitan mengelola waktu',
                'Tuntutan organisasi dan akademik secara bersamaan',
                'Kecemasan menghadapi ujian atau presentasi',
                'Kekhawatiran mengenai kelulusan, masa studi, atau Drop Out (DO)',
            ]],
            ['section_key' => 'stressor', 'subsection_key' => 'karier', 'subsection_label' => '2. Karier dan Masa Depan', 'subsection_urutan' => 2, 'items' => [
                'Kecemasan mengenai pekerjaan setelah lulus',
                'Kebingungan menentukan pilihan karier',
                'Tekanan untuk segera sukses atau mapan',
                'Perbandingan pencapaian dengan teman sebaya',
                'Kecemasan memasuki dunia kerja',
                'Ketidakpastian masa depan',
            ]],
            ['section_key' => 'stressor', 'subsection_key' => 'keluarga', 'subsection_label' => '3. Keluarga', 'subsection_urutan' => 3, 'items' => [
                'Konflik dengan orang tua atau saudara',
                'Harapan keluarga yang tinggi',
                'Masalah ekonomi keluarga',
                'Perceraian atau konflik keluarga',
                'Tanggung jawab membantu keluarga',
                'Menjadi penopang ekonomi keluarga',
                'Menjalani peran ganda sebagai mahasiswa dan pasangan/menikah',
            ]],
            ['section_key' => 'stressor', 'subsection_key' => 'relasi_sosial', 'subsection_label' => '4. Relasi Sosial', 'subsection_urutan' => 4, 'items' => [
                'Kesulitan membangun pertemanan',
                'Konflik dengan teman atau teman satu kelompok',
                'Kesepian atau isolasi sosial',
                'Kesulitan beradaptasi dengan lingkungan kampus',
                'Bullying atau cyberbullying',
                'Konflik dalam organisasi kemahasiswaan',
                'Kesulitan berhubungan dengan dosen mata kuliah atau dosen pembimbing skripsi/tugas akhir',
            ]],
            ['section_key' => 'stressor', 'subsection_key' => 'relasi_romantis', 'subsection_label' => '5. Relasi Romantis', 'subsection_urutan' => 5, 'items' => [
                'Putus hubungan',
                'Konflik dengan pasangan',
                'Hubungan yang tidak sehat (toxic relationship)',
                'Kesulitan membangun hubungan romantis/intim',
                'Hubungan romantis dengan sesama jenis',
            ]],
            ['section_key' => 'stressor', 'subsection_key' => 'finansial', 'subsection_label' => '6. Finansial', 'subsection_urutan' => 6, 'items' => [
                'Kesulitan biaya kuliah',
                'Kesulitan memenuhi biaya hidup sehari-hari',
                'Harus bekerja sambil kuliah',
                'Kekhawatiran terhadap kondisi ekonomi keluarga',
            ]],
            ['section_key' => 'stressor', 'subsection_key' => 'digital', 'subsection_label' => '7. Digital dan Media Sosial', 'subsection_urutan' => 7, 'items' => [
                'Fear of Missing Out (FOMO)',
                'Tekanan akibat perbandingan sosial di media sosial',
                'Paparan berita negatif yang berlebihan',
                'Konflik atau tekanan di media sosial',
                'Penggunaan media sosial yang berlebihan',
                'Gangguan konsentrasi akibat penggunaan gawai',
            ]],
            ['section_key' => 'stressor', 'subsection_key' => 'kesehatan', 'subsection_label' => '8. Kesehatan', 'subsection_urutan' => 8, 'items' => [
                'Gangguan tidur',
                'Penyakit fisik',
                'Kelelahan kronis',
                'Kurangnya aktivitas fisik',
                'Pola makan tidak sehat',
            ]],

            /* ── C. KERENTANAN ───────────────────────────────────────────── */
            ['section_key' => 'kerentanan', 'subsection_key' => 'biologis', 'subsection_label' => '1. Biologis', 'subsection_urutan' => 1, 'items' => [
                'Riwayat gangguan psikologis dalam keluarga',
                'Gangguan tidur kronis',
                'Penyakit kronis',
                'Sensitivitas fisiologis terhadap stres',
                'Riwayat penggunaan zat atau obat tertentu',
            ]],
            ['section_key' => 'kerentanan', 'subsection_key' => 'psikologis', 'subsection_label' => '2. Psikologis', 'subsection_urutan' => 2, 'items' => [
                'Perfeksionisme',
                'Self-criticism yang tinggi',
                'Harga diri rendah',
                'Self-efficacy rendah',
                'Kesulitan regulasi emosi',
                'Intoleransi terhadap ketidakpastian',
                'Kecenderungan overthinking',
                'Kecenderungan catastrophizing',
                'Ketergantungan pada validasi eksternal',
                'Sensitivitas terhadap penolakan sosial',
                'Kesulitan mengambil keputusan',
                'Riwayat trauma atau pengalaman negatif di masa lalu',
                'Kesulitan beradaptasi dengan perubahan',
            ]],
            ['section_key' => 'kerentanan', 'subsection_key' => 'sosial', 'subsection_label' => '3. Sosial dan Lingkungan', 'subsection_urutan' => 3, 'items' => [
                'Dukungan sosial terbatas',
                'Kesulitan membangun relasi interpersonal',
                'Konflik keluarga berkepanjangan',
                'Pengalaman bullying atau diskriminasi',
                'Tinggal jauh dari keluarga',
                'Kesulitan beradaptasi dengan budaya atau lingkungan baru',
                'Tekanan norma sosial atau ekspektasi keluarga',
                'Kurangnya akses terhadap bantuan profesional',
                'Academic belonging yang rendah (minim interaksi positif dengan sivitas akademika)',
            ]],

            /* ── D. PROTEKTIF ────────────────────────────────────────────── */
            ['section_key' => 'protektif', 'subsection_key' => 'personal', 'subsection_label' => '1. Personal (Pribadi)', 'subsection_urutan' => 1, 'items' => [
                'Self-efficacy yang baik',
                'Resiliensi yang baik (self-resiliency)',
                'Kemampuan problem solving',
                'Regulasi emosi yang baik',
                'Optimisme',
                'Fleksibilitas psikologis',
                'Growth mindset',
                'Kemampuan manajemen waktu',
                'Kemampuan mencari bantuan saat diperlukan',
                'Memiliki tujuan hidup yang jelas',
            ]],
            ['section_key' => 'protektif', 'subsection_key' => 'sosial', 'subsection_label' => '2. Sosial', 'subsection_urutan' => 2, 'items' => [
                'Dukungan keluarga',
                'Dukungan teman sebaya',
                'Hubungan positif dengan dosen, mentor, atau asisten perkuliahan',
                'Keterlibatan dalam organisasi atau komunitas',
                'Memiliki figur panutan (role model)',
            ]],
            ['section_key' => 'protektif', 'subsection_key' => 'spiritual', 'subsection_label' => '3. Spiritual dan Nilai Hidup', 'subsection_urutan' => 3, 'items' => [
                'Religiusitas atau spiritualitas',
                'Aktivitas ibadah yang membantu coping',
                'Makna hidup yang kuat',
                'Nilai pribadi yang jelas',
            ]],
            ['section_key' => 'protektif', 'subsection_key' => 'gaya_hidup', 'subsection_label' => '4. Gaya Hidup', 'subsection_urutan' => 4, 'items' => [
                'Tidur yang cukup',
                'Aktivitas fisik atau olahraga secara teratur',
                'Memiliki hobi atau aktivitas rekreatif',
                'Penggunaan media digital yang sehat',
                'Keseimbangan antara akademik dan kehidupan pribadi',
            ]],
            ['section_key' => 'protektif', 'subsection_key' => 'akademik', 'subsection_label' => '5. Dukungan Akademik', 'subsection_urutan' => 5, 'items' => [
                'Hubungan positif dengan dosen pembimbing akademik atau pembimbing skripsi/tugas akhir',
                'Memiliki dosen, mentor, asisten mata kuliah, atau figur akademik yang suportif',
                'Mendapatkan umpan balik yang konstruktif dari dosen',
                'Memiliki kelompok belajar yang mendukung',
                'Memiliki strategi belajar yang efektif',
                'Memiliki akses terhadap sumber belajar yang memadai',
                'Memiliki sense of belonging terhadap program studi atau kampus',
                'Merasa dihargai dan diterima di lingkungan kampus',
            ]],
            ['section_key' => 'protektif', 'subsection_key' => 'akses', 'subsection_label' => '6. Akses Bantuan', 'subsection_urutan' => 6, 'items' => [
                'Akses terhadap layanan konseling',
                'Dukungan akademik dari program studi',
                'Dukungan finansial atau beasiswa',
                'Kemudahan mengakses layanan kesehatan',
            ]],

            /* ── E. KOPING ───────────────────────────────────────────────── */
            ['section_key' => 'koping', 'subsection_key' => 'adaptif', 'subsection_label' => '1. Koping Adaptif', 'subsection_urutan' => 1, 'items' => [
                'Mencari dukungan sosial yang sehat dan positif',
                'Berkonsultasi dengan dosen, mentor, atau asisten perkuliahan',
                'Menggunakan layanan konseling',
                'Problem solving',
                'Membuat perencanaan dan prioritas',
                'Restrukturisasi kognitif (positive reframing)',
                'Aktivitas religius atau spiritual',
                'Aktivitas fisik atau olahraga',
                'Menulis jurnal atau refleksi diri',
                'Teknik relaksasi atau mindfulness',
                'Mengembangkan keterampilan baru',
                'Mencari sumber belajar tambahan secara mandiri',
            ]],
            ['section_key' => 'koping', 'subsection_key' => 'maladaptif', 'subsection_label' => '2. Koping Maladaptif', 'subsection_urutan' => 2, 'items' => [
                'Menunda pekerjaan (procrastination)',
                'Menghindari masalah',
                'Menarik diri dari lingkungan sosial maupun aktivitas akademik',
                'Doomscrolling di media sosial',
                'Bermain game secara berlebihan',
                'Penggunaan media sosial secara kompulsif',
                'Tidur berlebihan',
                'Makan berlebihan atau kehilangan nafsu makan',
                'Menyalahkan diri sendiri secara berlebihan',
                'Ledakan emosi atau agresivitas',
                'Penggunaan rokok, alkohol, atau zat lainnya',
                'Self-harm atau perilaku berisiko lainnya',
            ]],
            ['section_key' => 'koping', 'subsection_key' => 'pola', 'subsection_label' => '3. Ringkasan Pola Koping Klien', 'subsection_urutan' => 3, 'items' => [
                'Didominasi koping adaptif',
                'Didominasi koping maladaptif',
                'Campuran koping adaptif dan maladaptif',
            ], 'input_type' => 'radio'],
        ];

        $now = date('Y-m-d H:i:s');
        foreach ($items as $group) {
            $sectionId   = $secIds[$group['section_key']];
            $inputType   = $group['input_type'] ?? 'checkbox';
            foreach ($group['items'] as $order => $label) {
                $db->table('checklist_items')->insert([
                    'section_id'        => $sectionId,
                    'subsection_key'    => $group['subsection_key'],
                    'subsection_label'  => $group['subsection_label'],
                    'subsection_urutan' => $group['subsection_urutan'],
                    'item_label'        => $label,
                    'input_type'        => $inputType,
                    'item_urutan'       => $order + 1,
                    'is_active'         => 1,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);
            }
        }
    }
}
