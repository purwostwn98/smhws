<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * 21 pernyataan DASS-21 versi Indonesia.
 * Subscale: D=Depresi, A=Anxiety, S=Stres
 *
 * Mapping item → subscale (standar DASS-21):
 *   D: 3, 5, 10, 13, 16, 17, 21
 *   A: 2, 4,  7,  9, 15, 19, 20
 *   S: 1, 6,  8, 11, 12, 14, 18
 */
class DassItemsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['nomor' =>  1, 'subscale' => 'S', 'pernyataan' => 'Saya merasa bahwa diri saya menjadi marah karena hal-hal sepele.'],
            ['nomor' =>  2, 'subscale' => 'A', 'pernyataan' => 'Saya merasa mulut saya sering kering.'],
            ['nomor' =>  3, 'subscale' => 'D', 'pernyataan' => 'Saya sama sekali tidak dapat merasakan perasaan positif.'],
            ['nomor' =>  4, 'subscale' => 'A', 'pernyataan' => 'Saya mengalami kesulitan bernafas (misalnya: sering kali terengah-engah atau tidak dapat bernafas padahal tidak melakukan aktivitas fisik sebelumnya).'],
            ['nomor' =>  5, 'subscale' => 'D', 'pernyataan' => 'Saya sepertinya tidak kuat lagi untuk melakukan suatu kegiatan.'],
            ['nomor' =>  6, 'subscale' => 'S', 'pernyataan' => 'Saya cenderung bereaksi berlebihan terhadap suatu situasi.'],
            ['nomor' =>  7, 'subscale' => 'A', 'pernyataan' => 'Saya merasa gemetar (misalnya: pada tangan).'],
            ['nomor' =>  8, 'subscale' => 'S', 'pernyataan' => 'Saya merasa telah menghabiskan banyak energi disaat merasa cemas.'],
            ['nomor' =>  9, 'subscale' => 'A', 'pernyataan' => 'Saya merasa khawatir dengan situasi dimana saya mungkin menjadi panik dan mempermalukan diri sendiri.'],
            ['nomor' => 10, 'subscale' => 'D', 'pernyataan' => 'Saya merasa tidak ada hal yang dapat diharapkan di masa depan.'],
            ['nomor' => 11, 'subscale' => 'S', 'pernyataan' => 'Saya sedang merasa gelisah.'],
            ['nomor' => 12, 'subscale' => 'S', 'pernyataan' => 'Saya merasa sulit untuk bersantai.'],
            ['nomor' => 13, 'subscale' => 'D', 'pernyataan' => 'Saya merasa sedih dan tertekan.'],
            ['nomor' => 14, 'subscale' => 'S', 'pernyataan' => 'Saya sulit untuk sabar dalam menghadapi gangguan terhadap hal yang sedang saya lakukan.'],
            ['nomor' => 15, 'subscale' => 'A', 'pernyataan' => 'Saya merasa saya hampir panik.'],
            ['nomor' => 16, 'subscale' => 'D', 'pernyataan' => 'Saya tidak merasa antusias dalam hal apapun.'],
            ['nomor' => 17, 'subscale' => 'D', 'pernyataan' => 'Saya merasa bahwa saya tidak berharga sebagai seorang manusia.'],
            ['nomor' => 18, 'subscale' => 'S', 'pernyataan' => 'Saya merasa bahwa saya mudah tersinggung.'],
            ['nomor' => 19, 'subscale' => 'A', 'pernyataan' => 'Saya menyadari perubahan detak jantung, walaupun tidak sehabis melakukan aktivitas fisik (misalnya: merasa detak jantung meningkat atau melemah).'],
            ['nomor' => 20, 'subscale' => 'A', 'pernyataan' => 'Saya merasa takut tanpa alasan yang jelas.'],
            ['nomor' => 21, 'subscale' => 'D', 'pernyataan' => 'Saya merasa bahwa hidup tidak bermanfaat.'],
        ];

        $this->db->table('dass_items')->insertBatch($items);

        echo "DassItemsSeeder: 21 item DASS-21 berhasil disimpan.\n";
    }
}
