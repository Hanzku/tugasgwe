<?php
declare(strict_types=1);
require __DIR__ . '/includes/app.php';
require __DIR__ . '/includes/layout.php';

$data = find_pelajaran(text_input($_GET, 'kode'));
if (!$data) {
    http_response_code(404);
    page_start('Pelajaran tidak ditemukan');
    echo '<section class="panel empty"><p>Data tidak tersedia atau sudah dihapus.</p><a class="button" href="pelajaran.php">Kembali ke Daftar</a></section>';
    page_end();
    exit;
}
page_start('Detail Pelajaran', 'Detail');
?>
<section class="panel form-panel">
  <div class="panel-heading"><h2>Informasi Pelajaran</h2><p>Data master mata pelajaran yang tersimpan.</p></div>
  <dl class="detail-list">
    <div><dt>Kode Pelajaran</dt><dd class="code"><?= e($data['kd_pelajaran']) ?></dd></div>
    <div><dt>Nama Pelajaran</dt><dd><?= e($data['nama_pelajaran']) ?></dd></div>
    <div><dt>Bobot (%)</dt><dd><?= e(bobot_label($data['bobot'])) ?></dd></div>
  </dl>
  <div class="form-actions"><a class="button" href="pelajaran.php">Kembali</a><a class="button primary" href="form_pelajaran.php?kode=<?= e(rawurlencode($data['kd_pelajaran'])) ?>">Edit</a></div>
</section>
<?php page_end(); ?>