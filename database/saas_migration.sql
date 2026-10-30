-- ============================================================================
-- SaaS Multi-Tenant & Multi-Template E-Commerce Platform Migration SQL
-- Database: ecommerce_main
-- Run via: C:\xampp\php\php.exe database/migrate_saas.php
-- ============================================================================

CREATE TABLE IF NOT EXISTS `saas_templates` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `template_key` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(120) NOT NULL,
    `badge` VARCHAR(60) DEFAULT '',
    `tagline` VARCHAR(255) DEFAULT '',
    `description` TEXT,
    `features` TEXT,
    `preview_image` VARCHAR(255) DEFAULT '',
    `demo_url` VARCHAR(255) DEFAULT '',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `saas_subscriptions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `subscription_number` VARCHAR(60) NOT NULL UNIQUE,
    `tenant_id` INT NOT NULL,
    `plan_id` INT NOT NULL,
    `template_key` VARCHAR(50) NOT NULL DEFAULT 'fashion',
    `billing_cycle` VARCHAR(30) NOT NULL DEFAULT 'monthly',
    `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
    `status` VARCHAR(30) NOT NULL DEFAULT 'pending',
    `start_date` DATETIME DEFAULT NULL,
    `end_date` DATETIME DEFAULT NULL,
    `trial_ends_at` DATETIME DEFAULT NULL,
    `cancelled_at` DATETIME DEFAULT NULL,
    `license_key` VARCHAR(100) DEFAULT NULL,
    `notes` TEXT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_sub_tenant` (`tenant_id`),
    INDEX `idx_sub_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `saas_payments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(60) NOT NULL UNIQUE,
    `subscription_id` INT UNSIGNED DEFAULT NULL,
    `tenant_id` INT DEFAULT NULL,
    `plan_id` INT NOT NULL,
    `customer_name` VARCHAR(150) NOT NULL,
    `customer_email` VARCHAR(150) NOT NULL,
    `customer_phone` VARCHAR(50) DEFAULT '',
    `business_name` VARCHAR(150) NOT NULL,
    `store_slug` VARCHAR(120) NOT NULL,
    `chosen_template` VARCHAR(50) NOT NULL DEFAULT 'fashion',
    `gateway` VARCHAR(50) NOT NULL DEFAULT 'stripe',
    `gateway_session_id` VARCHAR(255) DEFAULT NULL,
    `transaction_id` VARCHAR(150) DEFAULT NULL,
    `idempotency_key` VARCHAR(120) DEFAULT NULL UNIQUE,
    `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
    `payment_status` VARCHAR(30) NOT NULL DEFAULT 'pending',
    `setup_payload` LONGTEXT,
    `gateway_response` LONGTEXT,
    `paid_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_pay_tenant` (`tenant_id`),
    INDEX `idx_pay_status` (`payment_status`),
    INDEX `idx_pay_tx` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `saas_tenant_templates` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `template_key` VARCHAR(50) NOT NULL,
    `custom_settings` LONGTEXT,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_tenant_tpl` (`tenant_id`, `template_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `saas_enquiries` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(50) DEFAULT '',
    `store_type` VARCHAR(50) DEFAULT '',
    `subject` VARCHAR(255) DEFAULT '',
    `message` TEXT NOT NULL,
    `status` VARCHAR(30) NOT NULL DEFAULT 'new',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
