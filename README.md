1. Setup Awal Laravel & Git Lokal
Langkah pertama untuk membuat proyek di komputer Anda dan menyiapkan pelacakan versi.

composer create-project laravel/laravel hris-apps : Mengunduh dan menginstal kerangka kerja Laravel baru.

cd hris-apps : Berpindah masuk ke folder proyek.

git init : Mengaktifkan sistem Git di dalam folder proyek.

git add . : Menandai seluruh file bawaan untuk disimpan ke pencatatan Git.

git commit -m "Initial commit" : Mengunci penyimpanan versi pertama di komputer lokal.

2. Menghubungkan Proyek ke GitHub
Langkah untuk mengunggah proyek lokal ke repositori daring GitHub.

Buat repositori baru di website GitHub (biarkan kosong tanpa README/.gitignore).

git branch -M main : Memastikan nama cabang utama lokal bernama main.

git remote add origin [https://github.com/username/hris-apps.git](https://github.com/username/hris-apps.git) : Menghubungkan folder lokal dengan tautan repositori GitHub.

git push -u origin main : Mengirim seluruh kode dari komputer lokal ke GitHub.

3. Menambahkan Partner Kolaborasi
Langkah agar akun partner memiliki izin mengunggah perubahan kode ke repositori.

Buka repositori Anda di browser.

Buka tab Settings (ikon gear di ujung kanan atas).

Pilih menu Collaborators di bilah kiri, klik tombol Add people, lalu masukkan username GitHub partner Anda.

4. Persiapan di Sisi Partner (Clone Proyek)
Perintah yang dijalankan oleh partner di komputernya sendiri untuk mengunduh proyek.

git clone [https://github.com/username/hris-apps.git](https://github.com/username/hris-apps.git) : Mengunduh salinan repositori ke komputer partner.

cd hris-apps : Masuk ke dalam folder hasil unduhan.

composer install : Mengunduh seluruh pustaka inti (folder vendor) yang diabaikan oleh Git.

copy .env.example .env : Membuat salinan file konfigurasi lokal.

php artisan key:generate : Membuat kunci enkripsi aplikasi.

5. Alur Kerja Harian (Fitur Baru & Merge)
Siklus wajib saat bekerja agar kode tidak tertimpa dan terhindar dari konflik.

git checkout main lalu git pull origin main : Selalu tarik versi terbaru dari GitHub sebelum mulai menulis kode baru.

git checkout -b nama-cabang-fitur : Membuat dan berpindah ke cabang terpisah khusus fitur tersebut.

Tulis kode atau buat migrasi baru.

git add . : Menandai file yang baru diedit atau ditambah.

git commit -m "Keterangan perubahan" : Menyimpan catatan perubahan kode lokal.

git push origin nama-cabang-fitur : Mengirim cabang fitur ke GitHub (bukan langsung ke main).

Buka browser di GitHub, klik tombol Compare & pull request, lalu klik Merge pull request untuk menyatukan kode ke main.

git checkout main lalu git pull origin main : Kembali ke cabang utama di komputer dan tarik hasil penggabungan tadi.

php artisan migrate : Jalankan di komputer masing-masing jika ada perubahan struktur tabel database.