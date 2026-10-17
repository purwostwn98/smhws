<?php

namespace App\Models;

use CodeIgniter\Model;

class LearnKontenModel extends Model
{
    protected $table      = 'learn_konten';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'tipe', 'judul', 'slug', 'ringkasan', 'topik_id', 'thumbnail', 'isi',
        'youtube_id', 'file', 'link_eksternal', 'penulis',
        'status', 'diterbitkan_at', 'dilihat',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public const TIPE = [
        'artikel' => 'Artikel',
        'video'   => 'Video',
        'poster'  => 'Poster & Infografis',
        'media'   => 'Media Belajar',
        'tanya_jawab' => 'Tanya Jawab',
    ];

    /** Tipe yang dibuat lewat form SMHWS Learn (tanya_jawab berasal dari Ask Our Psychologist). */
    public const TIPE_MANUAL = ['artikel', 'video', 'poster', 'media'];

    public const TIPE_ICON = [
        'artikel' => 'tabler-article',
        'video'   => 'tabler-brand-youtube',
        'poster'  => 'tabler-photo',
        'media'   => 'tabler-file-text',
        'tanya_jawab' => 'tabler-message-question',
    ];

    /** Pilih kolom konten + nama topik. */
    public function denganTopik(): static
    {
        return $this->select('learn_konten.*, t.nama AS topik_nama, t.slug AS topik_slug')
            ->join('learn_topik t', 't.id = learn_konten.topik_id', 'left');
    }

    /** Konten terbit dengan filter (dipakai halaman publik, lanjutkan dengan paginate()). */
    public function cari(?string $tipe, ?int $topikId, ?string $q): static
    {
        $this->denganTopik()->where('learn_konten.status', 'terbit');
        if ($tipe && isset(self::TIPE[$tipe])) $this->where('learn_konten.tipe', $tipe);
        if ($topikId)                          $this->where('learn_konten.topik_id', $topikId);
        if ($q !== null && $q !== '') {
            $this->groupStart()->like('learn_konten.judul', $q)->orLike('learn_konten.ringkasan', $q)->groupEnd();
        }
        return $this->orderBy('learn_konten.diterbitkan_at', 'DESC');
    }

    public function bySlugTerbit(string $slug): ?array
    {
        return $this->denganTopik()
            ->where('learn_konten.slug', $slug)
            ->where('learn_konten.status', 'terbit')
            ->first();
    }

    public function terkait(array $konten, int $limit = 4): array
    {
        $this->denganTopik()
            ->where('learn_konten.status', 'terbit')
            ->where('learn_konten.id !=', $konten['id']);
        if ($konten['topik_id']) {
            $this->where('learn_konten.topik_id', $konten['topik_id']);
        } else {
            $this->where('learn_konten.tipe', $konten['tipe']);
        }
        return $this->orderBy('learn_konten.diterbitkan_at', 'DESC')->findAll($limit);
    }

    public function tambahDilihat(int $id): void
    {
        $this->builder()->set('dilihat', 'dilihat + 1', false)->where('id', $id)->update();
    }

    /** Slug unik dari judul. */
    public function buatSlug(string $judul, ?int $kecualiId = null): string
    {
        helper('url');
        $base = url_title($judul, '-', true) ?: 'konten';
        $base = substr($base, 0, 250);
        $slug = $base;
        for ($i = 2; ; $i++) {
            $b = $this->builder()->where('slug', $slug);
            if ($kecualiId) $b->where('id !=', $kecualiId);
            if ($b->countAllResults() === 0) return $slug;
            $slug = $base . '-' . $i;
        }
    }

    /** URL gambar untuk kartu: thumbnail → poster → thumbnail YouTube. */
    public static function gambarKartu(array $k): ?string
    {
        if (! empty($k['thumbnail']))                       return base_url($k['thumbnail']);
        if ($k['tipe'] === 'poster' && ! empty($k['file'])) return base_url($k['file']);
        if ($k['tipe'] === 'video' && ! empty($k['youtube_id'])) {
            return 'https://img.youtube.com/vi/' . rawurlencode($k['youtube_id']) . '/hqdefault.jpg';
        }
        return null;
    }

    /** Ekstrak ID video dari URL YouTube (watch, youtu.be, shorts, embed) atau ID langsung. */
    public static function youtubeId(string $url): ?string
    {
        $url = trim($url);
        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $url)) return $url;
        $pola = '~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~i';
        return preg_match($pola, $url, $m) ? $m[1] : null;
    }
}
