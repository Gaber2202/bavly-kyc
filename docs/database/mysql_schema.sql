-- =============================================================================
-- Bavly KYC — MySQL 8+ schema (utf8mb4)
-- InnoDB, foreign keys, soft deletes, audit-oriented columns
-- Run after creating database: CREATE DATABASE bavly_kyc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- users — authentication + RBAC flags (admin | employee)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `username` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NULL,
    `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` VARCHAR(32) NOT NULL COMMENT 'admin | employee',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `can_view_all_kyc` TINYINT(1) NOT NULL DEFAULT 0,
    `can_view_reports` TINYINT(1) NOT NULL DEFAULT 0,
    `must_change_password` TINYINT(1) NOT NULL DEFAULT 0,
    `last_login_at` TIMESTAMP NULL DEFAULT NULL,
    `remember_token` VARCHAR(100) NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_username_unique` (`username`),
    UNIQUE KEY `users_email_unique` (`email`),
    KEY `users_role_index` (`role`),
    KEY `users_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- kyc_records — one row per client intake (Arabic-capable text fields)
-- assigned_to derived from service_type in app; stored for reporting/audit snapshot
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `kyc_records` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    -- عمليات أساسية
    `employee_name` VARCHAR(255) NOT NULL COMMENT 'اسم الموظف المُدخل',
    `client_full_name` VARCHAR(255) NOT NULL,
    `age` TINYINT UNSIGNED NULL,
    `passport_job_title` VARCHAR(255) NULL,
    `other_job_title` VARCHAR(255) NULL,
    `service_type` VARCHAR(64) NOT NULL COMMENT 'بافلي | ترانس روفر | أخرى',
    `assigned_to` VARCHAR(255) NULL COMMENT 'منطق التعيين من نوع الخدمة',
    -- مالية
    `has_bank_statement` VARCHAR(8) NOT NULL COMMENT 'نعم | لا',
    `available_balance` DECIMAL(15,2) NULL,
    `expected_balance` DECIMAL(15,2) NULL,
    -- اجتماعية / جنسية
    `marital_status` VARCHAR(32) NOT NULL,
    `children_count` SMALLINT UNSIGNED NULL,
    `has_relatives_abroad` VARCHAR(8) NOT NULL,
    `nationality_type` VARCHAR(32) NOT NULL,
    `nationality` VARCHAR(255) NULL,
    `residency_status` VARCHAR(255) NULL,
    `governorate` VARCHAR(255) NULL,
    -- تواصل
    `consultation_method` VARCHAR(32) NOT NULL,
    `email` VARCHAR(255) NULL,
    `phone_number` VARCHAR(32) NOT NULL,
    `whatsapp_number` VARCHAR(32) NULL,
    -- تأشيرات / رفض
    `previous_rejected` VARCHAR(8) NOT NULL,
    `rejection_numbers` VARCHAR(255) NULL,
    `rejection_reason` TEXT NULL,
    `rejection_country` VARCHAR(255) NULL,
    `has_previous_visas` VARCHAR(8) NOT NULL,
    `previous_visa_countries` TEXT NULL,
    -- حالة الملف
    `recommendation` TEXT NULL,
    `status` VARCHAR(64) NOT NULL,
    -- مرجعية ومراجعة
    `created_by` BIGINT UNSIGNED NOT NULL,
    `updated_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `kyc_records_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `kyc_records_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
    KEY `kyc_records_service_type_index` (`service_type`),
    KEY `kyc_records_status_index` (`status`),
    KEY `kyc_records_phone_number_index` (`phone_number`),
    KEY `kyc_records_created_by_created_at_index` (`created_by`, `created_at`),
    KEY `kyc_records_client_full_name_created_at_index` (`client_full_name`, `created_at`),
    KEY `kyc_records_created_at_index` (`created_at`),
    KEY `kyc_records_status_created_at_index` (`status`, `created_at`),
    KEY `kyc_records_service_type_created_at_index` (`service_type`, `created_at`),
    KEY `kyc_records_created_at_user_index` (`created_at`, `created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- activity_logs — append-only style audit (polymorphic subject optional)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL,
    `action` VARCHAR(128) NOT NULL,
    `subject_type` VARCHAR(255) NULL,
    `subject_id` BIGINT UNSIGNED NULL,
    `properties` JSON NULL,
    `ip_address` VARCHAR(45) NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `activity_logs_user_id_foreign` (`user_id`),
    KEY `activity_logs_action_index` (`action`),
    KEY `activity_logs_created_at_index` (`created_at`),
    KEY `activity_logs_subject_index` (`subject_type`, `subject_id`),
    CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- password_reset_logs — admin-initiated resets (no plaintext secrets)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `password_reset_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `target_user_id` BIGINT UNSIGNED NOT NULL,
    `reset_by_user_id` BIGINT UNSIGNED NOT NULL,
    `temporary_password_issued` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `password_reset_logs_target_user_id_foreign` (`target_user_id`),
    KEY `password_reset_logs_reset_by_user_id_foreign` (`reset_by_user_id`),
    CONSTRAINT `password_reset_logs_target_user_id_foreign` FOREIGN KEY (`target_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `password_reset_logs_reset_by_user_id_foreign` FOREIGN KEY (`reset_by_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- failed_login_attempts — security / optional analytics (no FK to allow orphaned usernames)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `failed_login_attempts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(255) NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `attempted_at` TIMESTAMP NOT NULL,
    PRIMARY KEY (`id`),
    KEY `failed_login_attempts_username_index` (`username`),
    KEY `failed_login_attempts_ip_address_index` (`ip_address`),
    KEY `failed_login_attempts_attempted_at_index` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- report_snapshots (optional) — materialized aggregates for history, exports, cron
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `report_snapshots` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `snapshot_key` VARCHAR(64) NOT NULL COMMENT 'e.g. dashboard, weekly_summary',
    `period_start` DATE NOT NULL,
    `period_end` DATE NOT NULL,
    `filter_hash` CHAR(64) NOT NULL COMMENT 'SHA-256 of canonical filter JSON',
    `payload` JSON NOT NULL COMMENT 'precomputed KPIs/charts data',
    `generated_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `report_snapshots_unique` (`snapshot_key`, `period_start`, `period_end`, `filter_hash`),
    KEY `report_snapshots_period_index` (`period_start`, `period_end`),
    CONSTRAINT `report_snapshots_generated_by_foreign` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Laravel default tables (sessions, jobs, cache) — keep as framework migrations

SET FOREIGN_KEY_CHECKS = 1;
