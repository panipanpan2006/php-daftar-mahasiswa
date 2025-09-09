<?php
require __DIR__ . '/functions.php';

$errors = [];
$old = [
    'nama' => '',
    'nim'  => '',
    'mk'   => '',
    'nilai'=> ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama  = trim($_POST['nama'] ?? '');
    $nim   = trim($_POST['nim'] ?? '');
    $mk    = trim($_POST['mk'] ?? '');
    $nilai = $_POST['nilai'] ?? '';

    $old = compact('nama', 'nim', 'mk', 'nilai');

    if ($nama === '') $errors[] = 'Nama wajib diisi.';
    if ($nim === '') $errors[] = 'NIM wajib diisi.';
    if ($mk === '') $errors[] = 'Mata kuliah wajib diisi.';
    if ($nilai === '' || !is_numeric($nilai)) {
        $errors[] = 'Nilai harus berupa angka.';
    } else {
        $nilai = (int)$nilai;
        if ($nilai < 0 || $nilai > 100) $errors[] = 'Nilai harus antara 0 sampai 100.';
    }

    if (empty($errors)) {
        $mahasiswa = load_mahasiswa();
        $mahasiswa[] = [
            'nama'  => $nama,
            'nim'   => $nim,
            'mk'    => $mk,
            'nilai' => (int)$nilai,
        ];

        save_mahasiswa($mahasiswa);
        header('Location: index.php?added=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Mahasiswa</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .grade {
            font-weight: bold;
            margin-top: 8px;
            display: block;
        }
        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: bold;
            color: white;
            margin-top: 6px;
            min-width: 50px;
            text-align: center;
        }
        .badge.A { background-color: green; }
        .badge.B { background-color: blue; }
        .badge.C { background-color: orange; }
        .badge.D { background-color: brown; }
        .badge.E { background-color: red; }
    </style>
</head>
<body>
<header class="site-header">
    <h1>Tambah Mahasiswa</h1>
    <nav>
        <a class="btn" href="index.php">Daftar</a>
        <a class="btn btn-primary" href="tambah.php">Tambah</a>
    </nav>
</header>

<main class="container">
    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="card form-card">
        <div class="form-group">
            <label for="nama">Nama</label>
            <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($old['nama']) ?>" required>
        </div>

        <div class="form-group">
            <label for="nim">NIM</label>
            <input type="text" id="nim" name="nim" value="<?= htmlspecialchars($old['nim']) ?>" required>
        </div>

        <div class="form-group">
            <label for="mk">Mata Kuliah</label>
            <input type="text" id="mk" name="mk" value="<?= htmlspecialchars($old['mk']) ?>" required>
        </div>

        <div class="form-group">
            <label for="nilai">Nilai</label>
            <input type="number" id="nilai" name="nilai" min="0" max="100" 
                   value="<?= htmlspecialchars($old['nilai']) ?>" required>
            <span id="grade-output" class="grade"></span>
            <span id="grade-badge"></span>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="index.php" class="btn">Batal</a>
        </div>
    </form>
</main>

<script>
    const nilaiInput = document.getElementById('nilai');
    const gradeOutput = document.getElementById('grade-output');
    const gradeBadge = document.getElementById('grade-badge');

    function konversiNilai(angka) {
        if (angka >= 85) return "A";
        else if (angka >= 70) return "B";
        else if (angka >= 60) return "C";
        else if (angka >= 50) return "D";
        else return "E";
    }

    nilaiInput.addEventListener('input', () => {
        const angka = parseInt(nilaiInput.value, 10);
        if (!isNaN(angka)) {
            const grade = konversiNilai(angka);
            gradeOutput.textContent = "Nilai Huruf: " + grade;
            gradeBadge.textContent = grade;
            gradeBadge.className = "badge " + grade;
        } else {
            gradeOutput.textContent = "";
            gradeBadge.textContent = "";
            gradeBadge.className = "";
        }
    });
</script>
</body>
</html>