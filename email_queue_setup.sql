-- Create Email Notification Queue Table
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
  `metadata` json DEFAULT NULL COMMENT 'Additional data for the email',
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

-- Create Email Queue Log Table for tracking
CREATE TABLE IF NOT EXISTS `tbl_email_queue_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue_id` int(11) NOT NULL,
  `action` enum('queued','processing','sent','failed','retry','cancelled') NOT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `queue_id` (`queue_id`),
  KEY `action` (`action`),
  KEY `created_at` (`created_at`),
  FOREIGN KEY (`queue_id`) REFERENCES `tbl_email_queue` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Email Rate Limiting Table
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
