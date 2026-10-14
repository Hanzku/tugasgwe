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
page_start('Hapus Pelajaran', 'Konfirmasi Hapus');
?>
<section class="panel form-panel">
  <div class="panel-heading"><h2>Konfirmasi Penghapusan</h2><p>Tindakan ini tidak dapat dibatalkan.</p></div>
  <div class="confirm-body"><p>Hapus pelajaran <strong><?= e($data['kd_pelajaran']) ?></strong> — <?= e($data['nama_pelajaran']) ?> dengan bobot <?= e(bobot_label($data['bobot'])) ?>?</p><p class="muted">Pelajaran yang masih dirujuk oleh relasi database tidak dapat dihapus.</p></div>
  <form method="post" action="proses_pelajaran.php" data-delete-form>
    <input type="hidden" name="csrf" value="<?= e(token()) ?>">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="original_code" value="<?= e($data['kd_pelajaran']) ?>">
    <div class="form-actions"><button class="button danger" type="submit">Ya, Hapus Data</button><a class="button" href="pelajaran.php">Batal</a></div>
  </form>
</section>
<?php page_end(); ?>