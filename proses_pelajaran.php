<?php
declare(strict_types=1);
require __DIR__ . '/includes/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Operasi ini hanya menerima POST.');
}
verify_token();
$action = text_input($_POST, 'action');
if (!in_array($action, ['add', 'edit', 'delete'], true)) {
    http_response_code(400);
    exit('Operasi tidak dikenal.');
}
$original = text_input($_POST, 'original_code');
if ($action !== 'add' && !find_pelajaran($original)) {
    flash('error', 'Pelajaran tidak ditemukan atau sudah dihapus.');
    go('pelajaran.php');
}

if ($action === 'delete') {
    try {
        $query = db()->prepare('DELETE FROM pelajaran WHERE kd_pelajaran = ?');
        $query->execute([$original]);
        flash($query->rowCount() ? 'success' : 'error', $query->rowCount() ? 'Pelajaran berhasil dihapus.' : 'Pelajaran sudah dihapus oleh permintaan lain.');
    } catch (PDOException $error) {
        error_log((string) $error);
        flash('error', (int) ($error->errorInfo[1] ?? 0) === 1451
            ? 'Pelajaran tidak dapat dihapus karena masih digunakan oleh data lain (misalnya Mempelajari atau Diampu).'
            : 'Pelajaran gagal dihapus. Silakan coba lagi atau periksa log server.');
    }
    go('pelajaran.php');
}

[$data, $errors] = validate_pelajaran($_POST);
if ($action === 'edit' && $data['kd_pelajaran'] !== $original) {
    $errors['kd_pelajaran'] = 'Kode adalah identitas tetap. Gunakan kode asli saat mengedit.';
    $data['kd_pelajaran'] = $original;
}
if (!$errors) {
    try {
        if ($action === 'add') {
            $query = db()->prepare('INSERT INTO pelajaran (kd_pelajaran, nama_pelajaran, bobot) VALUES (?, ?, ?)');
            $query->execute([$data['kd_pelajaran'], $data['nama_pelajaran'], $data['bobot']]);
        } else {
            // Lock the row so a concurrent deletion cannot produce a false success message.
            db()->beginTransaction();
            $lock = db()->prepare('SELECT kd_pelajaran FROM pelajaran WHERE kd_pelajaran = ? FOR UPDATE');
            $lock->execute([$original]);
            if (!$lock->fetch()) {
                db()->rollBack();
                flash('error', 'Pelajaran sudah dihapus oleh permintaan lain.');
                go('pelajaran.php');
            }
            $query = db()->prepare('UPDATE pelajaran SET nama_pelajaran = ?, bobot = ? WHERE kd_pelajaran = ?');
            $query->execute([$data['nama_pelajaran'], $data['bobot'], $original]);
            db()->commit();
        }
        unset($_SESSION['form']);
        flash('success', $action === 'add' ? 'Pelajaran berhasil ditambahkan.' : 'Perubahan pelajaran berhasil disimpan.');
        go('detail_pelajaran.php?kode=' . rawurlencode($data['kd_pelajaran']));
    } catch (PDOException $error) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        error_log((string) $error);
        if ((int) ($error->errorInfo[1] ?? 0) === 1062) {
            $errors['kd_pelajaran'] = 'Kode pelajaran sudah digunakan. Pilih kode yang berbeda.';
        } else {
            $errors['nama_pelajaran'] = 'Data gagal disimpan. Coba lagi atau periksa log server.';
        }
    }
}
$_SESSION['form'] = ['code' => $action === 'edit' ? $original : '', 'data' => $data, 'errors' => $errors];
go('form_pelajaran.php' . ($action === 'edit' ? '?kode=' . rawurlencode($original) : ''));