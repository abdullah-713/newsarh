-- ╔══════════════════════════════════════════════════════════════════════════════════════════╗
-- ║                                                                                          ║
-- ║                            صرح الإتقان - SARH AL-ITQAN                                   ║
-- ║                        COMPLETE DATABASE SCHEMA v2.0.0                                   ║
-- ║                                                                                          ║
-- ╠══════════════════════════════════════════════════════════════════════════════════════════╣
-- ║  📋 ملف قاعدة البيانات الشامل - يحتوي على جميع الجداول والبيانات                        ║
-- ║  🗓️ التاريخ: 2026-01-31                                                                  ║
-- ║                                                                                          ║
-- ║  📦 المحتويات:                                                                           ║
-- ║    Section 01: Core System Tables (الجداول الأساسية)                                    ║
-- ║    Section 02: Live Operations (العمليات الحية)                                         ║
-- ║    Section 03: Integrity Module (وحدة النزاهة)                                          ║
-- ║    Section 04: Psychological Traps (الفخاخ النفسية)                                     ║
-- ║    Section 05: Gamification (التحفيز والمكافآت)                                         ║
-- ║    Section 06: Chat System (نظام الدردشة)                                               ║
-- ║    Section 07: Permissions System (نظام الصلاحيات)                                      ║
-- ║    Section 08: Leave Management (إدارة الإجازات)                                        ║
-- ║    Section 09: Stored Procedures (الإجراءات المخزنة)                                    ║
-- ║    Section 10: Views (العروض)                                                            ║
-- ║    Section 11: Default Data (البيانات الافتراضية)                                       ║
-- ║                                                                                          ║
-- ╚══════════════════════════════════════════════════════════════════════════════════════════╝

-- ============================================================================
-- DATABASE CONFIGURATION
-- ============================================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET collation_connection = 'utf8mb4_unicode_520_ci';
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- ============================================================================
-- SECTION 01: CORE SYSTEM TABLES (الجداول الأساسية)
-- ============================================================================

-- ────────────────────────────────────────────────────────────────────────────
-- 01.01 System Settings (إعدادات النظام)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `system_settings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` JSON NULL DEFAULT NULL,
    `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
    `setting_type` ENUM('string', 'number', 'boolean', 'json', 'text') NOT NULL DEFAULT 'string',
    `description` VARCHAR(255) NULL DEFAULT NULL,
    `is_public` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_setting_key` (`setting_key`),
    INDEX `idx_setting_group` (`setting_group`),
    INDEX `idx_setting_public` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 01.02 Roles (الأدوار)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `roles` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(50) NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `role_level` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `permissions` JSON NULL DEFAULT NULL,
    `color` VARCHAR(20) NULL DEFAULT '#6c757d',
    `icon` VARCHAR(50) NULL DEFAULT 'bi-person',
    `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_role_slug` (`slug`),
    INDEX `idx_role_level` (`role_level`),
    INDEX `idx_role_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 01.03 Branches (الفروع)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `branches` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `address` TEXT NULL DEFAULT NULL,
    `city` VARCHAR(100) NULL DEFAULT NULL,
    `phone` VARCHAR(20) NULL DEFAULT NULL,
    `email` VARCHAR(100) NULL DEFAULT NULL,
    `latitude` DECIMAL(10, 7) NULL DEFAULT NULL,
    `longitude` DECIMAL(10, 7) NULL DEFAULT NULL,
    `geofence_radius` INT UNSIGNED NOT NULL DEFAULT 100,
    `timezone` VARCHAR(50) NOT NULL DEFAULT 'Asia/Riyadh',
    `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `is_ghost_branch` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    `ghost_visible_to` JSON NULL DEFAULT NULL,
    `settings` JSON NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_branch_code` (`code`),
    INDEX `idx_branch_active` (`is_active`),
    INDEX `idx_branch_ghost` (`is_ghost_branch`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 01.03.1 Departments (الأقسام)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `departments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `branch_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `manager_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_department_code` (`code`),
    INDEX `idx_department_branch` (`branch_id`),
    INDEX `idx_department_active` (`is_active`),
    CONSTRAINT `fk_department_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 01.03.2 Teams (الفرق)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `teams` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `department_id` INT UNSIGNED NULL DEFAULT NULL,
    `lead_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_team_code` (`code`),
    INDEX `idx_team_department` (`department_id`),
    INDEX `idx_team_active` (`is_active`),
    CONSTRAINT `fk_team_department` FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 01.03.3 Job Titles (المسميات الوظيفية)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `job_titles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `level` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_job_title_code` (`code`),
    INDEX `idx_job_title_level` (`level`),
    INDEX `idx_job_title_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 01.04 Users (المستخدمين)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `emp_code` VARCHAR(50) NOT NULL,
    `username` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NULL DEFAULT NULL,
    `avatar` VARCHAR(255) NULL DEFAULT NULL,
    `role_id` BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `branch_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `managed_by` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'المدير المباشر',
    `department` VARCHAR(100) NULL DEFAULT NULL,
    `job_title` VARCHAR(100) NULL DEFAULT NULL,
    `department_id` INT UNSIGNED NULL DEFAULT NULL,
    `team_id` INT UNSIGNED NULL DEFAULT NULL,
    `job_title_id` INT UNSIGNED NULL DEFAULT NULL,
    `hire_date` DATE NULL DEFAULT NULL,
    `national_id` VARCHAR(20) NULL DEFAULT NULL,
    `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `is_super_admin` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'صلاحيات مطلقة',
    `is_online` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `last_login_at` TIMESTAMP NULL DEFAULT NULL,
    `last_activity_at` TIMESTAMP NULL DEFAULT NULL,
    `last_latitude` DECIMAL(10, 7) NULL DEFAULT NULL,
    `last_longitude` DECIMAL(10, 7) NULL DEFAULT NULL,
    `login_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `locked_until` TIMESTAMP NULL DEFAULT NULL,
    `remember_token` VARCHAR(100) NULL DEFAULT NULL,
    `current_points` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `total_points_earned` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `total_points_deducted` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `streak_count` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'عداد الأيام المثالية المتتالية',
    `preferences` JSON NULL DEFAULT NULL,
    `custom_schedule` JSON NULL DEFAULT NULL,
    `permissions` JSON NULL DEFAULT NULL COMMENT 'صلاحيات فردية للمستخدم',
    `visible_modules` JSON NULL DEFAULT NULL COMMENT 'الوحدات المرئية للمستخدم',
    `theme_mode` ENUM('light', 'dark', 'auto') DEFAULT 'auto',
    `dark_mode_start` TIME DEFAULT '18:00:00',
    `dark_mode_end` TIME DEFAULT '06:00:00',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_emp_code` (`emp_code`),
    UNIQUE KEY `uk_username` (`username`),
    UNIQUE KEY `uk_email` (`email`),
    INDEX `idx_user_role` (`role_id`),
    INDEX `idx_user_branch` (`branch_id`),
    INDEX `idx_user_managed_by` (`managed_by`),
    INDEX `idx_user_department` (`department_id`),
    INDEX `idx_user_team` (`team_id`),
    INDEX `idx_user_job_title` (`job_title_id`),
    INDEX `idx_user_active` (`is_active`),
    INDEX `idx_user_super_admin` (`is_super_admin`),
    INDEX `idx_user_online` (`is_online`),
    INDEX `idx_user_activity` (`last_activity_at`),
    CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_user_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_user_manager` FOREIGN KEY (`managed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_user_department` FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_user_team` FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_user_job_title` FOREIGN KEY (`job_title_id`) REFERENCES `job_titles`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 01.05 User Sessions (جلسات المستخدمين)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `user_sessions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `session_token` VARCHAR(255) NOT NULL,
    `device_type` VARCHAR(50) NULL DEFAULT NULL,
    `device_name` VARCHAR(100) NULL DEFAULT NULL,
    `browser` VARCHAR(100) NULL DEFAULT NULL,
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `user_agent` TEXT NULL DEFAULT NULL,
    `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `last_activity_at` TIMESTAMP NULL DEFAULT NULL,
    `expires_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_session_token` (`session_token`),
    INDEX `idx_session_user` (`user_id`),
    INDEX `idx_session_active` (`is_active`),
    INDEX `idx_session_expires` (`expires_at`),
    CONSTRAINT `fk_session_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 01.05.1 Multi-Factor Auth (التحقق متعدد العوامل)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `mfa_methods` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `type` ENUM('totp', 'sms', 'email', 'webauthn') NOT NULL,
    `secret` VARCHAR(255) NULL DEFAULT NULL,
    `phone` VARCHAR(20) NULL DEFAULT NULL,
    `email` VARCHAR(100) NULL DEFAULT NULL,
    `is_verified` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    `last_used_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_mfa_user` (`user_id`),
    CONSTRAINT `fk_mfa_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 01.05.2 Password Policies (سياسات كلمات المرور)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `password_policies` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `min_length` TINYINT UNSIGNED NOT NULL DEFAULT 8,
    `require_uppercase` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `require_lowercase` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `require_number` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `require_symbol` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `max_age_days` SMALLINT UNSIGNED NOT NULL DEFAULT 90,
    `history_count` TINYINT UNSIGNED NOT NULL DEFAULT 5,
    `lockout_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 5,
    `lockout_duration_minutes` SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_policy_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 01.05.3 User Password History (سجل كلمات المرور)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `user_password_history` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `changed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_pwdhist_user` (`user_id`),
    CONSTRAINT `fk_pwdhist_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 01.05.4 Password Resets (إعادة تعيين كلمة المرور)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `reset_token` VARCHAR(255) NOT NULL,
    `expires_at` TIMESTAMP NOT NULL,
    `used_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_reset_token` (`reset_token`),
    INDEX `idx_reset_user` (`user_id`),
    INDEX `idx_reset_expires` (`expires_at`),
    CONSTRAINT `fk_reset_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 01.06 Attendance (الحضور والانصراف)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `attendance` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `branch_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `recorded_branch_id` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'الفرع المسجل فيه الحضور (للتاريخ)',
    `date` DATE NOT NULL,
    `check_in_time` TIME NULL DEFAULT NULL,
    `check_out_time` TIME NULL DEFAULT NULL,
    `check_in_lat` DECIMAL(10, 7) NULL DEFAULT NULL,
    `check_in_lng` DECIMAL(10, 7) NULL DEFAULT NULL,
    `check_out_lat` DECIMAL(10, 7) NULL DEFAULT NULL,
    `check_out_lng` DECIMAL(10, 7) NULL DEFAULT NULL,
    `check_in_address` VARCHAR(255) NULL DEFAULT NULL,
    `check_out_address` VARCHAR(255) NULL DEFAULT NULL,
    `check_in_method` ENUM('manual', 'auto_gps') NULL DEFAULT 'manual' COMMENT 'طريقة تسجيل الحضور',
    `check_in_distance` DECIMAL(10, 2) NULL DEFAULT NULL,
    `check_out_distance` DECIMAL(10, 2) NULL DEFAULT NULL,
    `work_minutes` INT UNSIGNED NULL DEFAULT NULL,
    `late_minutes` INT UNSIGNED NOT NULL DEFAULT 0,
    `early_leave_minutes` INT UNSIGNED NOT NULL DEFAULT 0,
    `overtime_minutes` INT UNSIGNED NOT NULL DEFAULT 0,
    `penalty_points` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `bonus_points` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `status` ENUM('present', 'absent', 'late', 'half_day', 'leave', 'holiday') NOT NULL DEFAULT 'present',
    `notes` TEXT NULL DEFAULT NULL,
    `is_locked` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'قفل السجل بعد الترحيل',
    `mood_score` TINYINT NULL DEFAULT NULL,
    `device_fingerprint` VARCHAR(64) NULL DEFAULT NULL,
    `fraud_flags` JSON NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_attendance_user_date` (`user_id`, `date`),
    INDEX `idx_attendance_branch` (`branch_id`),
    INDEX `idx_attendance_recorded_branch` (`recorded_branch_id`),
    INDEX `idx_attendance_date` (`date`),
    INDEX `idx_attendance_status` (`status`),
    INDEX `idx_attendance_check_in_method` (`check_in_method`),
    CONSTRAINT `fk_attendance_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_attendance_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 01.07 Employee Schedules (جداول دوام الموظفين)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `employee_schedules` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `work_start_time` TIME NOT NULL DEFAULT '08:00:00',
    `work_end_time` TIME NOT NULL DEFAULT '17:00:00',
    `grace_period_minutes` INT UNSIGNED NOT NULL DEFAULT 15,
    `attendance_mode` ENUM('unrestricted', 'time_only', 'location_only', 'time_and_location') NOT NULL DEFAULT 'time_and_location',
    `working_days` JSON NULL DEFAULT NULL COMMENT 'أيام العمل [0=الأحد, 6=السبت]',
    `allowed_branches` JSON NULL DEFAULT NULL COMMENT 'الفروع المسموح التسجيل منها',
    `geofence_radius` INT UNSIGNED NOT NULL DEFAULT 100 COMMENT 'نصف قطر السماح بالمتر',
    `is_flexible_hours` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    `min_working_hours` DECIMAL(4,2) NOT NULL DEFAULT 8.00,
    `max_working_hours` DECIMAL(4,2) NOT NULL DEFAULT 12.00,
    `early_checkin_minutes` INT UNSIGNED NOT NULL DEFAULT 30,
    `late_checkout_allowed` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `overtime_allowed` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `remote_checkin_allowed` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    `late_penalty_per_minute` DECIMAL(5,2) NOT NULL DEFAULT 0.50,
    `early_bonus_points` DECIMAL(5,2) NOT NULL DEFAULT 5.00,
    `overtime_bonus_per_hour` DECIMAL(5,2) NOT NULL DEFAULT 10.00,
    `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `effective_from` DATE NULL DEFAULT NULL,
    `effective_until` DATE NULL DEFAULT NULL,
    `notes` TEXT NULL DEFAULT NULL,
    `created_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_schedule_user` (`user_id`),
    INDEX `idx_schedule_mode` (`attendance_mode`),
    INDEX `idx_schedule_active` (`is_active`),
    CONSTRAINT `fk_schedule_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 01.08 Work Shifts (المناوبات)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `work_shifts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `is_overnight` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    `grace_period_minutes` INT UNSIGNED NOT NULL DEFAULT 15,
    `min_working_hours` DECIMAL(4,2) NOT NULL DEFAULT 8.00,
    `max_working_hours` DECIMAL(4,2) NOT NULL DEFAULT 12.00,
    `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_shift_code` (`code`),
    INDEX `idx_shift_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 01.09 Shift Templates (قوالب المناوبات الأسبوعية)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `shift_templates` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `created_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_template_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 01.10 Shift Template Days (أيام القوالب)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `shift_template_days` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `template_id` INT UNSIGNED NOT NULL,
    `day_of_week` TINYINT UNSIGNED NOT NULL COMMENT '0=الأحد, 6=السبت',
    `shift_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_template_day` (`template_id`, `day_of_week`),
    INDEX `idx_template_day_shift` (`shift_id`),
    CONSTRAINT `fk_template_day_template` FOREIGN KEY (`template_id`) REFERENCES `shift_templates`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_template_day_shift` FOREIGN KEY (`shift_id`) REFERENCES `work_shifts`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 01.11 User Shift Assignments (إسناد المناوبات للموظفين)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `user_shift_assignments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `shift_id` INT UNSIGNED NOT NULL,
    `effective_from` DATE NOT NULL,
    `effective_until` DATE NULL DEFAULT NULL,
    `assigned_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `notes` TEXT NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_user_shift_user` (`user_id`),
    INDEX `idx_user_shift_effective` (`effective_from`, `effective_until`),
    CONSTRAINT `fk_user_shift_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_user_shift_shift` FOREIGN KEY (`shift_id`) REFERENCES `work_shifts`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 01.12 Shift Roster (جدولة المناوبات اليومية)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `shift_roster` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `shift_id` INT UNSIGNED NOT NULL,
    `shift_date` DATE NOT NULL,
    `template_id` INT UNSIGNED NULL DEFAULT NULL,
    `published_at` TIMESTAMP NULL DEFAULT NULL,
    `published_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `notes` TEXT NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_roster_user_date` (`user_id`, `shift_date`),
    INDEX `idx_roster_shift_date` (`shift_date`),
    INDEX `idx_roster_shift` (`shift_id`),
    CONSTRAINT `fk_roster_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_roster_shift` FOREIGN KEY (`shift_id`) REFERENCES `work_shifts`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_roster_template` FOREIGN KEY (`template_id`) REFERENCES `shift_templates`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 01.13 Official Holidays (العطل الرسمية)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `official_holidays` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(150) NOT NULL,
    `holiday_date` DATE NOT NULL,
    `is_paid` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `branch_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_holiday_date_branch` (`holiday_date`, `branch_id`),
    INDEX `idx_holiday_date` (`holiday_date`),
    CONSTRAINT `fk_holiday_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 01.14 Holiday Calendar (تقويم العطل الموسمية)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `holiday_calendar` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(150) NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `is_paid` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `branch_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_holiday_calendar_range` (`start_date`, `end_date`),
    CONSTRAINT `fk_holiday_calendar_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 01.15 Activity Log (سجل النشاطات)
-- 📝 ملاحظة: يُنصح بتقسيم هذا الجدول بالتاريخ (PARTITION BY RANGE) للأداء
-- مثال: PARTITION BY RANGE (TO_DAYS(created_at))
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `activity_log` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `model_type` VARCHAR(100) NULL DEFAULT NULL,
    `model_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `old_values` JSON NULL DEFAULT NULL,
    `new_values` JSON NULL DEFAULT NULL,
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `user_agent` TEXT NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`, `created_at`),
    INDEX `idx_activity_user` (`user_id`),
    INDEX `idx_activity_action` (`action`),
    INDEX `idx_activity_model` (`model_type`, `model_id`),
    INDEX `idx_activity_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci
COMMENT='جدول سجل النشاطات - يُنصح بتفعيل التقسيم الزمني عند النمو';


-- ============================================================================
-- SECTION 02: LIVE OPERATIONS TABLES (العمليات الحية)
-- ============================================================================

-- ────────────────────────────────────────────────────────────────────────────
-- 02.01 Notifications (الإشعارات)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type` VARCHAR(50) NOT NULL DEFAULT 'info',
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NULL DEFAULT NULL,
    `icon` VARCHAR(50) NULL DEFAULT 'bi-bell',
    `scope_type` ENUM('global', 'branch', 'user') NOT NULL DEFAULT 'user',
    `scope_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `action_url` VARCHAR(255) NULL DEFAULT NULL,
    `is_persistent` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    `expires_at` TIMESTAMP NULL DEFAULT NULL,
    `created_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_notification_type` (`type`),
    INDEX `idx_notification_scope` (`scope_type`, `scope_id`),
    INDEX `idx_notification_expires` (`expires_at`),
    INDEX `idx_notification_created` (`created_at` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 02.02 User Notification Reads (قراءة الإشعارات)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `user_notification_reads` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `notification_id` BIGINT UNSIGNED NOT NULL,
    `read_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_notification` (`user_id`, `notification_id`),
    CONSTRAINT `fk_unr_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_unr_notification` FOREIGN KEY (`notification_id`) REFERENCES `notifications`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 02.03 Push Subscriptions (اشتراكات الإشعارات الفورية)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `push_subscriptions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `endpoint` TEXT NOT NULL,
    `endpoint_hash` CHAR(64) NOT NULL,
    `p256dh` VARCHAR(255) NOT NULL,
    `auth` VARCHAR(255) NOT NULL,
    `subscription_json` JSON NOT NULL,
    `user_agent` TEXT NULL DEFAULT NULL,
    `device_type` VARCHAR(50) NULL DEFAULT NULL,
    `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `last_seen_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_push_endpoint_hash` (`endpoint_hash`),
    INDEX `idx_push_user` (`user_id`),
    INDEX `idx_push_active` (`is_active`),
    CONSTRAINT `fk_push_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 02.04 User Location History (سجل المواقع)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `user_location_history` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `latitude` DECIMAL(10, 7) NOT NULL,
    `longitude` DECIMAL(10, 7) NOT NULL,
    `accuracy` DECIMAL(10, 2) NULL DEFAULT NULL,
    `source` ENUM('gps', 'network', 'manual') NOT NULL DEFAULT 'gps',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_location_user` (`user_id`),
    INDEX `idx_location_created` (`created_at`),
    CONSTRAINT `fk_location_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 02.05 Scheduled Notifications (الإشعارات المجدولة)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `scheduled_notifications` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `notification_type` ENUM('checkin_reminder', 'checkout_reminder', 'challenge_reminder', 'custom') NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `body` TEXT NULL DEFAULT NULL,
    `scheduled_time` TIME NOT NULL,
    `days_of_week` JSON DEFAULT '[0,1,2,3,4]',
    `is_active` TINYINT(1) DEFAULT 1,
    `last_sent_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_scheduled_user` (`user_id`),
    INDEX `idx_scheduled_active` (`is_active`),
    CONSTRAINT `fk_scheduled_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- SECTION 03: INTEGRITY MODULE TABLES (وحدة النزاهة)
-- ============================================================================

-- ────────────────────────────────────────────────────────────────────────────
-- 03.01 Integrity Logs (سجل النزاهة)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `integrity_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `action_type` VARCHAR(100) NOT NULL,
    `target_type` VARCHAR(50) NULL DEFAULT NULL,
    `target_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `details` JSON NULL DEFAULT NULL,
    `severity` ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'low',
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `user_agent` TEXT NULL DEFAULT NULL,
    `location_lat` DECIMAL(10, 7) NULL DEFAULT NULL,
    `location_lng` DECIMAL(10, 7) NULL DEFAULT NULL,
    `is_reviewed` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    `reviewed_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `reviewed_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_integrity_user` (`user_id`),
    INDEX `idx_integrity_type` (`action_type`),
    INDEX `idx_integrity_severity` (`severity`),
    INDEX `idx_integrity_created` (`created_at` DESC),
    INDEX `idx_integrity_reviewed` (`is_reviewed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 03.02 Integrity Reports (التقارير السرية - المنجم)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `integrity_reports` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `sender_id` BIGINT UNSIGNED NOT NULL,
    `reported_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `report_type` ENUM('violation', 'harassment', 'theft', 'fraud', 'other') NOT NULL DEFAULT 'violation',
    `content` TEXT NOT NULL,
    `evidence_files` JSON NULL DEFAULT NULL,
    `is_anonymous_claim` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `admin_notes` TEXT NULL DEFAULT NULL,
    `status` ENUM('pending', 'investigating', 'resolved', 'dismissed', 'fake') NOT NULL DEFAULT 'pending',
    `resolved_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `resolved_at` TIMESTAMP NULL DEFAULT NULL,
    `sender_revealed_to` JSON NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_reports_sender` (`sender_id`),
    INDEX `idx_reports_reported` (`reported_id`),
    INDEX `idx_reports_status` (`status`),
    INDEX `idx_reports_created` (`created_at` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 03.03 Fraud Detection Logs (سجل اكتشاف التلاعب)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `fraud_detection_logs` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `detection_type` ENUM('mock_gps', 'vpn', 'emulator', 'root', 'time_manipulation', 'location_jump', 'suspicious_pattern') NOT NULL,
    `severity` ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    `details` JSON NULL DEFAULT NULL,
    `latitude` DECIMAL(10, 8) NULL DEFAULT NULL,
    `longitude` DECIMAL(11, 8) NULL DEFAULT NULL,
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `user_agent` TEXT NULL DEFAULT NULL,
    `device_info` JSON NULL DEFAULT NULL,
    `action_taken` ENUM('none', 'warning', 'blocked', 'reported') DEFAULT 'none',
    `reviewed_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `reviewed_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_fraud_user` (`user_id`),
    INDEX `idx_fraud_type` (`detection_type`),
    INDEX `idx_fraud_severity` (`severity`),
    INDEX `idx_fraud_created` (`created_at`),
    CONSTRAINT `fk_fraud_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_fraud_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- SECTION 04: PSYCHOLOGICAL TRAPS TABLES (الفخاخ النفسية)
-- ============================================================================

-- ────────────────────────────────────────────────────────────────────────────
-- 04.01 Psychological Profiles (الملفات النفسية)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `psychological_profiles` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `trust_score` INT NOT NULL DEFAULT 100,
    `curiosity_score` INT NOT NULL DEFAULT 0,
    `integrity_score` INT NOT NULL DEFAULT 100,
    `profile_type` ENUM('loyal_sentinel', 'curious_observer', 'opportunist', 'active_exploiter', 'potential_insider', 'undetermined') NOT NULL DEFAULT 'undetermined',
    `risk_level` ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'low',
    `total_traps_seen` INT UNSIGNED NOT NULL DEFAULT 0,
    `total_violations` INT UNSIGNED NOT NULL DEFAULT 0,
    `last_trap_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_profile_user` (`user_id`),
    INDEX `idx_profile_type` (`profile_type`),
    INDEX `idx_profile_risk` (`risk_level`),
    INDEX `idx_profile_trust` (`trust_score`),
    CONSTRAINT `fk_profile_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 04.02 Trap Configurations (إعدادات الفخاخ)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `trap_configurations` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `trap_type` VARCHAR(50) NOT NULL,
    `trap_name` VARCHAR(100) NOT NULL,
    `trap_name_ar` VARCHAR(100) NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `trigger_chance` DECIMAL(4, 2) NOT NULL DEFAULT 0.10,
    `cooldown_minutes` INT UNSIGNED NOT NULL DEFAULT 10080,
    `min_role_level` INT UNSIGNED NOT NULL DEFAULT 1,
    `max_role_level` INT UNSIGNED NOT NULL DEFAULT 7,
    `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `settings` JSON NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_trap_type` (`trap_type`),
    INDEX `idx_trap_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 04.03 Trap Logs (سجل الفخاخ)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `trap_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `trap_type` VARCHAR(50) NOT NULL,
    `trap_config_id` INT UNSIGNED NULL DEFAULT NULL,
    `action_taken` VARCHAR(50) NOT NULL,
    `action_category` ENUM('positive', 'neutral', 'negative', 'critical') NOT NULL DEFAULT 'neutral',
    `score_change` INT NOT NULL DEFAULT 0,
    `trust_delta` INT NOT NULL DEFAULT 0,
    `curiosity_delta` INT NOT NULL DEFAULT 0,
    `integrity_delta` INT NOT NULL DEFAULT 0,
    `response_time_ms` INT UNSIGNED NULL DEFAULT NULL,
    `context_data` JSON NULL DEFAULT NULL,
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `user_agent` TEXT NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_traplog_user` (`user_id`),
    INDEX `idx_traplog_type` (`trap_type`),
    INDEX `idx_traplog_category` (`action_category`),
    INDEX `idx_traplog_created` (`created_at` DESC),
    CONSTRAINT `fk_traplog_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 04.04 User Trap Cooldowns (فترات انتظار الفخاخ)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `user_trap_cooldowns` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `trap_type` VARCHAR(50) NOT NULL,
    `last_shown_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `cooldown_until` TIMESTAMP NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_trap` (`user_id`, `trap_type`),
    CONSTRAINT `fk_cooldown_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


-- ============================================================================
-- SECTION 05: GAMIFICATION TABLES (التحفيز والمكافآت)
-- ============================================================================

-- ────────────────────────────────────────────────────────────────────────────
-- 05.01 Badges (الشارات)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `badges` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `icon` VARCHAR(50) DEFAULT '🏅',
    `color` VARCHAR(20) DEFAULT '#ffc107',
    `points_reward` INT DEFAULT 0,
    `criteria_type` ENUM('attendance_streak', 'points_threshold', 'early_arrival', 'overtime', 'perfect_month', 'custom') NOT NULL,
    `criteria_value` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 05.02 User Badges (شارات المستخدمين)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `user_badges` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `badge_id` INT NOT NULL,
    `earned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_user_badge` (`user_id`, `badge_id`),
    CONSTRAINT `fk_ubadge_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ubadge_badge` FOREIGN KEY (`badge_id`) REFERENCES `badges`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 05.03 Challenges (التحديات)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `challenges` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `challenge_type` ENUM('individual', 'team', 'branch', 'company') DEFAULT 'individual',
    `target_type` ENUM('attendance_count', 'no_late', 'early_arrival', 'overtime', 'points', 'custom') NOT NULL,
    `target_value` INT DEFAULT 1,
    `reward_points` INT DEFAULT 100,
    `reward_badge_id` INT NULL DEFAULT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `max_participants` INT NULL DEFAULT NULL,
    `created_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_challenges_dates` (`start_date`, `end_date`),
    INDEX `idx_challenges_active` (`is_active`),
    CONSTRAINT `fk_challenge_badge` FOREIGN KEY (`reward_badge_id`) REFERENCES `badges`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 05.04 User Challenges (تحديات المستخدمين)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `user_challenges` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `challenge_id` INT NOT NULL,
    `progress` INT DEFAULT 0,
    `completed` TINYINT(1) DEFAULT 0,
    `completed_at` TIMESTAMP NULL DEFAULT NULL,
    `joined_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_user_challenge` (`user_id`, `challenge_id`),
    INDEX `idx_user_challenges_progress` (`user_id`, `completed`),
    CONSTRAINT `fk_uchallenge_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_uchallenge_challenge` FOREIGN KEY (`challenge_id`) REFERENCES `challenges`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 05.05 Rewards Store (متجر المكافآت)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `rewards` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `icon` VARCHAR(50) DEFAULT '🎁',
    `category` ENUM('leave', 'voucher', 'gift', 'privilege', 'recognition') DEFAULT 'gift',
    `points_required` INT NOT NULL,
    `stock` INT DEFAULT 99,
    `image_url` VARCHAR(255) NULL DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 05.06 Reward Redemptions (طلبات استبدال النقاط)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `reward_redemptions` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `reward_id` INT NOT NULL,
    `points_spent` INT NOT NULL,
    `status` ENUM('pending', 'approved', 'delivered', 'rejected') DEFAULT 'pending',
    `notes` TEXT NULL DEFAULT NULL,
    `approved_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `approved_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_redemption_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_redemption_reward` FOREIGN KEY (`reward_id`) REFERENCES `rewards`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_redemption_approver` FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 05.07 Mood Surveys (استبيان المزاج اليومي)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `mood_surveys` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `mood_score` TINYINT NOT NULL,
    `mood_emoji` VARCHAR(10) DEFAULT '😐',
    `energy_level` TINYINT NULL DEFAULT NULL,
    `stress_level` TINYINT NULL DEFAULT NULL,
    `notes` TEXT NULL DEFAULT NULL,
    `survey_date` DATE NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_user_date` (`user_id`, `survey_date`),
    INDEX `idx_mood_surveys_date` (`survey_date`),
    CONSTRAINT `fk_mood_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 05.08 Announcements (الإعلانات)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `announcements` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `type` ENUM('info', 'warning', 'success', 'danger') DEFAULT 'info',
    `priority` ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    `target_type` ENUM('all', 'branch', 'department', 'role', 'user') DEFAULT 'all',
    `target_ids` JSON NULL DEFAULT NULL,
    `is_pinned` TINYINT(1) DEFAULT 0,
    `expires_at` TIMESTAMP NULL DEFAULT NULL,
    `created_by` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_announcements_active` (`expires_at`, `is_pinned`),
    CONSTRAINT `fk_announcement_creator` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 05.09 Announcement Reads (قراءة الإعلانات)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `announcement_reads` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `announcement_id` INT NOT NULL,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `read_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_announcement_user` (`announcement_id`, `user_id`),
    CONSTRAINT `fk_aread_announcement` FOREIGN KEY (`announcement_id`) REFERENCES `announcements`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_aread_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- SECTION 07: PERMISSIONS SYSTEM TABLES (نظام الصلاحيات)
-- ============================================================================

-- ────────────────────────────────────────────────────────────────────────────
-- 07.01 Available Permissions (الصلاحيات المتاحة)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `available_permissions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `permission_key` VARCHAR(100) NOT NULL,
    `permission_name_ar` VARCHAR(150) NOT NULL,
    `permission_name_en` VARCHAR(150) NOT NULL,
    `category` VARCHAR(50) NOT NULL DEFAULT 'general',
    `description` TEXT NULL DEFAULT NULL,
    `is_dangerous` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'صلاحية خطرة تتطلب تأكيد',
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_permission_key` (`permission_key`),
    INDEX `idx_permission_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- ────────────────────────────────────────────────────────────────────────────
-- 07.02 Available Modules (الوحدات المتاحة)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `available_modules` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `module_key` VARCHAR(100) NOT NULL,
    `module_name_ar` VARCHAR(150) NOT NULL,
    `module_name_en` VARCHAR(150) NOT NULL,
    `icon` VARCHAR(50) NULL DEFAULT 'bi-app',
    `url` VARCHAR(255) NULL DEFAULT NULL,
    `parent_module` VARCHAR(100) NULL DEFAULT NULL,
    `required_permission` VARCHAR(100) NULL DEFAULT NULL,
    `is_menu_item` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_module_key` (`module_key`),
    INDEX `idx_module_parent` (`parent_module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


-- ============================================================================
-- SECTION 09: STORED PROCEDURES (الإجراءات المخزنة)
-- ============================================================================

DROP PROCEDURE IF EXISTS `sp_update_psychological_profile`;

DELIMITER //

CREATE PROCEDURE `sp_update_psychological_profile`(IN p_user_id BIGINT UNSIGNED)
BEGIN
    DECLARE v_trust INT DEFAULT 100;
    DECLARE v_curiosity INT DEFAULT 0;
    DECLARE v_integrity INT DEFAULT 100;
    DECLARE v_profile_type VARCHAR(30) DEFAULT 'undetermined';
    DECLARE v_risk_level VARCHAR(20) DEFAULT 'low';
    DECLARE v_total_traps INT DEFAULT 0;
    DECLARE v_total_violations INT DEFAULT 0;
    
    -- حساب الدرجات من سجل الفخاخ
    SELECT 
        GREATEST(0, LEAST(100, 100 + COALESCE(SUM(trust_delta), 0))),
        GREATEST(0, COALESCE(SUM(CASE WHEN curiosity_delta > 0 THEN curiosity_delta ELSE 0 END), 0)),
        GREATEST(0, LEAST(100, 100 + COALESCE(SUM(integrity_delta), 0))),
        COUNT(*),
        SUM(CASE WHEN action_category IN ('negative', 'critical') THEN 1 ELSE 0 END)
    INTO v_trust, v_curiosity, v_integrity, v_total_traps, v_total_violations
    FROM trap_logs WHERE user_id = p_user_id;
    
    -- تحديد نوع الملف الشخصي ومستوى الخطر
    IF v_trust >= 90 AND v_integrity >= 90 THEN
        SET v_profile_type = 'loyal_sentinel';
        SET v_risk_level = 'low';
    ELSEIF v_curiosity >= 30 AND v_trust >= 70 THEN
        SET v_profile_type = 'curious_observer';
        SET v_risk_level = 'low';
    ELSEIF v_trust < 50 AND v_integrity < 50 THEN
        SET v_profile_type = 'active_exploiter';
        SET v_risk_level = 'critical';
    ELSEIF v_trust < 70 AND v_curiosity >= 20 THEN
        SET v_profile_type = 'opportunist';
        SET v_risk_level = 'medium';
    ELSEIF v_trust < 40 THEN
        SET v_profile_type = 'potential_insider';
        SET v_risk_level = 'high';
    END IF;
    
    -- إدراج أو تحديث الملف النفسي
    INSERT INTO psychological_profiles (user_id, trust_score, curiosity_score, integrity_score, profile_type, risk_level, total_traps_seen, total_violations, last_trap_at)
    VALUES (p_user_id, v_trust, v_curiosity, v_integrity, v_profile_type, v_risk_level, v_total_traps, v_total_violations, NOW())
    ON DUPLICATE KEY UPDATE
        trust_score = v_trust, 
        curiosity_score = v_curiosity, 
        integrity_score = v_integrity,
        profile_type = v_profile_type, 
        risk_level = v_risk_level,
        total_traps_seen = v_total_traps, 
        total_violations = v_total_violations,
        last_trap_at = NOW(), 
        updated_at = NOW();
END //

DELIMITER ;


-- ============================================================================
-- SECTION 10: VIEWS (العروض)
-- ============================================================================

-- ────────────────────────────────────────────────────────────────────────────
-- 10.01 Psychological Profiles View (عرض الملفات النفسية)
-- ────────────────────────────────────────────────────────────────────────────
CREATE OR REPLACE VIEW `v_psychological_profiles` AS
SELECT 
    pp.*, 
    u.full_name, 
    u.emp_code, 
    u.email, 
    r.name AS role_name, 
    b.name AS branch_name
FROM psychological_profiles pp
JOIN users u ON pp.user_id = u.id
LEFT JOIN roles r ON u.role_id = r.id
LEFT JOIN branches b ON u.branch_id = b.id;

-- ────────────────────────────────────────────────────────────────────────────
-- 10.02 Trap Statistics View (عرض إحصائيات الفخاخ)
-- ────────────────────────────────────────────────────────────────────────────
CREATE OR REPLACE VIEW `v_trap_statistics` AS
SELECT 
    trap_type, 
    COUNT(*) AS total_shown,
    SUM(CASE WHEN action_category = 'positive' THEN 1 ELSE 0 END) AS positive_responses,
    SUM(CASE WHEN action_category = 'negative' THEN 1 ELSE 0 END) AS negative_responses,
    SUM(CASE WHEN action_category = 'critical' THEN 1 ELSE 0 END) AS critical_responses,
    AVG(response_time_ms) AS avg_response_time_ms
FROM trap_logs 
GROUP BY trap_type;

-- ────────────────────────────────────────────────────────────────────────────
-- 10.03 Employee Attendance Summary View (عرض ملخص الحضور)
-- ────────────────────────────────────────────────────────────────────────────
CREATE OR REPLACE VIEW `v_employee_attendance_summary` AS
SELECT 
    u.id AS user_id,
    u.emp_code,
    u.full_name,
    u.department,
    b.name AS branch_name,
    COUNT(a.id) AS total_days,
    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) AS present_days,
    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) AS absent_days,
    SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) AS late_days,
    SUM(COALESCE(a.late_minutes, 0)) AS total_late_minutes,
    SUM(COALESCE(a.overtime_minutes, 0)) AS total_overtime_minutes,
    SUM(COALESCE(a.work_minutes, 0)) AS total_work_minutes,
    u.current_points
FROM users u
LEFT JOIN attendance a ON u.id = a.user_id
LEFT JOIN branches b ON u.branch_id = b.id
WHERE u.is_active = 1
GROUP BY u.id, u.emp_code, u.full_name, u.department, b.name, u.current_points;


-- ============================================================================
-- SECTION 11: DEFAULT DATA (البيانات الافتراضية)
-- ============================================================================

-- ────────────────────────────────────────────────────────────────────────────
-- 11.01 Default Roles (الأدوار الافتراضية)
-- ────────────────────────────────────────────────────────────────────────────
INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `role_level`, `permissions`, `color`, `icon`) VALUES
(1, 'موظف', 'employee', 'موظف عادي', 1, '["attendance.view", "attendance.checkin"]', '#6c757d', 'bi-person'),
(2, 'مشرف', 'supervisor', 'مشرف على الفريق', 3, '["attendance.*", "reports.view"]', '#17a2b8', 'bi-person-badge'),
(3, 'مدير فرع', 'branch_manager', 'مدير الفرع', 5, '["attendance.*", "reports.*", "employees.view"]', '#28a745', 'bi-building'),
(4, 'مدير عام', 'general_manager', 'المدير العام', 8, '["*"]', '#fd7e14', 'bi-briefcase'),
(5, 'مدير النظام', 'super_admin', 'مدير النظام الكامل', 10, '["*"]', '#dc3545', 'bi-shield-lock'),
(6, 'المطور', 'developer', 'مطور النظام - صلاحيات كاملة', 99, '["*", "developer.*", "system.*"]', '#9c27b0', 'bi-code-slash')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ────────────────────────────────────────────────────────────────────────────
-- 11.02 Default Branches (الفروع الافتراضية)
-- ────────────────────────────────────────────────────────────────────────────
INSERT INTO `branches` (`id`, `name`, `code`, `address`, `city`, `phone`, `email`, `latitude`, `longitude`, `geofence_radius`, `timezone`, `is_active`, `settings`) VALUES
(1, 'صرح الاتقان الرئيسي', 'SARH01', 'المقر الرئيسي', 'الرياض', '+966500000000', 'sarh1@sarh.io', 24.572368, 46.602829, 17, 'Asia/Riyadh', 1, '{"attendance_mode":"flexible"}'),
(2, 'صرح الاتقان كورنر', 'SARH02', 'فرع كورنر', 'الرياض', '+966500000001', 'sarh2@sarh.io', 24.572439, 46.603008, 17, 'Asia/Riyadh', 1, '{"attendance_mode":"flexible"}'),
(3, 'صرح الاتقان 2', 'SARH03', 'الفرع الثاني', 'الرياض', '+966500000002', 'sarh3@sarh.io', 24.572262, 46.602580, 17, 'Asia/Riyadh', 1, '{"attendance_mode":"flexible"}'),
(4, 'فضاء المحركات 1', 'FADA01', 'فضاء المحركات الأول', 'الرياض', '+966500000003', 'fada1@sarh.io', 24.56968126, 46.61405911, 17, 'Asia/Riyadh', 1, '{"attendance_mode":"flexible"}'),
(5, 'فضاء المحركات 2', 'FADA02', 'فضاء المحركات الثاني', 'الرياض', '+966500000004', 'fada2@sarh.io', 24.566088, 46.621759, 17, 'Asia/Riyadh', 1, '{"attendance_mode":"flexible"}')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `latitude` = VALUES(`latitude`), `longitude` = VALUES(`longitude`), `geofence_radius` = VALUES(`geofence_radius`);

-- ────────────────────────────────────────────────────────────────────────────
-- 11.03 Default Admin Account (حساب مدير النظام)
-- Password: Admin@2026
-- ────────────────────────────────────────────────────────────────────────────
INSERT INTO `users` (`id`, `emp_code`, `username`, `email`, `password_hash`, `full_name`, `phone`, `role_id`, `branch_id`, `department`, `job_title`, `hire_date`, `is_active`, `is_super_admin`, `current_points`) VALUES
(1, 'ADMIN001', 'admin', 'admin@sarh.io', '$2y$10$e96Olh1wZfTHeSLTWy7U/eYp0leFXc1zk.Sxeu/bp3v0YpUXzK2ou', 'مدير النظام', '+966500000001', 5, 1, 'الإدارة', 'مدير النظام', '2026-01-01', 1, 1, 1000)
ON DUPLICATE KEY UPDATE `full_name` = VALUES(`full_name`);

-- ────────────────────────────────────────────────────────────────────────────
-- 11.04 Default Developer Account (حساب المطور)
-- Password: MySecretPass2026
-- ────────────────────────────────────────────────────────────────────────────
INSERT INTO `users` (`id`, `emp_code`, `username`, `email`, `password_hash`, `full_name`, `phone`, `role_id`, `branch_id`, `department`, `job_title`, `hire_date`, `is_active`, `is_super_admin`, `current_points`) VALUES
(2, 'DEV001', 'The_Architect', 'architect@sarh.io', '$2y$10$vYcI66G7HDYYvuQTr.h6/.R4bYtg5it/usz3TBuMeGLiyPFZtyiqm', 'المهندس المعماري', '+966500000002', 6, 1, 'التطوير', 'مهندس النظام', '2026-01-01', 1, 1, 9999)
ON DUPLICATE KEY UPDATE `full_name` = VALUES(`full_name`);

-- ────────────────────────────────────────────────────────────────────────────
-- 11.05 Sample Employees (موظفين تجريبيين)
-- Password: Employee@2026
-- ────────────────────────────────────────────────────────────────────────────
INSERT INTO `users` (`emp_code`, `username`, `email`, `password_hash`, `full_name`, `phone`, `role_id`, `branch_id`, `department`, `job_title`, `hire_date`, `is_active`, `current_points`) VALUES
('EMP001', 'ahmed', 'ahmed@sarh.io', '$2y$10$dlLatzxdanQS7grKwn29WOQIPjdpu5dQOV0vjSLENC5B3Q52970Ae', 'أحمد محمد', '+966501111111', 1, 1, 'المبيعات', 'مندوب مبيعات', '2026-01-01', 1, 500),
('EMP002', 'sara', 'sara@sarh.io', '$2y$10$dlLatzxdanQS7grKwn29WOQIPjdpu5dQOV0vjSLENC5B3Q52970Ae', 'سارة أحمد', '+966502222222', 1, 1, 'الموارد البشرية', 'أخصائية موارد بشرية', '2026-01-01', 1, 600),
('EMP003', 'khalid', 'khalid@sarh.io', '$2y$10$dlLatzxdanQS7grKwn29WOQIPjdpu5dQOV0vjSLENC5B3Q52970Ae', 'خالد العتيبي', '+966503333333', 2, 1, 'تقنية المعلومات', 'مشرف تقني', '2026-01-01', 1, 750),
('EMP004', 'fatima', 'fatima@sarh.io', '$2y$10$dlLatzxdanQS7grKwn29WOQIPjdpu5dQOV0vjSLENC5B3Q52970Ae', 'فاطمة السالم', '+966504444444', 1, 1, 'المحاسبة', 'محاسبة', '2026-01-01', 1, 550),
('EMP005', 'omar', 'omar@sarh.io', '$2y$10$dlLatzxdanQS7grKwn29WOQIPjdpu5dQOV0vjSLENC5B3Q52970Ae', 'عمر الشمري', '+966505555555', 3, 1, 'العمليات', 'مدير فرع', '2026-01-01', 1, 850)
ON DUPLICATE KEY UPDATE `full_name` = VALUES(`full_name`);

-- ────────────────────────────────────────────────────────────────────────────
-- 11.06 Employee Schedules (جداول الدوام)
-- ────────────────────────────────────────────────────────────────────────────
INSERT INTO `employee_schedules` (`user_id`, `work_start_time`, `work_end_time`, `grace_period_minutes`, `attendance_mode`, `working_days`, `geofence_radius`, `is_flexible_hours`, `remote_checkin_allowed`, `is_active`) VALUES
(1, '08:00:00', '21:00:00', 999, 'unrestricted', '[0,1,2,3,4,5,6]', 500, 1, 1, 1),
(2, '00:00:00', '23:59:59', 999, 'unrestricted', '[0,1,2,3,4,5,6]', 99999, 1, 1, 1),
(3, '08:00:00', '21:00:00', 999, 'unrestricted', '[0,1,2,3,4,5,6]', 150, 1, 1, 1),
(4, '08:00:00', '21:00:00', 999, 'unrestricted', '[0,1,2,3,4,5,6]', 150, 1, 1, 1),
(5, '08:00:00', '21:00:00', 999, 'unrestricted', '[0,1,2,3,4,5,6]', 200, 1, 1, 1),
(6, '08:00:00', '21:00:00', 999, 'unrestricted', '[0,1,2,3,4,5,6]', 150, 1, 1, 1),
(7, '08:00:00', '21:00:00', 999, 'unrestricted', '[0,1,2,3,4,5,6]', 200, 1, 1, 1)
ON DUPLICATE KEY UPDATE `attendance_mode` = VALUES(`attendance_mode`);

-- ────────────────────────────────────────────────────────────────────────────
-- 11.07 System Settings (إعدادات النظام)
-- ────────────────────────────────────────────────────────────────────────────
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_group`, `setting_type`, `description`, `is_public`) VALUES
('app_name', '"صرح الإتقان"', 'general', 'string', 'اسم التطبيق', 1),
('app_logo', '""', 'general', 'string', 'رابط الشعار', 1),
('timezone', '"Asia/Riyadh"', 'general', 'string', 'المنطقة الزمنية', 0),
('work_start_time', '"08:00"', 'attendance', 'string', 'وقت بدء العمل', 1),
('work_end_time', '"21:00"', 'attendance', 'string', 'وقت انتهاء العمل', 1),
('grace_period_minutes', '999', 'attendance', 'number', 'فترة السماح', 0),
('checkin_cutoff_hour', '18', 'attendance', 'number', 'ساعة إغلاق الحضور', 0),
('late_penalty_per_minute', '0.5', 'attendance', 'number', 'خصم التأخير لكل دقيقة', 0),
('overtime_bonus_per_minute', '0.25', 'attendance', 'number', 'مكافأة الإضافي لكل دقيقة', 0),
('default_attendance_mode', '"unrestricted"', 'attendance', 'string', 'نوع الحضور الافتراضي', 0),
('map_visibility_mode', '"branch"', 'live_ops', 'string', 'وضع رؤية الخريطة', 0),
('heartbeat_interval', '10000', 'live_ops', 'number', 'فاصل النبضات بالمللي ثانية', 0),
('live_mode_enabled', 'true', 'live_ops', 'boolean', 'تفعيل الوضع الحي', 0),
('ghost_branch_enabled', 'true', 'integrity', 'boolean', 'تفعيل الفروع الوهمية', 0),
('main_branch_lat', '24.5723738', 'location', 'string', 'خط عرض المقر الرئيسي', 0),
('main_branch_lng', '46.6028185', 'location', 'string', 'خط طول المقر الرئيسي', 0)
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- ────────────────────────────────────────────────────────────────────────────
-- 11.08 Default Trap Configurations (إعدادات الفخاخ)
-- ────────────────────────────────────────────────────────────────────────────
INSERT INTO `trap_configurations` (`trap_type`, `trap_name`, `trap_name_ar`, `trigger_chance`, `cooldown_minutes`, `max_role_level`, `settings`) VALUES
('data_leak', 'Salary Data Leak', 'تسريب بيانات الراتب', 0.10, 10080, 7, '{"severity_weight": 10}'),
('gps_debug', 'GPS Debug Mode', 'وضع تصحيح GPS', 0.08, 14400, 5, '{"requires_gps_error": true}'),
('admin_override', 'Ghost Admin Button', 'زر المدير الشبح', 0.05, 20160, 7, '{"appear_duration_ms": 8000}'),
('confidential_bait', 'Confidential Notification', 'طُعم الإشعار السري', 0.12, 7200, 7, '{"auto_dismiss_ms": 12000}'),
('recruitment', 'Recruitment Test', 'اختبار التجنيد', 0.03, 43200, 4, '{"reward_amount": 500}')
ON DUPLICATE KEY UPDATE `trap_name` = VALUES(`trap_name`);

-- ────────────────────────────────────────────────────────────────────────────
-- 11.09 Default Badges (الشارات الافتراضية)
-- ────────────────────────────────────────────────────────────────────────────
INSERT INTO `badges` (`name`, `description`, `icon`, `points_reward`, `criteria_type`, `criteria_value`) VALUES
('المبتدئ المثابر', 'أكملت أسبوعك الأول بدون غياب', '🌱', 50, 'attendance_streak', 7),
('النجم الصاعد', 'حضور مثالي لمدة شهر كامل', '⭐', 200, 'perfect_month', 1),
('البطل الخارق', 'حضور مثالي 3 أشهر متتالية', '🦸', 500, 'perfect_month', 3),
('طائر الفجر', 'وصول مبكر 20 مرة قبل الدوام بـ 15 دقيقة', '🌅', 150, 'early_arrival', 20),
('المحارب', 'تجاوز 1000 نقطة', '⚔️', 100, 'points_threshold', 1000),
('الأسطورة', 'تجاوز 5000 نقطة', '👑', 300, 'points_threshold', 5000),
('عامل الليل', '50 ساعة عمل إضافي', '🌙', 250, 'overtime', 50),
('الملتزم', 'لا تأخير لمدة شهر', '🎯', 150, 'custom', 0),
('روح الفريق', 'المشاركة في 10 تحديات جماعية', '🤝', 200, 'custom', 0),
('نجم الشهر', 'أفضل موظف في الشهر', '🏆', 500, 'custom', 0)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ────────────────────────────────────────────────────────────────────────────
-- 11.10 Default Rewards (المكافآت الافتراضية)
-- ────────────────────────────────────────────────────────────────────────────
INSERT INTO `rewards` (`name`, `description`, `icon`, `category`, `points_required`, `stock`) VALUES
('نصف يوم إجازة', 'الحصول على نصف يوم إجازة مدفوعة', '🏖️', 'leave', 500, 99),
('يوم إجازة كامل', 'الحصول على يوم إجازة مدفوعة كامل', '🌴', 'leave', 1000, 99),
('قسيمة مطعم 50 ريال', 'قسيمة شراء من مطاعم مختارة', '🍽️', 'voucher', 300, 50),
('قسيمة مطعم 100 ريال', 'قسيمة شراء من مطاعم مختارة', '🍕', 'voucher', 550, 30),
('بطاقة شحن 50 ريال', 'بطاقة شحن رصيد للجوال', '📱', 'gift', 400, 100),
('سماعات بلوتوث', 'سماعات لاسلكية عالية الجودة', '🎧', 'gift', 2000, 10),
('ساعة ذكية', 'ساعة ذكية متعددة الاستخدامات', '⌚', 'gift', 5000, 5),
('العمل من المنزل (يوم)', 'يوم عمل من المنزل', '🏠', 'privilege', 800, 99),
('مكان VIP للسيارة', 'موقف سيارة مميز لمدة شهر', '🚗', 'privilege', 1500, 3),
('شهادة تقدير', 'شهادة تقدير موقعة من المدير العام', '🏆', 'recognition', 200, 99)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ────────────────────────────────────────────────────────────────────────────
-- 11.11 Default Permissions (الصلاحيات الافتراضية)
-- ────────────────────────────────────────────────────────────────────────────
INSERT INTO `available_permissions` (`permission_key`, `permission_name_ar`, `permission_name_en`, `category`, `is_dangerous`, `sort_order`) VALUES
-- صلاحيات الحضور
('view_attendance', 'عرض سجل الحضور', 'View Attendance', 'attendance', 0, 10),
('checkin_checkout', 'تسجيل الحضور والانصراف', 'Check In/Out', 'attendance', 0, 11),
('edit_own_attendance', 'تعديل حضوره الشخصي', 'Edit Own Attendance', 'attendance', 0, 12),
('edit_all_attendance', 'تعديل حضور الجميع', 'Edit All Attendance', 'attendance', 1, 13),
('view_team_attendance', 'عرض حضور الفريق', 'View Team Attendance', 'attendance', 0, 14),
('approve_corrections', 'الموافقة على طلبات التصحيح', 'Approve Corrections', 'attendance', 0, 15),
-- صلاحيات الموظفين
('view_employees', 'عرض قائمة الموظفين', 'View Employees', 'employees', 0, 20),
('create_employee', 'إضافة موظف جديد', 'Create Employee', 'employees', 0, 21),
('edit_employee', 'تعديل بيانات الموظفين', 'Edit Employee', 'employees', 0, 22),
('delete_employee', 'حذف موظف', 'Delete Employee', 'employees', 1, 23),
('manage_employees', 'إدارة الموظفين كاملة', 'Manage Employees', 'employees', 0, 24),
('reset_password', 'إعادة تعيين كلمات المرور', 'Reset Passwords', 'employees', 1, 25),
-- صلاحيات الفروع
('view_branches', 'عرض الفروع', 'View Branches', 'branches', 0, 30),
('create_branch', 'إضافة فرع جديد', 'Create Branch', 'branches', 0, 31),
('edit_branch', 'تعديل الفروع', 'Edit Branch', 'branches', 0, 32),
('delete_branch', 'حذف فرع', 'Delete Branch', 'branches', 1, 33),
('manage_branches', 'إدارة الفروع كاملة', 'Manage Branches', 'branches', 0, 34),
-- صلاحيات التقارير
('view_reports', 'عرض التقارير', 'View Reports', 'reports', 0, 40),
('export_reports', 'تصدير التقارير', 'Export Reports', 'reports', 0, 41),
('view_analytics', 'عرض التحليلات', 'View Analytics', 'reports', 0, 42),
('view_secret_reports', 'عرض التقارير السرية', 'View Secret Reports', 'reports', 1, 43),
-- صلاحيات الإشعارات
('send_notifications', 'إرسال إشعارات', 'Send Notifications', 'notifications', 0, 60),
('broadcast_notifications', 'إرسال إشعارات للجميع', 'Broadcast Notifications', 'notifications', 0, 61),
-- صلاحيات النظام
('view_settings', 'عرض الإعدادات', 'View Settings', 'system', 0, 70),
('edit_settings', 'تعديل الإعدادات', 'Edit Settings', 'system', 1, 71),
('view_logs', 'عرض سجلات النظام', 'View Logs', 'system', 0, 72),
('manage_roles', 'إدارة الأدوار', 'Manage Roles', 'system', 1, 73),
('manage_permissions', 'إدارة الصلاحيات', 'Manage Permissions', 'system', 1, 74),
('access_developer', 'الوصول لأدوات المطور', 'Access Developer Tools', 'system', 1, 75),
-- صلاحيات النزاهة
('view_integrity', 'عرض سجلات النزاهة', 'View Integrity Logs', 'integrity', 0, 80),
('manage_integrity', 'إدارة النزاهة', 'Manage Integrity', 'integrity', 1, 81),
('view_traps', 'عرض الفخاخ', 'View Traps', 'integrity', 1, 82),
('manage_traps', 'إدارة الفخاخ', 'Manage Traps', 'integrity', 1, 83),
-- صلاحيات المناوبات
('view_shifts', 'عرض المناوبات', 'View Shifts', 'shifts', 0, 90),
('manage_shifts', 'إدارة المناوبات', 'Manage Shifts', 'shifts', 0, 91),
('assign_shifts', 'إسناد المناوبات', 'Assign Shifts', 'shifts', 0, 92),
-- صلاحيات خاصة
('bypass_geofence', 'تجاوز السياج الجغرافي', 'Bypass Geofence', 'special', 1, 100),
('work_remotely', 'العمل عن بعد', 'Work Remotely', 'special', 0, 101),
('flexible_hours', 'ساعات عمل مرنة', 'Flexible Hours', 'special', 0, 102),
('immunity', 'حصانة من العقوبات', 'Immunity', 'special', 1, 103)
ON DUPLICATE KEY UPDATE `permission_name_ar` = VALUES(`permission_name_ar`);

-- ────────────────────────────────────────────────────────────────────────────
-- 11.12 Default Modules (الوحدات الافتراضية)
-- ────────────────────────────────────────────────────────────────────────────
INSERT INTO `available_modules` (`module_key`, `module_name_ar`, `module_name_en`, `icon`, `url`, `required_permission`, `sort_order`) VALUES
('dashboard', 'الرئيسية', 'Dashboard', 'bi-house-fill', 'index.php', NULL, 1),
('attendance', 'الحضور', 'Attendance', 'bi-calendar-check', 'attendance.php', 'view_attendance', 2),
('quick_attendance', 'حضور سريع', 'Quick Attendance', 'bi-lightning-fill', 'quick-attendance.php', 'checkin_checkout', 3),
('team_attendance', 'حضور الفريق', 'Team Attendance', 'bi-people-fill', 'team-attendance.php', 'view_team_attendance', 4),
('employees', 'الموظفين', 'Employees', 'bi-person-badge', 'employees.php', 'view_employees', 5),
('branches', 'الفروع', 'Branches', 'bi-building', 'admin/management.php?tab=branches', 'view_branches', 6),
('reports', 'التقارير', 'Reports', 'bi-file-earmark-bar-graph', 'reports.php', 'view_reports', 7),
('analytics', 'التحليلات', 'Analytics', 'bi-graph-up-arrow', 'analytics.php', 'view_analytics', 8),
('shifts', 'المناوبات', 'Shifts', 'bi-calendar2-week', 'shifts.php', 'view_shifts', 9),
('notifications', 'الإشعارات', 'Notifications', 'bi-bell', 'notifications.php', NULL, 10),
('settings', 'الإعدادات', 'Settings', 'bi-gear', 'settings.php', 'view_settings', 11),
('profile', 'الملف الشخصي', 'Profile', 'bi-person-circle', 'profile.php', NULL, 13),
('activity_log', 'سجل النشاط', 'Activity Log', 'bi-list-check', 'activity-log.php', 'view_logs', 14),
('management', 'مركز الإدارة', 'Management', 'bi-sliders', 'admin/management.php', 'manage_employees', 15),
('integrity', 'النزاهة', 'Integrity', 'bi-shield-check', 'admin/management.php?tab=integrity', 'view_integrity', 16),
('live_map', 'الخريطة الحية', 'Live Map', 'bi-map', 'admin/live-map.php', 'view_team_attendance', 17),
('traps', 'الفخاخ', 'Traps', 'bi-bug', 'admin/traps.php', 'manage_traps', 18),
('arena', 'الحلبة', 'Arena', 'bi-trophy', 'dashboard/arena.php', NULL, 19)
ON DUPLICATE KEY UPDATE `module_name_ar` = VALUES(`module_name_ar`);

-- ────────────────────────────────────────────────────────────────────────────
-- 11.13 Welcome Notification (إشعار الترحيب)
-- ────────────────────────────────────────────────────────────────────────────
INSERT INTO `notifications` (`type`, `title`, `message`, `icon`, `scope_type`, `is_persistent`, `created_at`) VALUES
('success', 'مرحباً بك في نظام صرح الإتقان!', 'تم تثبيت النظام بنجاح. يمكنك البدء باستخدام جميع الميزات المتاحة.', 'bi-rocket-takeoff', 'global', 1, NOW());

-- ────────────────────────────────────────────────────────────────────────────
-- 11.14 Default Work Shifts (المناوبات الافتراضية)
-- ────────────────────────────────────────────────────────────────────────────
INSERT INTO `work_shifts` (`name`, `code`, `start_time`, `end_time`, `is_overnight`, `grace_period_minutes`, `min_working_hours`, `is_active`) VALUES
('مناوبة صباحية', 'MORNING', '08:00:00', '17:00:00', 0, 15, 8.00, 1),
('مناوبة مسائية', 'EVENING', '14:00:00', '23:00:00', 0, 15, 8.00, 1),
('مناوبة ليلية', 'NIGHT', '22:00:00', '07:00:00', 1, 15, 8.00, 1),
('دوام كامل مرن', 'FLEXIBLE', '08:00:00', '21:00:00', 0, 999, 8.00, 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ────────────────────────────────────────────────────────────────────────────
-- 11.15 Default Holidays (العطل الرسمية)
-- ────────────────────────────────────────────────────────────────────────────
INSERT INTO `official_holidays` (`name`, `holiday_date`, `is_paid`) VALUES
('اليوم الوطني السعودي', '2026-09-23', 1),
('عيد الفطر المبارك', '2026-04-01', 1),
('عيد الأضحى المبارك', '2026-06-08', 1),
('يوم التأسيس', '2026-02-22', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);


-- ============================================================================
-- FINALIZATION
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 1;

-- ════════════════════════════════════════════════════════════════════════════
-- ✅ INSTALLATION COMPLETE
-- ════════════════════════════════════════════════════════════════════════════

SELECT '╔══════════════════════════════════════════════════════════════════════════════╗' AS '';
SELECT '║  ✅ تم تثبيت قاعدة بيانات صرح الإتقان v2.0.0 بنجاح!                          ║' AS '';
SELECT '╠══════════════════════════════════════════════════════════════════════════════╣' AS '';
SELECT '║  📍 الفرع الرئيسي: الرياض (24.5723738, 46.6028185)                           ║' AS '';
SELECT '║  👤 مدير النظام: admin / Admin@2026                                          ║' AS '';
SELECT '║  🔧 المطور: The_Architect / MySecretPass2026                                  ║' AS '';
SELECT '╚══════════════════════════════════════════════════════════════════════════════╝' AS '';
