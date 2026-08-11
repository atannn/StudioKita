# Alur Penggunaan Midtrans Pada Sistem StudioKita

Dokumen ini menjelaskan alur penggunaan Midtrans pada sistem StudioKita, mulai dari pengajuan payment settings oleh owner, pengisian dan pengujian key oleh developer, proses booking, pembuatan transaksi Snap, checkout customer, pelunasan, callback/webhook Midtrans, hingga update status payment dan booking.

---

## 1. Konsep Utama

Midtrans pada StudioKita digunakan sebagai payment gateway untuk pembayaran booking studio.

Sistem ini bersifat multi-tenant, sehingga konfigurasi Midtrans tidak bersifat global untuk seluruh aplikasi. Setiap tenant atau studio dapat memiliki konfigurasi Midtrans sendiri.

Artinya:

- setiap studio memiliki akun/payment configuration masing-masing;
- client key dan server key Midtrans disimpan per tenant;
- transaksi Midtrans dibuat berdasarkan booking dan payment milik tenant tersebut;
- callback Midtrans dicocokkan ke tenant menggunakan `order_id`;
- update payment dilakukan pada database tenant yang sesuai.

---

## 2. File Utama Yang Berhubungan Dengan Midtrans

File paling penting:

- `app/Support/TenantMidtransService.php`
- `app/Http/Controllers/StudioBookingController.php`
- `app/Http/Controllers/PaymentController.php`
- `app/Http/Controllers/Owner/PaymentSettingsController.php`
- `app/Http/Controllers/Developer/TenantPaymentSettingsController.php`
- `app/Http/Controllers/Developer/TenantController.php`
- `resources/views/payments/checkout.blade.php`
- `app/Models/TenantPaymentAccount.php`
- `app/Models/TenantMidtransSubmission.php`
- `app/Models/Payment.php`
- `app/Models/Booking.php`

Migration penting:

- `database/migrations/2026_04_12_000001_create_tenant_midtrans_submissions_table.php`
- `database/migrations/tenant/0001_01_01_000015_create_tenant_payment_accounts_table.php`
- `database/migrations/tenant/0001_01_01_000013_add_midtrans_checkout_fields_to_payments_table.php`
- `database/migrations/tenant/0001_01_01_000014_add_payment_plan_fields_to_bookings_table.php`
- `database/migrations/tenant/0001_01_01_000017_add_dp_settings_to_tenant_payment_accounts_table.php`
- `database/migrations/tenant/0001_01_01_000019_add_cash_settings_to_tenant_payment_accounts_table.php`

---

## 3. Pembagian Data Midtrans

### 3.1 Data Pengajuan Midtrans

Data pengajuan Midtrans dari owner disimpan di database pusat.

Model:

- `app/Models/TenantMidtransSubmission.php`

Tabel:

- `tenant_midtrans_submissions`

Migration:

- `database/migrations/2026_04_12_000001_create_tenant_midtrans_submissions_table.php`

Status pengajuan:

```php
public const STATUS_DRAFT = 'draft';
public const STATUS_SUBMITTED = 'submitted';
public const STATUS_REVISION_NEEDED = 'revision_needed';
public const STATUS_APPROVED = 'approved';
```

Letak kode:

- `app/Models/TenantMidtransSubmission.php:7`

Data yang disimpan di pengajuan:

- bentuk badan usaha
- nama legal bisnis
- brand name
- kategori bisnis
- email bisnis
- nomor telepon bisnis
- URL bisnis
- data PIC
- data rekening bank
- catatan pengajuan
- status review
- waktu submit
- waktu review
- developer reviewer

### 3.2 Data Akun Midtrans Tenant

Konfigurasi Midtrans yang benar-benar dipakai untuk transaksi disimpan di database tenant.

Model:

- `app/Models/TenantPaymentAccount.php`

Tabel:

- `tenant_payment_accounts`

Migration:

- `database/migrations/tenant/0001_01_01_000015_create_tenant_payment_accounts_table.php`

Field penting:

- `tenant_id`
- `provider`
- `merchant_id`
- `midtrans_client_key_enc`
- `midtrans_server_key_enc`
- `is_production`
- `is_active`
- `midtrans_last_test_success`
- `midtrans_last_tested_at`
- `dp_enabled`
- `dp_percent`
- `cash_enabled`
- `cash_instruction`

Letak model:

- `app/Models/TenantPaymentAccount.php`

Hal penting:

- `TenantPaymentAccount` memakai koneksi `tenant`.
- Client key dan server key disimpan dalam bentuk terenkripsi.
- Data ini yang dipakai saat sistem membuat transaksi Snap Midtrans.

```php
protected $connection = 'tenant';
```

Letak kode:

- `app/Models/TenantPaymentAccount.php:7`

### 3.3 Data Payment

Data payment disimpan di database tenant pada tabel `payments`.

Model:

- `app/Models/Payment.php`

Field penting untuk Midtrans:

- `method`
- `midtrans_order_id`
- `midtrans_transaction_id`
- `snap_token`
- `snap_redirect_url`
- `status`
- `raw_status`
- `webhook_payload`
- `payment_time`
- `expires_time`
- `paid_at`
- `failed_at`
- `payment_type`
- `amount`
- `tenants_idTenant`
- `booking_idbooking`

Letak model:

- `app/Models/Payment.php`

Migration tambahan field Midtrans:

- `database/migrations/tenant/0001_01_01_000013_add_midtrans_checkout_fields_to_payments_table.php`

Field yang ditambahkan:

```php
$table->string('snap_token', 255)->nullable();
$table->string('snap_redirect_url', 255)->nullable();
$table->string('raw_status', 50)->nullable();
$table->text('webhook_payload')->nullable();
$table->dateTime('paid_at')->nullable();
$table->dateTime('failed_at')->nullable();
```

### 3.4 Data Booking

Data booking menyimpan ringkasan status pembayaran.

Model:

- `app/Models/Booking.php`

Field payment pada booking:

- `payment_scheme`
- `dp_percent`
- `payment_state`
- `paid_amount`

Migration:

- `database/migrations/tenant/0001_01_01_000014_add_payment_plan_fields_to_bookings_table.php`

Kode migration:

```php
$table->string('payment_scheme', 20)->default('full');
$table->unsignedTinyInteger('dp_percent')->nullable();
$table->string('payment_state', 20)->default('unpaid');
$table->decimal('paid_amount', 12, 2)->default(0);
```

Fungsinya:

- `payment_scheme` menyimpan pilihan pembayaran `full` atau `dp`;
- `dp_percent` menyimpan persentase DP;
- `payment_state` menyimpan status ringkas pembayaran booking;
- `paid_amount` menyimpan total nominal yang sudah berhasil dibayar.

---

## 4. Alur Aktivasi Midtrans Tenant

Sebelum customer bisa membayar dengan Midtrans, tenant harus memiliki konfigurasi pembayaran yang aktif dan valid.

Alur aktivasi:

1. Owner membuka halaman payment settings.
2. Owner mengisi data pengajuan Midtrans.
3. Owner mengirim pengajuan ke developer.
4. Developer mengisi atau memperbarui client key dan server key.
5. Developer melakukan test koneksi Midtrans.
6. Jika test berhasil, sistem menandai konfigurasi siap.
7. Developer menyetujui pengajuan Midtrans.
8. Tenant dapat diaktifkan jika verifikasi dan payment settings sudah siap.

---

## 5. Owner Mengajukan Payment Settings

Controller owner:

- `app/Http/Controllers/Owner/PaymentSettingsController.php`

### 5.1 Menampilkan Halaman Payment Settings

Method:

```php
public function edit()
```

Letak kode:

- `app/Http/Controllers/Owner/PaymentSettingsController.php:24`

Fungsinya:

- mengambil tenant owner;
- mengambil submission Midtrans;
- mengambil payment account tenant;
- mengecek dokumen verifikasi;
- mengecek apakah payment sudah siap.

Kode penting:

```php
$paymentAccount = $this->tenantMidtransService->getPaymentAccount($tenant);
```

Letak kode:

- `app/Http/Controllers/Owner/PaymentSettingsController.php:38`

### 5.2 Menyimpan Draft Pengajuan

Method:

```php
public function update(Request $request): RedirectResponse
```

Letak kode:

- `app/Http/Controllers/Owner/PaymentSettingsController.php:50`

Fungsinya:

- menyimpan draft pengajuan Midtrans;
- status pengajuan dikembalikan menjadi `draft`.

Kode penting:

```php
$submission->fill($validated);
$submission->status = TenantMidtransSubmission::STATUS_DRAFT;
$submission->save();
```

### 5.3 Submit Pengajuan Ke Developer

Method:

```php
public function submit(Request $request): RedirectResponse
```

Letak kode:

- `app/Http/Controllers/Owner/PaymentSettingsController.php:65`

Fungsinya:

- memvalidasi data pengajuan;
- mengecek dokumen verifikasi tenant;
- mengubah status pengajuan menjadi `submitted`;
- mencatat waktu submit.

Kode pengecekan dokumen:

```php
if (!$this->tenantVerificationService->hasAllRequiredDocuments($tenant)) {
    return back()
        ->withErrors([
            'payment_submission' => 'Dokumen verifikasi studio belum lengkap. Lengkapi dokumen terlebih dahulu sebelum mengirim pengajuan Midtrans.',
        ])
        ->withInput();
}
```

Kode update status submit:

```php
$submission->forceFill([
    'status' => TenantMidtransSubmission::STATUS_SUBMITTED,
    'submitted_at' => now(),
    'reviewed_at' => null,
    'reviewed_by' => null,
    'review_notes' => null,
])->save();
```

### 5.4 Owner Mengatur Preferensi DP Dan Cash

Method:

```php
public function updatePreferences(Request $request): RedirectResponse
```

Letak kode:

- `app/Http/Controllers/Owner/PaymentSettingsController.php:93`

Fungsinya:

- mengatur apakah DP aktif;
- mengatur persentase DP;
- mengatur apakah pembayaran cash aktif;
- mengatur instruksi pembayaran cash.

Kode validasi:

```php
$validated = $request->validate([
    'dp_enabled' => 'nullable|boolean',
    'dp_percent' => 'nullable|integer|min:1|max:90',
    'cash_enabled' => 'nullable|boolean',
    'cash_instruction' => 'nullable|string|max:2000',
]);
```

Kode penyimpanan:

```php
$account->fill([
    'provider' => $account->provider ?: 'midtrans',
    'dp_enabled' => $dpEnabled,
    'dp_percent' => $dpPercent,
    'cash_enabled' => $cashEnabled,
    'cash_instruction' => $cashEnabled ? $this->normalizeNullableString($validated['cash_instruction'] ?? null) : null,
]);
$account->save();
```

Letak kode:

- `app/Http/Controllers/Owner/PaymentSettingsController.php:109`

---

## 6. Developer Mengisi Dan Menguji Key Midtrans

Controller developer:

- `app/Http/Controllers/Developer/TenantPaymentSettingsController.php`

### 6.1 Developer Menyimpan Konfigurasi Midtrans

Method:

```php
public function update(Request $request, Tenant $tenant): RedirectResponse
```

Letak kode:

- `app/Http/Controllers/Developer/TenantPaymentSettingsController.php:28`

Langkah yang dilakukan:

1. Mencegah developer memproses payment tenant miliknya sendiri.
2. Mengaktifkan database tenant.
3. Memvalidasi merchant ID, client key, server key, mode production, dan status active.
4. Mengecek format key sesuai mode.
5. Mengenkripsi client key dan server key.
6. Menyimpan konfigurasi ke `tenant_payment_accounts`.
7. Jika key atau mode berubah, status test koneksi direset.

Database tenant diaktifkan:

```php
$this->tenantDbManager->activateForTenant($tenant, true);
```

Letak kode:

- `app/Http/Controllers/Developer/TenantPaymentSettingsController.php:30`

Client key dan server key dienkripsi:

```php
$account->midtrans_client_key_enc = Crypt::encryptString($newClientKey);
```

```php
$account->midtrans_server_key_enc = Crypt::encryptString($newServerKey);
```

Letak kode:

- `app/Http/Controllers/Developer/TenantPaymentSettingsController.php:92`
- `app/Http/Controllers/Developer/TenantPaymentSettingsController.php:96`

Jika key atau mode berubah, hasil test direset:

```php
if ($modeChanged || $credentialsChanged) {
    $account->midtrans_last_test_success = false;
    $account->midtrans_last_tested_at = null;
}
```

Letak kode:

- `app/Http/Controllers/Developer/TenantPaymentSettingsController.php:100`

### 6.2 Validasi Format Key

Method validasi client key:

```php
private function isValidMidtransClientKey(string $key, bool $isProduction): bool
```

Letak kode:

- `app/Http/Controllers/Developer/TenantPaymentSettingsController.php:300`

Method validasi server key:

```php
private function isValidMidtransServerKey(string $key, bool $isProduction): bool
```

Letak kode:

- `app/Http/Controllers/Developer/TenantPaymentSettingsController.php:309`

Ketentuan:

- production client key diawali `Mid-client-`;
- production server key diawali `Mid-server-`;
- sandbox client key dapat diawali `SB-Mid-client-`;
- sandbox server key dapat diawali `SB-Mid-server-`.

### 6.3 Test Koneksi Midtrans

Method:

```php
public function testConnection(Request $request, Tenant $tenant): RedirectResponse
```

Letak kode:

- `app/Http/Controllers/Developer/TenantPaymentSettingsController.php:116`

Fungsinya:

- mengambil key yang sedang diuji;
- mengatur konfigurasi Midtrans;
- memanggil status transaksi dummy;
- jika response 404 transaction not found, key dianggap valid;
- jika 401 unauthorized, key dianggap salah;
- menyimpan hasil test jika konfigurasi yang dites sudah tersimpan.

Kode konfigurasi Midtrans:

```php
MidtransConfig::$serverKey = $serverKey;
MidtransConfig::$isProduction = $isProduction;
MidtransConfig::$isSanitized = true;
MidtransConfig::$is3ds = true;
```

Letak kode:

- `app/Http/Controllers/Developer/TenantPaymentSettingsController.php:177`

Kode test transaksi:

```php
$probeOrderId = 'SK-CONN-'.$tenant->idTenant.'-'.now()->timestamp;
MidtransTransaction::status($probeOrderId);
```

Letak kode:

- `app/Http/Controllers/Developer/TenantPaymentSettingsController.php:182`

Pencatatan hasil test:

```php
$account->forceFill([
    'midtrans_last_test_success' => $isSuccess,
    'midtrans_last_tested_at' => now(),
])->save();
```

Letak kode:

- `app/Http/Controllers/Developer/TenantPaymentSettingsController.php:371`

### 6.4 Review Pengajuan Midtrans

Method:

```php
public function reviewSubmission(Request $request, Tenant $tenant): RedirectResponse
```

Letak kode:

- `app/Http/Controllers/Developer/TenantPaymentSettingsController.php:241`

Jika developer menyetujui pengajuan, sistem memastikan konfigurasi Midtrans sudah aktif dan lulus test:

```php
if (!$this->tenantMidtransService->hasReadyActivationConfiguration($tenant)) {
    return back()->withErrors([
        'payment_submission' => 'Pengajuan belum bisa disetujui karena konfigurasi Midtrans tenant belum aktif dan lulus test koneksi.',
    ]);
}
```

Letak kode:

- `app/Http/Controllers/Developer/TenantPaymentSettingsController.php:271`

---

## 7. Syarat Tenant Bisa Aktif Dengan Midtrans

Tenant tidak cukup hanya punya data studio. Untuk diaktifkan, tenant juga harus:

- lolos verifikasi tenant;
- memiliki konfigurasi Midtrans aktif;
- sudah berhasil test koneksi Midtrans.

Controller:

- `app/Http/Controllers/Developer/TenantController.php`

Method:

```php
public function updateStatus(Tenant $tenant): RedirectResponse
```

Letak kode:

- `app/Http/Controllers/Developer/TenantController.php:113`

Pengecekan verifikasi:

```php
if ($newStatus === 'active' && !$this->verificationService->canActivate($tenant)) {
    return back()->withErrors([
        'status' => 'Studio belum Verified Level 2, sehingga belum bisa diaktifkan.',
    ]);
}
```

Letak kode:

- `app/Http/Controllers/Developer/TenantController.php:117`

Pengecekan Midtrans:

```php
if (!$this->tenantMidtransService->hasReadyActivationConfiguration($tenant)) {
    return back()->withErrors([
        'status' => 'Studio belum bisa diaktifkan karena Payment Settings Midtrans belum tervalidasi (aktif + test koneksi berhasil).',
    ]);
}
```

Letak kode:

- `app/Http/Controllers/Developer/TenantController.php:126`

---

## 8. TenantMidtransService Sebagai Pusat Logika Midtrans

Service utama:

- `app/Support/TenantMidtransService.php`

Service ini memusatkan logika Midtrans agar controller tidak langsung mengurus detail key, Snap, signature, dan mapping status.

### 8.1 Mengambil Kebijakan DP

Method:

```php
public function getDpPolicy(Tenant $tenant): array
```

Letak kode:

- `app/Support/TenantMidtransService.php:21`

Fungsinya:

- membaca apakah DP aktif;
- membaca persentase DP;
- memberikan default 30% jika tidak ada konfigurasi.

### 8.2 Mengambil Kebijakan Cash

Method:

```php
public function getCashPolicy(Tenant $tenant): array
```

Letak kode:

- `app/Support/TenantMidtransService.php:39`

Fungsinya:

- membaca apakah cash aktif;
- membaca instruksi cash tenant.

### 8.3 Mengecek Konfigurasi Midtrans Aktif

Method:

```php
public function hasActiveConfiguration(Tenant $tenant): bool
```

Letak kode:

- `app/Support/TenantMidtransService.php:55`

Syarat aktif:

- ada `TenantPaymentAccount`;
- `is_active = true`;
- client key bisa didekripsi;
- server key bisa didekripsi.

Kode:

```php
return $account instanceof TenantPaymentAccount
    && (bool) $account->is_active
    && (bool) $this->decryptSecret($account->midtrans_client_key_enc)
    && (bool) $this->decryptSecret($account->midtrans_server_key_enc);
```

### 8.4 Mengecek Konfigurasi Siap Aktivasi

Method:

```php
public function hasReadyActivationConfiguration(Tenant $tenant): bool
```

Letak kode:

- `app/Support/TenantMidtransService.php:65`

Syarat ready:

- konfigurasi aktif;
- test koneksi terakhir sukses;
- waktu test tersedia.

### 8.5 Mengambil Client Key Untuk Snap JS

Method:

```php
public function getSnapClientKey(Tenant $tenant): ?string
```

Letak kode:

- `app/Support/TenantMidtransService.php:74`

Client key dikirim ke view checkout agar Snap JS dapat digunakan di browser.

### 8.6 Menentukan URL Snap JS

Method:

```php
public function getSnapJsUrl(Tenant $tenant): string
```

Letak kode:

- `app/Support/TenantMidtransService.php:84`

Jika production:

```php
https://app.midtrans.com/snap/snap.js
```

Jika sandbox:

```php
https://app.sandbox.midtrans.com/snap/snap.js
```

### 8.7 Membuat Transaksi Snap

Method:

```php
public function createSnapTransaction(
    Tenant $tenant,
    Booking $booking,
    Payment $payment,
    ?User $user = null,
    ?CarbonInterface $expiresAt = null
): array
```

Letak kode:

- `app/Support/TenantMidtransService.php:97`

Fungsinya:

- mengambil akun Midtrans tenant;
- memastikan akun aktif;
- mendekripsi server key;
- mengatur konfigurasi Midtrans;
- membuat `order_id`;
- mengambil nominal dari payment;
- membentuk parameter transaksi;
- mengirim request ke Midtrans Snap;
- menerima `token` dan `redirect_url`;
- mengembalikan data transaksi ke controller.

Kode konfigurasi Midtrans:

```php
$this->configureMidtrans($serverKey, (bool) $account->is_production);
```

Letak kode:

- `app/Support/TenantMidtransService.php:115`

Kode pembuatan `order_id`:

```php
$orderId = $payment->midtrans_order_id ?: $this->buildOrderId(
    (int) $tenant->idTenant,
    (int) $payment->idpayments,
    (int) $booking->idbooking
);
```

Letak kode:

- `app/Support/TenantMidtransService.php:117`

Kode parameter transaksi:

```php
$params = [
    'transaction_details' => [
        'order_id' => $orderId,
        'gross_amount' => $amount,
    ],
    'customer_details' => [
        'first_name' => $user?->name ?: 'Customer',
        'email' => $user?->email ?: $tenant->email,
        'phone' => $user?->no_telp ?: $tenant->no_telp,
    ],
    'item_details' => [[
        'id' => 'BOOKING-'.$booking->idbooking,
        'price' => $amount,
        'quantity' => 1,
        'name' => substr((string) ('Booking '.$tenant->nama), 0, 50),
    ]],
];
```

Letak kode:

- `app/Support/TenantMidtransService.php:130`

Kode custom expiry untuk pelunasan:

```php
if ($expiresAt && $expiresAt->isFuture()) {
    $params['custom_expiry'] = [
        'start_time' => now()->format('Y-m-d H:i:s O'),
        'unit' => 'minute',
        'duration' => max(1, now()->diffInMinutes($expiresAt)),
    ];
}
```

Letak kode:

- `app/Support/TenantMidtransService.php:148`

Request ke Midtrans:

```php
$response = Snap::createTransaction($params);
```

Letak kode:

- `app/Support/TenantMidtransService.php:155`

Data yang dikembalikan:

```php
return [
    'order_id' => $orderId,
    'token' => $token,
    'redirect_url' => $redirectUrl,
];
```

Letak kode:

- `app/Support/TenantMidtransService.php:163`

---

## 9. Format Order ID Midtrans

Order ID dibuat dengan format:

```text
SK-{tenant_id}-{payment_id}-{booking_id}-{timestamp}
```

Method:

```php
private function buildOrderId(int $tenantId, int $paymentId, int $bookingId): string
```

Letak kode:

- `app/Support/TenantMidtransService.php:226`

Kode:

```php
return 'SK-'.$tenantId.'-'.$paymentId.'-'.$bookingId.'-'.now()->timestamp;
```

Fungsi format ini:

- membedakan transaksi antar tenant;
- memudahkan webhook mencari tenant;
- memudahkan webhook mencari payment;
- memudahkan webhook mencari booking.

Contoh:

```text
SK-1-25-14-1761234567
```

Artinya:

- tenant ID: `1`
- payment ID: `25`
- booking ID: `14`
- timestamp: `1761234567`

---

## 10. Alur Booking Hingga Membuat Transaksi Midtrans

Controller:

- `app/Http/Controllers/StudioBookingController.php`

### 10.1 Form Booking Membaca Kebijakan DP Dan Cash

Method:

```php
public function create(Tenant $tenant)
```

Letak kode:

- `app/Http/Controllers/StudioBookingController.php:28`

Kode:

```php
$dpPolicy = $this->tenantMidtransService->getDpPolicy($tenant);
$cashPolicy = $this->tenantMidtransService->getCashPolicy($tenant);
```

Letak kode:

- `app/Http/Controllers/StudioBookingController.php:55`

Fungsinya:

- menentukan apakah DP tersedia;
- menentukan persentase DP;
- menentukan apakah cash tersedia;
- mengirim kebijakan tersebut ke form booking.

### 10.2 Store Booking Memvalidasi Metode Pembayaran

Method:

```php
public function store(Tenant $tenant, Request $request)
```

Letak kode:

- `app/Http/Controllers/StudioBookingController.php:111`

Kode:

```php
$dpPolicy = $this->tenantMidtransService->getDpPolicy($tenant);
$cashPolicy = $this->tenantMidtransService->getCashPolicy($tenant);
$allowedPaymentMethods = $cashPolicy['enabled'] ? ['midtrans', 'cash'] : ['midtrans'];
```

Letak kode:

- `app/Http/Controllers/StudioBookingController.php:128`

Artinya:

- jika cash aktif, customer boleh memilih Midtrans atau cash;
- jika cash tidak aktif, customer hanya boleh memilih Midtrans.

### 10.3 Menentukan Full Payment Atau DP

Kode:

```php
$paymentMethod = (string) $request->input('payment_method', 'midtrans');
if (!$cashPolicy['enabled']) {
    $paymentMethod = 'midtrans';
}

$isCashPayment = $paymentMethod === 'cash';
$allowedSchemes = !$isCashPayment && $dpPolicy['enabled'] ? ['full', 'dp'] : ['full'];
$paymentScheme = (string) $request->input('payment_scheme', 'full');
```

Letak kode:

- `app/Http/Controllers/StudioBookingController.php:214`

Artinya:

- payment cash hanya boleh full;
- payment Midtrans bisa full atau DP jika DP tenant aktif.

### 10.4 Menghitung Nominal Payment Awal

Kode:

```php
$bookingTotal = (float) $service->getPriceForDate($jadwal->tanggal);
$initialChargeAmount = !$isCashPayment && $paymentScheme === 'dp'
    ? max(1, round($bookingTotal * ($dpPercent / 100), 2))
    : $bookingTotal;
```

Letak kode:

- `app/Http/Controllers/StudioBookingController.php:241`

Jika skema `full`, maka nominal payment sama dengan total booking.

Jika skema `dp`, maka nominal payment adalah:

```text
total booking x persen DP
```

### 10.5 Membuat Booking

Kode:

```php
$booking = Booking::create([
    'tanggal_booking'   => now(),
    'total_harga'       => $bookingTotal,
    'status'            => 'pending',
    'payment_scheme'    => $paymentScheme,
    'dp_percent'        => $dpPercent,
    'payment_state'     => 'unpaid',
    'paid_amount'       => 0,
    'tenants_idTenant'  => $tenant->idTenant,
    'rooms_idrooms'     => $room->idrooms,
    'service_idservice' => $service->idservice,
    'Jadwal_idJadwal'   => $jadwal->idJadwal,
    'user_id'           => Auth::id(),
]);
```

Letak kode:

- `app/Http/Controllers/StudioBookingController.php:246`

Status awal booking:

- `status = pending`
- `payment_state = unpaid`
- `paid_amount = 0`

### 10.6 Membuat Payment

Kode:

```php
$payment = Payment::query()->create([
    'method' => $isCashPayment ? 'Cash' : 'Midtrans',
    'status' => 'pending',
    'payment_type' => !$isCashPayment && $paymentScheme === 'dp' ? 'dp' : 'full',
    'amount' => $initialChargeAmount,
    'tenants_idTenant' => $tenant->idTenant,
    'booking_idbooking' => $booking->idbooking,
    'raw_status' => $isCashPayment ? 'cash_waiting_owner_confirmation' : null,
]);
```

Letak kode:

- `app/Http/Controllers/StudioBookingController.php:266`

Payment awal berisi:

- metode pembayaran;
- status pending;
- jenis pembayaran `full` atau `dp`;
- nominal yang harus dibayar;
- relasi ke booking.

### 10.7 Membuat Transaksi Snap Midtrans

Sistem mengecek apakah Midtrans tenant aktif:

```php
$isPaymentConfigured = $this->tenantMidtransService->hasActiveConfiguration($tenant);
```

Letak kode:

- `app/Http/Controllers/StudioBookingController.php:282`

Jika aktif, sistem membuat transaksi Snap:

```php
$snap = $this->tenantMidtransService->createSnapTransaction(
    $tenant,
    $booking,
    $payment,
    Auth::user()
);
```

Letak kode:

- `app/Http/Controllers/StudioBookingController.php:287`

Hasil dari Midtrans disimpan ke payment:

```php
$payment->forceFill([
    'midtrans_order_id' => $snap['order_id'],
    'snap_token' => $snap['token'],
    'snap_redirect_url' => $snap['redirect_url'],
    'status' => 'pending',
])->save();
```

Letak kode:

- `app/Http/Controllers/StudioBookingController.php:294`

Data yang disimpan:

- `midtrans_order_id`
- `snap_token`
- `snap_redirect_url`
- `status = pending`

---

## 11. Alur Checkout Customer

Controller:

- `app/Http/Controllers/PaymentController.php`

Method:

```php
public function checkout(Tenant $tenant, int $paymentId, Request $request)
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:25`

### 11.1 Mengambil Payment Dan Booking

Kode:

```php
$payment = Payment::query()
    ->where('idpayments', $paymentId)
    ->where('tenants_idTenant', $tenant->idTenant)
    ->firstOrFail();
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:28`

Booking diambil berdasarkan payment:

```php
$booking = Booking::query()
    ->with(['room', 'service', 'jadwal', 'payments' => fn ($query) => $query->latest('idpayments')])
    ->where('idbooking', $payment->booking_idbooking)
    ->firstOrFail();
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:33`

### 11.2 Validasi Hak Akses Checkout

Kode:

```php
$isOwnerOfTenant = $user->role === 'owner' && (int) $user->tenants_idTenant === (int) $tenant->idTenant;
$isBookingOwner = (int) $booking->user_id === (int) $user->id;
abort_unless($isOwnerOfTenant || $isBookingOwner, 403);
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:39`

Artinya, checkout hanya boleh diakses oleh:

- owner studio tersebut;
- customer pemilik booking.

### 11.3 Sinkronisasi Status Booking

Kode:

```php
$this->syncBookingPaymentState($booking);
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:44`

Tujuannya:

- menghitung ulang total payment sukses;
- mengisi `paid_amount`;
- mengisi `payment_state`.

### 11.4 Membuat Ulang Snap Token Jika Diperlukan

Sistem akan membuat ulang transaksi Snap jika:

- payment bukan cash;
- konfigurasi payment aktif;
- jendela pelunasan belum tertutup;
- `snap_token` kosong;
- `midtrans_order_id` kosong;
- status payment `failed`, `expired`, atau `cancelled`.

Kode:

```php
if (!$isCashPayment && $isPaymentConfigured && !$isRemainingPaymentWindowClosed && (
    !$payment->snap_token
    || !$payment->midtrans_order_id
    || $shouldRegenerateSnap
)) {
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:90`

Jika status lama failed/expired/cancelled, order ID lama dikosongkan:

```php
$payment->midtrans_order_id = null;
$payment->snap_token = null;
$payment->snap_redirect_url = null;
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:98`

Lalu sistem membuat transaksi Snap baru:

```php
$snap = $this->tenantMidtransService->createSnapTransaction(
    $tenant,
    $booking,
    $payment,
    $user,
    $remainingPaymentDeadline
);
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:103`

### 11.5 Data Yang Dikirim Ke View Checkout

Kode:

```php
return view('payments.checkout', [
    'tenant' => $tenant,
    'booking' => $booking,
    'payment' => $payment,
    'snapClientKey' => $this->tenantMidtransService->getSnapClientKey($tenant),
    'snapJsUrl' => $this->tenantMidtransService->getSnapJsUrl($tenant),
    'isPaymentConfigured' => $isPaymentConfigured,
    'bookingTotal' => $bookingTotal,
    'paidAmount' => $paidAmount,
    'remainingAmount' => $remainingAmount,
]);
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:130`

Data penting untuk Snap:

- `snapClientKey`
- `snapJsUrl`
- `payment.snap_token`

---

## 12. Alur Snap JS Di Halaman Checkout

View:

- `resources/views/payments/checkout.blade.php`

### 12.1 Menentukan Checkout Siap Atau Tidak

Kode:

```php
$snapReady = !$isCashPayment && (bool) ($snapClientKey && $payment->snap_token);
```

Letak kode:

- `resources/views/payments/checkout.blade.php:43`

Artinya, Snap siap jika:

- pembayaran bukan cash;
- client key tersedia;
- snap token tersedia.

### 12.2 Memuat Library Snap JS

Kode:

```blade
@if ($snapReady)
    <script src="{{ $snapJsUrl }}" data-client-key="{{ $snapClientKey }}"></script>
@endif
```

Letak kode:

- `resources/views/payments/checkout.blade.php:556`

Jika tenant memakai sandbox, URL yang dimuat:

```text
https://app.sandbox.midtrans.com/snap/snap.js
```

Jika tenant memakai production:

```text
https://app.midtrans.com/snap/snap.js
```

### 12.3 Tombol Bayar Membuka Popup Midtrans

Tombol bayar:

```blade
<button id="pay-button">
```

Letak kode:

- `resources/views/payments/checkout.blade.php:525`

Token Snap diambil dari payment:

```js
const snapToken = @json($payment->snap_token);
```

Letak kode:

- `resources/views/payments/checkout.blade.php:565`

Ketika tombol diklik, sistem memanggil:

```js
window.snap.pay(snapToken, {
    onSuccess: function () {
        updateLiveStatus('Pembayaran berhasil. Anda akan diarahkan ke halaman profil.', 'success');
        window.location.href = successRedirectUrl;
    },
    onPending: function () {
        updateLiveStatus('Pembayaran masih pending. Selesaikan pembayaran di kanal yang dipilih.', 'default');
        payButton.disabled = false;
        payButton.textContent = payButtonDefaultLabel;
    },
    onError: function () {
        updateLiveStatus('Pembayaran gagal. Silakan coba metode lain atau ulangi lagi.', 'danger');
        payButton.disabled = false;
        payButton.textContent = 'Coba Bayar Lagi';
    },
    onClose: function () {
        updateLiveStatus('Popup ditutup sebelum selesai. Anda bisa lanjutkan pembayaran kapan saja.', 'default');
        payButton.disabled = false;
        payButton.textContent = payButtonDefaultLabel;
    }
});
```

Letak kode:

- `resources/views/payments/checkout.blade.php:685`

Fungsi callback di browser:

- `onSuccess`: customer diarahkan ke profil;
- `onPending`: customer diberi informasi pembayaran belum selesai;
- `onError`: tombol bayar diaktifkan kembali;
- `onClose`: customer bisa membuka ulang pembayaran.

Catatan penting:

Callback browser bukan sumber utama kebenaran data. Status final pembayaran tetap diambil dari callback/webhook server Midtrans.

---

## 13. Alur Pelunasan

Pelunasan digunakan jika booking awal memakai skema DP.

Controller:

- `app/Http/Controllers/PaymentController.php`

Method:

```php
public function createRemainingPayment(Tenant $tenant, int $bookingId, Request $request)
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:151`

### 13.1 Menghitung Sisa Pembayaran

Kode:

```php
$this->syncBookingPaymentState($booking);
$booking->refresh();

$remainingAmount = $this->calculateRemainingAmount($booking);
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:171`

Sisa pembayaran dihitung dari:

```text
total booking - total payment success
```

Method hitung sisa:

```php
private function calculateRemainingAmount(Booking $booking): float
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:460`

### 13.2 Menentukan Deadline Pelunasan

Kode:

```php
$remainingPaymentDeadline = $this->resolveRemainingPaymentDeadline($booking);
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:181`

Method:

```php
private function resolveRemainingPaymentDeadline(Booking $booking): ?Carbon
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:503`

Kode:

```php
return Carbon::parse($jadwal->tanggal.' '.$jadwal->waktu_selesai)->subMinutes(15);
```

Artinya, pelunasan maksimal dilakukan 15 menit sebelum jadwal selesai.

### 13.3 Membuat Payment Pelunasan

Kode:

```php
$payment = Payment::query()->create([
    'method' => 'Midtrans',
    'status' => 'pending',
    'payment_type' => 'remaining',
    'amount' => $remainingAmount,
    'expires_time' => $remainingPaymentDeadline,
    'tenants_idTenant' => $tenant->idTenant,
    'booking_idbooking' => $booking->idbooking,
]);
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:211`

### 13.4 Membuat Snap Untuk Pelunasan

Kode:

```php
$snap = $this->tenantMidtransService->createSnapTransaction(
    $tenant,
    $booking,
    $payment,
    $user,
    $remainingPaymentDeadline
);
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:222`

Karena `$remainingPaymentDeadline` dikirim ke `createSnapTransaction()`, service akan membuat `custom_expiry`.

Kode custom expiry:

```php
$params['custom_expiry'] = [
    'start_time' => now()->format('Y-m-d H:i:s O'),
    'unit' => 'minute',
    'duration' => max(1, now()->diffInMinutes($expiresAt)),
];
```

Letak kode:

- `app/Support/TenantMidtransService.php:148`

---

## 14. Alur Callback Atau Webhook Midtrans

Callback/webhook adalah notifikasi server-to-server dari Midtrans ke sistem StudioKita.

Webhook dipanggil ketika status transaksi berubah, misalnya:

- pending
- settlement
- capture
- expire
- cancel
- deny
- failure

Route webhook:

- `routes/web.php:149`

Kode route:

```php
Route::post('/payments/midtrans/webhook', [PaymentController::class, 'midtransWebhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('payments.midtrans.webhook');
```

Controller:

- `app/Http/Controllers/PaymentController.php`

Method:

```php
public function midtransWebhook(Request $request)
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:366`

### 14.1 Mengambil Payload Dan Order ID

Kode:

```php
$payload = $request->all();
$orderId = (string) ($payload['order_id'] ?? '');
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:368`

Jika `order_id` kosong, sistem menolak:

```php
if ($orderId === '') {
    return response()->json(['message' => 'order_id wajib'], 422);
}
```

### 14.2 Parse Order ID Untuk Menemukan Tenant

Kode:

```php
$parsed = $this->tenantMidtransService->parseOrderId($orderId);
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:375`

Method parse:

```php
public function parseOrderId(string $orderId): ?array
```

Letak kode:

- `app/Support/TenantMidtransService.php:173`

Regex:

```php
if (!preg_match('/^SK-(\d+)-(\d+)-(\d+)-(\d+)$/', $orderId, $matches)) {
    return null;
}
```

Return:

```php
return [
    'tenant_id' => (int) $matches[1],
    'payment_id' => (int) $matches[2],
    'booking_id' => (int) $matches[3],
];
```

### 14.3 Mengaktifkan Database Tenant

Setelah tenant ID diketahui, sistem mencari tenant di database pusat:

```php
$tenant = Tenant::query()->find($parsed['tenant_id']);
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:380`

Kemudian database tenant diaktifkan:

```php
$this->tenantDbManager->activateForTenant($tenant, true);
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:385`

Ini penting karena payment dan booking berada di database tenant, bukan database pusat.

### 14.4 Validasi Signature Midtrans

Kode:

```php
if (!$this->tenantMidtransService->isValidSignature($tenant, $payload)) {
    return response()->json(['message' => 'signature tidak valid'], 403);
}
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:387`

Method validasi:

```php
public function isValidSignature(Tenant $tenant, array $payload): bool
```

Letak kode:

- `app/Support/TenantMidtransService.php:186`

Formula signature:

```php
$raw = $orderId.$statusCode.$grossAmount.$serverKey;
$expected = hash('sha512', $raw);
```

Letak kode:

- `app/Support/TenantMidtransService.php:207`

Sistem membandingkan signature dari Midtrans dengan hasil hash sistem:

```php
return hash_equals($expected, $signatureKey);
```

Letak kode:

- `app/Support/TenantMidtransService.php:210`

### 14.5 Mencari Payment Dan Booking

Webhook update dilakukan di database tenant:

```php
$this->tenantDbManager->runForTenant($tenant, function () use ($payload, $parsed, $orderId) {
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:391`

Payment dicari berdasarkan payment ID dan booking ID:

```php
$payment = Payment::query()
    ->where('idpayments', $parsed['payment_id'])
    ->where('booking_idbooking', $parsed['booking_id'])
    ->first();
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:392`

Jika tidak ditemukan, payment dicari berdasarkan `midtrans_order_id`:

```php
$payment = Payment::query()
    ->where('midtrans_order_id', $orderId)
    ->first();
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:398`

### 14.6 Mapping Status Midtrans Ke Status Sistem

Kode:

```php
$mappedStatus = $this->tenantMidtransService->mapStatus(
    (string) ($payload['transaction_status'] ?? ''),
    (string) ($payload['fraud_status'] ?? '')
);
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:411`

Method:

```php
public function mapStatus(string $transactionStatus, ?string $fraudStatus = null): string
```

Letak kode:

- `app/Support/TenantMidtransService.php:213`

Mapping:

```php
return match ($transactionStatus) {
    'capture' => $fraudStatus === 'accept' ? 'success' : 'pending',
    'settlement' => 'success',
    'pending' => 'pending',
    'deny', 'failure' => 'failed',
    'expire' => 'expired',
    'cancel' => 'cancelled',
    default => 'pending',
};
```

### 14.7 Update Payment Dari Payload Midtrans

Kode:

```php
$payment->forceFill([
    'method' => 'Midtrans',
    'midtrans_order_id' => $orderId,
    'midtrans_transaction_id' => $payload['transaction_id'] ?? $payment->midtrans_transaction_id,
    'status' => $mappedStatus,
    'raw_status' => $payload['transaction_status'] ?? $payment->raw_status,
    'webhook_payload' => json_encode($payload),
    'payment_time' => $mappedStatus === 'success'
        ? ($transactionTime ?: $now)
        : $payment->payment_time,
    'paid_at' => $mappedStatus === 'success'
        ? $now
        : $payment->paid_at,
    'failed_at' => in_array($mappedStatus, ['failed', 'expired', 'cancelled'], true)
        ? $now
        : $payment->failed_at,
    'expires_time' => $expiryTime ?: $payment->expires_time,
])->save();
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:421`

Field yang diupdate:

- `method`
- `midtrans_order_id`
- `midtrans_transaction_id`
- `status`
- `raw_status`
- `webhook_payload`
- `payment_time`
- `paid_at`
- `failed_at`
- `expires_time`

### 14.8 Sinkronisasi Booking

Setelah payment diupdate, booking dihitung ulang:

```php
$this->syncBookingPaymentState($booking);
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:443`

Jika payment sukses dan booking masih pending, booking menjadi confirmed:

```php
if ($mappedStatus === 'success' && $booking->status === 'pending') {
    $booking->update(['status' => 'confirmed']);
}
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:445`

---

## 15. Sinkronisasi Status Pembayaran Booking

Method:

```php
private function syncBookingPaymentState(Booking $booking): void
```

Letak kode:

- `app/Http/Controllers/PaymentController.php:469`

Fungsinya:

- menghitung semua payment `success` pada booking;
- menjumlahkan nominalnya;
- mengisi `paid_amount`;
- menentukan `payment_state`.

Kode:

```php
$paid = $this->calculatePaidAmount((int) $booking->idbooking);
$normalizedPaid = min(max($paid, 0), $total);
```

Status payment booking:

```php
$state = 'unpaid';
if ($normalizedPaid > 0 && $normalizedPaid < $total) {
    $state = 'partial';
} elseif ($normalizedPaid >= $total && $total > 0) {
    $state = 'paid';
}
```

Field booking yang disimpan:

```php
$booking->forceFill([
    'paid_amount' => round($normalizedPaid, 2),
    'payment_state' => $state,
])->save();
```

Arti status:

- `unpaid`: belum ada payment success;
- `partial`: sudah bayar sebagian, biasanya DP;
- `paid`: sudah lunas.

---

## 16. Data Yang Dibutuhkan Sebelum Transaksi Midtrans Dibuat

Sebelum sistem memanggil `Snap::createTransaction()`, data yang dibutuhkan adalah:

### 16.1 Data Konfigurasi Tenant

Sumber:

- `tenant_payment_accounts`
- `app/Models/TenantPaymentAccount.php`
- `app/Support/TenantMidtransService.php`

Data:

- client key
- server key
- mode sandbox atau production
- status active
- status test koneksi

### 16.2 Data Tenant

Sumber:

- `tenants`
- `app/Models/Tenant.php`

Data:

- ID tenant
- nama studio
- email studio
- nomor telepon studio

### 16.3 Data Booking

Sumber:

- `bookings`
- `app/Models/Booking.php`

Data:

- ID booking
- total harga
- skema pembayaran
- persentase DP jika ada
- status booking

### 16.4 Data Payment

Sumber:

- `payments`
- `app/Models/Payment.php`

Data:

- ID payment
- nominal payment
- tipe payment: `full`, `dp`, atau `remaining`
- status payment

### 16.5 Data Customer

Sumber:

- user login
- `app/Models/User.php`

Data:

- nama
- email
- nomor telepon

### 16.6 Data Expiry Jika Pelunasan

Sumber:

- jadwal booking
- `PaymentController::resolveRemainingPaymentDeadline()`

Data:

- deadline pelunasan
- custom expiry Midtrans

---

## 17. Data Yang Dihasilkan Dari Transaksi Midtrans

### 17.1 Saat Create Transaction

Dihasilkan oleh:

```php
Snap::createTransaction($params)
```

Letak kode:

- `app/Support/TenantMidtransService.php:155`

Data yang diterima:

- `order_id`
- `token`
- `redirect_url`

Disimpan ke tabel `payments` sebagai:

- `midtrans_order_id`
- `snap_token`
- `snap_redirect_url`

### 17.2 Saat Callback/Webhook

Data penting dari payload Midtrans:

- `order_id`
- `transaction_id`
- `transaction_status`
- `fraud_status`
- `status_code`
- `gross_amount`
- `signature_key`
- `transaction_time`
- `expiry_time`

Digunakan untuk:

- mencari tenant;
- mencari payment;
- validasi signature;
- mapping status;
- update payment;
- update booking.

### 17.3 Data Yang Disimpan Ke Sistem

Pada tabel `payments`:

- `midtrans_order_id`
- `midtrans_transaction_id`
- `status`
- `raw_status`
- `webhook_payload`
- `payment_time`
- `paid_at`
- `failed_at`
- `expires_time`

Pada tabel `bookings`:

- `payment_state`
- `paid_amount`
- `status`

---

## 18. Perbedaan Callback Browser Dan Webhook

Pada checkout, Snap JS memiliki callback browser:

- `onSuccess`
- `onPending`
- `onError`
- `onClose`

Letak kode:

- `resources/views/payments/checkout.blade.php:685`

Callback browser hanya digunakan untuk memberi respons di halaman customer.

Sumber kebenaran status pembayaran tetap webhook server:

- `PaymentController::midtransWebhook()`
- `app/Http/Controllers/PaymentController.php:366`

Alasannya:

- callback browser bisa tidak terpanggil jika browser ditutup;
- customer bisa kehilangan koneksi;
- status pembayaran final harus berasal dari notifikasi server Midtrans;
- webhook lebih aman karena divalidasi dengan signature key.

---

## 19. Alur Lengkap Dalam Bentuk Ringkas

Alur aktivasi:

```text
Owner isi pengajuan Midtrans
Owner submit pengajuan
Developer isi client key dan server key
Developer test koneksi Midtrans
Developer approve pengajuan
Tenant siap menerima pembayaran Midtrans
```

Alur booking dan pembayaran:

```text
Customer pilih studio
Customer pilih room, service, jadwal
Customer pilih skema pembayaran full atau DP
Sistem membuat booking pending
Sistem membuat payment pending
Sistem membuat transaksi Snap Midtrans
Sistem menyimpan order_id, snap_token, redirect_url
Customer membuka halaman checkout
Checkout memuat Snap JS
Customer klik bayar
Midtrans menampilkan popup pembayaran
Customer menyelesaikan pembayaran
Midtrans mengirim webhook ke sistem
Sistem validasi signature
Sistem update payment
Sistem update payment_state booking
Jika sukses, booking menjadi confirmed
```

Alur pelunasan:

```text
Booking awal dibayar DP
Payment DP sukses
Booking payment_state menjadi partial
Customer membuat tagihan pelunasan
Sistem menghitung sisa pembayaran
Sistem membuat payment remaining
Sistem membuat transaksi Snap dengan custom expiry
Customer membayar pelunasan
Webhook Midtrans masuk
Payment remaining menjadi success
Booking payment_state menjadi paid
```

---

## 20. Jawaban Singkat Untuk Sidang

Jika penguji bertanya, "Bagaimana alur Midtrans di sistem ini?", jawab:

```text
Midtrans pada StudioKita digunakan per tenant. Owner mengajukan data payment settings, lalu developer mengisi client key dan server key Midtrans serta melakukan test koneksi. Jika konfigurasi aktif dan test berhasil, tenant dapat menerima pembayaran online. Saat customer booking, sistem membuat data booking dan payment di database tenant. Jika pembayaran memakai Midtrans, sistem membuat transaksi Snap melalui TenantMidtransService. Service tersebut mengirim order_id, gross_amount, customer_details, dan item_details ke Midtrans. Midtrans mengembalikan snap_token dan redirect_url yang disimpan ke tabel payments. Pada halaman checkout, sistem memuat Snap JS memakai client key tenant dan membuka popup pembayaran menggunakan snap_token. Status akhir pembayaran tidak hanya bergantung pada browser, tetapi diproses melalui webhook Midtrans. Webhook membawa order_id, transaction_status, fraud_status, gross_amount, dan signature_key. Sistem membaca tenant dari order_id, mengaktifkan database tenant, memvalidasi signature dengan server key, lalu mengupdate payment dan booking. Jika pembayaran sukses, payment menjadi success, paid_amount booking bertambah, payment_state berubah menjadi partial atau paid, dan booking pending menjadi confirmed.
```

---

## 21. File Paling Penting Untuk Ditunjukkan Saat Sidang

Urutan file yang paling kuat untuk menjelaskan Midtrans:

- `app/Http/Controllers/Owner/PaymentSettingsController.php:65`
- `app/Http/Controllers/Developer/TenantPaymentSettingsController.php:28`
- `app/Http/Controllers/Developer/TenantPaymentSettingsController.php:116`
- `app/Support/TenantMidtransService.php:55`
- `app/Support/TenantMidtransService.php:97`
- `app/Support/TenantMidtransService.php:155`
- `app/Http/Controllers/StudioBookingController.php:246`
- `app/Http/Controllers/StudioBookingController.php:266`
- `app/Http/Controllers/StudioBookingController.php:287`
- `app/Http/Controllers/PaymentController.php:25`
- `resources/views/payments/checkout.blade.php:556`
- `resources/views/payments/checkout.blade.php:685`
- `app/Http/Controllers/PaymentController.php:151`
- `app/Http/Controllers/PaymentController.php:366`
- `app/Http/Controllers/PaymentController.php:421`
- `app/Http/Controllers/PaymentController.php:469`

---

## 22. Kesimpulan

Implementasi Midtrans pada StudioKita dibangun dengan pola:

```text
konfigurasi Midtrans per tenant
key disimpan terenkripsi
Snap transaction dibuat dari data booking dan payment
checkout memakai Snap JS
status final diproses melalui webhook
webhook divalidasi dengan signature key
payment dan booking diupdate pada database tenant yang benar
```

Dengan alur ini, pembayaran online dapat berjalan pada sistem SaaS multi-tenant tanpa mencampur data transaksi antar studio.

