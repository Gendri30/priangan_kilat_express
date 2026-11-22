# Priangan Kilat Express

# 1. Ringkasan
Priangan Kilat Express adalah sistem ekspedisi yang terdiri dari website untuk kantor (Manajer, Admin, dan cek resi pelanggan) serta aplikasi Android untuk kurir. Sistem ini dibuat untuk menggantikan pencatatan manual di kertas dan kalkulator, supaya tidak ada lagi nomor resi yang kembar, ongkos kirim dan biaya COD terhitung otomatis (tanpa mengubah harga paket lama kalau tarif naik), muatan kurir dibatasi maksimal 20 paket, nomor HP penerima aman disensor pada lacak paket, dan setoran uang COD lebih tertib. Alur kerjanya dimulai dari Admin yang mencatat data paket masuk di loket, memindahkannya ke gudang (transit), lalu menyerahkannya ke kurir. Setelah itu, kurir mengantar paket dan mengubah status paket lewat aplikasi serta menyetor uang COD di loket, sementara pelanggan bisa melacak paketnya di website tanpa login dan Manajer bisa mengatur tarif zona serta melihat laporan.

# 2. Teknologi
- Web & Backend: PHP 8.4.12, Laravel 13.43.0, dan Filament v5.11.2 -> dipilih karena di sekolah saya sudah belajar Laravel, lalu saya tambahkan Filament supaya mudah saat membuat panel Admin dan Manajer.
- Database: MySQL 8.4.3 (Laragon) -> dipilih karena di sekolah saya belajar MySQL dan sudah terbiasa menggunakannya.
- REST API & Autentikasi Mobile: Laravel Sanctum -> dipilih untuk menghubungkan aplikasi Android kurir dengan server Laravel menggunakan "bearer token" yang aman.
- Aplikasi Mobile: Ionic React 9.0.0, React 19.0.0, TypeScript 5.9.0, dan Capacitor 8.6.2 (Node.js v24.4.1, Android `minSdkVersion 24` / `targetSdkVersion 36`) -> dipilih atas rekomendasi Kaprog dan lebih ringan dibandingkan Kotlin atau Flutter, namun tetap menghasilkan file APK Android.

# 3.  Cara menjalankan server

1. Nyalakan MySQL melalui aplikasi Laragon (klik Start All), lalu buat database baru bernama priangan_kilat_express.

2. Clone repositori dan masuk ke folder backend melalui terminal atau PowerShell:
git clone https://github.com/Gendri30/priangan_kilat_express
cd priangan_kilat_express/web-backend

3. Instal dependensi Laravel dan buat file .env:
composer install
copy .env.example .env
php artisan key:generate

4. Buka file web-backend/.env lalu sesuaikan pengaturan database MySQL lokal dan API Pusat Wilayah:
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=priangan_kilat_express
DB_USERNAME=root
DB_PASSWORD=

PUSAT_WILAYAH_URL=ISI_URL_PUSAT_WILAYAH
PUSAT_WILAYAH_KEY=ISI_API_KEY_PUSAT_WILAYAH

5. Jalankan migrasi tabel dan seeder awal untuk membuat akun Admin, Manajer, dan Zona Tarif:
php artisan migrate
php artisan db:seed

6. Impor 3 file CSV data awal (kurir.csv, pelanggan.csv, dan paket_historis.csv) secara berurutan lewat tombol Impor CSV di panel Admin (/admin), lalu jalankan ulang perintah seeder agar kuota uji 20 paket pada kurir KUR-02 siap dipakai:
php artisan db:seed

7. Jalankan server Laravel dengan host 0.0.0.0 supaya bisa diakses oleh HP Android kurir dalam satu jaringan Wi-Fi atau Hotspot:
php artisan serve --host=0.0.0.0 --port=8000

Alamat akses lokal:
Halaman Cek Resi Publik: http://127.0.0.1:8000
Panel Admin dan Manajer: http://127.0.0.1:8000/admin

# 4. Cara memasang APK

1. Pastikan laptop server dan HP Android terhubung ke satu jaringan Wi-Fi atau Mobile Hotspot yang sama. Jika memakai Mobile Hotspot dari laptop Windows, alamat IP bawaannya adalah 192.168.137.1.

2. Salin berkas apk/priangan-kilat-kurir.apk dari repositori ini ke HP Android (minimal Android 8.0 / API 26).

3. Instal berkas APK tersebut di HP dan izinkan pemasangan dari sumber tidak dikenal (Install unknown apps) jika diminta.

4. Pastikan server Laravel di laptop sudah menyala menggunakan perintah php artisan serve --host=0.0.0.0 --port=8000.

5. Buka aplikasi Priangan Kilat Express di HP. Secara bawaan aplikasi sudah diarahkan ke alamat http://192.168.137.1:8000. Jika menggunakan jaringan Wi-Fi lain, tekan tombol pengaturan IP Server di layar Login lalu masukkan alamat http://IP_IPV4_LAPTOP:8000 (jangan menggunakan localhost atau 127.0.0.1 karena alamat tersebut mengarah ke HP itu sendiri).

# 5. Akun Uji

1. Admin Loket (Web /admin)
   Email: admin@priangankilat.test
   Username: admin
   Password: admin123
   Keterangan: Akses penuh loket, kelola pelanggan, kurir, paket, dan setoran COD.

2. Manajer (Web /admin)
   Email: manajer@priangankilat.test
   Username: manajer
   Password: manajer123
   Keterangan: Ubah tarif zona, lihat 3 laporan, dan hanya bisa melihat data pelanggan/kurir.

3. Kurir - Uji Login Awal (APK Android)
   Kode Kurir: KUR-01
   Password Sementara: kurir123
   Keterangan: Akun kurir aktif, wajib ganti password (min. 8 karakter) saat login pertama.

4. Kurir - Uji Batas 20 Paket (APK Android)
   Kode Kurir: KUR-02 (Mega Saputra)
   Password Sementara: kurir123
   Keterangan: Membawa tepat 20 paket berstatus Dikirim untuk menguji penolakan paket ke-21.

# 6. Letak Berkas

1. Migrasi Database:
   web-backend/database/migrations/

2. Impor Data Awal CSV:
   - Logika pembersih CSV: web-backend/app/Services/Csv/ (ImportKurirCsv.php, ImportPelangganCsv.php, ImportPaketCsv.php)
   - Tombol impor di panel Admin (/admin): web-backend/app/Filament/Resources/ (pada ListKurirs.php, ListPelanggans.php, dan ListPakets.php)

3. Seeder Data Demo:
   web-backend/database/seeders/DatabaseSeeder.php
   (membaca file CSV seeder di web-backend/storage/app/Varian_B_Ekspedisi/Data_Seeder_CSV/)

4. Perintah Menjalankan Seeder:
   php artisan db:seed
   (dijalankan di dalam folder web-backend; setelah impor data awal CSV selesai, seeder ini melengkapi kurir KUR-02 agar membawa tepat 20 paket berstatus Dikirim).

5. Dokumentasi dan APK:
   - ERD: docs/erd/
   - State Diagram UML: docs/uml/
   - File APK Android: apk/priangan-kilat-kurir.apk

# 7. Keputusan Anda

1. Cara Menyimpan Snapshot Harga Transaksi Lama:
   Saat paket baru diinput, sistem langsung menyalin tarif per kg zona saat itu ke kolom tarif_dasar_snapshot dan mengunci hasil hitungannya di kolom ongkos_kirim pada tabel pakets. Dengan cara ini, jika Manajer mengubah tarif zona di kemudian hari, ongkos kirim dan laporan paket lama tidak ikut berubah.

2. Aturan Validasi Format Nomor Telepon:
   Selain wajib minimal 10 digit dan tidak boleh ada huruf, nomor telepon ditetapkan hanya boleh berisi angka (0-9), wajib diawali 08, dan panjangnya maksimal 14 digit.

3. Cara Menangani Data Awal CSV yang Belum Rapi Saat Impor:
   - Nomor Telepon (pelanggan.csv dan paket_historis.csv): Spasi dan tanda hubung dibersihkan, lalu awalan +62, 62, atau 8 diseragamkan menjadi 08.
   - Penulisan Nama (pelanggan.csv dan paket_historis.csv): Dirapikan ke format Title Case (huruf besar di awal setiap kata).
   - Nomor Resi Ganda (paket_historis.csv): Jika ada nomor resi yang tercatat dua kali, sistem hanya menyimpan data pertama yang ditemukan agar nomor resi tetap unik.
   - Paket Tanpa Data Berat (paket_historis.csv): Kolom berat_aktual dibiarkan kosong (null), berat_tagih diisi sementara 1 kg, dan diberi tanda perlu_verifikasi = true agar mudah dicek Admin di daftar paket.
   - Paket Historis COD Terkirim: Paket COD lama dari CSV yang statusnya sudah Terkirim otomatis ditandai sudah_disetor.

# 8. Lembar Self-Check

Daftar Penerimaan (14 Butir Wajib):

1. Input paket baru, validasi nomor HP (tanpa huruf, min. 10 digit), dan terbit resi otomatis PKE-YYMMDD-0001
   Klaim: Ya
   Langkah Uji: Login Admin -> menu Pakets -> New Paket -> coba isi nomor HP dengan huruf atau kurang dari 10 digit (ditolak), lalu isi data valid dan cek format nomor resi yang terbit.

2. (Butir Inti) Pembulatan berat ke atas (min. 1 kg), tarif Express +50%, biaya COD 2% (min. Rp2.000), dan snapshot tarif
   Klaim: Ya
   Langkah Uji: Input paket 1,2 kg Express + COD Rp50.000 -> cek ongkir (2 kg x tarif x 1,5) dan biaya COD Rp2.000. Ubah tarif zona lewat akun Manajer, pastikan ongkir paket lama tidak berubah.

3. (Butir Inti) Perubahan status paket berjenjang (Diterima -> Transit -> Dikirim -> Terkirim / Gagal Kirim -> Retur) tanpa lompat
   Klaim: Ya
   Langkah Uji: Cek tombol aksi di tabel Pakets akun Admin; paket berstatus Diterima hanya menampilkan tombol Ubah ke Transit, lalu setelah Transit baru muncul Tugaskan Kurir.

4. Kelola akun kurir (KUR-xx), wajib ganti password perdana/reset, dan larangan nonaktifkan kurir yang masih membawa paket
   Klaim: Ya
   Langkah Uji: Buat kurir baru di menu Kurirs, coba nonaktifkan kurir yang sedang membawa paket berstatus Dikirim (sistem menolak), dan coba fitur reset password.

5. Gagal kirim wajib catatan alasan; gagal 1x bisa kirim ulang, gagal 2x hanya bisa diubah menjadi Retur
   Klaim: Ya
   Langkah Uji: Update Gagal Kirim ke-1 dari APK -> di web Admin muncul tombol Kirim Ulang. Update Gagal Kirim ke-2 -> tombol Kirim Ulang hilang dan hanya muncul tombol Ubah ke Retur.

6. Pembatasan kurir maksimal membawa 20 paket berstatus Dikirim secara bersamaan
   Klaim: Ya
   Langkah Uji: Login Admin -> tugaskan paket baru ke kurir KUR-02 (yang sudah membawa 20 paket Dikirim) -> sistem menolak penugasan dengan pesan error.

7. Halaman cek resi publik tanpa login dengan sensor nomor HP di server serta menyembunyikan alamat dan nilai COD
   Klaim: Ya
   Langkah Uji: Buka halaman utama (/) tanpa login -> cari nomor resi -> pastikan nomor HP tampil tersensor (0812****789), serta alamat lengkap dan nominal COD tidak ditampilkan.

8. Pencatatan setoran uang COD dari kurir ke kantor (Belum Disetor -> Sudah Disetor)
   Klaim: Ya
   Langkah Uji: Pada paket COD yang berstatus Terkirim, klik Catat Setoran COD di akun Admin -> status kas berubah menjadi Sudah Disetor, status fisik tetap Terkirim, dan tombol tidak bisa diklik dua kali.

9. Ubah tarif zona dan 3 laporan khusus Manajer (Pengiriman, Pendapatan per Zona, Rekap COD) serta pembatasan hak akses
   Klaim: Ya
   Langkah Uji: Login Manajer -> ubah tarif zona dan buka Laporan Manajer dengan filter tanggal. Login Admin -> pastikan menu Laporan Manajer dan ubah tarif tidak bisa diakses.

10. Impor 3 file CSV data awal (kurir.csv, pelanggan.csv, paket_historis.csv) dengan pembersihan data
    Klaim: Ya
    Langkah Uji: Impor 3 CSV di panel Admin -> cek penyeragaman nomor HP (08...), nama Title Case, resi ganda menjadi satu, dan tanda Perlu Verifikasi pada paket tanpa data berat.

11. Integrasi API Pusat Wilayah beserta mode fallback pilih zona manual jika API mati atau kota tidak dikenal
    Klaim: Ya
    Langkah Uji: Input kota yang tidak dikenal API atau ubah API key/URL di .env menjadi salah -> pilih zona manual di form input paket dan pastikan tersimpan sebagai zona manual.

12. Autentikasi REST API Kurir (Sanctum) dan kewajiban ganti password saat login pertama di APK
    Klaim: Ya
    Langkah Uji: Login di APK memakai akun kurir baru (KUR-01 / kurir123) -> layar terkunci di form ganti password (min. 8 karakter) sebelum bisa masuk ke daftar tugas.

13. Operasional kurir di APK: melihat daftar paket Dikirim miliknya dan mengubah status ke Terkirim atau Gagal Kirim (wajib catatan)
    Klaim: Ya
    Langkah Uji: Login APK sebagai kurir -> pastikan hanya paket miliknya yang tampil, coba update Gagal Kirim tanpa catatan (ditolak), lalu update dengan catatan atau ubah ke Terkirim.

14. Rekap saldo COD di APK: melihat paket COD berstatus Terkirim yang diantarnya dan total nilai COD yang belum disetor
    Klaim: Ya
    Langkah Uji: Buka tab Setor COD di APK -> pastikan daftar paket COD Terkirim dan total nominal yang belum disetor tampil sesuai dengan data di server.

Fitur Opsional yang Dikerjakan:
- O1 (Berat volumetrik): Tidak
- O2 (Asuransi opsional 0,5%): Tidak
- O3 (Laporan performa kurir): Tidak
- O4 (Penanda paket tertahan > 3 hari di Transit): Tidak
- O5 (Reset password lewat email lokal): Tidak
Keterangan: Fokus menyelesaikan seluruh 14 butir penerimaan utama terlebih dahulu.

# 9. Belum Selesai / Keterbatasan

1. Seluruh fitur opsional (O1 sampai O5) belum dikerjakan karena fokus menyelesaikan 14 butir penerimaan utama terlebih dahulu.

# 10. Referensi dan Bantuan

1. Dokumentasi Resmi:
   Dokumentasi Laravel, Filament v5, Laravel Sanctum, Ionic React, dan Capacitor digunakan sebagai acuan konfigurasi backend, autentikasi API, dan proses build APK Android.

2. Bantuan AI:
   Digunakan sebagai teman diskusi untuk menjelaskan konsep Ionic React, membantu menyusun kerangka awal kode (migrasi, komponen tampilan, dan layanan API), membantu debugging error (seperti TypeScript, ESLint, dan build Gradle), serta membantu merapikan struktur dokumentasi.

   Bagian yang disesuaikan dan dikerjakan secara mandiri: menyesuaikan seluruh aturan bisnis Priangan Kilat Express (hitung ongkir, snapshot tarif, batas 20 paket kurir, dan hak akses peran), merancang diagram ERD dan State Diagram, serta menguji langsung alur kerja web dan APK.