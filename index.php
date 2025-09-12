<?php
// index.php
require __DIR__ . '/functions.php';

// Jika file data kosong, inisialisasi dengan contoh default
$exists = file_exists(DATA_FILE) && filesize(DATA_FILE) > 0;
if (!$exists) {
    $mahasiswa_default = [
        ["nama" => "Andi",     "nim" => "21060125150101", "mk" => "Pengembangan Web",         "nilai" => 88],
        ["nama" => "Budi",     "nim" => "21060125150102", "mk" => "Basis Data",               "nilai" => 75],
        ["nama" => "Zain",     "nim" => "21060123110050", "mk" => "Multimedia",               "nilai" => 90],
        ["nama" => "Frans",    "nim" => "21060123140119", "mk" => "Basis Data",               "nilai" => 72],
        ["nama" => "Banar",    "nim" => "21060123140160", "mk" => "Komputasi Cerdas",         "nilai" => 87],
        ["nama" => "Alfath",   "nim" => "21060123140178", "mk" => "Basis Data",               "nilai" => 70],
        ["nama" => "Royyan",   "nim" => "21060123140127", "mk" => "Fisika Mekanika dan Panas","nilai" => 91],
        ["nama" => "Antarest", "nim" => "21060125150108", "mk" => "Elektronika Kedokteran",   "nilai" => 64],
        ["nama" => "Nopal",    "nim" => "21060123140205", "mk" => "Kimia Dasar",              "nilai" => 53],
        ["nama" => "Rafael",   "nim" => "21060123130087", "mk" => "Mekatronika",              "nilai" => 81],
    ];
    save_mahasiswa($mahasiswa_default);
}

// Proses HAPUS (POST)
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $nimToDelete = trim($_POST['nim'] ?? '');
    if ($nimToDelete === '') {
        $message = 'NIM tidak ditemukan.';
    } else {
        $err = null;
        if (delete_mahasiswa($nimToDelete, $err)) {
            header('Location: index.php?msg=deleted');
            exit;
        } else {
            $message = $err ?? 'Gagal menghapus data.';
        }
    }
}

// Ambil daftar untuk tampil
$mahasiswa = load_mahasiswa();

// filter / search
$filter_mk = trim($_GET['mk'] ?? '');
$search     = trim($_GET['search'] ?? '');
$sort       = in_array($_GET['sort'] ?? '', ['asc','desc']) ? $_GET['sort'] : 'desc';

// Filter & sort
$mk_list = array_values(array_unique(array_map(fn($m) => $m['mk'], $mahasiswa)));
sort($mk_list);

$filtered = array_filter($mahasiswa, function($m) use ($filter_mk, $search) {
    if ($filter_mk !== '' && $m['mk'] !== $filter_mk) return false;
    if ($search !== '') {
        $s = strtolower($search);
        if (strpos(strtolower($m['nama']), $s) === false && strpos(strtolower($m['nim']), $s) === false) {
            return false;
        }
    }
    return true;
});

usort($filtered, function($a, $b) use ($sort) {
    return $sort === 'asc' ? $a['nilai'] <=> $b['nilai'] : $b['nilai'] <=> $a['nilai'];
});

$total = count($filtered);
$sum = array_reduce($filtered, fn($c, $m) => $c + $m['nilai'], 0);
$avg = $total ? round($sum / $total, 2) : 0;
$nilai_tertinggi = $total ? max(array_column($filtered,'nilai')) : null;
$nilai_terendah  = $total ? min(array_column($filtered,'nilai')) : null;

// Jumlah lulus & tidak lulus + persentase
$lulus = count(array_filter($filtered, fn($m) => $m['nilai'] >= 60));
$tidak_lulus = $total - $lulus;
$persen_lulus = $total ? round(($lulus / $total) * 100, 1) : 0;
$persen_tidak_lulus = $total ? round(($tidak_lulus / $total) * 100, 1) : 0;

function qs(array $overrides = []) {
    $params = $_GET;
    foreach ($overrides as $k => $v) {
        if ($v === null) unset($params[$k]); else $params[$k] = $v;
    }
    return '?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Mahasiswa & Nilai</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body{font-family:Inter,Arial;background:#f6f8fb;color:#0f172a;padding:18px}
        .wrap{max-width:1100px;margin:0 auto}
        .card{background:#fff;padding:14px;border-radius:10px;box-shadow:0 6px 18px rgba(15,23,42,0.05)}
        table{width:100%;border-collapse:collapse;margin-top:8px}
        th,td{padding:10px 8px;border-bottom:1px solid rgba(15,23,42,0.06);text-align:left}
        .btn{padding:6px 8px;border-radius:6px;text-decoration:none;border:1px solid rgba(15,23,42,0.08);cursor:pointer}
        .btn-danger{background:#fff1f2;color:#dc2626;border:none}
        .btn-edit{background:#eaf3ff;color:#2563eb;border:none}
        form.inline{display:inline}
        .center{text-align:center}

        /* Statistik Cards */
        .stats-container {
            display: flex;
            gap: 20px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        .stat-card {
            flex: 1;
            min-width: 150px;
            background: #fff;
            padding: 16px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(15,23,42,0.05);
            text-align: center;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
        }
        .stat-label {
            margin-top: 6px;
            font-size: 14px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-sub {
            margin-top: 4px;
            font-size: 13px;
            color: #374151;
        }
        .stat-card.pass .stat-value { color: #16a34a; }
        .stat-card.fail .stat-value { color: #dc2626; }

        /* Progress bar */
        .progress {
            margin-top: 8px;
            width: 100%;
            height: 8px;
            background: #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            transition: width 0.4s ease;
        }
        .progress-bar.pass { background: #16a34a; }
        .progress-bar.fail { background: #dc2626; }
    </style>
    <script>
        function confirmDelete(nim, name) {
            if (!confirm('Hapus data ' + name + ' (NIM: ' + nim + ')?')) return false;
            const f = document.createElement('form');
            f.method = 'post';
            f.action = 'index.php';
            f.innerHTML = `<input type="hidden" name="action" value="delete">
                           <input type="hidden" name="nim" value="${nim}">`;
            document.body.appendChild(f);
            f.submit();
            return true;
        }
    </script>
</head>
<body>
<div class="wrap">
    <h1>Daftar Mahasiswa & Nilai</h1>

    <?php if (isset($_GET['msg']) && $_GET['msg']==='deleted'): ?>
        <div style="background:#fff0f0;padding:8px;border-radius:8px;margin-bottom:8px;color:#dc2626">Data berhasil dihapus.</div>
    <?php endif; ?>

    <?php if ($message !== ''): ?>
        <div style="background:#fff3cd;padding:8px;border-radius:8px;margin-bottom:8px;color:#92400e"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
            <form method="get" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                <input type="text" name="search" placeholder="Cari nama atau NIM" value="<?= htmlspecialchars($search) ?>">
                <select name="mk" onchange="this.form.submit()">
                    <option value="">-- Semua MK --</option>
                    <?php foreach ($mk_list as $mk): ?>
                        <option value="<?= htmlspecialchars($mk) ?>" <?= $filter_mk === $mk ? 'selected' : '' ?>><?= htmlspecialchars($mk) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="sort" onchange="this.form.submit()">
                    <option value="desc" <?= $sort==='desc' ? 'selected' : '' ?>>Nilai: Tertinggi → Terendah</option>
                    <option value="asc"  <?= $sort==='asc' ? 'selected' : '' ?>>Nilai: Terendah → Tertinggi</option>
                </select>
                <button type="submit" class="btn">Terapkan</button>
                <a class="btn" href="index.php">Reset</a>
            </form>
            <a class="btn" href="tambah.php" style="background:#2563eb;color:white">+ Tambah Mahasiswa</a>
        </div>

        <!-- Statistik Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-value"><?= $total ?></div>
                <div class="stat-label">Total Ditampilkan</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $avg ?></div>
                <div class="stat-label">Rata-rata</div>
            </div>
            <?php if ($nilai_tertinggi !== null): ?>
                <div class="stat-card">
                    <div class="stat-value"><?= $nilai_tertinggi ?></div>
                    <div class="stat-label">Tertinggi</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $nilai_terendah ?></div>
                    <div class="stat-label">Terendah</div>
                </div>
            <?php endif; ?>
            <div class="stat-card pass">
                <div class="stat-value"><?= $lulus ?></div>
                <div class="stat-label">Lulus (≥60)</div>
                <div class="stat-sub"><?= $persen_lulus ?>%</div>
                <div class="progress"><div class="progress-bar pass" style="width: <?= $persen_lulus ?>%"></div></div>
            </div>
            <div class="stat-card fail">
                <div class="stat-value"><?= $tidak_lulus ?></div>
                <div class="stat-label">Tidak Lulus (&lt;60)</div>
                <div class="stat-sub"><?= $persen_tidak_lulus ?>%</div>
                <div class="progress"><div class="progress-bar fail" style="width: <?= $persen_tidak_lulus ?>%"></div></div>
            </div>
        </div>

        <table aria-label="Tabel mahasiswa" style="margin-top:8px">
            <thead>
                <tr><th>Nama</th><th>NIM</th><th>Mata Kuliah</th><th class="center">Nilai</th><th class="center">Huruf</th><th class="center">Aksi</th></tr>
            </thead>
            <tbody>
                <?php if ($total === 0): ?>
                    <tr><td colspan="6" class="center">Tidak ada data</td></tr>
                <?php else: ?>
                    <?php foreach ($filtered as $m): ?>
                        <?php $grade = konversiNilai($m['nilai']); ?>
                        <tr>
                            <td><?= htmlspecialchars($m['nama']) ?></td>
                            <td><?= htmlspecialchars($m['nim']) ?></td>
                            <td><?= htmlspecialchars($m['mk']) ?></td>
                            <td class="center"><?= (int)$m['nilai'] ?></td>
                            <td class="center"><span style="padding:4px 8px;border-radius:6px;font-weight:600"><?= $grade ?></span></td>
                            <td class="center">
                                <a class="btn btn-edit" href="simpan.php?nim=<?= urlencode($m['nim']) ?>">Edit</a>
                                <button class="btn btn-danger" onclick="confirmDelete('<?= htmlspecialchars($m['nim'], ENT_QUOTES) ?>','<?= htmlspecialchars($m['nama'], ENT_QUOTES) ?>')">Hapus</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>