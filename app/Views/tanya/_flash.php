<?php
$errors = session()->getFlashdata('errors') ?? [];
if ($e = session()->getFlashdata('error')) $errors[] = $e;
?>
<?php if ($msg = session()->getFlashdata('success')): ?>
  <div class="alert alert-success alert-dismissible mb-4" role="alert">
    <i class="ti tabler-circle-check me-2"></i><?= esc($msg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>
<?php if ($errors): ?>
  <div class="alert alert-danger alert-dismissible mb-4" role="alert">
    <ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= esc($err) ?></li><?php endforeach ?></ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>
