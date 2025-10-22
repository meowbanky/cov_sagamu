-- =====================================================
-- SQL SCRIPT TO CREATE MISSING TABLES
-- Database: emmaggic_cofv
-- Run this script in your MySQL/phpMyAdmin
-- =====================================================

-- ===========================================
-- 1. SPECIAL SAVINGS TABLE
-- ===========================================
CREATE TABLE IF NOT EXISTS `tbl_special_savings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `memberid` int(11) NOT NULL,
  `special_savings_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `added_by` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `memberid` (`memberid`),
  KEY `status` (`status`),
  KEY `date_added` (`date_added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- 2. EMAIL QUEUE TABLE
-- ===========================================
CREATE TABLE IF NOT EXISTS `tbl_email_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `email_type` enum('transaction_summary','monthly_statement','loan_reminder','contribution_alert') NOT NULL DEFAULT 'transaction_summary',
  `recipient_email` varchar(255) NOT NULL,
  `recipient_name` varchar(255) NOT NULL,
  `subject` varchar(500) NOT NULL,
  `message_body` text NOT NULL,
  `status` enum('pending','processing','sent','failed','cancelled') NOT NULL DEFAULT 'pending',
  `priority` int(11) NOT NULL DEFAULT 1 COMMENT '1=high, 2=normal, 3=low',
  `retry_count` int(11) NOT NULL DEFAULT 0,
  `max_retries` int(11) NOT NULL DEFAULT 3,
  `scheduled_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `metadata` text DEFAULT NULL COMMENT 'Additional data for the email (JSON)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `scheduled_at` (`scheduled_at`),
  KEY `priority` (`priority`),
  KEY `member_id` (`member_id`),
  KEY `period_id` (`period_id`),
  KEY `email_type` (`email_type`),
  KEY `status_scheduled` (`status`, `scheduled_at`),
  KEY `status_priority` (`status`, `priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- 3. EMAIL QUEUE LOG TABLE
-- ===========================================
CREATE TABLE IF NOT EXISTS `tbl_email_queue_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue_id` int(11) NOT NULL,
  `action` enum('queued','processing','sent','failed','retry','cancelled') NOT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `queue_id` (`queue_id`),
  KEY `action` (`action`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- 4. EMAIL RATE LIMIT TABLE
-- ===========================================
CREATE TABLE IF NOT EXISTS `tbl_email_rate_limit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `hour` tinyint(4) NOT NULL COMMENT '0-23',
  `emails_sent` int(11) NOT NULL DEFAULT 0,
  `emails_failed` int(11) NOT NULL DEFAULT 0,
  `last_sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `date_hour` (`date`, `hour`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- ADD FOREIGN KEY CONSTRAINTS (OPTIONAL)
-- Uncomment if you want to enforce referential integrity
-- ===========================================

-- Special Savings Foreign Key
-- ALTER TABLE `tbl_special_savings` 
-- ADD CONSTRAINT `fk_special_savings_member` 
-- FOREIGN KEY (`memberid`) REFERENCES `tbl_personalinfo` (`memberid`) 
-- ON DELETE CASCADE ON UPDATE CASCADE;

-- Email Queue Log Foreign Key
-- ALTER TABLE `tbl_email_queue_log`
-- ADD CONSTRAINT `fk_email_queue_log_queue`
-- FOREIGN KEY (`queue_id`) REFERENCES `tbl_email_queue` (`id`)
-- ON DELETE CASCADE ON UPDATE CASCADE;

-- ===========================================
-- VERIFICATION QUERIES
-- Run these to verify tables were created
-- ===========================================

-- Check if tables exist
-- SELECT TABLE_NAME FROM information_schema.TABLES 
-- WHERE TABLE_SCHEMA = 'emmaggic_cofv' 
-- AND TABLE_NAME IN ('tbl_special_savings', 'tbl_email_queue', 'tbl_email_queue_log', 'tbl_email_rate_limit');

-- Check table structures
-- DESCRIBE tbl_special_savings;
-- DESCRIBE tbl_email_queue;
-- DESCRIBE tbl_email_queue_log;
-- DESCRIBE tbl_email_rate_limit;

-- ===========================================
-- NOTES:
-- ===========================================
-- 1. All tables use utf8mb4 charset for better Unicode support
-- 2. Indexes are added for performance optimization
-- 3. TIMESTAMP fields automatically update with CURRENT_TIMESTAMP
-- 4. Email queue metadata is stored as TEXT (not JSON) for MySQL < 5.7
-- 5. Foreign keys are commented out - uncomment if needed
-- 6. IF NOT EXISTS prevents errors if tables already exist
-- ===========================================

