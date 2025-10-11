-- Create Special Savings Members Table
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

-- Add foreign key constraint (uncomment if needed)
-- ALTER TABLE `tbl_special_savings` 
-- ADD CONSTRAINT `fk_special_savings_member` 
-- FOREIGN KEY (`memberid`) REFERENCES `tbl_personalinfo` (`memberid`) 
-- ON DELETE CASCADE ON UPDATE CASCADE;
