<?php
declare(strict_types=1);
require __DIR__ . '/includes/app.php';
require __DIR__ . '/includes/layout.php';

$code = text_input($_GET, 'kode');
$editing = $code !== '';
$data = $editing ? find_pelajaran($code) : ['kd_pelajaran' => '', 'nama_pelajaran' => '', 'bobot' => ''];
if ($data === false) {
    http_response_code(404);
    page_start('Pelajaran tidak ditemukan');
    echo '<div class="panel empty"><p>Data tidak tersedia atau sudah dihapus.</p><a class="button" href="pelajaran.php">Kembali ke Daftar</a></div>';
    page_end();
    exit;
}
$errors = [];
$previous = $_SESSION['form'] ?? null;
unset($_SESSION['form']);
if ($previous && $previous['code'] === $code) {
    $data = $previous['data'];
    $errors = $previous['errors'];
}
page_start($editing ? 'Edit Pelajaran' : 'Tambah Pelajaran', $editing ? 'Edit Data' : 'Tambah Data');
?>
<section class="panel form-panel">
  <div class="panel-heading"><h2>Informasi Pelajaran</h2><p>Semua kolom wajib diisi. Bobot tiap pelajaran tidak harus berjumlah total 100%.</p></div>
  <?php if ($errors): ?><div class="form-summary alert error" role="alert">Data belum disimpan. Periksa kolom yang ditandai.</div><?php endif; ?>
  <form action="proses_pelajaran.php" method="post">
    <input type="hidden" name="csrf" value="<?= e(token()) ?>">
    <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
    <?php if ($editing): ?><input type="hidden" name="original_code" value="<?= e($code) ?>"><?php endif; ?>
    <?php foreach (['kd_pelajaran' => 'Kode Pelajaran', 'nama_pelajaran' => 'Nama Pelajaran', 'bobot' => 'Bobot (%)'] as $key => $label): ?>
    <div class="form-row">
      <label for="<?= e($key) ?>"><?= e($label) ?></label>
      <div class="field">
        <?php if ($key === 'kd_pelajaran'): ?>
          <input id="kd_pelajaran" name="kd_pelajaran" value="<?= e($data[$key]) ?>" placeholder="Contoh: TKJ-03" maxlength="20" pattern="[A-Za-z0-9_\-]{1,20}" required <?= $editing ? 'readonly' : '' ?> aria-describedby="kd_pelajaran-help" <?= isset($errors[$key]) ? 'aria-invalid="true"' : '' ?>>
        <?php elseif ($key === 'nama_pelajaran'): ?>
          <input id="nama_pelajaran" name="nama_pelajaran" value="<?= e($data[$key]) ?>" placeholder="Masukkan nama pelajaran" maxlength="100" required aria-describedby="nama_pelajaran-help" <?= isset($errors[$key]) ? 'aria-invalid="true"' : '' ?>>
        <?php else: ?>
          <input id="bobot" name="bobot" type="number" value="<?= e($data[$key]) ?>" placeholder="1 - 100" min="1" max="100" step="0.01" required aria-describedby="bobot-help" <?= isset($errors[$key]) ? 'aria-invalid="true"' : '' ?>>
        <?php endif; ?>
        <div id="<?= e($key) ?>-help" class="help <?= isset($errors[$key]) ? 'field-error' : '' ?>"><?= e($errors[$key] ?? match ($key) { 'kd_pelajaran' => $editing ? 'Kode merupakan identitas tetap dan tidak dapat diubah.' : 'Maksimal 20 karakter. Kode otomatis disimpan dalam huruf kapital.', 'nama_pelajaran' => 'Maksimal 100 karakter.', default => 'Rentang 1 sampai 100, maksimal dua angka desimal.' }) ?></div>
      </div>
    </div>
    <?php endforeach; ?>
    <div class="form-actions"><button type="submit" class="button primary">Simpan Data</button><a class="button" href="<?= $editing ? 'detail_pelajaran.php?kode=' . e(rawurlencode($code)) : 'pelajaran.php' ?>">Batal</a></div>
  </form>
</section>
<?php page_end(); ?>