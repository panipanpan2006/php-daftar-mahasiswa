# Contoh Aplikasi Nilai & Daftar mahasiswa

## Description
Ini adalah proyek mini yang dibuat sebagai tugas mata kuliah Pemrograman Web. Proyek ini merupakan aplikasi sederhana berbasis web untuk mengelola dan menampilkan daftar nilai mahasiswa menggunakan PHP murni!

### Fitur Utama
1. Halaman Utama (index.php): Menampilkan daftar mahasiswa, NIM, mata kuliah, dan nilai (angka dan huruf) dalam format tabel yang rapi.
2. Konversi Nilai Otomatis: Sistem akan mengonversi nilai angka menjadi nilai huruf secara otomatis (A, B, C, D, E) berdasarkan kriteria yang telah ditentukan.
3. Form Tambah Data: Anda dapat menambahkan data mahasiswa baru melalui form yang intuitif. 
4. Navigasi Halaman: Terdapat menu navigasi yang memudahkan pengguna untuk beralih antara halaman daftar mahasiswa dan halaman tambah data.
5. Styling Sederhana: Menggunakan CSS eksternal untuk membuat tampilan lebih bersih dan terorganisir.

### Cara pakai
1. Tempatkan semua file pada folder project web Anda (mis. Pengembangan Web/tugasphp).
2. Pastikan folder data/ dapat ditulis oleh webserver (chmod 775 atau 777 jika perlu).
3. Buka browser ke http://localhost/path-ke-project/index.php


### Catatan keamanan
1. Ini contoh sederhana menggunakan file JSON. Untuk aplikasi production gunakan database (MySQL/SQLite) agar validasi lebih ketat.
2. Anda dapat menambahkan fitur edit/hapus, paging, atau import/export CSV.