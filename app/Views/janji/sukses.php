<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Pendaftaran Berhasil<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="card text-center">
      <div class="card-body py-5">
        <div class="avatar avatar-xl mx-auto mb-3">
          <div class="avatar-initial rounded-circle"
            style="background:linear-gradient(135deg,#2d9b6e,#57c5b6);color:#fff;font-size:2rem;">
            <i class="ti tabler-circle-check"></i>
          </div>
        </div>
        <h4 class="fw-bold mb-2" style="color:#1a2b40;">Pendaftaran Berhasil!</h4>
        <p class="text-muted mb-4">
          Terima kasih, <strong><?= esc(session()->get('user_name')) ?></strong>.<br />
          Formulir pendaftaran konselingmu sudah kami terima dengan nomor
          <strong class="text-primary">#<?= str_pad($janji['id'], 5, '0', STR_PAD_LEFT) ?></strong>.
        </p>

        <div class="d-flex flex-column gap-2 text-start mb-4 mx-auto" style="max-width:360px;">
          <div class="d-flex justify-content-between py-2 border-bottom">
            <span class="text-muted" style="font-size:.85rem;">Nomor Janji</span>
            <strong>#<?= str_pad($janji['id'], 5, '0', STR_PAD_LEFT) ?></strong>
          </div>
          <div class="d-flex justify-content-between py-2 border-bottom">
            <span class="text-muted" style="font-size:.85rem;">Metode</span>
            <span class="badge bg-label-primary"><?= ucfirst($janji['metode']) ?></span>
          </div>
          <div class="d-flex justify-content-between py-2 border-bottom">
            <span class="text-muted" style="font-size:.85rem;">Status</span>
            <span class="badge bg-label-warning">Menunggu Konfirmasi</span>
          </div>
          <div class="d-flex justify-content-between py-2">
            <span class="text-muted" style="font-size:.85rem;">Tanggal Daftar</span>
            <span><?= date('d M Y, H:i', strtotime($janji['created_at'])) ?></span>
          </div>
        </div>

        <div class="p-3 rounded-3 mb-4 smhws-emergency text-start">
          <div class="d-flex gap-2 align-items-start">
            <i class="ti tabler-info-circle mt-1" style="color:#f0a500;"></i>
            <div style="font-size:.82rem;">
              Tim konselor SMHWS akan menghubungimu melalui <strong>WhatsApp</strong> untuk konfirmasi
              jadwal konseling. Mohon pastikan nomor WA-mu aktif.
            </div>
          </div>
        </div>

        <div class="d-flex gap-3 justify-content-center flex-wrap">
          <a href="<?= base_url('janji') ?>" class="btn btn-primary">
            <i class="ti tabler-list me-1"></i>Lihat Janji Saya
          </a>
          <a href="<?= base_url('dashboard') ?>" class="btn btn-label-primary">
            <i class="ti tabler-smart-home me-1"></i>Dashboard
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
