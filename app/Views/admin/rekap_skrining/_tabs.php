<?php
/** Pilihan jenis assessment pada rekap skrining. Variabel: $jenisAktif */
$jenisAktif = $jenisAktif ?? 'dass';
$tabs = [
    'dass'     => ['DASS-21', 'admin/rekap-skrining', 'tabler-brain'],
    'internet' => ['Pola Penggunaan Internet', 'admin/rekap-skrining/internet', 'tabler-world-www'],
];
?>
<ul class="nav nav-pills mb-4 gap-1">
  <?php foreach ($tabs as $kode => [$label, $url, $icon]): ?>
    <li class="nav-item">
      <a class="nav-link <?= $jenisAktif === $kode ? 'active' : '' ?>" href="<?= base_url($url) ?>"><i class="ti <?= $icon ?> me-1"></i><?= $label ?></a>
    </li>
  <?php endforeach ?>
</ul>
