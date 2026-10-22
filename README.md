# SPK Pemilihan Bahan Pangan Bergizi (prototipe Laragon)

## Isi paket
- `index.php`: dashboard contoh, peringkat alternatif dengan bobot contoh, dan uji koneksi MySQL melalui catatan.
- `database.sql`: membuat database `spk_pangan_ahp` dan tabel catatan.

## Cara menjalankan dengan Laragon
1. Ekstrak folder `spk-pangan-ahp` ke `C:\laragon\www\spk-pangan-ahp`.
2. Buka Laragon dan klik **Start All**.
3. Buka phpMyAdmin (jika terpasang) melalui `http://localhost/phpmyadmin`.
4. Impor `database.sql`, atau buat database bernama `spk_pangan_ahp`.
5. Jika kredensial MySQL berbeda, ubah `$host`, `$db`, `$user`, dan `$pass` di bagian awal `index.php`.
6. Buka `http://localhost/spk-pangan-ahp/`.

## Penting
Ini prototipe pembelajaran dengan data pangan, harga, dan bobot contoh. Bobot belum berasal dari matriks perbandingan berpasangan yang diinput pengguna dan hasilnya bukan rekomendasi gizi resmi. Untuk penelitian/skripsi, perlu melengkapi input matriks AHP, perhitungan eigenvector, Consistency Index/Ratio, pengelolaan data kriteria/alternatif, dan validasi data.
