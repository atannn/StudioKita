# StudioKita

StudioKita adalah aplikasi web untuk mencari studio, melakukan pemesanan, mengelola operasional studio, dan memproses pembayaran. Aplikasi ini menggunakan arsitektur multi-tenant sehingga setiap pemilik studio dapat mengelola data studionya secara terpisah.

## Fitur Utama

### Customer

- Melihat daftar dan detail studio.
- Memilih ruangan serta jadwal yang tersedia.
- Membuat dan memantau pemesanan.
- Melakukan pembayaran melalui Midtrans.
- Mengelola profil pengguna.

### Owner Studio

- Dashboard operasional studio.
- Pengelolaan ruangan, layanan, fasilitas, dan foto studio.
- Pengaturan jadwal reguler, template jadwal, dan perubahan jadwal harian.
- Pengelolaan serta konfirmasi status pemesanan.
- Pengaturan profil tenant dan metode pembayaran.
- Pengajuan dan verifikasi identitas pemilik studio.

### Developer/Administrator

- Dashboard untuk memantau tenant.
- Pengelolaan status dan verifikasi tenant.
- Peninjauan konfigurasi pembayaran tenant.
- Pengelolaan pengumuman untuk pengguna aplikasi.

## Teknologi

- PHP 8.2+
- Laravel 12
- MySQL
- Laravel Breeze
- Blade, Tailwind CSS, dan Alpine.js
- Vite
- Chart.js
- Midtrans PHP SDK
- Pest/PHPUnit

## Persyaratan Sistem

Pastikan perangkat sudah memiliki:

- PHP 8.2 atau versi yang lebih baru
- Composer
- Node.js dan npm
- MySQL
- Git

## Instalasi

1. Clone repository dan masuk ke folder proyek.

   ```bash
   git clone https://github.com/atannn/StudioKita.git
   cd StudioKita
   ```

2. Instal dependency PHP dan JavaScript.

   ```bash
   composer install
   npm install
   ```

3. Salin konfigurasi environment dan buat application key.

   Pada Windows PowerShell:

   ```powershell
   Copy-Item .env.example .env
   php artisan key:generate
   ```

   Pada Linux/macOS:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Buat database MySQL bernama `studiokita`, kemudian sesuaikan konfigurasi berikut di `.env`:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=studiokita
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. Jalankan migrasi, seeder, dan buat symbolic link untuk penyimpanan publik.

   ```bash
   php artisan migrate --seed
   php artisan storage:link
   ```

6. Jalankan aplikasi dalam mode pengembangan.

   ```bash
   composer run dev
   ```

   Aplikasi dapat diakses melalui `http://127.0.0.1:8000`.

## Akun Demo

Akun berikut dibuat oleh `php artisan migrate --seed` dan hanya ditujukan untuk pengembangan lokal:

| Peran | Email | Password |
| --- | --- | --- |
| Owner | `owner@studiokita.test` | `password123` |
| Customer | `customer@studiokita.test` | `password123` |

Jangan gunakan kredensial demo tersebut pada lingkungan produksi.

## Menjalankan Secara Terpisah

Jika tidak ingin menggunakan `composer run dev`, jalankan perintah berikut pada terminal terpisah:

```bash
php artisan serve
npm run dev
php artisan queue:listen
```

## Build untuk Produksi

```bash
npm run build
php artisan optimize
```

Pastikan `APP_ENV`, `APP_DEBUG`, database, mail, queue, dan konfigurasi pembayaran telah disesuaikan untuk lingkungan produksi.

## Pengujian

```bash
composer test
```

atau:

```bash
php artisan test
```

## Struktur Singkat

```text
app/          Logika aplikasi, model, middleware, dan controller
config/       Konfigurasi aplikasi
database/     Migration, factory, dan seeder
public/       Entry point dan aset publik
resources/    Blade view, CSS, dan JavaScript
routes/       Definisi route aplikasi
tests/        Pengujian aplikasi
```

## Catatan Keamanan

- Jangan commit file `.env` ke repository.
- Jangan menyimpan credential database atau Midtrans di source code.
- Nonaktifkan mode bypass pembayaran di luar lingkungan lokal atau testing.
- Ganti seluruh akun dan password demo sebelum deployment.

## Lisensi

Proyek ini dibuat untuk pengembangan StudioKita. Hubungi pemilik repository untuk informasi penggunaan dan distribusi.
