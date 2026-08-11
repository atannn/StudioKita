# Alur SaaS Sistem StudioKita

Dokumen ini menjelaskan alur SaaS pada sistem StudioKita, mulai dari pendaftaran owner, pembuatan tenant, pembuatan database tenant, pemisahan data, pengelolaan studio oleh owner, pengelolaan tenant oleh developer, hingga proses booking dan pembayaran oleh customer.

---

## 1. Konsep Utama SaaS Pada StudioKita

StudioKita menggunakan konsep **SaaS multi-tenant**.

Artinya:

- Satu aplikasi Laravel digunakan oleh banyak studio.
- Setiap studio disebut sebagai **tenant**.
- Data identitas tenant disimpan di database pusat.
- Data operasional masing-masing studio disimpan di database tenant masing-masing.

Dengan konsep ini, satu aplikasi dapat melayani banyak studio, tetapi data operasional antar studio tetap dipisahkan.

---

## 2. Pembagian Database

### 2.1 Database Pusat

Database pusat menggunakan koneksi:

```php
mysql
```

Database pusat menyimpan data yang bersifat global, seperti:

- `users`
- `tenants`
- `tenant_databases`
- data verifikasi tenant
- data pengajuan Midtrans tenant
- data developer

Letak kode:

- `config/database.php`
- `app/Models/User.php`
- `app/Models/Tenant.php`
- `app/Models/TenantDatabase.php`
- `database/migrations/0001_01_01_000001_create_tenants_table.php`
- `database/migrations/0001_01_01_000003_create_users_table.php`
- `database/migrations/2026_02_14_000002_create_tenant_databases_table.php`

### 2.2 Database Tenant

Database tenant menggunakan koneksi:

```php
tenant
```

Database tenant menyimpan data operasional studio, seperti:

- `rooms`
- `services`
- `facilities`
- `room_facilities`
- `photos`
- `jadwals`
- `bookings`
- `payments`
- `tenant_profiles`
- `tenant_payment_accounts`
- `schedule_templates`
- `schedule_date_harian_overrides`

Letak migration tenant:

- `database/migrations/tenant`

---

## 3. Alur Besar Sistem SaaS

Secara umum, alur SaaS pada sistem StudioKita adalah sebagai berikut:

1. User mendaftar sebagai owner atau customer.
2. Jika user mendaftar sebagai owner, user diarahkan ke setup studio.
3. Owner mengisi data studio.
4. Sistem membuat data tenant di database pusat.
5. Sistem menghubungkan user owner dengan tenant melalui kolom `tenants_idTenant`.
6. Sistem membuat database tenant secara otomatis.
7. Sistem menjalankan migration tenant ke database tenant tersebut.
8. Saat owner mengakses dashboard, middleware `tenant.db` mengaktifkan database tenant miliknya.
9. Owner mengelola data studio di database tenant, bukan di database pusat.
10. Developer memverifikasi tenant dan mengaktifkan studio.
11. Customer melihat studio aktif dari database pusat.
12. Saat customer membuka detail studio atau melakukan booking, sistem mengaktifkan database tenant berdasarkan slug studio.
13. Data booking dan pembayaran customer masuk ke database tenant studio tersebut.
14. Callback Midtrans mencari tenant dari `order_id`, lalu mengaktifkan database tenant yang benar untuk update payment dan booking.

---

## 4. Alur Register User

User dapat mendaftar sebagai:

- owner
- customer

Letak kode:

- `app/Http/Controllers/Auth/RegisteredUserController.php`

Validasi role:

```php
'role' => ['required', 'in:owner,customer'],
```

Jika user mendaftar sebagai owner, sistem mengarahkan user ke setup studio:

```php
if ($user->role === 'owner') {
    return redirect(route('owner.setup.step1', absolute: false));
}
```

Letak kode:

- `app/Http/Controllers/Auth/RegisteredUserController.php:35`
- `app/Http/Controllers/Auth/RegisteredUserController.php:50`

---

## 5. Alur Setup Tenant Oleh Owner

Setup studio dilakukan oleh owner setelah register.

Letak controller:

- `app/Http/Controllers/Owner/SetupController.php`

Method utama:

- `stepOne()`
- `storeStepOne()`
- `stepTwo()`
- `storeStepTwo()`
- `stepThree()`
- `storeStepThree()`
- `welcome()`

### 5.1 Step One: Input Data Studio

Pada tahap ini owner mengisi data utama studio, seperti:

- nama studio
- nama pemilik
- email
- nomor telepon
- alamat
- provinsi
- kota
- kecamatan
- logo

Jika tenant belum ada, sistem membuat tenant baru di database pusat:

```php
$tenant = Tenant::create([
    'nama' => $request->nama,
    'slug' => $slug,
    'nama_pemilik' => $request->nama_pemilik,
    'email' => $request->email,
    'no_telp' => $request->no_telp,
    'alamat' => $request->alamat,
    'provinsi' => $request->provinsi,
    'kota' => $request->kota,
    'kecamatan' => $request->kecamatan,
    'status' => 'inactive',
]);
```

Letak kode:

- `app/Http/Controllers/Owner/SetupController.php:90`

Setelah tenant dibuat, user owner dihubungkan ke tenant:

```php
$user->tenants_idTenant = $tenant->idTenant;
```

Letak kode:

- `app/Http/Controllers/Owner/SetupController.php:106`

### 5.2 Aktivasi Database Tenant Saat Setup

Setelah tenant dibuat, sistem memanggil:

```php
$this->tenantDbManager->activateForTenant($tenant);
```

Letak kode:

- `app/Http/Controllers/Owner/SetupController.php:120`

Fungsi ini akan:

- membuat record database tenant jika belum ada
- membuat database MySQL tenant jika belum ada
- mengarahkan koneksi `tenant` ke database tenant tersebut
- menjalankan migration tenant

### 5.3 Sinkronisasi Profil Tenant

Setelah data tenant disimpan, sistem menyinkronkan profil tenant ke database tenant:

```php
$this->tenantProfileSynchronizer->sync($tenant);
```

Letak kode:

- `app/Http/Controllers/Owner/SetupController.php:143`

Tujuannya agar database tenant memiliki salinan profil studio pada tabel `tenant_profiles`.

---

## 6. TenantDatabaseManager Sebagai Inti SaaS

Bagian paling penting dalam implementasi SaaS ada pada:

- `app/Support/TenantDatabaseManager.php`

Class ini bertugas mengatur database tenant.

### 6.1 Method `activateForTenant()`

```php
public function activateForTenant(Tenant $tenant, bool $ensureMigrated = true): void
{
    $connection = $this->ensureConnectionRecord($tenant);

    $this->configureTenantConnection($connection);

    if ($ensureMigrated) {
        $this->ensureTenantSchema($tenant, $connection);
    }
}
```

Letak kode:

- `app/Support/TenantDatabaseManager.php:20`

Fungsi method ini:

- memastikan tenant punya data koneksi database
- mengatur koneksi database tenant
- menjalankan migration tenant jika diperlukan

### 6.2 Method `ensureConnectionRecord()`

```php
public function ensureConnectionRecord(Tenant $tenant): TenantDatabase
```

Letak kode:

- `app/Support/TenantDatabaseManager.php:38`

Fungsinya:

- mengecek apakah tenant sudah punya record di tabel `tenant_databases`
- jika belum ada, sistem membuat nama database tenant
- sistem membuat database MySQL tenant
- sistem menyimpan record database tenant di database pusat

### 6.3 Method `buildTenantDatabaseName()`

```php
private function buildTenantDatabaseName(Tenant $tenant): string
```

Letak kode:

- `app/Support/TenantDatabaseManager.php:70`

Fungsinya:

- membentuk nama database tenant berdasarkan nama database utama dan ID tenant

Contoh format:

```text
studiokita_tenant_1
studiokita_tenant_2
studiokita_tenant_3
```

### 6.4 Method `configureTenantConnection()`

```php
private function configureTenantConnection(TenantDatabase $connection): void
```

Letak kode:

- `app/Support/TenantDatabaseManager.php:79`

Fungsinya:

- mengubah konfigurasi koneksi `tenant`
- mengarahkan koneksi `tenant` ke database tenant yang sedang aktif
- melakukan `DB::purge('tenant')`
- melakukan `DB::reconnect('tenant')`

Kode penting:

```php
config([
    'database.connections.tenant' => array_merge(
        $this->tenantMysqlBaseConfig(),
        ['database' => $connection->database_name]
    ),
]);

DB::purge('tenant');
DB::reconnect('tenant');
```

### 6.5 Method `ensureTenantSchema()`

```php
private function ensureTenantSchema(Tenant $tenant, TenantDatabase $connection): void
```

Letak kode:

- `app/Support/TenantDatabaseManager.php:109`

Fungsinya:

- menjalankan migration khusus tenant
- memastikan tabel operasional tenant sudah tersedia

Kode penting:

```php
Artisan::call('migrate', [
    '--database' => 'tenant',
    '--path' => 'database/migrations/tenant',
    '--force' => true,
]);
```

Dengan kode tersebut, migration yang dijalankan bukan migration utama, tetapi migration yang berada di:

```text
database/migrations/tenant
```

---

## 7. Middleware Tenant

Middleware tenant digunakan agar setiap request owner atau customer diarahkan ke database tenant yang benar.

Letak kode:

- `app/Http/Middleware/EnsureTenantDatabaseConnection.php`

Middleware didaftarkan di:

- `bootstrap/app.php`

Kode pendaftaran:

```php
'tenant.db' => \App\Http\Middleware\EnsureTenantDatabaseConnection::class,
```

Letak kode:

- `bootstrap/app.php:17`

### 7.1 Cara Middleware Menentukan Tenant

Pertama, middleware mengecek apakah route memiliki parameter `tenant`:

```php
$tenant = $request->route('tenant');
```

Letak kode:

- `app/Http/Middleware/EnsureTenantDatabaseConnection.php:20`

Jika parameter tenant berupa slug, sistem mencari tenant berdasarkan slug:

```php
$tenant = Tenant::query()
    ->where('slug', $tenant)
    ->first();
```

Jika route tidak memiliki parameter tenant, sistem mengambil tenant dari owner yang sedang login:

```php
if ($user && $user->role === 'owner' && $user->tenants_idTenant) {
    $tenant = Tenant::query()->find($user->tenants_idTenant);
}
```

Letak kode:

- `app/Http/Middleware/EnsureTenantDatabaseConnection.php:30`

Jika tenant ditemukan, database tenant diaktifkan:

```php
$this->tenantDbManager->activateForTenant($tenant, true);
```

Letak kode:

- `app/Http/Middleware/EnsureTenantDatabaseConnection.php:36`

---

## 8. Route SaaS

### 8.1 Route Owner

Route owner memakai middleware:

```php
['auth', 'owner', 'tenant.db']
```

Letak kode:

- `routes/web.php:46`

Kode:

```php
Route::middleware(['auth', 'owner', 'tenant.db'])
    ->prefix('owner')
    ->name('owner.')
    ->group(function () {
        // route owner
    });
```

Artinya, setiap owner masuk ke halaman owner, sistem otomatis mengaktifkan database tenant milik owner tersebut.

### 8.2 Route Developer

Route developer memakai middleware:

```php
['auth', 'developer']
```

Letak kode:

- `routes/web.php:93`

Developer tidak otomatis memakai database tenant tertentu, karena developer bekerja pada level pusat. Jika developer ingin melihat data tenant tertentu, sistem memakai `runForTenant()`.

### 8.3 Route Customer Booking

Route booking customer memakai parameter tenant slug dan middleware tenant:

```php
Route::get('/studios/{tenant:slug}/booking/create', [StudioBookingController::class, 'create'])
    ->middleware('tenant.db')
    ->name('studios.booking.create');
```

Letak kode:

- `routes/web.php:119`

Dengan route ini, customer yang membuka booking studio tertentu akan diarahkan ke database tenant studio tersebut.

### 8.4 Route Checkout

Route checkout juga memakai middleware tenant:

```php
Route::get('/studios/{tenant:slug}/payments/{paymentId}/checkout', [PaymentController::class, 'checkout'])
    ->middleware('tenant.db')
    ->name('studios.payments.checkout');
```

Letak kode:

- `routes/web.php:131`

### 8.5 Route Webhook Midtrans

Webhook Midtrans tidak memakai slug tenant:

```php
Route::post('/payments/midtrans/webhook', [PaymentController::class, 'midtransWebhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('payments.midtrans.webhook');
```

Letak kode:

- `routes/web.php:149`

Karena webhook tidak memiliki slug tenant, sistem mencari tenant dari `order_id` Midtrans.

---

## 9. Alur Owner Mengelola Studio

Setelah database tenant aktif, owner dapat mengelola data studio.

Fitur owner:

- dashboard
- room
- service
- facility
- jadwal
- template jadwal
- override jadwal harian
- payment settings
- booking
- setup studio
- verifikasi

Letak controller:

- `app/Http/Controllers/Owner/DashboardController.php`
- `app/Http/Controllers/Owner/RoomController.php`
- `app/Http/Controllers/Owner/ServiceController.php`
- `app/Http/Controllers/Owner/FacilityController.php`
- `app/Http/Controllers/Owner/JadwalController.php`
- `app/Http/Controllers/Owner/JadwalTemplateController.php`
- `app/Http/Controllers/Owner/JadwalHarianOverrideController.php`
- `app/Http/Controllers/Owner/BookingController.php`
- `app/Http/Controllers/Owner/PaymentSettingsController.php`

Contoh pengambilan tenant owner:

```php
$tenantId = Auth::user()->tenants_idTenant;
```

Contoh query data room tenant:

```php
$rooms = Room::where('tenants_idTenant', $tenantId)
    ->latest()
    ->get();
```

Letak kode:

- `app/Http/Controllers/Owner/RoomController.php:17`
- `app/Http/Controllers/Owner/RoomController.php:19`

Walaupun sudah memakai database tenant terpisah, field `tenants_idTenant` tetap digunakan sebagai pengaman tambahan dan referensi data.

---

## 10. Alur Developer Mengelola Tenant

Developer mengelola tenant dari database pusat.

Letak controller:

- `app/Http/Controllers/Developer/TenantController.php`

### 10.1 Developer Melihat Detail Tenant

Method:

```php
public function show(Tenant $tenant)
```

Letak kode:

- `app/Http/Controllers/Developer/TenantController.php:33`

Ketika developer ingin membaca data operasional tenant, sistem menjalankan kode di dalam database tenant:

```php
$this->tenantDbManager->runForTenant($tenant, function () use (&$rooms, &$services, &$facilities, &$photos, &$primaryPhoto) {
    $rooms = Room::query()
        ->orderBy('nama_ruangan')
        ->get();

    $services = Service::query()
        ->orderBy('nama_service')
        ->get();

    $facilities = Facility::query()
        ->orderBy('nama_fasilitas')
        ->get();
});
```

Letak kode:

- `app/Http/Controllers/Developer/TenantController.php:44`

### 10.2 Developer Mengaktifkan Tenant

Method:

```php
public function updateStatus(Tenant $tenant): RedirectResponse
```

Letak kode:

- `app/Http/Controllers/Developer/TenantController.php:113`

Sebelum tenant aktif, sistem mengecek apakah tenant sudah verified:

```php
if ($newStatus === 'active' && !$this->verificationService->canActivate($tenant)) {
    return back()->withErrors([
        'status' => 'Studio belum Verified Level 2, sehingga belum bisa diaktifkan.',
    ]);
}
```

Letak kode:

- `app/Http/Controllers/Developer/TenantController.php:117`

Sebelum aktif, sistem juga memastikan database tenant sudah tersedia:

```php
$this->tenantDbManager->activateForTenant($tenant, true);
```

Letak kode:

- `app/Http/Controllers/Developer/TenantController.php:124`

### 10.3 Developer Menghapus Tenant

Jika tenant dihapus, sistem juga mencoba menghapus database tenant:

```php
$this->tenantDbManager->dropMySqlDatabase((string) $tenantDatabase->database_name);
```

Letak kode:

- `app/Http/Controllers/Developer/TenantController.php:318`

---

## 11. Alur Customer Melihat Studio

Customer melihat daftar studio aktif dari database pusat.

Letak controller:

- `app/Http/Controllers/StudioController.php`

Query studio aktif:

```php
Tenant::query()
    ->where('status', 'active')
```

Letak kode:

- `app/Http/Controllers/StudioController.php:29`

Saat customer membuka detail studio, sistem mengaktifkan database tenant:

```php
$this->tenantDbManager->activateForTenant($tenant);
```

Letak kode:

- `app/Http/Controllers/StudioController.php:145`

Setelah database tenant aktif, sistem dapat membaca:

- room
- service
- fasilitas
- foto
- jadwal

---

## 12. Alur Booking Customer

Booking customer dikelola oleh:

- `app/Http/Controllers/StudioBookingController.php`

### 12.1 Menampilkan Form Booking

Method:

```php
public function create(Tenant $tenant)
```

Letak kode:

- `app/Http/Controllers/StudioBookingController.php:28`

Database tenant diaktifkan:

```php
$this->tenantDbManager->activateForTenant($tenant, true);
```

Letak kode:

- `app/Http/Controllers/StudioBookingController.php:41`

### 12.2 Mengambil Slot Jadwal

Method:

```php
public function slots(Tenant $tenant, Request $request)
```

Letak kode:

- `app/Http/Controllers/StudioBookingController.php:72`

Method ini membaca jadwal dari database tenant berdasarkan:

- tenant
- room
- service
- tanggal

### 12.3 Menyimpan Booking

Method:

```php
public function store(Tenant $tenant, Request $request)
```

Letak kode:

- `app/Http/Controllers/StudioBookingController.php:111`

Sebelum booking dibuat, database tenant diaktifkan:

```php
$this->tenantDbManager->activateForTenant($tenant, true);
```

Letak kode:

- `app/Http/Controllers/StudioBookingController.php:125`

Sistem mengunci jadwal untuk mencegah double booking:

```php
->lockForUpdate()
```

Letak kode:

- `app/Http/Controllers/StudioBookingController.php:163`

Booking dibuat di database tenant:

```php
$booking = Booking::create([
    // data booking
]);
```

Letak kode:

- `app/Http/Controllers/StudioBookingController.php:246`

Payment dibuat di database tenant:

```php
$payment = Payment::create([
    // data payment
]);
```

Letak kode:

- `app/Http/Controllers/StudioBookingController.php:266`

Jika metode pembayaran Midtrans, sistem membuat transaksi Snap:

```php
$snap = $this->tenantMidtransService->createSnapTransaction(
    $tenant,
    $booking,
    $payment,
    $user
);
```

Letak kode:

- `app/Http/Controllers/StudioBookingController.php:287`

---

## 13. Alur Pembayaran

Pembayaran dikelola oleh:

- `app/Http/Controllers/PaymentController.php`

### 13.1 Checkout

Method:

```php
public function checkout(Tenant $tenant, int $paymentId, Request $request)
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:25`

Fungsinya:

- mengambil data payment dari database tenant
- memastikan user berhak melihat payment
- melakukan sinkronisasi status booking
- menampilkan halaman checkout
- membuat ulang Snap token jika diperlukan

### 13.2 Pelunasan

Method:

```php
public function createRemainingPayment(Tenant $tenant, int $bookingId, Request $request)
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:151`

Fungsinya:

- menghitung sisa pembayaran
- membuat payment pelunasan
- membuat transaksi Midtrans untuk pelunasan jika konfigurasi aktif

### 13.3 Callback Midtrans

Method:

```php
public function midtransWebhook(Request $request)
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:366`

Alurnya:

1. Midtrans mengirim payload ke sistem.
2. Sistem mengambil `order_id`.
3. Sistem membaca tenant ID, payment ID, dan booking ID dari `order_id`.
4. Sistem mencari tenant di database pusat.
5. Sistem memvalidasi signature Midtrans.
6. Sistem mengaktifkan database tenant.
7. Sistem mengupdate payment.
8. Sistem menyinkronkan status booking.

Parsing `order_id`:

```php
$parsed = $this->tenantMidtransService->parseOrderId($orderId);
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:375`

Validasi signature:

```php
if (!$this->tenantMidtransService->isValidSignature($tenant, $payload)) {
    return response()->json(['message' => 'Invalid signature'], 403);
}
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:387`

Update data di database tenant:

```php
$this->tenantDbManager->runForTenant($tenant, function () use ($payload, $parsed, $orderId) {
    // update payment dan booking
});
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:391`

Sinkronisasi status pembayaran booking:

```php
private function syncBookingPaymentState(Booking $booking): void
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:469`

---

## 14. Midtrans Per Tenant

Konfigurasi Midtrans tenant dikelola oleh:

- `app/Support/TenantMidtransService.php`

Fungsi penting:

- membaca konfigurasi pembayaran tenant
- mendekripsi client key dan server key
- menentukan mode sandbox atau production
- membuat Snap transaction
- membuat `order_id`
- membaca tenant dari `order_id`
- memvalidasi signature callback
- memetakan status Midtrans ke status sistem

Format `order_id`:

```text
SK-{tenant_id}-{payment_id}-{booking_id}-{timestamp}
```

Format ini penting karena callback Midtrans tidak membawa slug tenant. Dengan `order_id`, sistem dapat mengetahui callback tersebut milik tenant, payment, dan booking yang mana.

---

## 15. Verifikasi Tenant

Verifikasi tenant dikelola oleh:

- `app/Support/TenantVerificationService.php`

### 15.1 Cek Tenant Boleh Aktif

Method:

```php
public function canActivate(Tenant $tenant): bool
```

Letak kode:

- `app/Support/TenantVerificationService.php:95`

Tenant boleh aktif jika:

```php
$tenant->verification_level === 'verified'
&& $tenant->verification_status === 'approved'
```

### 15.2 Refresh Basic Verification

Method:

```php
public function refreshBasicVerification(Tenant $tenant): Tenant
```

Letak kode:

- `app/Support/TenantVerificationService.php:101`

Fungsinya:

- mengecek kelengkapan data dasar tenant
- mengecek logo
- mengecek foto galeri
- mengecek OTP email
- menentukan apakah tenant mencapai basic verification

### 15.3 Reset Verifikasi Jika Email Berubah

Method:

```php
public function resetForEmailChange(Tenant $tenant): Tenant
```

Letak kode:

- `app/Support/TenantVerificationService.php:132`

Fungsinya:

- menghapus status verifikasi email
- mengubah tenant menjadi inactive
- mengembalikan status verifikasi ke draft

---

## 16. Sinkronisasi Profil Tenant

Karena data tenant utama ada di database pusat, sistem menyalin ringkasan profil tenant ke database tenant.

Letak kode:

- `app/Support/TenantProfileSynchronizer.php`

Method:

```php
public function sync(Tenant $tenant): void
```

Letak kode:

- `app/Support/TenantProfileSynchronizer.php:15`

Data disimpan ke tabel `tenant_profiles`:

```php
DB::connection('tenant')->table('tenant_profiles')->upsert(
```

Letak kode:

- `app/Support/TenantProfileSynchronizer.php:40`

Tujuannya:

- database tenant memiliki salinan profil studio
- tampilan tenant dapat membaca profil dari database tenant
- saat status tenant berubah, profil tenant ikut tersinkron

---

## 17. Jawaban Singkat Untuk Sidang

Jika penguji bertanya, "SaaS-nya di mana?", jawaban singkatnya:

```text
Sistem ini menerapkan SaaS multi-tenant. Satu aplikasi Laravel digunakan oleh banyak studio sebagai tenant. Data pusat seperti user, tenant, dan status verifikasi disimpan di database utama. Setelah owner membuat studio, sistem membuat database MySQL khusus untuk tenant tersebut melalui TenantDatabaseManager. Setiap request owner atau customer yang masuk ke studio tertentu melewati middleware tenant.db, sehingga koneksi database tenant diarahkan ke database studio yang benar. Data operasional seperti room, service, jadwal, booking, payment, dan fasilitas disimpan di database tenant masing-masing. Developer mengelola tenant dari database pusat, termasuk verifikasi, aktivasi, dan penghapusan tenant.
```

---

## 18. File Paling Penting Untuk Ditunjukkan Saat Sidang

Bagian yang paling kuat untuk membuktikan konsep SaaS:

- `routes/web.php:46`
- `routes/web.php:93`
- `routes/web.php:119`
- `routes/web.php:149`
- `bootstrap/app.php:17`
- `app/Http/Middleware/EnsureTenantDatabaseConnection.php:18`
- `app/Support/TenantDatabaseManager.php:20`
- `app/Support/TenantDatabaseManager.php:109`
- `app/Http/Controllers/Owner/SetupController.php:90`
- `app/Http/Controllers/Owner/SetupController.php:120`
- `app/Http/Controllers/Developer/TenantController.php:113`
- `app/Http/Controllers/StudioBookingController.php:111`
- `app/Http/Controllers/PaymentController.php:366`
- `app/Support/TenantProfileSynchronizer.php:15`

---

## 19. Kesimpulan

SaaS pada StudioKita dibangun dengan pola:

```text
1 aplikasi Laravel
1 database pusat
banyak database tenant
middleware tenant.db untuk memilih database tenant
TenantDatabaseManager untuk membuat dan mengaktifkan database tenant
developer sebagai pengelola tenant
owner sebagai pengelola studio
customer sebagai pengguna layanan booking
```

Dengan pola ini, StudioKita dapat melayani banyak studio dalam satu aplikasi, tetapi data operasional setiap studio tetap terpisah di database masing-masing.

