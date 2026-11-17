<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\BaseConnection;

/**
 * Impor data layanan konseling dari sistem lama (CSV hasil olahan file Excel petugas SMHWS).
 *
 *   php spark smhws:impor-lama writable/impor/konseling_lama.csv            → simulasi (tidak menyimpan)
 *   php spark smhws:impor-lama writable/impor/konseling_lama.csv --simpan   → impor sungguhan
 *   php spark smhws:impor-lama --batal=<batch>                              → hapus semua data satu batch impor
 *   php spark smhws:impor-lama --isi-stressor [--simpan]                    → estimasi stressor dari keluhan (data impor)
 *   php spark smhws:impor-lama --hapus-stressor                             → kosongkan lagi stressor hasil estimasi
 *
 * Setiap baris yang dibuat dicatat di tabel impor_data_lama sehingga dapat dibatalkan.
 * Baris yang tidak dapat diimpor ditulis ke writable/impor/ditolak_<waktu>.csv.
 */
class ImporKonselingLama extends BaseCommand
{
    protected $group       = 'SMHWS';
    protected $name        = 'smhws:impor-lama';
    protected $description = 'Impor data layanan konseling dari sistem lama (CSV).';
    protected $usage       = 'smhws:impor-lama <file.csv> [--simpan] | --batal=<batch> | --isi-stressor [--simpan] | --hapus-stressor';

    /** Penanda pada JSON stressor bahwa isinya hasil estimasi dari teks keluhan, bukan asesmen psikolog. */
    public const STRESSOR_ESTIMASI = 'estimasi_keluhan_data_lama';

    /** Kata kunci di teks keluhan → stressor (satu sesi boleh lebih dari satu). */
    private const STRESSOR_KW = [
        'akademik'        => '/kuliah|skripsi|tesis|akademik|\bipk\b|tugas|ujian|\bdosen|belajar|praktikum|\bkkn\b|\bpkl\b|koas|magang|\bstudi|\bnilai\b/u',
        'karier'          => '/karier|karir|pekerjaan|\bbekerja|masa depan|\blulus|wisuda|\bprofesi\b/u',
        'keluarga'        => '/keluarga|orang ?tua|\bortu\b|\bayah|\bibu|\bbapak|\bmama\b|\bpapa\b|\badik|\bkakak|saudara|\bcerai|perceraian|\bkdrt/u',
        'relasi_sosial'   => '/teman|pertemanan|sahabat|perundungan|\bbully|kesepian|sosialisasi|bergaul|pergaulan|lingkungan sosial|organisasi|rekan/u',
        'relasi_romantis' => '/pacar|percintaan|\bputus\b|pasangan|mantan|asmara|lawan jenis|pernikahan|menikah|\bsuami|\bistri|tunangan|\bldr\b/u',
        'finansial'       => '/biaya|\buang\b|ekonomi|finansial|keuangan|\bukt\b|hutang|utang|beasiswa|sambil kuliah|nafkah/u',
        'digital'         => '/media sosial|sosmed|medsos|instagram|tiktok|twitter|\bgame|internet|\bhp\b|gadget|smartphone|scroll|\bchat/u', // "online" tidak dipakai: umumnya berarti kuliah daring
        'kesehatan'       => '/sakit|penyakit|\btidur|insomnia|\bmedis|dokter|skoliosis|haid|menstruasi|operasi|dirawat|psikiater|\bobat\b|gangguan makan|nafsu makan/u',
    ];

    private const TEMA = [
        'akademik' => 'akademik', 'keorganisasian' => 'keorganisasian', 'pengembangan diri' => 'pengembangan_diri',
        'relasi' => 'relasi', 'pribadi' => 'pribadi', 'keluarga' => 'keluarga', 'lainnya' => 'lainnya',
    ];

    /** Diagnosis lama (dinormalisasi) → label DSM-5 di sistem. */
    private const DSM5 = [
        'kondisi lain yang berkaitan dengan riwayat pribadi' => 'Kondisi lain yang berkaitan dengan riwayat pribadi',
        'masalah relasional' => 'Masalah relasional',
        'masalah pendidikan dan pekerjaan' => 'Masalah pendidikan dan pekerjaan',
        'masalah lain yang berkaitan dengan lingkungan sosial' => 'Masalah yang berkaitan dengan lingkungan sosial',
        'masalah yang berkaitan dengan lingkungan sosial' => 'Masalah yang berkaitan dengan lingkungan sosial',
        'kekerasan dan penelantaran' => 'Kekerasan dan penelantaran',
        'perilaku melukai diri' => 'Perilaku melukai diri (self-harm)',
        'perilaku bunuh diri' => 'Perilaku bunuh diri',
        'masalah perumahan dan ekonomi' => 'Masalah perumahan dan ekonomi',
        'masalah yang berkaitan dengan tindak kriminal atau interaksi dengan sistem hukum' => 'Masalah yang berkaitan dengan tindak kriminal atau interaksi dengan sistem hukum',
        'masalah psikososial, personal, dan lingkungan lainnya' => 'Masalah psikososial, personal, dan lingkungan lainnya',
        'kunjungan layanan kesehatan lainnya untuk konseling dan pemberian nasihat medis' => 'Kunjungan layanan kesehatan untuk konseling atau pemberian nasihat medis',
    ];

    private const STATUS_PENANGANAN = ['Selesai', 'Monitoring', 'Konseling Lanjutan', 'Rujukan', 'Terminasi atas kesepakatan bersama', 'Lainnya'];

    private BaseConnection $db;
    private string $batch = '';

    public function run(array $params)
    {
        $this->db = \Config\Database::connect();

        if ($batal = CLI::getOption('batal')) {
            $this->batal((string) $batal);
            return;
        }
        if (CLI::getOption('isi-stressor') !== null) {
            $this->isiStressor(CLI::getOption('simpan') !== null);
            return;
        }
        if (CLI::getOption('hapus-stressor') !== null) {
            $n = $this->db->table('hasil_konseling')
                ->where("JSON_UNQUOTE(JSON_EXTRACT(stressor, '$._sumber')) =", self::STRESSOR_ESTIMASI)
                ->set('stressor', null)->update();
            CLI::write('Stressor hasil estimasi dikosongkan: ' . $this->db->affectedRows() . ' sesi.', 'green');
            return;
        }

        $file = $params[0] ?? null;
        if ($file && ! is_file($file) && is_file(ROOTPATH . $file)) {
            $file = ROOTPATH . $file; // spark berjalan dari folder public/, path relatif dihitung dari root proyek
        }
        if (! $file || ! is_file($file)) {
            CLI::error('File CSV tidak ditemukan. ' . $this->usage);
            return;
        }
        $simpan = CLI::getOption('simpan') !== null;

        [$rows, $ditolak] = $this->bacaDanValidasi($file);
        $this->tandaiSesi($rows);

        $lembaga  = $this->petaLembaga();
        $konselor = $this->petaKonselor();
        $users    = $this->petaUsers();
        $mhs      = array_change_key_case(array_flip(array_map('strtolower', array_column(
            $this->db->table('mahasiswa')->select('nim')->get()->getResultArray(), 'nim'))));

        // ── Ringkasan rencana ──
        $nimBaru = $psiBaru = $tanpaProdi = [];
        $tanpaHasil = 0;
        foreach ($rows as &$r) {
            $r['lembaga'] = $this->cariProdi($r['nim'], $r['prodi'], $lembaga);
            if (! $r['lembaga']) $tanpaProdi[strtoupper($r['nim'])] = $r['prodi'];
            if (! isset($users[strtolower($r['nim'])])) $nimBaru[strtolower($r['nim'])] = true;
            $r['psi_kunci'] = $this->kunciNama($r['psikolog']);
            if ($r['psi_kunci'] === '') $tanpaHasil++;
            elseif (! isset($konselor[$r['psi_kunci']])) $psiBaru[$r['psi_kunci']] = $r['psikolog'];
        }
        unset($r);

        CLI::write('Baris di file           : ' . (count($rows) + count($ditolak)));
        CLI::write('Siap diimpor (sesi)     : ' . count($rows), 'green');
        CLI::write('Tidak dapat diimpor     : ' . count($ditolak), count($ditolak) ? 'red' : 'green');
        CLI::write('Akun mahasiswa baru     : ' . count($nimBaru));
        CLI::write('NIM tanpa prodi dikenal : ' . count($tanpaProdi) . ' (tetap masuk dashboard universitas)');
        CLI::write('Psikolog arsip baru     : ' . count($psiBaru) . ($psiBaru ? ' (' . implode('; ', $psiBaru) . ')' : ''));
        CLI::write('Sesi tanpa psikolog     : ' . $tanpaHasil . ' (diimpor tanpa data hasil konseling)');

        $laporan = $this->tulisDitolak($ditolak);
        if ($laporan) CLI::write('Laporan baris ditolak   : ' . $laporan, 'yellow');

        if (! $simpan) {
            CLI::write(PHP_EOL . 'SIMULASI selesai — belum ada data yang disimpan. Tambahkan --simpan untuk mengimpor.', 'yellow');
            return;
        }

        if ($this->db->table('impor_data_lama')->where('tabel', 'janji')->countAllResults() > 0
            && CLI::getOption('ulang') === null) {
            CLI::error('Sudah ada data impor sebelumnya. Batalkan dulu (--batal=<batch>) atau tambahkan --ulang bila sengaja.');
            return;
        }

        $this->batch = 'lama-' . date('YmdHis');
        $this->db->transException(true)->transStart();
        try {
            foreach ($psiBaru as $kunci => $nama) {
                $konselor[$kunci] = $this->buatKonselorArsip($nama);
            }
            $n = 0;
            $userLama = $users; // akun yang sudah ada sebelum batch ini
            foreach ($rows as $r) {
                $nimL = strtolower($r['nim']);
                if (! isset($users[$nimL])) {
                    $users[$nimL] = $this->buatUser($r);
                }
                // Impor susulan: nomor sesi ikut menghitung sesi mahasiswa yang sudah ada di sistem
                if (isset($userLama[$nimL])) {
                    $r['sesi_ke'] += $this->db->table('janji')
                        ->where('user_id', $userLama[$nimL])
                        ->where('tanggal_konseling <', $r['tanggal'])
                        ->where('deleted_at', null)
                        ->countAllResults();
                }
                if (! isset($mhs[$nimL])) {
                    $this->buatMahasiswa($r);
                    $mhs[$nimL] = true;
                }
                $kid = $r['psi_kunci'] !== '' ? ($konselor[$r['psi_kunci']] ?? null) : null;
                $janjiId = $this->buatJanji($r, (int) $users[$nimL], $kid);
                if ($kid) $this->buatHasil($r, $janjiId, $kid);
                if (++$n % 250 === 0) CLI::write("  {$n} sesi...");
            }
            $this->db->transComplete();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            CLI::error('Impor dibatalkan, tidak ada data yang tersimpan: ' . $e->getMessage());
            return;
        }

        CLI::write(PHP_EOL . "IMPOR SELESAI: {$n} sesi tersimpan. Batch: {$this->batch}", 'green');
        CLI::write("Untuk membatalkan: php spark smhws:impor-lama --batal={$this->batch}");
    }

    // ══ Baca & validasi ═══════════════════════════════════════════════════

    private function bacaDanValidasi(string $file): array
    {
        $fh = fopen($file, 'r');
        $head = fgetcsv($fh);
        $rows = $ditolak = [];
        while (($v = fgetcsv($fh)) !== false) {
            if (count($v) !== count($head)) continue;
            $r = array_map('trim', array_combine($head, $v));
            $alasan = [];

            if (! preg_match('/^[A-Za-z][A-Za-z0-9]{6,14}$/', $r['nim'])) $alasan[] = 'NIM kosong/tidak valid';
            if ($r['nama'] === '') $alasan[] = 'Nama mahasiswa kosong';
            $tgl = \DateTime::createFromFormat('!Y-m-d', $r['tanggal']);
            if (! $tgl || $tgl->format('Y-m-d') !== $r['tanggal']) $alasan[] = 'Tanggal tidak valid ("' . $r['tanggal'] . '")';
            if (! in_array(strtolower($r['jk']), ['laki-laki', 'perempuan'], true)) $alasan[] = 'Jenis kelamin tidak valid';
            if (! ctype_digit($r['semester']) || (int) $r['semester'] < 1 || (int) $r['semester'] > 14) $alasan[] = 'Semester kosong/tidak valid';
            if (! isset(self::TEMA[strtolower($r['tema'])])) $alasan[] = 'Tema konseling kosong/tidak valid';

            if ($alasan) {
                $ditolak[] = $r + ['alasan' => implode('; ', $alasan)];
            } else {
                $rows[] = $r;
            }
        }
        fclose($fh);
        return [$rows, $ditolak];
    }

    /** Nomor sesi per mahasiswa (urut tanggal) untuk penanda "pernah konseling". */
    private function tandaiSesi(array &$rows): void
    {
        $per = [];
        foreach ($rows as $i => $r) $per[strtolower($r['nim'])][] = $i;
        foreach ($per as $idx) {
            usort($idx, fn($a, $b) => [$rows[$a]['tanggal'], (int) $rows[$a]['baris']] <=> [$rows[$b]['tanggal'], (int) $rows[$b]['baris']]);
            foreach ($idx as $k => $i) $rows[$i]['sesi_ke'] = $k + 1;
        }
    }

    private function tulisDitolak(array $ditolak): ?string
    {
        if (! $ditolak) return null;
        $path = WRITEPATH . 'impor/ditolak_' . date('Ymd_His') . '.csv';
        $fh = fopen($path, 'w');
        fwrite($fh, "\xEF\xBB\xBF");
        fputcsv($fh, ['Baris Excel', 'No', 'NIM', 'Nama Mahasiswa', 'Hari dan Tanggal', 'Alasan']);
        foreach ($ditolak as $d) fputcsv($fh, [$d['baris'], $d['no'], $d['nim'], $d['nama'], $d['tanggal'], $d['alasan']]);
        fclose($fh);
        return $path;
    }

    // ══ Pemetaan ═══════════════════════════════════════════════════════════

    private function petaLembaga(): array
    {
        $out = ['fid' => [], 'nama' => []];
        foreach ($this->db->table('lembaga')->get()->getResultArray() as $l) {
            $out['fid'][strtoupper($l['fid'])] = $l;
            $out['nama'][$this->normProdi($l['nama_prodi'])] = $l;
        }
        return $out;
    }

    private function cariProdi(string $nim, string $prodi, array $lembaga): ?array
    {
        return $lembaga['fid'][strtoupper(substr($nim, 0, 4))]
            ?? $lembaga['nama'][$this->normProdi($prodi)]
            ?? null;
    }

    private function normProdi(string $s): string
    {
        $s = strtolower(preg_replace('/\s+/', ' ', $s));
        $s = preg_replace('/^(program studi|prodi|s-?\d|d-?\d)\s+/', '', trim($s));
        return trim(preg_replace('/\(.*?\)/', '', $s));
    }

    /** Kunci nama psikolog: nama tanpa gelar, huruf kecil. */
    private function kunciNama(string $nama): string
    {
        $n = strtolower(trim(explode(',', $nama)[0] ?? ''));
        $n = preg_replace('/^((dr|dra|drs|prof)\.?\s+)+/', '', $n);
        return trim(preg_replace('/\s+/', ' ', rtrim($n, '. ')));
    }

    private function petaKonselor(): array
    {
        $out = [];
        foreach ($this->db->table('konselor k')->select('k.id, u.name')->join('users u', 'u.id = k.user_id')
                     ->where('k.deleted_at', null)->get()->getResultArray() as $k) {
            $out[$this->kunciNama($k['name'])] = (int) $k['id'];
        }
        return $out;
    }

    private function petaUsers(): array
    {
        $out = [];
        foreach ($this->db->table('users')->select('id, uniid')->where('uniid IS NOT NULL')->get()->getResultArray() as $u) {
            $out[strtolower($u['uniid'])] = (int) $u['id'];
        }
        return $out;
    }

    // ══ Penyimpanan ═══════════════════════════════════════════════════════

    private function catat(string $tabel, $refId, ?string $ket = null): void
    {
        $this->db->table('impor_data_lama')->insert([
            'batch' => $this->batch, 'tabel' => $tabel, 'ref_id' => (string) $refId,
            'keterangan' => $ket, 'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /** Psikolog periode lama yang belum ada di sistem: akun nonaktif (tidak bisa login, tidak muncul di pilihan). */
    private function buatKonselorArsip(string $namaLengkap): int
    {
        $bagian = array_map('trim', explode(',', $namaLengkap));
        $nama   = array_shift($bagian);
        $depan  = preg_match('/^((?:Dr|Dra|Drs|Prof)\.?\s+)+/i', $nama, $m) ? trim($m[0]) : null;
        $nama   = trim(rtrim(substr($nama, strlen($m[0] ?? '')), '. '));
        $gelar  = implode(', ', array_unique(array_filter($bagian)));
        $now    = date('Y-m-d H:i:s');

        $email = preg_replace('/[^a-z0-9]+/', '.', strtolower($nama)) . '.arsip@smhws.ums.ac.id';
        $this->db->table('users')->insert([
            'name' => $nama, 'email' => $email, 'password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
            'role' => 'konselor', 'is_active' => 0, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $userId = (int) $this->db->insertID();
        $this->catat('users', $userId, 'psikolog arsip: ' . $nama);

        $this->db->table('konselor')->insert([
            'user_id' => $userId, 'gelar_depan' => $depan, 'gelar_belakang' => $gelar ?: null,
            'bio' => 'Psikolog SMHWS periode sebelumnya (data impor sistem lama).',
            'is_available' => 0, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $id = (int) $this->db->insertID();
        $this->catat('konselor', $id, $nama);
        return $id;
    }

    private function buatUser(array $r): int
    {
        $nimL  = strtolower($r['nim']);
        $email = $nimL . '@student.ums.ac.id';
        if ($this->db->table('users')->where('email', $email)->countAllResults() > 0) {
            $email = $nimL . '.lama@student.ums.ac.id';
        }
        $now = date('Y-m-d H:i:s');
        $this->db->table('users')->insert([
            'name' => $r['nama'], 'email' => $email,
            'password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
            'role' => 'mahasiswa', 'uniid' => strtoupper($r['nim']),
            'fakultas' => $r['lembaga']['nama_fakultas'] ?? null, 'prodi' => $r['lembaga']['nama_prodi'] ?? ($r['prodi'] ?: null),
            'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $id = (int) $this->db->insertID();
        $this->catat('users', $id, $r['nim']);
        return $id;
    }

    private function buatMahasiswa(array $r): void
    {
        $nimL = strtolower($r['nim']);
        $angkatan = preg_match('/^[a-z][a-z0-9]{3}(\d{2})/', $nimL, $m) ? 2000 + (int) $m[1] : null;
        $now = date('Y-m-d H:i:s');
        $this->db->table('mahasiswa')->insert([
            'nim' => $nimL, 'nama' => $r['nama'], 'email' => $nimL . '@student.ums.ac.id',
            'program_studi' => $r['lembaga']['id_lembaga'] ?? null,
            'angkatan' => ($angkatan && $angkatan <= (int) date('Y')) ? $angkatan : null,
            'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->catat('mahasiswa', $nimL);
    }

    private function buatJanji(array $r, int $userId, ?int $konselorId): int
    {
        $waktu  = $r['tanggal'] . ' 08:00:00';
        $metode = strtolower($r['metode']);
        $catatan = 'Data impor sistem lama — ID Klien ' . ($r['id_klien'] ?: '-') . ', sesi ke-' . $r['sesi_ke']
            . (! $konselorId && $r['psikolog'] !== '' ? ', psikolog: ' . $r['psikolog'] : '') . '.';

        $this->db->table('janji')->insert([
            'user_id' => $userId, 'konselor_id' => $konselorId,
            'jenis_kelamin' => strtolower($r['jk']), 'usia' => 0, 'agama' => 'Tidak diketahui',
            'semester' => (int) $r['semester'],
            'pernah_konseling_smhws' => $r['sesi_ke'] > 1 ? 1 : 0,
            'metode' => in_array($metode, ['offline', 'online'], true) ? $metode : 'offline',
            'keluhan_utama' => $r['masalah'] !== '' ? $r['masalah'] : '-',
            'tema_konseling' => self::TEMA[strtolower($r['tema'])],
            'status' => 'selesai', 'tanggal_konseling' => $r['tanggal'],
            'catatan_admin' => $catatan, 'created_at' => $waktu, 'updated_at' => $waktu,
        ]);
        $id = (int) $this->db->insertID();
        $this->catat('janji', $id, $r['nim'] . ' baris ' . $r['baris']);
        return $id;
    }

    private function buatHasil(array $r, int $janjiId, int $konselorId): void
    {
        $dsm5 = $lain = [];
        foreach (explode(';', $r['diagnosis']) as $d) {
            $k = strtolower(preg_replace('/\s+/', ' ', trim(preg_replace('/\d+$/', '', trim($d)))));
            if ($k === '') continue;
            if (isset(self::DSM5[$k])) $dsm5[] = self::DSM5[$k];
            elseif ($k === 'indikasi klinis') $lain[] = 'Indikasi Klinis';
        }

        $fokus = [];
        $fokusLain = '';
        foreach (explode(';', $r['fokus']) as $f) {
            $f = trim(preg_replace('/\d+$/', '', trim($f)));
            if ($f === '' || preg_match('/^D\d+$/i', $f)) continue;
            if (stripos($f, 'Lainnya') === 0) { $fokus[] = 'Lainnya'; $fokusLain = trim(substr($f, 8), ': '); continue; }
            $fokus[] = $f;
        }

        // Sistem lama memakai "Follow-up" untuk yang kini bernama "Konseling Lanjutan"
        $status = $r['status'] === 'Follow-up' ? 'Konseling Lanjutan' : $r['status'];
        $status = in_array($status, self::STATUS_PENANGANAN, true) ? $status : null;
        $waktu  = $r['tanggal'] . ' 08:00:00';

        $this->db->table('hasil_konseling')->insert([
            'janji_id' => $janjiId, 'konselor_id' => $konselorId,
            'ada_rujukan' => $status === 'Rujukan' ? 1 : 0,
            'sesi_lanjutan' => $status === 'Konseling Lanjutan' ? 1 : 0,
            'diagnosis' => json_encode(['dsm5' => array_values(array_unique($dsm5)), 'dsm5_lainnya' => implode(', ', array_unique($lain))], JSON_UNESCAPED_UNICODE),
            'intervensi' => json_encode(['fokus' => array_values(array_unique($fokus)), 'fokus_lainnya' => $fokusLain], JSON_UNESCAPED_UNICODE),
            'rekomendasi' => json_encode(['status' => $status], JSON_UNESCAPED_UNICODE),
            'created_at' => $waktu, 'updated_at' => $waktu,
        ]);
        $this->catat('hasil_konseling', (int) $this->db->insertID());
    }

    // ══ Estimasi stressor ══════════════════════════════════════════════════

    /**
     * Isi stressor sesi impor (yang stressor-nya masih kosong) dari teks keluhan utama.
     * Ditandai dengan "_sumber" agar dapat dibedakan dari asesmen psikolog dan dapat dihapus.
     */
    private function isiStressor(bool $simpan): void
    {
        $rows = $this->db->table('hasil_konseling h')
            ->select('h.id, j.keluhan_utama')
            ->join('janji j', 'j.id = h.janji_id')
            ->join('impor_data_lama i', "i.tabel = 'hasil_konseling' AND i.ref_id = h.id")
            ->where('h.stressor', null)
            ->get()->getResultArray();

        $hitung = array_fill_keys(array_keys(self::STRESSOR_KW), 0);
        $contoh = [];
        $update = [];
        $tanpa  = 0;
        foreach ($rows as $r) {
            $teks = mb_strtolower((string) $r['keluhan_utama']);
            $data = [];
            foreach (self::STRESSOR_KW as $key => $rx) {
                if (preg_match($rx, $teks)) {
                    $data[$key] = ['Estimasi dari keluhan (data sistem lama)'];
                    $hitung[$key]++;
                    if (count($contoh[$key] ?? []) < 3) $contoh[$key][] = mb_strimwidth($r['keluhan_utama'], 0, 90, '…');
                }
            }
            if (! $data) { $tanpa++; continue; }
            $data['_sumber'] = self::STRESSOR_ESTIMASI;
            $update[] = ['id' => $r['id'], 'stressor' => json_encode($data, JSON_UNESCAPED_UNICODE)];
        }

        CLI::write('Sesi impor dengan stressor kosong : ' . count($rows));
        CLI::write('Mendapat minimal satu stressor    : ' . count($update), 'green');
        CLI::write('Tidak ada petunjuk (tetap kosong) : ' . $tanpa);
        foreach ($hitung as $k => $n) {
            CLI::write(str_pad($k, 16) . ': ' . str_pad((string) $n, 5, ' ', STR_PAD_LEFT));
            foreach ($contoh[$k] ?? [] as $c) CLI::write('      - ' . $c, 'light_gray');
        }

        if (! $simpan) {
            CLI::write(PHP_EOL . 'PRATINJAU — belum disimpan. Tambahkan --simpan untuk menyimpan.', 'yellow');
            return;
        }
        foreach (array_chunk($update, 500) as $chunk) {
            $this->db->table('hasil_konseling')->updateBatch($chunk, 'id');
        }
        CLI::write('Tersimpan: ' . count($update) . ' sesi.', 'green');
    }

    // ══ Pembatalan ═════════════════════════════════════════════════════════

    private function batal(string $batch): void
    {
        $log = $this->db->table('impor_data_lama')->where('batch', $batch)->get()->getResultArray();
        if (! $log) {
            CLI::error("Batch {$batch} tidak ditemukan.");
            return;
        }
        $per = [];
        foreach ($log as $l) $per[$l['tabel']][] = $l['ref_id'];

        $this->db->transException(true)->transStart();
        // Urutan: anak dulu, induk terakhir
        foreach (['hasil_konseling' => 'id', 'janji' => 'id', 'mahasiswa' => 'nim', 'konselor' => 'id', 'users' => 'id'] as $tabel => $pk) {
            foreach (array_chunk($per[$tabel] ?? [], 500) as $ids) {
                $this->db->table($tabel)->whereIn($pk, $ids)->delete();
            }
            CLI::write(str_pad($tabel, 16) . ': ' . count($per[$tabel] ?? []) . ' baris dihapus');
        }
        $this->db->table('impor_data_lama')->where('batch', $batch)->delete();
        $this->db->transComplete();
        CLI::write("Batch {$batch} dibatalkan.", 'green');
    }
}
