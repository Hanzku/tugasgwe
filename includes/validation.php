<?php
declare(strict_types=1);

function text_input(array $input, string $key): string
{
    return is_string($input[$key] ?? null) ? trim($input[$key]) : '';
}

function validate_pelajaran(array $input): array
{
    $data = [
        'kd_pelajaran' => strtoupper(text_input($input, 'kd_pelajaran')),
        'nama_pelajaran' => text_input($input, 'nama_pelajaran'),
        'bobot' => text_input($input, 'bobot'),
    ];
    $errors = [];
    if (!preg_match('/\A[A-Z0-9_-]{1,20}\z/', $data['kd_pelajaran'])) {
        $errors['kd_pelajaran'] = 'Kode wajib diisi, maksimal 20 karakter: huruf, angka, tanda hubung, atau garis bawah.';
    }
    $name = $data['nama_pelajaran'];
    $length = preg_match_all('/./us', $name);
    if ($length === false || $length > 100 || preg_match('/\A\s*\z/u', $name) || preg_match('/[\x00-\x1F\x7F]/', $name)) {
        $errors['nama_pelajaran'] = 'Nama wajib diisi, maksimal 100 karakter, tanpa karakter kontrol.';
    }
    if (!preg_match('/\A[0-9]+(?:\.[0-9]{1,2})?\z/', $data['bobot']) || (float) $data['bobot'] < 1 || (float) $data['bobot'] > 100) {
        $errors['bobot'] = 'Bobot harus berupa angka 1 sampai 100, maksimal dua angka desimal (contoh: 12.50).';
    }
    return [$data, $errors];
}