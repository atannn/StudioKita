<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Owner\DashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Owner\RoomController;
use App\Http\Controllers\Owner\ServiceController;
use App\Http\Controllers\Owner\JadwalController;
use App\Http\Controllers\Owner\JadwalHarianOverrideController;
use App\Http\Controllers\Owner\JadwalTemplateController;
use App\Http\Controllers\Owner\FacilityController;
use App\Http\Controllers\Owner\TenantController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Owner\BookingController as OwnerBookingController;
use App\Http\Controllers\StudioController;
use App\Http\Controllers\StudioBookingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Developer\DashboardController as DeveloperDashboardController;
use App\Http\Controllers\Developer\AnnouncementController as DeveloperAnnouncementController;
use App\Http\Controllers\Developer\TenantController as DeveloperTenantController;
use App\Http\Controllers\Developer\TenantPaymentSettingsController as DeveloperTenantPaymentSettingsController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\Owner\VerificationController as OwnerVerificationController;
use App\Http\Controllers\Owner\PaymentSettingsController as OwnerPaymentSettingsController;


Route::get('/', function () {
    return redirect()->route('studios.index');
});

Route::get('/dashboard', function () {
    $user = Auth::user();

    if ($user?->role === 'owner') {
        return redirect()->route('owner.dashboard');
    }

    if ($user?->role === 'developer') {
        return redirect()->route('developer.dashboard');
    }

    return redirect()->route('studios.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'owner', 'tenant.db'])->prefix('owner')->name('owner.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('/rooms', RoomController::class)->except(['show']);
        Route::resource('/services', ServiceController::class)->except(['show']);
        Route::resource('/facilities', FacilityController::class)->except(['show']);
        Route::resource('/jadwals', JadwalController::class)->except(['show']);
        Route::get('/jadwals/harian/create', [JadwalHarianOverrideController::class, 'create'])->name('jadwals.harian.create');
        Route::post('/jadwals/harian', [JadwalHarianOverrideController::class, 'store'])->name('jadwals.harian.store');
        Route::get('/jadwals/harian/{id}/edit', [JadwalHarianOverrideController::class, 'edit'])->name('jadwals.harian.edit');
        Route::put('/jadwals/harian/{id}', [JadwalHarianOverrideController::class, 'update'])->name('jadwals.harian.update');
        Route::delete('/jadwals/harian/{id}', [JadwalHarianOverrideController::class, 'destroy'])->name('jadwals.harian.destroy');
        Route::get('/jadwals/templates', [JadwalTemplateController::class, 'index'])->name('jadwals.templates.index');
        Route::get('/jadwals/templates/create', [JadwalTemplateController::class, 'create'])->name('jadwals.templates.create');
        Route::post('/jadwals/templates', [JadwalTemplateController::class, 'store'])->name('jadwals.templates.store');
        Route::get('/jadwals/templates/{id}/edit', [JadwalTemplateController::class, 'edit'])->name('jadwals.templates.edit');
        Route::put('/jadwals/templates/{id}', [JadwalTemplateController::class, 'update'])->name('jadwals.templates.update');
        Route::patch('/jadwals/templates/{id}/toggle-active', [JadwalTemplateController::class, 'toggleActive'])->name('jadwals.templates.toggle-active');
        Route::delete('/jadwals/templates/{id}', [JadwalTemplateController::class, 'destroy'])->name('jadwals.templates.destroy');
        Route::post('/jadwals/templates/sync-all', [JadwalTemplateController::class, 'syncAll'])->name('jadwals.templates.sync-all');
        Route::post('/jadwals/templates/{id}/sync', [JadwalTemplateController::class, 'syncOne'])->name('jadwals.templates.sync');
        Route::get('/tenant/settings', [TenantController::class, 'edit'])->name('tenant.edit');
        Route::put('/tenant/settings', [TenantController::class, 'update'])->name('tenant.update');
        Route::post('/tenant/photos', [TenantController::class, 'updatePhotos'])->name('tenant.photos');
        Route::delete('/tenant/photos/{id}', [TenantController::class, 'destroyPhoto'])->name('tenant.photos.destroy');
        Route::get('/verification', [OwnerVerificationController::class, 'index'])->name('verification.index');
        Route::post('/verification/email-otp/send', [OwnerVerificationController::class, 'sendEmailOtp'])->name('verification.email-otp.send');
        Route::post('/verification/email-otp/verify', [OwnerVerificationController::class, 'verifyEmailOtp'])->name('verification.email-otp.verify');
        Route::post('/verification/manual-submit', [OwnerVerificationController::class, 'submitManual'])->name('verification.manual-submit');
        Route::get('/verification/documents/{document}', [OwnerVerificationController::class, 'downloadDocument'])->name('verification.documents.download');
        Route::get('/payment-settings', [OwnerPaymentSettingsController::class, 'edit'])->name('payment-settings.edit');
        Route::match(['post', 'put'], '/payment-settings', [OwnerPaymentSettingsController::class, 'update'])->name('payment-settings.update');
        Route::post('/payment-settings/submit', [OwnerPaymentSettingsController::class, 'submit'])->name('payment-settings.submit');
        Route::post('/payment-settings/preferences', [OwnerPaymentSettingsController::class, 'updatePreferences'])->name('payment-settings.preferences');
        Route::get('/bookings', [OwnerBookingController::class, 'index'])->name('bookings.index');
        Route::patch('/bookings/{id}/confirm', [OwnerBookingController::class, 'confirm'])->name('bookings.confirm');
        Route::patch('/bookings/{id}/mark-no-show', [OwnerBookingController::class, 'markNoShow'])->name('bookings.mark-no-show');
        Route::patch('/bookings/{id}/cancel', [OwnerBookingController::class, 'cancel'])->name('bookings.cancel');
        Route::patch('/bookings/{id}/complete', [OwnerBookingController::class, 'complete'])->name('bookings.complete');
        Route::get('/setup/step-1', [\App\Http\Controllers\Owner\SetupController::class, 'stepOne'])->name('setup.step1');
        Route::post('/setup/step-1', [\App\Http\Controllers\Owner\SetupController::class, 'storeStepOne'])->name('setup.step1.store');
        Route::get('/setup/step-2', [\App\Http\Controllers\Owner\SetupController::class, 'stepTwo'])->name('setup.step2');
        Route::post('/setup/step-2', [\App\Http\Controllers\Owner\SetupController::class, 'storeStepTwo'])->name('setup.step2.store');
        Route::get('/setup/step-3', [\App\Http\Controllers\Owner\SetupController::class, 'stepThree'])->name('setup.step3');
        Route::post('/setup/step-3', [\App\Http\Controllers\Owner\SetupController::class, 'storeStepThree'])->name('setup.step3.store');
        Route::get('/welcome', [\App\Http\Controllers\Owner\SetupController::class, 'welcome'])->name('welcome');
    });

Route::middleware(['auth', 'developer'])->prefix('developer')->name('developer.')->group(function () {
    Route::get('/dashboard', [DeveloperDashboardController::class, 'index'])->name('dashboard');
    Route::post('/announcements', [DeveloperAnnouncementController::class, 'store'])->name('announcements.store');
    Route::get('/tenants/{tenant:slug}', [DeveloperTenantController::class, 'show'])->name('tenants.show');
    Route::patch('/tenants/{tenant:slug}/payment-submission/review', [DeveloperTenantPaymentSettingsController::class, 'reviewSubmission'])->name('tenants.payment-submission.review');
    Route::put('/tenants/{tenant:slug}/payment-settings/test', [DeveloperTenantPaymentSettingsController::class, 'testConnection'])->name('tenants.payment-settings.test');
    Route::put('/tenants/{tenant:slug}/payment-settings', [DeveloperTenantPaymentSettingsController::class, 'update'])->name('tenants.payment-settings.update');
    Route::patch('/tenants/{tenant:slug}/status', [DeveloperTenantController::class, 'updateStatus'])->name('tenants.status');
    Route::delete('/tenants/{tenant:slug}', [DeveloperTenantController::class, 'destroy'])->name('tenants.destroy');
    Route::patch('/tenants/{tenant:slug}/verification/approve', [DeveloperTenantController::class, 'approveVerification'])->name('tenants.verification.approve');
    Route::patch('/tenants/{tenant:slug}/verification/reject', [DeveloperTenantController::class, 'rejectVerification'])->name('tenants.verification.reject');
    Route::get('/verification/documents/{document}', [DeveloperTenantController::class, 'downloadVerificationDocument'])->name('verification.documents.download');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/customer/profile', [CustomerProfileController::class, 'index'])->name('customer.profile');
    Route::get('/booking/create', [BookingController::class, 'create'])
        ->middleware('tenant.db')
        ->name('booking.create');
    Route::post('/booking', [BookingController::class, 'store'])
        ->middleware('tenant.db')
        ->name('booking.store');
    Route::get('/studios/{tenant:slug}/booking/create', [StudioBookingController::class, 'create'])
        ->middleware('tenant.db')
        ->name('studios.booking.create');
    Route::post('/studios/{tenant:slug}/booking', [StudioBookingController::class, 'store'])
        ->middleware('tenant.db')
        ->name('studios.booking.store');
    Route::get('/studios/{tenant:slug}/booking/rooms', [StudioBookingController::class, 'rooms'])
        ->middleware('tenant.db')
        ->name('studios.booking.rooms');
    Route::get('/studios/{tenant:slug}/booking/slots', [StudioBookingController::class, 'slots'])
        ->middleware('tenant.db')
        ->name('studios.booking.slots');
    Route::get('/studios/{tenant:slug}/payments/{paymentId}/checkout', [PaymentController::class, 'checkout'])
        ->middleware('tenant.db')
        ->name('studios.payments.checkout');
    Route::post('/studios/{tenant:slug}/bookings/{bookingId}/payments/remaining', [PaymentController::class, 'createRemainingPayment'])
        ->middleware('tenant.db')
        ->name('studios.payments.remaining.create');
    Route::post('/studios/{tenant:slug}/payments/{paymentId}/cancel-booking', [PaymentController::class, 'cancelBooking'])
        ->middleware('tenant.db')
        ->name('studios.payments.cancel-booking');
    Route::post('/studios/{tenant:slug}/payments/{paymentId}/bypass-success', [PaymentController::class, 'bypassSuccess'])
        ->middleware('tenant.db')
        ->name('studios.payments.bypass-success');
    // ajax untuk ambil jadwal available
    Route::get('/booking/slots', [BookingController::class, 'slots'])
        ->middleware('tenant.db')
        ->name('booking.slots');
});

Route::post('/payments/midtrans/webhook', [PaymentController::class, 'midtransWebhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
    ->name('payments.midtrans.webhook');

Route::get('/studios', [StudioController::class, 'index'])->name('studios.index');
Route::get('/studios/katalog', [StudioController::class, 'catalog'])->name('studios.catalog');
Route::get('/cara-kerja', [StudioController::class, 'how'])->name('studios.how');
Route::get('/gabung', [StudioController::class, 'join'])->name('studios.join');
Route::get('/studios/{tenant:slug}', [StudioController::class, 'show'])
    ->middleware('tenant.db')
    ->name('studios.show');

require __DIR__.'/auth.php';
