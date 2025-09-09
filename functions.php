<?php
// functions.php
if (!defined('DATA_FILE')) define('DATA_FILE', __DIR__ . '/data/mahasiswa.json');

/**
 * Load seluruh data mahasiswa dari file JSON.
 * @return array
 */
function load_mahasiswa() {
    $file = DATA_FILE;
    if (!file_exists($file)) return [];
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    return is_array($data) ? array_values($data) : [];
}

/**
 * Simpan array mahasiswa ke file JSON (LOCK_EX).
 * @param array $arr
 * @return bool
 */
function save_mahasiswa(array $arr) {
    $file = DATA_FILE;
    $dir = dirname($file);
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $json = json_encode(array_values($arr), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return (bool) file_put_contents($file, $json, LOCK_EX);
}

/**
 * Konversi nilai angka ke huruf.
 */
function konversiNilai($angka) {
    $angka = (int)$angka;
    if ($angka >= 85) return 'A';
    elseif ($angka >= 70) return 'B';
    elseif ($angka >= 60) return 'C';
    elseif ($angka >= 50) return 'D';
    else return 'E';
}

/**
 * Cari index mahasiswa berdasarkan NIM.
 * @param string $nim
 * @return int|false index jika ditemukan, false jika tidak
 */
function find_index_by_nim($nim) {
    $list = load_mahasiswa();
    foreach ($list as $i => $m) {
        if (isset($m['nim']) && $m['nim'] === $nim) return $i;
    }
    return false;
}

/**
 * Ambil record mahasiswa berdasarkan NIM (atau null jika tidak ada).
 */
function get_mahasiswa_by_nim($nim) {
    $idx = find_index_by_nim($nim);
    if ($idx === false) return null;
    $list = load_mahasiswa();
    return $list[$idx];
}

/**
 * Update mahasiswa: mencari berdasarkan $original_nim lalu menggantinya dengan $newData.
 * $newData harus mengandung keys: nama, nim, mk, nilai
 * Mengembalikan true jika berhasil, false + pesan kesalahan bila gagal.
 */
function update_mahasiswa($original_nim, array $newData, &$error = null) {
    $list = load_mahasiswa();
    $idx = null;
    foreach ($list as $i => $m) {
        if (isset($m['nim']) && $m['nim'] === $original_nim) {
            $idx = $i;
            break;
        }
    }
    if ($idx === null) {
        $error = "Data mahasiswa dengan NIM {$original_nim} tidak ditemukan.";
        return false;
    }

    // Cek duplikasi NIM jika NIM diubah
    $newNim = trim($newData['nim']);
    foreach ($list as $i => $m) {
        if ($i === $idx) continue;
        if (isset($m['nim']) && $m['nim'] === $newNim) {
            $error = "NIM {$newNim} sudah ada. Pilih NIM lain.";
            return false;
        }
    }

    // Normalisasi nilai
    $newData['nilai'] = (int)$newData['nilai'];
    // Replace
    $list[$idx] = [
        'nama' => trim($newData['nama']),
        'nim'  => $newNim,
        'mk'   => trim($newData['mk']),
        'nilai'=> $newData['nilai'],
    ];

    return save_mahasiswa($list);
}

/**
 * Hapus mahasiswa berdasarkan NIM.
 */
function delete_mahasiswa($nim, &$error = null) {
    $list = load_mahasiswa();
    $found = false;
    foreach ($list as $i => $m) {
        if (isset($m['nim']) && $m['nim'] === $nim) {
            $found = true;
            unset($list[$i]);
            break;
        }
    }
    if (!$found) {
        $error = "Data mahasiswa dengan NIM {$nim} tidak ditemukan.";
        return false;
    }
    // Simpan kembali (reindex)
    return save_mahasiswa(array_values($list));
}