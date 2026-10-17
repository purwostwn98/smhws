# Mental Health Check (Skrining Publik)

Fitur skrining kesehatan mental tanpa login (anonim). Mahasiswa memilih jenis assessment, mengisi jenis kelamin, semester, dan program studi, lalu menjawab soal.

## Alur URL

| URL | Fungsi |
|---|---|
| `GET /skrining` | Menu pilih jenis assessment (`SkriningController::pilih`) |
| `GET /skrining/dass` | Form DASS-21 |
| `POST /skrining/dass/submit` | Validasi, hitung skor, simpan |
| `GET /skrining/dass/hasil` | Halaman hasil (dari session) |
| `GET /skrining/dass/hasil/pdf` | Unduh hasil PDF |

Kartu "Mental Health Check" di `app/Views/home/index.php` mengarah ke `/skrining`.

## Struktur

```
app/Config/Assessment.php                   daftar jenis assessment di menu
app/Controllers/SkriningController.php      hanya menu pilih
app/Controllers/Skrining/
  BaseSkriningController.php                data diri, prodi (tabel lembaga), cooldown, session hasil, PDF
  DassController.php                        khusus DASS-21
app/Libraries/DassDeskripsi.php             teks interpretasi + rekomendasi DASS (dipakai halaman hasil & PDF)
app/Models/SkriningPublikModel.php          simpan skor + jawaban DASS (reuse DassAssessmentModel::hitungKategori)
app/Views/skrining/
  pilih.php                                 menu
  _data_diri.php                            partial kartu data diri (dipakai semua form)
  dass/ index.php, hasil.php, pdf_content.php
app/Database/Migrations/2026-10-16-000001_CreateSkriningPublikTable.php
```

## Data

- `skrining_publik` (satu baris per pengisian DASS: data diri, skor raw/akhir, kategori) dan `skrining_publik_jawaban` (21 jawaban per pengisian). Tidak menyimpan data pribadi/IP.
- Bank soal memakai `dass_items`; ambang kategori dari `DassAssessmentModel::hitungKategori()`; skor akhir = skor raw x 2.
- Prodi diambil dari tabel `lembaga` (`id_lembaga`, `nama_prodi`, `nama_fakultas`).
- Tabel dan model `skrining_publik` milik DASS saja. Assessment lain sebaiknya punya tabel hasil sendiri dengan kolom data diri yang sama.

## Perilaku penting

- Hasil disimpan di session per jenis (`skrining_hasil_<kode>`), tidak ada ID di URL. Tanpa session, `/hasil` dan `/hasil/pdf` kembali ke form.
- Jeda 30 detik antar pengiriman per jenis (`skrining_terakhir_<kode>`).
- Waktu pada hasil/PDF memakai Asia/Jakarta (WIB). `appTimezone` aplikasi tetap UTC, jadi `created_at` di database juga UTC.
- Halaman `Home::index` masih menampilkan halaman maintenance; `home/index.php` belum aktif.

## Menambah assessment baru

1. Buat controller turunan `BaseSkriningController` di `app/Controllers/Skrining/` dan implementasikan `kodeJenis()` (lihat `DassController`).
2. Buat tabel hasil sendiri (kolom data diri sama dengan `skrining_publik`) + model.
3. Buat view di `app/Views/skrining/<kode>/` (`index`, `hasil`, `pdf_content`). Form menyertakan `<?= $this->include('skrining/_data_diri') ?>`.
4. Daftarkan di `Config\Assessment::$jenis` (`'aktif' => false` menampilkan "Segera Hadir").
5. Tambahkan route di group `skrining` pada `app/Config/Routes.php`.
