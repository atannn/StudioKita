from pathlib import Path


ROOT = Path(__file__).resolve().parent
OUTPUT = ROOT / "Manual_Book_StudioKita.html"


HTML = r"""<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manual Book StudioKita</title>
    <style>
        @page {
            size: A4;
            margin: 16mm 16mm 18mm;
        }

        * {
            box-sizing: border-box;
        }

        html {
            color-scheme: light;
        }

        body {
            margin: 0;
            color: #152238;
            background: #ffffff;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5pt;
            line-height: 1.55;
        }

        h1, h2, h3, h4 {
            margin: 0;
            color: #101a31;
            line-height: 1.25;
        }

        h1 {
            font-size: 28pt;
        }

        h2 {
            margin-bottom: 10px;
            font-size: 20pt;
        }

        h3 {
            margin: 16px 0 7px;
            font-size: 14pt;
        }

        h4 {
            margin: 12px 0 5px;
            font-size: 11.5pt;
        }

        p {
            margin: 6px 0 10px;
            text-align: justify;
        }

        ul, ol {
            margin: 6px 0 12px;
            padding-left: 22px;
        }

        li {
            margin: 3px 0;
        }

        a {
            color: #0f766e;
            text-decoration: none;
        }

        .cover {
            position: relative;
            display: flex;
            min-height: 255mm;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            padding: 22mm 18mm;
            background:
                radial-gradient(circle at 8% 12%, rgba(16, 185, 129, 0.28), transparent 42%),
                radial-gradient(circle at 92% 18%, rgba(249, 115, 22, 0.24), transparent 44%),
                #f7f4ee;
            page-break-after: always;
        }

        .cover::after {
            position: absolute;
            right: -42mm;
            bottom: -42mm;
            width: 115mm;
            height: 115mm;
            border: 18mm solid rgba(15, 118, 110, 0.10);
            border-radius: 50%;
            content: "";
        }

        .cover-logo {
            width: 58mm;
            max-height: 28mm;
            object-fit: contain;
            object-position: left center;
        }

        .cover-kicker {
            margin-top: 32mm;
            color: #0f766e;
            font-size: 11pt;
            font-weight: 700;
            letter-spacing: 0.22em;
        }

        .cover h1 {
            margin-top: 8px;
            font-size: 40pt;
            letter-spacing: 0.02em;
        }

        .cover-subtitle {
            max-width: 130mm;
            margin-top: 14px;
            color: #42526a;
            font-size: 17pt;
            line-height: 1.4;
            text-align: left;
        }

        .cover-meta {
            position: relative;
            z-index: 2;
            padding-top: 10mm;
            border-top: 1px solid rgba(15, 118, 110, 0.25);
            color: #42526a;
        }

        .chapter {
            page-break-before: always;
        }

        .section {
            margin-top: 12px;
        }

        .lead {
            color: #42526a;
            font-size: 11pt;
        }

        .eyebrow {
            margin-bottom: 6px;
            color: #0f766e;
            font-size: 8.5pt;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .toc {
            page-break-after: always;
        }

        .toc-row {
            display: grid;
            grid-template-columns: 14mm 1fr;
            gap: 4mm;
            padding: 7px 0;
            border-bottom: 1px solid #dfe6e4;
        }

        .toc-number {
            color: #0f766e;
            font-weight: 700;
        }

        .note, .warning, .success {
            margin: 12px 0;
            padding: 10px 12px;
            border-left: 4px solid #0f766e;
            border-radius: 5px;
            background: #ecfdf5;
        }

        .warning {
            border-left-color: #ea580c;
            background: #fff7ed;
        }

        .success {
            border-left-color: #16a34a;
            background: #f0fdf4;
        }

        table {
            width: 100%;
            margin: 10px 0 16px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        th, td {
            padding: 7px 8px;
            border: 1px solid #d8e1df;
            vertical-align: top;
        }

        th {
            background: #e8f5f1;
            color: #0b5f58;
            text-align: left;
        }

        pre {
            margin: 10px 0 14px;
            padding: 12px 14px;
            overflow-wrap: anywhere;
            white-space: pre-wrap;
            border: 1px solid #26354c;
            border-radius: 6px;
            color: #eef7f5;
            background: #162235;
            font-family: Consolas, "Courier New", monospace;
            font-size: 8.8pt;
            line-height: 1.48;
            page-break-inside: avoid;
        }

        code {
            font-family: Consolas, "Courier New", monospace;
        }

        .inline-code {
            padding: 1px 4px;
            border-radius: 3px;
            color: #0b5f58;
            background: #e8f5f1;
            font-family: Consolas, "Courier New", monospace;
            font-size: 9pt;
        }

        .architecture {
            display: grid;
            grid-template-columns: 1fr 16mm 1fr;
            align-items: center;
            margin: 14px 0 18px;
            page-break-inside: avoid;
        }

        .architecture-box {
            min-height: 47mm;
            padding: 12px;
            border: 1px solid #b7cbc6;
            border-radius: 8px;
            background: #f8fbfa;
        }

        .architecture-arrow {
            color: #0f766e;
            font-size: 22pt;
            font-weight: 700;
            text-align: center;
        }

        .flow {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 7px;
            margin: 12px 0 16px;
            page-break-inside: avoid;
        }

        .flow-item {
            padding: 9px;
            border: 1px solid #cbd9d5;
            border-radius: 6px;
            background: #f8fbfa;
            text-align: center;
        }

        .flow-number {
            display: block;
            width: 24px;
            height: 24px;
            margin: 0 auto 5px;
            border-radius: 50%;
            color: #ffffff;
            background: #0f766e;
            font-weight: 700;
            line-height: 24px;
        }

        .figure-page {
            page-break-before: always;
        }

        figure {
            margin: 12px 0 14px;
            page-break-inside: avoid;
        }

        figure img {
            display: block;
            width: 100%;
            max-height: 166mm;
            object-fit: contain;
            object-position: top center;
            border: 1px solid #ced8d6;
            border-radius: 6px;
            background: #ffffff;
        }

        figcaption {
            margin-top: 6px;
            color: #526177;
            font-size: 9pt;
            text-align: center;
        }

        .role-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 9px;
            margin: 12px 0 16px;
        }

        .role-card {
            padding: 11px;
            border: 1px solid #cbd9d5;
            border-radius: 7px;
            background: #f8fbfa;
        }

        .role-card h4 {
            margin-top: 0;
            color: #0f766e;
        }

        .checklist {
            list-style: none;
            padding-left: 0;
        }

        .checklist li {
            position: relative;
            padding-left: 21px;
        }

        .checklist li::before {
            position: absolute;
            left: 0;
            color: #0f766e;
            content: "\2713";
            font-weight: 700;
        }

        .small {
            font-size: 9pt;
        }

        .page-break {
            page-break-before: always;
        }

        .avoid-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <section class="cover">
        <div>
            <img class="cover-logo" src="assets/studiokita-logo.png" alt="Logo StudioKita">
            <div class="cover-kicker">MANUAL BOOK</div>
            <h1>STUDIOKITA</h1>
            <div class="cover-subtitle">
                Sistem Booking dan Manajemen Studio Musik Berbasis Web dengan Arsitektur Multi-Tenant
            </div>
        </div>
        <div class="cover-meta">
            <div><strong>Isi dokumen:</strong> kebutuhan sistem, instalasi, konfigurasi, restore database, dan penggunaan aplikasi.</div>
            <div><strong>Versi dokumentasi:</strong> 1.0</div>
            <div><strong>Tanggal:</strong> Juli 2026</div>
        </div>
    </section>

    <section class="toc">
        <div class="eyebrow">Daftar Isi</div>
        <h2>Manual StudioKita</h2>
        <div class="toc-row"><div class="toc-number">1</div><div>Pendahuluan dan gambaran sistem</div></div>
        <div class="toc-row"><div class="toc-number">2</div><div>Kebutuhan sistem: hardware dan software</div></div>
        <div class="toc-row"><div class="toc-number">3</div><div>Cara instalasi, konfigurasi, migration, dan restore database</div></div>
        <div class="toc-row"><div class="toc-number">4</div><div>Cara penggunaan untuk customer, owner, dan developer</div></div>
        <div class="toc-row"><div class="toc-number">5</div><div>Backup, restore, dan pemeliharaan</div></div>
        <div class="toc-row"><div class="toc-number">6</div><div>Troubleshooting dan checklist aplikasi siap digunakan</div></div>
        <div class="note">
            Seluruh screenshot pada dokumen ini diambil dari aplikasi StudioKita yang dijalankan
            pada lingkungan lokal dengan data demonstrasi. Nama studio dan pengguna pada gambar
            merupakan data contoh.
        </div>
    </section>

    <section>
        <div class="eyebrow">Bab 1</div>
        <h2>1. Pendahuluan</h2>
        <p class="lead">
            StudioKita merupakan aplikasi berbasis web untuk mempertemukan customer dengan studio
            musik sekaligus membantu owner mengelola kegiatan operasional studio. Aplikasi menyediakan
            katalog studio, booking jadwal, pembayaran, pengelolaan ruangan dan fasilitas, verifikasi
            studio, serta monitoring tenant oleh developer.
        </p>
        <p>
            Sistem menggunakan arsitektur multi-tenant dengan pola satu database pusat dan satu database
            MySQL untuk setiap tenant studio. Database pusat menyimpan akun pengguna, identitas tenant,
            status verifikasi, pengumuman, dan metadata koneksi tenant. Database tenant menyimpan data
            operasional studio seperti room, service, facility, jadwal, booking, dan payment.
        </p>

        <div class="architecture">
            <div class="architecture-box">
                <h4>Database pusat: studiokita</h4>
                <ul>
                    <li>Users dan role pengguna</li>
                    <li>Tenants dan status studio</li>
                    <li>Verifikasi dan metadata tenant</li>
                    <li>Metadata nama database tenant</li>
                    <li>Broadcast atau pengumuman</li>
                </ul>
            </div>
            <div class="architecture-arrow">&#8596;</div>
            <div class="architecture-box">
                <h4>Database tenant</h4>
                <ul>
                    <li>Rooms, services, dan facilities</li>
                    <li>Jadwal dan template jadwal</li>
                    <li>Bookings dan payments</li>
                    <li>Foto serta profil operasional</li>
                    <li>Pengaturan pembayaran tenant</li>
                </ul>
            </div>
        </div>

        <div class="warning">
            <strong>Penting:</strong> backup database pusat saja belum cukup. Setiap database tenant,
            misalnya <span class="inline-code">studiokita_tenant_1</span>, juga harus dibackup dan
            direstore agar data booking dan operasional studio tidak hilang.
        </div>

        <div class="role-grid">
            <div class="role-card">
                <h4>Customer</h4>
                <div>Mencari studio, melihat detail, booking, membayar, dan melihat riwayat pesanan.</div>
            </div>
            <div class="role-card">
                <h4>Owner</h4>
                <div>Mengelola profil studio, data master, jadwal, booking, pembayaran, dan verifikasi.</div>
            </div>
            <div class="role-card">
                <h4>Developer</h4>
                <div>Memantau tenant, memoderasi status, memeriksa verifikasi, dan mengatur Midtrans.</div>
            </div>
        </div>
    </section>

    <section class="chapter">
        <div class="eyebrow">Bab 2</div>
        <h2>2. Kebutuhan Sistem</h2>

        <h3>2.1 Kebutuhan Perangkat Keras</h3>
        <table>
            <thead>
                <tr><th>Komponen</th><th>Minimum</th><th>Disarankan</th></tr>
            </thead>
            <tbody>
                <tr><td>Prosesor</td><td>Intel Core i3 atau setara</td><td>Intel Core i5/AMD Ryzen 5 atau lebih tinggi</td></tr>
                <tr><td>RAM</td><td>4 GB</td><td>8 GB atau lebih</td></tr>
                <tr><td>Penyimpanan</td><td>2 GB ruang kosong</td><td>10 GB atau lebih untuk source, foto, log, dan backup</td></tr>
                <tr><td>Jaringan</td><td>LAN atau Wi-Fi lokal</td><td>Internet stabil untuk Composer, npm, email, dan Midtrans</td></tr>
                <tr><td>Resolusi layar</td><td>1366 x 768</td><td>1920 x 1080</td></tr>
            </tbody>
        </table>
        <p>
            Kebutuhan penyimpanan bertambah mengikuti jumlah tenant, foto studio, dokumen verifikasi,
            transaksi, dan backup. Untuk penggunaan produksi, database dan file upload sebaiknya memiliki
            media backup terpisah.
        </p>

        <h3>2.2 Kebutuhan Perangkat Lunak</h3>
        <table>
            <thead>
                <tr><th>Perangkat Lunak</th><th>Versi/Keterangan</th><th>Fungsi</th></tr>
            </thead>
            <tbody>
                <tr><td>Sistem operasi</td><td>Windows 10/11 atau Linux</td><td>Lingkungan server atau pengembangan</td></tr>
                <tr><td>PHP</td><td>8.2 atau lebih baru</td><td>Menjalankan Laravel</td></tr>
                <tr><td>Laravel</td><td>12.x</td><td>Framework backend aplikasi</td></tr>
                <tr><td>MySQL/MariaDB</td><td>MySQL 8.x atau MariaDB 10.4+</td><td>Database pusat dan database tenant</td></tr>
                <tr><td>Composer</td><td>2.x</td><td>Memasang dependensi PHP</td></tr>
                <tr><td>Node.js</td><td>20 LTS atau lebih baru</td><td>Menjalankan Vite dan build frontend</td></tr>
                <tr><td>npm</td><td>10.x atau versi yang sesuai Node.js</td><td>Memasang dependensi JavaScript</td></tr>
                <tr><td>XAMPP</td><td>PHP 8.2</td><td>Apache, PHP, MySQL/MariaDB, dan phpMyAdmin untuk lokal</td></tr>
                <tr><td>Browser</td><td>Chrome/Edge versi terbaru</td><td>Mengakses aplikasi dan dashboard</td></tr>
                <tr><td>Editor</td><td>Visual Studio Code, opsional</td><td>Mengubah konfigurasi dan source code</td></tr>
            </tbody>
        </table>

        <h3>2.3 Ekstensi dan Koneksi Pendukung</h3>
        <p>
            Pastikan ekstensi PHP <span class="inline-code">pdo_mysql</span>,
            <span class="inline-code">mbstring</span>, <span class="inline-code">openssl</span>,
            <span class="inline-code">fileinfo</span>, <span class="inline-code">tokenizer</span>,
            <span class="inline-code">xml</span>, dan <span class="inline-code">ctype</span> aktif.
            Koneksi internet diperlukan saat pertama kali menjalankan <span class="inline-code">composer install</span>
            dan <span class="inline-code">npm install</span>. Koneksi internet juga diperlukan untuk transaksi
            Midtrans dan pengiriman email OTP apabila layanan tersebut diaktifkan.
        </p>

        <h3>2.4 Versi yang Diverifikasi pada Proyek</h3>
        <pre>PHP       : 8.2.12
Laravel   : 12.46.0
Composer  : 2.8.5
Node.js   : v22.21.1
npm       : 10.9.4
Database  : MySQL/MariaDB
Frontend  : Vite, Tailwind CSS, Alpine.js, Chart.js</pre>
    </section>

    <section class="chapter">
        <div class="eyebrow">Bab 3</div>
        <h2>3. Cara Instalasi</h2>
        <p class="lead">
            Bagian ini menjelaskan instalasi source code hingga aplikasi siap digunakan pada komputer
            lokal berbasis Windows dan XAMPP. Sesuaikan lokasi folder serta credential database dengan
            lingkungan yang digunakan.
        </p>

        <h3>3.1 Instalasi Perangkat Lunak Pendukung</h3>
        <ol>
            <li>Pasang XAMPP dengan PHP 8.2 atau lebih baru.</li>
            <li>Pasang Composer versi 2.x.</li>
            <li>Pasang Node.js 20 LTS atau versi yang lebih baru.</li>
            <li>Pasang Google Chrome atau Microsoft Edge.</li>
            <li>Aktifkan MySQL/MariaDB dari XAMPP Control Panel.</li>
        </ol>
        <p>Periksa instalasi melalui Command Prompt atau PowerShell:</p>
        <pre>php --version
composer --version
node --version
npm --version</pre>
        <div class="success">
            Instalasi dianggap berhasil apabila setiap perintah menampilkan nomor versi dan tidak
            menampilkan pesan bahwa perintah tidak dikenali.
        </div>

        <h3>3.2 Menempatkan Source Code</h3>
        <ol>
            <li>Ekstrak source code StudioKita ke folder kerja, misalnya <span class="inline-code">C:\xampp82\htdocs\studiokita</span>.</li>
            <li>Buka terminal pada folder tersebut.</li>
            <li>Pastikan file <span class="inline-code">artisan</span>, <span class="inline-code">composer.json</span>, dan <span class="inline-code">package.json</span> tersedia.</li>
        </ol>
        <pre>cd C:\xampp82\htdocs\studiokita
composer install
npm install</pre>

        <h3>3.3 Membuat dan Mengonfigurasi File Environment</h3>
        <p>Salin file contoh menjadi file konfigurasi lokal:</p>
        <pre>copy .env.example .env
php artisan key:generate</pre>
        <p>Kemudian sesuaikan bagian utama pada file <span class="inline-code">.env</span>:</p>
        <pre>APP_NAME=StudioKita
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=studiokita
DB_USERNAME=root
DB_PASSWORD=</pre>
        <div class="note">
            Variabel <span class="inline-code">TENANT_DB_*</span> bersifat opsional. Jika tidak diisi,
            database tenant memakai host, port, username, dan password database pusat. Nama database
            tenant dibentuk otomatis dari nama database pusat dan ID tenant, misalnya
            <span class="inline-code">studiokita_tenant_1</span>.
        </div>

        <h3>3.4 Membuat Database Pusat</h3>
        <p>
            Buka phpMyAdmin melalui <span class="inline-code">http://localhost/phpmyadmin</span>, lalu
            buat database bernama <span class="inline-code">studiokita</span>. Alternatifnya, jalankan SQL berikut:
        </p>
        <pre>CREATE DATABASE studiokita
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;</pre>

        <h3>3.5 Instalasi Baru Menggunakan Migration</h3>
        <p>
            Untuk instalasi tanpa data lama, jalankan migration database pusat. Database tenant belum
            perlu dibuat manual karena sistem akan membuatnya ketika owner menyimpan setup studio.
        </p>
        <pre>php artisan migrate --force
php artisan storage:link
npm run build
php artisan optimize:clear</pre>
        <p>
            Saat owner menyelesaikan setup tahap pertama, <span class="inline-code">TenantDatabaseManager</span>
            membuat database tenant, menyimpan metadata pada tabel <span class="inline-code">tenant_databases</span>,
            lalu menjalankan seluruh migration dalam folder <span class="inline-code">database/migrations/tenant</span>.
        </p>

        <div class="flow">
            <div class="flow-item"><span class="flow-number">1</span>Owner registrasi</div>
            <div class="flow-item"><span class="flow-number">2</span>Data tenant disimpan</div>
            <div class="flow-item"><span class="flow-number">3</span>Database tenant dibuat</div>
            <div class="flow-item"><span class="flow-number">4</span>Migration tenant dijalankan</div>
        </div>
    </section>

    <section class="chapter">
        <div class="eyebrow">Instalasi Lanjutan</div>
        <h2>3.6 Restore Database dari Backup</h2>
        <p>
            Jika instalasi menggunakan data lama, restore harus dilakukan terhadap database pusat dan
            seluruh database tenant. Contoh berikut dijalankan melalui Command Prompt dengan utilitas
            MySQL XAMPP.
        </p>

        <h3>3.6.1 Restore Database Pusat</h3>
        <pre>C:\xampp82\mysql\bin\mysql.exe -u root -p -e "CREATE DATABASE studiokita CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

C:\xampp82\mysql\bin\mysql.exe -u root -p studiokita &lt; backup_studiokita.sql</pre>

        <h3>3.6.2 Restore Database Tenant</h3>
        <p>
            Lihat kolom <span class="inline-code">database_name</span> pada tabel
            <span class="inline-code">tenant_databases</span> di database pusat. Buat dan restore setiap
            database menggunakan nama yang sama.
        </p>
        <pre>C:\xampp82\mysql\bin\mysql.exe -u root -p -e "CREATE DATABASE studiokita_tenant_1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

C:\xampp82\mysql\bin\mysql.exe -u root -p studiokita_tenant_1 &lt; backup_studiokita_tenant_1.sql</pre>
        <div class="warning">
            Jangan mengubah nama database tenant tanpa memperbarui kolom
            <span class="inline-code">tenant_databases.database_name</span>. Ketidaksesuaian nama menyebabkan
            aplikasi terhubung ke database yang salah atau membuat database kosong baru.
        </div>

        <h3>3.6.3 Menyamakan Struktur Setelah Restore</h3>
        <p>
            Setelah restore, jalankan migration pusat. Migration tenant akan diperiksa ketika tenant
            diaktifkan oleh middleware atau manager database.
        </p>
        <pre>php artisan migrate --force
php artisan optimize:clear</pre>

        <h3>3.7 Build Frontend dan File Upload</h3>
        <pre>npm install
npm run build
php artisan storage:link</pre>
        <p>
            Perintah <span class="inline-code">npm run build</span> menghasilkan aset produksi pada
            folder <span class="inline-code">public/build</span>. Perintah
            <span class="inline-code">storage:link</span> membuat tautan
            <span class="inline-code">public/storage</span> agar logo, foto room, galeri, dan dokumen
            dapat diakses oleh browser.
        </p>

        <h3>3.8 Konfigurasi Email dan Midtrans</h3>
        <p>
            Untuk pengiriman OTP melalui email, ubah variabel <span class="inline-code">MAIL_*</span>
            sesuai layanan SMTP. Pada mode lokal, <span class="inline-code">MAIL_MAILER=log</span>
            menyimpan isi email ke log Laravel.
        </p>
        <pre>MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=akun-smtp
MAIL_PASSWORD=password-smtp
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com</pre>
        <p>
            Konfigurasi Midtrans dilakukan per tenant melalui menu Payment owner dan halaman detail
            tenant developer. Untuk production, URL webhook harus dapat diakses melalui internet:
        </p>
        <pre>https://domain-aplikasi.com/payments/midtrans/webhook</pre>
        <div class="warning">
            Midtrans tidak dapat mengirim webhook langsung ke alamat localhost. Gunakan domain publik
            atau tunnel HTTPS ketika melakukan pengujian callback dari komputer lokal.
        </div>

        <h3>3.9 Menjalankan Aplikasi</h3>
        <pre>php artisan serve
php artisan queue:work</pre>
        <p>
            Buka <span class="inline-code">http://127.0.0.1:8000</span> melalui browser. Untuk mode
            pengembangan lengkap, proyek juga menyediakan perintah <span class="inline-code">composer run dev</span>
            yang menjalankan web server, queue listener, log viewer, dan Vite secara bersamaan.
        </p>

        <h3>3.10 Pemeriksaan Akhir Instalasi</h3>
        <ul class="checklist">
            <li>Halaman <span class="inline-code">/studios</span> dapat dibuka.</li>
            <li>Registrasi dan login dapat dilakukan.</li>
            <li>Owner dapat menyimpan setup studio.</li>
            <li>Database tenant terbentuk otomatis di MySQL.</li>
            <li>Foto pada <span class="inline-code">public/storage</span> dapat ditampilkan.</li>
            <li>Dashboard owner dan developer dapat dibuka sesuai role.</li>
            <li>Queue worker berjalan apabila email atau job asynchronous digunakan.</li>
        </ul>
    </section>

    <section class="chapter">
        <div class="eyebrow">Bab 4</div>
        <h2>4. Cara Penggunaan</h2>
        <p class="lead">
            StudioKita menyediakan halaman publik dan tiga jenis akses utama. Menu yang muncul setelah
            login disesuaikan dengan role pengguna agar setiap pengguna hanya menjalankan operasi yang
            menjadi kewenangannya.
        </p>

        <h3>4.1 Alamat Akses Utama</h3>
        <table>
            <thead><tr><th>Halaman</th><th>Alamat</th><th>Keterangan</th></tr></thead>
            <tbody>
                <tr><td>Beranda</td><td><span class="inline-code">/studios</span></td><td>Informasi utama dan rekomendasi studio</td></tr>
                <tr><td>Katalog</td><td><span class="inline-code">/studios/katalog</span></td><td>Pencarian dan filter studio</td></tr>
                <tr><td>Login customer/owner</td><td><span class="inline-code">/login</span></td><td>Pilih tipe login pada halaman yang sama</td></tr>
                <tr><td>Login developer</td><td><span class="inline-code">/developer</span></td><td>Halaman login khusus developer</td></tr>
                <tr><td>Dashboard owner</td><td><span class="inline-code">/owner/dashboard</span></td><td>Memerlukan akun owner</td></tr>
                <tr><td>Dashboard developer</td><td><span class="inline-code">/developer/dashboard</span></td><td>Memerlukan akun developer</td></tr>
            </tbody>
        </table>

        <h3>4.2 Ringkasan Alur Customer</h3>
        <div class="flow">
            <div class="flow-item"><span class="flow-number">1</span>Cari studio</div>
            <div class="flow-item"><span class="flow-number">2</span>Login customer</div>
            <div class="flow-item"><span class="flow-number">3</span>Pilih jadwal dan booking</div>
            <div class="flow-item"><span class="flow-number">4</span>Bayar dan pantau status</div>
        </div>

        <h3>4.3 Ringkasan Alur Owner</h3>
        <div class="flow">
            <div class="flow-item"><span class="flow-number">1</span>Registrasi owner</div>
            <div class="flow-item"><span class="flow-number">2</span>Setup tenant</div>
            <div class="flow-item"><span class="flow-number">3</span>Kelola data dan jadwal</div>
            <div class="flow-item"><span class="flow-number">4</span>Kelola booking</div>
        </div>
    </section>

    <section class="figure-page">
        <div class="eyebrow">Penggunaan Publik</div>
        <h2>4.4 Beranda StudioKita</h2>
        <p>
            Akses aplikasi melalui alamat server, misalnya <span class="inline-code">http://127.0.0.1:8000</span>.
            Sistem mengarahkan pengguna ke halaman beranda. Pengguna dapat membuka katalog, melihat cara
            kerja, melakukan login, registrasi, atau langsung mencari studio berdasarkan nama dan kota.
        </p>
        <figure>
            <img src="assets/screenshots/01-beranda.png" alt="Beranda StudioKita">
            <figcaption>Gambar 1. Halaman beranda StudioKita.</figcaption>
        </figure>
    </section>

    <section class="figure-page">
        <div class="eyebrow">Penggunaan Publik</div>
        <h2>4.5 Mencari Studio pada Katalog</h2>
        <p>
            Buka menu Studio untuk menampilkan katalog. Gunakan kolom pencarian, kota, jenis layanan
            latihan atau rekaman, dan urutan nama. Klik kartu studio atau tombol detail untuk membuka
            profil studio yang dipilih.
        </p>
        <figure>
            <img src="assets/screenshots/02-katalog-studio.png" alt="Katalog StudioKita">
            <figcaption>Gambar 2. Katalog dan filter studio.</figcaption>
        </figure>
    </section>

    <section class="figure-page">
        <div class="eyebrow">Penggunaan Publik</div>
        <h2>4.6 Melihat Detail Studio</h2>
        <p>
            Halaman detail menampilkan identitas studio, alamat, jam operasional, status verifikasi,
            galeri, fasilitas, layanan, harga, dan daftar ruangan. Klik tombol Booking Studio untuk
            melanjutkan pemesanan. Pengguna yang belum login akan diminta login terlebih dahulu.
        </p>
        <figure>
            <img src="assets/screenshots/03-detail-studio.png" alt="Detail Studio">
            <figcaption>Gambar 3. Detail studio dan tombol booking.</figcaption>
        </figure>
    </section>

    <section class="figure-page">
        <div class="eyebrow">Akses Pengguna</div>
        <h2>4.7 Login Customer dan Owner</h2>
        <p>
            Masukkan email dan password, lalu pilih tipe login yang sesuai. Customer memakai tab
            Login sebagai Customer, sedangkan pemilik studio memakai tab Login sebagai Owner.
            Sistem menolak login apabila role akun tidak sesuai dengan tipe login yang dipilih.
        </p>
        <figure>
            <img src="assets/screenshots/04-login-customer.png" alt="Login StudioKita">
            <figcaption>Gambar 4. Halaman login customer dan owner.</figcaption>
        </figure>
        <ol>
            <li>Pilih tab login sesuai role.</li>
            <li>Isi email dan password.</li>
            <li>Aktifkan Remember me jika perangkat merupakan perangkat pribadi.</li>
            <li>Klik tombol Log in.</li>
        </ol>
    </section>

    <section class="figure-page">
        <div class="eyebrow">Customer</div>
        <h2>4.8 Membuat Booking Studio</h2>
        <p>
            Customer memilih layanan terlebih dahulu. Sistem kemudian menyaring room yang sesuai dengan
            tipe layanan. Setelah customer memilih tanggal, sistem menampilkan slot yang masih tersedia.
            Customer juga menentukan metode pembayaran dan skema pembayaran sebelum menekan Booking sekarang.
        </p>
        <figure>
            <img src="assets/screenshots/05-booking-customer.png" alt="Booking customer">
            <figcaption>Gambar 5. Form booking studio oleh customer.</figcaption>
        </figure>
        <ol>
            <li>Pilih service latihan atau rekaman.</li>
            <li>Pilih room yang tersedia.</li>
            <li>Pilih tanggal dan slot waktu.</li>
            <li>Pilih Midtrans atau Cash sesuai kebijakan tenant.</li>
            <li>Pilih pembayaran lunas atau DP jika tersedia.</li>
            <li>Periksa ringkasan dan klik Booking sekarang.</li>
        </ol>
    </section>

    <section class="figure-page">
        <div class="eyebrow">Customer</div>
        <h2>4.9 Profil dan Riwayat Booking Customer</h2>
        <p>
            Menu Profil Saya menampilkan identitas customer dan riwayat booking dari seluruh tenant.
            Customer dapat melihat studio, room, layanan, jadwal, total tagihan, status booking, dan
            status pembayaran. Apabila masih ada tagihan, tombol pembayaran atau pelunasan muncul sesuai
            kondisi transaksi.
        </p>
        <figure>
            <img src="assets/screenshots/06-profil-customer.png" alt="Profil customer">
            <figcaption>Gambar 6. Profil dan riwayat booking customer.</figcaption>
        </figure>
    </section>

    <section class="figure-page">
        <div class="eyebrow">Owner</div>
        <h2>4.10 Dashboard dan Analytics Owner</h2>
        <p>
            Setelah login, owner diarahkan ke dashboard tenant. Dashboard menampilkan total booking,
            booking aktif, booking selesai, pembatalan, pendapatan, grafik per tipe dan room, jumlah data
            master, kesiapan studio, serta layanan dan room terpopuler.
        </p>
        <figure>
            <img src="assets/screenshots/07-dashboard-owner.png" alt="Dashboard owner">
            <figcaption>Gambar 7. Dashboard analytics owner.</figcaption>
        </figure>
        <p>
            Gunakan filter tanggal untuk mengubah periode analytics. Menu di sisi kiri digunakan untuk
            membuka pengelolaan room, service, facility, jadwal, booking, payment, dan verifikasi.
        </p>
    </section>

    <section class="figure-page">
        <div class="eyebrow">Owner</div>
        <h2>4.11 Mengelola Room</h2>
        <p>
            Menu Rooms menampilkan ruangan yang dimiliki tenant. Owner dapat menambah, mengubah, atau
            menghapus room. Data room meliputi nama, foto, tipe latihan/rekaman, kapasitas, fasilitas,
            deskripsi, dan status aktif.
        </p>
        <figure>
            <img src="assets/screenshots/08-room-owner.png" alt="Manajemen room">
            <figcaption>Gambar 8. Daftar room pada dashboard owner.</figcaption>
        </figure>
        <div class="note">
            Foto room disimpan pada storage tenant dan ditampilkan pada katalog/detail studio.
            Fasilitas yang dipilih pada room harus sudah dibuat melalui menu Fasilitas.
        </div>
    </section>

    <section class="figure-page">
        <div class="eyebrow">Owner</div>
        <h2>4.12 Mengelola Layanan</h2>
        <p>
            Menu Services digunakan untuk membuat paket latihan atau rekaman. Owner mengisi nama
            layanan, tipe, durasi, harga weekday, harga weekend, deskripsi, dan status. Tipe layanan
            menentukan room yang dapat dipilih customer saat booking.
        </p>
        <figure>
            <img src="assets/screenshots/09-layanan-owner.png" alt="Manajemen layanan">
            <figcaption>Gambar 9. Daftar layanan dan harga studio.</figcaption>
        </figure>
    </section>

    <section class="figure-page">
        <div class="eyebrow">Owner</div>
        <h2>4.13 Mengelola Fasilitas</h2>
        <p>
            Menu Fasilitas menyimpan daftar alat dan fasilitas studio. Owner dapat mengatur nama,
            deskripsi, jumlah total, dan status. Satu fasilitas dapat dikaitkan ke room tertentu selama
            jumlah pemakaiannya tidak melebihi stok yang tersedia.
        </p>
        <figure>
            <img src="assets/screenshots/10-fasilitas-owner.png" alt="Manajemen fasilitas">
            <figcaption>Gambar 10. Pengelolaan fasilitas dan jumlah alat.</figcaption>
        </figure>
    </section>

    <section class="figure-page">
        <div class="eyebrow">Owner</div>
        <h2>4.14 Mengelola Jadwal</h2>
        <p>
            Kalender jadwal menampilkan slot available, booked, dan blocked. Owner dapat memfilter
            kalender berdasarkan room, membuat template jadwal berulang, serta membuat override harian
            untuk perubahan pada tanggal tertentu.
        </p>
        <figure>
            <img src="assets/screenshots/11-jadwal-owner.png" alt="Kalender jadwal">
            <figcaption>Gambar 11. Kalender dan manajemen jadwal studio.</figcaption>
        </figure>
        <ul>
            <li><strong>Template Jadwal:</strong> membuat pola berulang berdasarkan hari dan jam.</li>
            <li><strong>Atur Jadwal Harian:</strong> menambah, mengubah, atau memblokir slot pada tanggal tertentu.</li>
            <li><strong>Kalender:</strong> memeriksa hasil akhir slot setelah sinkronisasi template dan override.</li>
        </ul>
    </section>

    <section class="figure-page">
        <div class="eyebrow">Owner</div>
        <h2>4.15 Mengelola Booking</h2>
        <p>
            Menu Booking digunakan untuk memantau pesanan customer. Owner dapat memfilter status,
            mengonfirmasi booking, menandai no show, membatalkan, atau menyelesaikan sesi. Perubahan
            status booking juga memengaruhi ketersediaan jadwal dan status pembayaran terkait.
        </p>
        <figure>
            <img src="assets/screenshots/12-booking-owner.png" alt="Manajemen booking owner">
            <figcaption>Gambar 12. Daftar dan status booking pada dashboard owner.</figcaption>
        </figure>
    </section>

    <section class="figure-page">
        <div class="eyebrow">Owner</div>
        <h2>4.16 Pengaturan Pembayaran</h2>
        <p>
            Owner menggunakan menu Payment untuk menyimpan data pengajuan Midtrans dan preferensi
            pembayaran tenant. Preferensi mencakup pembayaran penuh, DP, persentase DP, pembayaran cash,
            dan instruksi pembayaran tunai. Key Midtrans akhir diisi dan diuji oleh developer.
        </p>
        <figure>
            <img src="assets/screenshots/13-pembayaran-owner.png" alt="Pengaturan pembayaran owner">
            <figcaption>Gambar 13. Pengajuan dan preferensi pembayaran tenant.</figcaption>
        </figure>
    </section>

    <section class="figure-page">
        <div class="eyebrow">Owner</div>
        <h2>4.17 Verifikasi Studio</h2>
        <p>
            Owner melakukan verifikasi email melalui OTP, melengkapi data dasar, lalu mengunggah dokumen
            verifikasi manual. Developer meninjau dokumen tersebut dan memberikan keputusan approve atau
            reject. Status verifikasi ditampilkan pada halaman detail studio dan dashboard.
        </p>
        <figure>
            <img src="assets/screenshots/14-verifikasi-owner.png" alt="Verifikasi owner">
            <figcaption>Gambar 14. Tahapan verifikasi tenant oleh owner.</figcaption>
        </figure>
    </section>

    <section class="figure-page">
        <div class="eyebrow">Developer</div>
        <h2>4.18 Monitoring Tenant</h2>
        <p>
            Developer login melalui alamat <span class="inline-code">/developer</span>. Dashboard
            menampilkan jumlah tenant, tenant aktif, owner, booking bulanan, tenant yang belum lengkap,
            dan pengajuan verifikasi yang masih menunggu. Daftar tenant dapat difilter berdasarkan nama,
            status, kota, dan status verifikasi.
        </p>
        <figure>
            <img src="assets/screenshots/15-dashboard-developer.png" alt="Dashboard developer">
            <figcaption>Gambar 15. Dashboard monitoring tenant oleh developer.</figcaption>
        </figure>
        <p>
            Developer juga dapat membuat broadcast pengumuman yang ditampilkan pada dashboard owner.
        </p>
    </section>

    <section class="figure-page">
        <div class="eyebrow">Developer</div>
        <h2>4.19 Detail, Moderasi, dan Konfigurasi Tenant</h2>
        <p>
            Klik Detail pada daftar tenant untuk membuka halaman pemeriksaan. Developer dapat melihat
            identitas tenant, kesiapan data, dokumen verifikasi, pengajuan Midtrans, status pengujian
            koneksi, dan status aktif studio.
        </p>
        <figure>
            <img src="assets/screenshots/16-detail-tenant-developer.png" alt="Detail tenant developer">
            <figcaption>Gambar 16. Detail tenant dan aksi developer.</figcaption>
        </figure>
        <ul>
            <li>Approve atau reject verifikasi manual.</li>
            <li>Menyimpan Client Key dan Server Key Midtrans tenant.</li>
            <li>Menguji koneksi Midtrans sebelum pembayaran diaktifkan.</li>
            <li>Mengaktifkan atau menonaktifkan tenant.</li>
            <li>Menghapus tenant secara permanen hanya apabila benar-benar diperlukan.</li>
        </ul>
    </section>

    <section class="chapter">
        <div class="eyebrow">Bab 5</div>
        <h2>5. Backup, Restore, dan Pemeliharaan</h2>

        <h3>5.1 Backup Database Pusat</h3>
        <pre>C:\xampp82\mysql\bin\mysqldump.exe -u root -p --databases studiokita &gt; backup_studiokita.sql</pre>

        <h3>5.2 Backup Database Tenant</h3>
        <pre>C:\xampp82\mysql\bin\mysqldump.exe -u root -p --databases studiokita_tenant_1 &gt; backup_studiokita_tenant_1.sql</pre>
        <p>
            Ulangi perintah tersebut untuk setiap nama database yang tercatat pada tabel
            <span class="inline-code">tenant_databases</span>. Simpan backup pusat, backup tenant,
            dan folder <span class="inline-code">storage/app/public</span> dalam satu periode backup
            yang sama agar data transaksi dan foto tetap konsisten.
        </p>

        <h3>5.3 Backup File Upload</h3>
        <p>
            Salin folder <span class="inline-code">storage/app/public</span> ke media backup. Folder ini
            berisi logo tenant, foto galeri, foto room, dan file publik lain. Dokumen verifikasi yang
            disimpan pada disk private juga harus dimasukkan ke prosedur backup sesuai konfigurasi aplikasi.
        </p>

        <h3>5.4 Pemeliharaan Berkala</h3>
        <ul>
            <li>Periksa log pada <span class="inline-code">storage/logs/laravel.log</span>.</li>
            <li>Pastikan queue worker aktif.</li>
            <li>Perbarui dependensi hanya setelah pengujian pada lingkungan staging.</li>
            <li>Jalankan migration pusat dan tenant setelah perubahan skema.</li>
            <li>Uji restore backup secara berkala, bukan hanya membuat file backup.</li>
            <li>Periksa masa berlaku credential SMTP dan Midtrans.</li>
        </ul>

        <h3>5.5 Keamanan Konfigurasi</h3>
        <div class="warning">
            File <span class="inline-code">.env</span>, password database, Server Key Midtrans, dan
            credential SMTP tidak boleh dimasukkan ke repository publik atau dicantumkan pada screenshot.
            Gunakan <span class="inline-code">.env.example</span> hanya sebagai template tanpa nilai rahasia.
        </div>
    </section>

    <section class="chapter">
        <div class="eyebrow">Bab 6</div>
        <h2>6. Troubleshooting</h2>
        <table>
            <thead><tr><th>Masalah</th><th>Penyebab Umum</th><th>Solusi</th></tr></thead>
            <tbody>
                <tr>
                    <td>Access denied for user MySQL</td>
                    <td>Username/password pada <span class="inline-code">.env</span> tidak sesuai</td>
                    <td>Periksa DB_HOST, DB_PORT, DB_USERNAME, dan DB_PASSWORD, lalu jalankan <span class="inline-code">php artisan optimize:clear</span>.</td>
                </tr>
                <tr>
                    <td>Unknown database tenant</td>
                    <td>Database tenant belum dibuat atau belum direstore</td>
                    <td>Periksa tabel tenant_databases dan restore database dengan nama yang sama.</td>
                </tr>
                <tr>
                    <td>Table tidak ditemukan pada tenant</td>
                    <td>Migration tenant belum berjalan</td>
                    <td>Akses ulang tenant atau jalankan migration tenant melalui koneksi tenant yang benar.</td>
                </tr>
                <tr>
                    <td>Vite manifest not found</td>
                    <td>Aset frontend belum dibuild</td>
                    <td>Jalankan <span class="inline-code">npm install</span> dan <span class="inline-code">npm run build</span>.</td>
                </tr>
                <tr>
                    <td>Foto tidak tampil</td>
                    <td>Storage link belum tersedia</td>
                    <td>Jalankan <span class="inline-code">php artisan storage:link</span> dan periksa file pada storage/app/public.</td>
                </tr>
                <tr>
                    <td>Error 419 Page Expired</td>
                    <td>Session atau CSRF token tidak valid</td>
                    <td>Hapus cookie browser, periksa SESSION_DRIVER, lalu jalankan optimize:clear.</td>
                </tr>
                <tr>
                    <td>Midtrans tidak membuka popup</td>
                    <td>Client Key, Snap token, atau konfigurasi tenant belum siap</td>
                    <td>Periksa pengaturan developer, uji koneksi, dan lihat log Laravel.</td>
                </tr>
                <tr>
                    <td>Webhook tidak diterima</td>
                    <td>URL masih localhost atau Server Key salah</td>
                    <td>Gunakan HTTPS publik/tunnel dan daftarkan endpoint webhook yang benar.</td>
                </tr>
                <tr>
                    <td>Port 8000 sudah digunakan</td>
                    <td>Ada proses lain pada port yang sama</td>
                    <td>Jalankan <span class="inline-code">php artisan serve --port=8001</span>.</td>
                </tr>
            </tbody>
        </table>

        <div class="page-break"></div>
        <h3>6.1 Checklist Aplikasi Siap Dipakai</h3>
        <ul class="checklist">
            <li>APP_KEY sudah terbentuk dan APP_URL benar.</li>
            <li>Koneksi database pusat berhasil.</li>
            <li>Seluruh database tenant tersedia dan dapat diakses.</li>
            <li>Migration pusat serta tenant telah selesai.</li>
            <li>Folder storage dapat ditulis dan storage link aktif.</li>
            <li>Aset frontend sudah dibuild.</li>
            <li>Akun customer, owner, dan developer dapat login sesuai role.</li>
            <li>Owner dapat membuat room, service, facility, dan jadwal.</li>
            <li>Customer dapat membuat booking dan melihat riwayat.</li>
            <li>Developer dapat membuka detail tenant dan melakukan moderasi.</li>
            <li>Email OTP dan Midtrans telah diuji jika digunakan.</li>
            <li>Backup database pusat, tenant, dan file upload tersedia.</li>
        </ul>

        <div class="success">
            Setelah seluruh checklist terpenuhi, StudioKita siap digunakan pada lingkungan lokal.
            Untuk deployment production, gunakan HTTPS, nonaktifkan APP_DEBUG, batasi akses database,
            jalankan queue worker secara permanen, dan terapkan backup otomatis.
        </div>
    </section>
</body>
</html>
"""


OUTPUT.write_text(HTML, encoding="utf-8")
print(OUTPUT)
