<?php

namespace App\Controllers;

use App\Models\JanjiModel;
use App\Models\FeedbackKonselingModel;
use App\Models\KonselorModel;

class FeedbackController extends BaseController
{
    private function authCheck(): ?\CodeIgniter\HTTP\RedirectResponse
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to(base_url('login'))
                ->with('error', 'Silakan masuk terlebih dahulu.');
        }
        return null;
    }

    /** GET /feedback/:janji_id */
    public function buat(int $janjiId): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if ($r = $this->authCheck()) return $r;

        $userId     = session()->get('user_id');
        $janjiModel = new JanjiModel();
        $janji      = $janjiModel->find($janjiId);

        if (! $janji || $janji['user_id'] != $userId) {
            return redirect()->to(base_url('janji'))
                ->with('error', 'Janji tidak ditemukan.');
        }

        if ($janji['status'] !== 'selesai') {
            return redirect()->to(base_url('janji/' . $janjiId))
                ->with('error', 'Feedback hanya dapat diberikan setelah sesi selesai.');
        }

        $feedbackModel = new FeedbackKonselingModel();
        if ($feedbackModel->byJanji($janjiId)) {
            return redirect()->to(base_url('janji/' . $janjiId))
                ->with('info', 'Kamu sudah memberikan feedback untuk sesi ini.');
        }

        $konselorNama = null;
        if (! empty($janji['konselor_id'])) {
            $konselorModel = new KonselorModel();
            $k = $konselorModel
                ->select('konselor.id, users.name, konselor.gelar_depan, konselor.gelar_belakang')
                ->join('users', 'users.id = konselor.user_id')
                ->find($janji['konselor_id']);
            if ($k) $konselorNama = KonselorModel::namaLengkap($k);
        }

        return view('feedback/buat', [
            'janji'        => $janji,
            'konselorNama' => $konselorNama,
        ]);
    }

    /** POST /feedback/simpan/:janji_id */
    public function simpan(int $janjiId): \CodeIgniter\HTTP\RedirectResponse
    {
        if ($r = $this->authCheck()) return $r;

        $userId     = session()->get('user_id');
        $janjiModel = new JanjiModel();
        $janji      = $janjiModel->find($janjiId);

        if (! $janji || $janji['user_id'] != $userId || $janji['status'] !== 'selesai') {
            return redirect()->to(base_url('janji'))
                ->with('error', 'Tidak dapat memberikan feedback saat ini.');
        }

        $feedbackModel = new FeedbackKonselingModel();
        if ($feedbackModel->byJanji($janjiId)) {
            return redirect()->to(base_url('janji/' . $janjiId))
                ->with('info', 'Feedback sudah pernah diberikan.');
        }

        $rating = (int) $this->request->getPost('rating');
        if ($rating < 1 || $rating > 5) {
            return redirect()->back()
                ->with('error', 'Rating harus antara 1–5.');
        }

        $feedbackModel->insert([
            'janji_id' => $janjiId,
            'user_id'  => $userId,
            'rating'   => $rating,
            'komentar' => $this->request->getPost('komentar') ?? null,
        ]);

        return redirect()->to(base_url('janji/' . $janjiId))
            ->with('success', 'Terima kasih atas feedback-mu! Ini membantu kami terus berkembang.');
    }
}
