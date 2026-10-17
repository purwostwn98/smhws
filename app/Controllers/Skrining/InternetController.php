<?php

namespace App\Controllers\Skrining;

use App\Libraries\PolaInternet;
use App\Models\SkriningInternetModel;

/**
 * Skrining Pola Penggunaan Internet publik (18 butir, skala 0–5).
 * Hasil disimpan di tabel `skrining_internet` (+ `skrining_internet_jawaban`).
 */
class InternetController extends BaseSkriningController
{
    protected function kodeJenis(): string
    {
        return 'internet';
    }

    public function index(): string
    {
        $old = $this->oldDataDiri();
        foreach (array_keys(PolaInternet::BUTIR) as $no) {
            $old['butir_' . $no] = old('butir_' . $no);
        }

        return view('skrining/internet/index', [
            'prodiPerFakultas' => $this->prodiPerFakultas(),
            'old'              => $old,
        ]);
    }

    public function submit()
    {
        if ($this->masihCooldown()) {
            return redirect()->back()->withInput()
                ->with('error', 'Mohon tunggu sebentar sebelum mengirim skrining lagi.');
        }

        $post = $this->request->getPost();
        [$dataDiri, $errors] = $this->validasiDataDiri($post);

        $nilaiValid = array_map('strval', array_keys(PolaInternet::SKALA));
        $jawaban    = [];
        foreach (array_keys(PolaInternet::BUTIR) as $no) {
            $v = $post['butir_' . $no] ?? null;
            if ($v !== null && in_array((string) $v, $nilaiValid, true)) {
                $jawaban[$no] = (int) $v;
            }
        }
        if (count($jawaban) !== count(PolaInternet::BUTIR)) {
            $errors[] = 'Semua ' . count(PolaInternet::BUTIR) . ' pertanyaan wajib dijawab.';
        }

        if ($errors) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $hasil = (new SkriningInternetModel())->simpanSkrining($dataDiri, $jawaban);
        $this->simpanHasilSession($hasil);

        return redirect()->to(base_url('skrining/internet/hasil'));
    }

    public function hasil()
    {
        $hasil = $this->hasilSession();
        if (! $hasil) {
            return redirect()->to($this->urlForm());
        }

        return view('skrining/internet/hasil', ['hasil' => $hasil]);
    }

    public function pdf()
    {
        $hasil = $this->hasilSession();
        if (! $hasil) {
            return redirect()->to($this->urlForm());
        }

        $html = view('skrining/internet/pdf_content', ['hasil' => $hasil]);

        $this->kirimPdf('Hasil Mental Health Check (Pola Penggunaan Internet)', $html, 'hasil-pola-penggunaan-internet.pdf');
    }
}
