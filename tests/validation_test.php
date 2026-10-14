<?php
declare(strict_types=1);
require __DIR__ . '/../includes/validation.php';

$checks = 0;
function check(bool $condition, string $message): void
{
    global $checks;
    ++$checks;
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

[$data, $errors] = validate_pelajaran(['kd_pelajaran' => ' tkj-01 ', 'nama_pelajaran' => ' Jaringan ', 'bobot' => '12.50']);
check($errors === [], 'Valid data rejected');
check($data['kd_pelajaran'] === 'TKJ-01', 'Code not normalized');
check($data['nama_pelajaran'] === 'Jaringan', 'Name not trimmed');
foreach (['1', '100', '1.01', '99.99'] as $value) {
    [, $errors] = validate_pelajaran(['kd_pelajaran' => 'A', 'nama_pelajaran' => 'Nama', 'bobot' => $value]);
    check($errors === [], 'Valid weight rejected: ' . $value);
}
foreach (['', '0', '-1', '100.01', '1.234', '1e2', 'NaN', '12,5', 'abc'] as $value) {
    [, $errors] = validate_pelajaran(['kd_pelajaran' => 'A', 'nama_pelajaran' => 'Nama', 'bobot' => $value]);
    check(isset($errors['bobot']), 'Invalid weight accepted: ' . $value);
}
foreach (['', str_repeat('A', 21), 'A B', "A';--"] as $value) {
    [, $errors] = validate_pelajaran(['kd_pelajaran' => $value, 'nama_pelajaran' => 'Nama', 'bobot' => '10']);
    check(isset($errors['kd_pelajaran']), 'Invalid code accepted');
}
foreach (['', '   ', str_repeat('a', 101), "abc\nxyz", "\xFF"] as $value) {
    [, $errors] = validate_pelajaran(['kd_pelajaran' => 'A', 'nama_pelajaran' => $value, 'bobot' => '10']);
    check(isset($errors['nama_pelajaran']), 'Invalid name accepted');
}
[, $errors] = validate_pelajaran(['kd_pelajaran' => [], 'nama_pelajaran' => [], 'bobot' => []]);
check(count($errors) === 3, 'Array inputs must fail validation');
[, $errors] = validate_pelajaran(['kd_pelajaran' => 'A', 'nama_pelajaran' => str_repeat('a', 100), 'bobot' => '10']);
check($errors === [], '100-character name rejected');
echo "PASS: $checks validation checks\n";