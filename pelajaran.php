<?php
declare(strict_types=1);
require __DIR__ . '/includes/app.php';
require __DIR__ . '/includes/layout.php';

$q = substr(text_input($_GET, 'q'), 0, 400);
$sort = text_input($_GET, 'sort');
$sort = in_array($sort, ['kd_pelajaran', 'nama_pelajaran', 'bobot'], true) ? $sort : 'kd_pelajaran';
$direction = text_input($_GET, 'dir') === 'desc' ? 'desc' : 'asc';
$page = max(1, min(1000000, (int) text_input($_GET, 'page')));
$perPage = 10;
$params = [];
$where = '';
if ($q !== '') {
    $where = " WHERE kd_pelajaran LIKE ? ESCAPE '=' OR nama_pelajaran LIKE ? ESCAPE '='";
    $pattern = '%' . strtr($q, ['=' => '==', '%' => '=%', '_' => '=_']) . '%';
    $params = [$pattern, $pattern];
}
$count = db()->prepare('SELECT COUNT(*) FROM pelajaran' . $where);
$count->execute($params);
$total = (int) $count->fetchColumn();
$pages = max(1, (int) ceil($total / $perPage));
$page = min($page, $pages);
$offset = ($page - 1) * $perPage;
// Only whitelisted identifiers and locally calculated integers enter the SQL string.
$query = db()->prepare('SELECT kd_pelajaran, nama_pelajaran, bobot FROM pelajaran' . $where . " ORDER BY $sort $direction, kd_pelajaran ASC LIMIT $perPage OFFSET $offset");
$query->execute($params);
$rows = $query->fetchAll();

function list_url(array $changes = []): string
{
    global $q, $sort, $direction, $page;
    return 'pelajaran.php?' . http_build_query(array_merge(['q' => $q, 'sort' => $sort, 'dir' => $direction, 'page' => $page], $changes));
}

page_start('Data Pelajaran');
?>
<div class="toolbar">
  <a class="button primary" href="form_pelajaran.php"><span aria-hidden="true">+</span> Tambah Data</a>
  <span class="muted">Kode unik · Nama pelajaran · Bobot</span>
</div>
<section class="panel" aria-label="Daftar pelajaran">
  <form method="get" class="filters" action="pelajaran.php">
    <div class="search-field"><label class="sr-only" for="q">Cari kode atau nama pelajaran</label><input id="q" name="q" value="<?= e($q) ?>" placeholder="Cari kode atau nama pelajaran..." maxlength="100"></div>
    <label class="sr-only" for="sort">Urutkan berdasarkan</label>
    <select id="sort" name="sort" aria-label="Urutkan berdasarkan">
      <?php foreach (['kd_pelajaran' => 'Kode pelajaran', 'nama_pelajaran' => 'Nama pelajaran', 'bobot' => 'Bobot'] as $key => $label): ?><option value="<?= e($key) ?>" <?= $sort === $key ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
    </select>
    <label class="sr-only" for="dir">Arah urutan</label><select id="dir" name="dir"><option value="asc" <?= $direction === 'asc' ? 'selected' : '' ?>>Menaik</option><option value="desc" <?= $direction === 'desc' ? 'selected' : '' ?>>Menurun</option></select>
    <button class="button" type="submit">Terapkan</button>
    <?php if ($q !== ''): ?><a href="pelajaran.php" class="text-link">Reset</a><?php endif; ?>
  </form>
  <div class="table-scroll" tabindex="0" role="region" aria-label="Tabel pelajaran; geser untuk melihat seluruh kolom">
    <table>
      <thead><tr>
        <?php foreach (['kd_pelajaran' => 'Kode Pelajaran', 'nama_pelajaran' => 'Nama Pelajaran', 'bobot' => 'Bobot'] as $key => $label): ?>
          <th scope="col" <?= $sort === $key ? 'aria-sort="' . ($direction === 'asc' ? 'ascending' : 'descending') . '"' : '' ?>><a href="<?= e(list_url(['sort' => $key, 'dir' => $sort === $key && $direction === 'asc' ? 'desc' : 'asc', 'page' => 1])) ?>"><?= e($label) ?><?= $sort === $key ? ($direction === 'asc' ? ' ↑' : ' ↓') : '' ?></a></th>
        <?php endforeach; ?>
        <th scope="col">Operasi</th>
      </tr></thead>
      <tbody>
      <?php foreach ($rows as $row): $code = rawurlencode($row['kd_pelajaran']); ?>
        <tr>
          <td class="code"><?= e($row['kd_pelajaran']) ?></td><td><?= e($row['nama_pelajaran']) ?></td><td class="weight"><?= e(bobot_label($row['bobot'])) ?></td>
          <td><div class="row-actions"><a href="detail_pelajaran.php?kode=<?= e($code) ?>" aria-label="Lihat <?= e($row['kd_pelajaran']) ?>">Lihat</a><a href="form_pelajaran.php?kode=<?= e($code) ?>" aria-label="Edit <?= e($row['kd_pelajaran']) ?>">Edit</a><a class="danger-link" href="hapus_pelajaran.php?kode=<?= e($code) ?>" aria-label="Hapus <?= e($row['kd_pelajaran']) ?>">Hapus</a></div></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="4"><div class="empty"><h2><?= $q !== '' ? 'Tidak ada hasil pencarian' : 'Belum ada data pelajaran' ?></h2><p><?= $q !== '' ? 'Coba kode atau nama pelajaran yang berbeda.' : 'Tambahkan pelajaran pertama untuk memulai.' ?></p><a class="button primary" href="<?= $q !== '' ? 'pelajaran.php' : 'form_pelajaran.php' ?>"><?= $q !== '' ? 'Tampilkan Semua' : 'Tambah Data' ?></a></div></td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="table-footer"><span>Menampilkan <?= $total ? $offset + 1 : 0 ?>–<?= min($offset + $perPage, $total) ?> dari <?= $total ?> pelajaran<?= $q !== '' ? ' sesuai pencarian' : '' ?></span>
    <nav class="pagination" aria-label="Halaman daftar">
      <?php if ($page > 1): ?><a class="button" href="<?= e(list_url(['page' => $page - 1])) ?>" aria-label="Halaman sebelumnya">&larr;</a><?php endif; ?>
      <span>Halaman <?= $page ?> / <?= $pages ?></span>
      <?php if ($page < $pages): ?><a class="button" href="<?= e(list_url(['page' => $page + 1])) ?>" aria-label="Halaman berikutnya">&rarr;</a><?php endif; ?>
    </nav>
  </div>
</section>
<p class="footnote">Data dengan kode DEMO dan nama bertanda (Demo) adalah contoh, bukan data resmi sekolah.</p>
<?php page_end(); ?>