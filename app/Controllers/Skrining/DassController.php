<?php

namespace App\Controllers\Skrining;

use App\Libraries\DassDeskripsi;
use App\Models\DassItemModel;
use App\Models\SkriningPublikModel;

/**
 * Skrining DASS-21 publik. Hasil disimpan di tabel `skrining_publik` (+ `skrining_publik_jawaban`).
 */
class DassController extends BaseSkriningController
{
    protected function kodeJenis(): string
    {
        return 'dass';
    }

    public function index(): string
    {
        $items = (new DassItemModel())->orderBy('nomor')->findAll();

        $old = $this->oldDataDiri();
        foreach ($items as $it) {
            $old['dass_item_' . $it['id']] = old('dass_item_' . $it['id']);
        }

        return view('skrining/dass/index', [
            'dassItems'        => $items,
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

        $items   = (new DassItemModel())->findAll();
        $jawaban = [];
        foreach ($items as $item) {
            $v = $post['dass_item_' . $item['id']] ?? null;
            if ($v !== null && in_array((string) $v, ['0', '1', '2', '3'], true)) {
                $jawaban[(int) $item['id']] = (int) $v;
            }
        }
        if (count($jawaban) !== count($items)) {
            $errors[] = 'Semua ' . count($items) . ' pernyataan wajib dijawab.';
        }

        if ($errors) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $hasil = (new SkriningPublikModel())->simpanSkrining($dataDiri, $jawaban);
        $this->simpanHasilSession($hasil);

        return redirect()->to(base_url('skrining/dass/hasil'));
    }

    public function hasil()
    {
        $hasil = $this->hasilSession();
        if (! $hasil) {
            return redirect()->to($this->urlForm());
        }

        return view('skrining/dass/hasil', ['hasil' => $hasil]);
    }

    public function pdf()
    {
        $hasil = $this->hasilSession();
        if (! $hasil) {
            return redirect()->to($this->urlForm());
        }

        $html = view('skrining/dass/pdf_content', [
            'hasil' => $hasil,
            'desk'  => DassDeskripsi::susun($hasil),
        ]);

        $this->kirimPdf('Hasil Mental Health Check (DASS-21)', $html, 'hasil-dass-21.pdf');
    }
}
