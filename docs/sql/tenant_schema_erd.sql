-- StudioKita - Tenant Database ERD Schema (MySQL Workbench)
-- =========================================================
-- Tujuan:
-- 1. Membuat schema TERPISAH khusus visualisasi ERD di MySQL Workbench.
-- 2. Menghindari tercampurnya tabel tenant dengan DB pusat `studiokita`.
--
-- Catatan penting:
-- - Runtime aplikasi tenant tetap memakai SQLite.
-- - Script ini untuk MODEL / VISUALISASI ERD di MySQL Workbench.
-- - Tabel pusat seperti `tenant_midtrans_submissions` SENGAJA tidak dimasukkan
--   karena workflow pengajuan Midtrans berada di DB pusat, bukan DB tenant.
-- - Kolom seperti `tenants_idTenant`, `tenant_id`, `user_id`, `created_by`,
--   dan `handled_by_user_id` adalah referensi logis ke DB pusat, sehingga
--   sengaja TIDAK dibuat sebagai foreign key di schema tenant ERD ini.
-- - Relasi internal antartabel tenant tetap digambar sebagai FK agar diagram
--   mudah dibaca.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS `studiokita_tenant_erd`;
CREATE DATABASE `studiokita_tenant_erd`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `studiokita_tenant_erd`;

CREATE TABLE `rooms` (
    `idrooms` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama_ruangan` VARCHAR(100) NOT NULL,
    `deskripsi` VARCHAR(255) NULL,
    `kapasitas` INT NOT NULL DEFAULT 1,
    `status` TINYINT NOT NULL DEFAULT 1,
    `tipe_ruangan` ENUM('latihan', 'rekaman') NOT NULL,
    `foto_ruangan` VARCHAR(255) NULL,
    `tenants_idTenant` BIGINT UNSIGNED NOT NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`idrooms`),
    UNIQUE KEY `rooms_tenant_name_unique` (`tenants_idTenant`, `nama_ruangan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `services` (
    `idservice` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama_service` VARCHAR(100) NOT NULL,
    `tipe_service` ENUM('latihan', 'rekaman') NOT NULL,
    `durasi_menit` INT NOT NULL,
    `weekday_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `weekend_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `deskripsi` VARCHAR(255) NULL,
    `status` TINYINT NOT NULL DEFAULT 1,
    `tenants_idTenant` BIGINT UNSIGNED NOT NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`idservice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `facilities` (
    `idfasiltas` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama_fasilitas` VARCHAR(100) NOT NULL,
    `deskripsi` VARCHAR(255) NULL,
    `status` TINYINT NOT NULL DEFAULT 1,
    `tenants_idTenant` BIGINT UNSIGNED NOT NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`idfasiltas`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `photos` (
    `idfoto` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `foto_path` VARCHAR(255) NOT NULL,
    `caption` VARCHAR(100) NULL,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
    `uploaded_at` DATETIME NULL,
    `status` TINYINT NOT NULL DEFAULT 1,
    `tenants_idTenant` BIGINT UNSIGNED NOT NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`idfoto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `schedule_templates` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenants_idTenant` BIGINT UNSIGNED NOT NULL,
    `rooms_idrooms` BIGINT UNSIGNED NOT NULL,
    `service_idservice` BIGINT UNSIGNED NOT NULL,
    `nama_template` VARCHAR(120) NOT NULL,
    `repeat_mode` ENUM('daily', 'weekdays', 'weekends', 'custom_days') NOT NULL DEFAULT 'daily',
    `days_of_week_json` TEXT NULL,
    `waktu_mulai` TIME NOT NULL,
    `waktu_selesai` TIME NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `schedule_templates_tenant_active_index` (`tenants_idTenant`, `is_active`),
    KEY `schedule_templates_room_service_index` (`rooms_idrooms`, `service_idservice`),
    CONSTRAINT `fk_schedule_templates_rooms`
        FOREIGN KEY (`rooms_idrooms`) REFERENCES `rooms` (`idrooms`) ON DELETE CASCADE,
    CONSTRAINT `fk_schedule_templates_services`
        FOREIGN KEY (`service_idservice`) REFERENCES `services` (`idservice`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `schedule_date_harian_overrides` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenants_idTenant` BIGINT UNSIGNED NOT NULL,
    `rooms_idrooms` BIGINT UNSIGNED NOT NULL,
    `service_idservice` BIGINT UNSIGNED NULL,
    `tanggal` DATE NOT NULL,
    `override_type` ENUM('add_slot', 'block_interval', 'close_day') NOT NULL,
    `waktu_mulai` TIME NULL,
    `waktu_selesai` TIME NULL,
    `catatan` TEXT NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `schedule_date_harian_overrides_tenant_date_index` (`tenants_idTenant`, `tanggal`),
    KEY `schedule_date_harian_overrides_room_date_index` (`rooms_idrooms`, `tanggal`),
    CONSTRAINT `fk_schedule_date_harian_overrides_rooms`
        FOREIGN KEY (`rooms_idrooms`) REFERENCES `rooms` (`idrooms`) ON DELETE CASCADE,
    CONSTRAINT `fk_schedule_date_harian_overrides_services`
        FOREIGN KEY (`service_idservice`) REFERENCES `services` (`idservice`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jadwals` (
    `idJadwal` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tanggal` DATE NOT NULL,
    `waktu_mulai` TIME NOT NULL,
    `waktu_selesai` TIME NOT NULL,
    `status` ENUM('available', 'booked', 'blocked') NOT NULL DEFAULT 'available',
    `tenants_idTenant` BIGINT UNSIGNED NOT NULL,
    `rooms_idrooms` BIGINT UNSIGNED NOT NULL,
    `service_idservice` BIGINT UNSIGNED NULL,
    `source_type` VARCHAR(20) NOT NULL DEFAULT 'manual',
    `schedule_template_id` BIGINT UNSIGNED NULL,
    `schedule_date_harian_override_id` BIGINT UNSIGNED NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`idJadwal`),
    UNIQUE KEY `jadwals_room_date_time_unique` (`rooms_idrooms`, `tanggal`, `waktu_mulai`, `waktu_selesai`),
    KEY `jadwals_rooms_idrooms_index` (`rooms_idrooms`),
    KEY `jadwals_service_idservice_index` (`service_idservice`),
    KEY `jadwals_schedule_template_id_index` (`schedule_template_id`),
    KEY `jadwals_schedule_date_harian_override_id_index` (`schedule_date_harian_override_id`),
    KEY `jadwals_source_template_lookup_index` (`source_type`, `schedule_template_id`),
    CONSTRAINT `fk_jadwals_rooms`
        FOREIGN KEY (`rooms_idrooms`) REFERENCES `rooms` (`idrooms`) ON DELETE CASCADE,
    CONSTRAINT `fk_jadwals_services`
        FOREIGN KEY (`service_idservice`) REFERENCES `services` (`idservice`) ON DELETE SET NULL,
    CONSTRAINT `fk_jadwals_schedule_templates`
        FOREIGN KEY (`schedule_template_id`) REFERENCES `schedule_templates` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_jadwals_schedule_date_harian_overrides`
        FOREIGN KEY (`schedule_date_harian_override_id`) REFERENCES `schedule_date_harian_overrides` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bookings` (
    `idbooking` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tanggal_booking` DATETIME NOT NULL,
    `total_harga` DECIMAL(12,2) NOT NULL,
    `status` ENUM('pending', 'confirmed', 'cancelled', 'completed', 'no_show') NOT NULL DEFAULT 'pending',
    `tenants_idTenant` BIGINT UNSIGNED NOT NULL,
    `rooms_idrooms` BIGINT UNSIGNED NOT NULL,
    `service_idservice` BIGINT UNSIGNED NOT NULL,
    `Jadwal_idJadwal` BIGINT UNSIGNED NOT NULL,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `payment_scheme` VARCHAR(20) NOT NULL DEFAULT 'full',
    `dp_percent` TINYINT UNSIGNED NULL,
    `payment_state` VARCHAR(20) NOT NULL DEFAULT 'unpaid',
    `paid_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`idbooking`),
    KEY `bookings_user_id_index` (`user_id`),
    KEY `bookings_jadwal_id_index` (`Jadwal_idJadwal`),
    KEY `bookings_rooms_idrooms_index` (`rooms_idrooms`),
    KEY `bookings_service_idservice_index` (`service_idservice`),
    CONSTRAINT `fk_bookings_rooms`
        FOREIGN KEY (`rooms_idrooms`) REFERENCES `rooms` (`idrooms`) ON DELETE CASCADE,
    CONSTRAINT `fk_bookings_services`
        FOREIGN KEY (`service_idservice`) REFERENCES `services` (`idservice`) ON DELETE CASCADE,
    CONSTRAINT `fk_bookings_jadwals`
        FOREIGN KEY (`Jadwal_idJadwal`) REFERENCES `jadwals` (`idJadwal`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payments` (
    `idpayments` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `method` ENUM('Midtrans', 'Cash') NOT NULL,
    `midtrans_order_id` VARCHAR(100) NULL,
    `midtrans_transaction_id` VARCHAR(100) NULL,
    `snap_token` VARCHAR(255) NULL,
    `snap_redirect_url` VARCHAR(255) NULL,
    `status` ENUM('pending', 'success', 'failed', 'expired', 'cancelled') NOT NULL DEFAULT 'pending',
    `raw_status` VARCHAR(50) NULL,
    `webhook_payload` TEXT NULL,
    `payment_time` DATETIME NULL,
    `paid_at` DATETIME NULL,
    `failed_at` DATETIME NULL,
    `expires_time` DATETIME NULL,
    `payment_type` ENUM('dp', 'full', 'remaining') NOT NULL DEFAULT 'full',
    `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `tenants_idTenant` BIGINT UNSIGNED NOT NULL,
    `booking_idbooking` BIGINT UNSIGNED NOT NULL,
    `handled_by_user_id` BIGINT UNSIGNED NULL,
    `handled_at` DATETIME NULL,
    `payment_note` TEXT NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`idpayments`),
    KEY `payments_booking_idbooking_index` (`booking_idbooking`),
    CONSTRAINT `fk_payments_bookings`
        FOREIGN KEY (`booking_idbooking`) REFERENCES `bookings` (`idbooking`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `operasionals` (
    `idoperasional` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `day` ENUM('mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun') NOT NULL,
    `open_time` TIME NULL,
    `close_time` TIME NULL,
    `is_closed` TINYINT(1) NOT NULL DEFAULT 0,
    `tenants_idTenant` BIGINT UNSIGNED NOT NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`idoperasional`),
    UNIQUE KEY `operasionals_tenant_day_unique` (`tenants_idTenant`, `day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tenant_profiles` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `nama` VARCHAR(45) NOT NULL,
    `slug` VARCHAR(100) NULL,
    `deskripsi` TEXT NULL,
    `nama_pemilik` VARCHAR(45) NULL,
    `email` VARCHAR(45) NULL,
    `no_telp` VARCHAR(45) NULL,
    `alamat` VARCHAR(45) NULL,
    `provinsi` VARCHAR(100) NULL,
    `kota` VARCHAR(100) NULL,
    `kecamatan` VARCHAR(100) NULL,
    `open_time` TIME NULL,
    `close_time` TIME NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `source_updated_at` DATETIME NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `tenant_profiles_tenant_id_unique` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tenant_payment_accounts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `provider` VARCHAR(30) NOT NULL DEFAULT 'midtrans',
    `merchant_id` VARCHAR(100) NULL,
    `midtrans_client_key_enc` TEXT NULL,
    `midtrans_server_key_enc` TEXT NULL,
    `is_production` TINYINT(1) NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 0,
    -- Preferensi pembayaran tenant tetap disimpan di DB tenant.
    `dp_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `dp_percent` TINYINT UNSIGNED NOT NULL DEFAULT 30,
    `cash_enabled` TINYINT(1) NOT NULL DEFAULT 0,
    `cash_instruction` TEXT NULL,
    `midtrans_last_test_success` TINYINT(1) NOT NULL DEFAULT 0,
    `midtrans_last_tested_at` DATETIME NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `tenant_payment_accounts_tenant_id_unique` (`tenant_id`),
    KEY `tenant_payment_accounts_tenant_active_index` (`tenant_id`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tenant_email_otps` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `email` VARCHAR(255) NOT NULL,
    `code_hash` VARCHAR(255) NOT NULL,
    `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `expires_at` DATETIME NOT NULL,
    `verified_at` DATETIME NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `tenant_email_otps_tenant_created_index` (`tenant_id`, `created_at`),
    KEY `tenant_email_otps_tenant_verified_index` (`tenant_id`, `verified_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
