<?php
require __DIR__ . '/functions.php';

$err = '';
$success = '';
$original_nim = isset($_GET['nim']) ? trim($_GET['nim']) : (isset($_POST['original_nim']) ? trim($_POST['original_nim']) : '');

if ($original_nim === '') {
    $err = 'NIM tidak diberikan.';
}

// Bila form disubmit (POST) => proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $nama = trim($_POST['nama'] ?? '');
    $nim  = trim($_POST['nim'] ?? '');
    $mk   = trim($_POST['mk'] ?? '');
    $nilai= trim($_POST['nilai'] ?? '');

    // Validasi sederhana
    $errors = [];
    if ($nama === '') $errors[] = 'Nama harus diisi.';
    if ($nim === '')  $errors[] = 'NIM harus diisi.';
    if ($mk === '')   $errors[] = 'Mata Kuliah harus diisi.';
    if ($nilai === '' || !is_numeric($nilai)) $errors[] = 'Nilai harus angka.';
    else {
        $nilai = (int)$nilai;
        if ($nilai < 0 || $nilai > 100) $errors[] = 'Nilai antara 0-100.';
    }

    if (empty($errors)) {
        $data = ['nama'=>$nama, 'nim'=>$nim, 'mk'=>$mk, 'nilai'=>$nilai];
        $msg = null;
        if (update_mahasiswa($original_nim, $data, $msg)) {
            header('Location: index.php?msg=updated');
            exit;
        } else {
            $err = $msg ?? 'Gagal menyimpan perubahan.';
        }
    } else {
        $err = implode('<br>', $errors);
    }
}

// Ambil data untuk ditampilkan di form
$record = null;
if ($original_nim !== '') $record = get_mahasiswa_by_nim($original_nim);
if (!$record && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $err = "Data mahasiswa dengan NIM {$original_nim} tidak ditemukan.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Edit Mahasiswa</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body{font-family:Inter,Arial;background:#f6f8fb;padding:18px;color:#0f172a}
        .wrap{max-width:780px;margin:0 auto}
        .card{background:#fff;padding:14px;border-radius:10px}
        input, select{width:100%;padding:8px;border-radius:8px;border:1px solid rgba(15,23,42,0.08);margin-bottom:8px}
        .btn{padding:8px 10px;border-radius:8px;border:none}
        .btn-primary{background:#2563eb;color:#fff}
        .btn-muted{background:#f3f4f6}
        .alert{padding:8px;border-radius:8px;margin-bottom:8px}
        .alert.error{background:#fff0f0;color:#dc2626}
    </style>
</head>
<body>
<div class="wrap">
    <h1>Edit Mahasiswa</h1>
    <div class="card">
        <?php if ($err): ?>
            <div class="alert error"><?= $err ?></div>
        <?php endif; ?>

        <form method="post" action="simpan.php">
            <input type="hidden" name="original_nim" value="<?= htmlspecialchars($original_nim) ?>">

            <label>Nama
                <input type="text" name="nama" value="<?= htmlspecialchars($_POST['nama'] ?? $record['nama'] ?? '') ?>" required>
            </label>

            <label>NIM
                <input type="text" name="nim" value="<?= htmlspecialchars($_POST['nim'] ?? $record['nim'] ?? '') ?>" required>
            </label>

            <label>Mata Kuliah
                <input type="text" name="mk" value="<?= htmlspecialchars($_POST['mk'] ?? $record['mk'] ?? '') ?>" required>
            </label>

            <label>Nilai (0-100)
                <input type="number" name="nilai" min="0" max="100" value="<?= htmlspecialchars($_POST['nilai'] ?? $record['nilai'] ?? '') ?>" required>
            </label>

            <div style="display:flex;gap:8px">
                <button class="btn btn-primary" type="submit" name="save">Simpan Perubahan</button>
                <a class="btn btn-muted" href="index.php">Batal</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>