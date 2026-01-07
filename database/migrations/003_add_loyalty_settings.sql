-- Migration: Add loyalty settings table
-- Created: 2025-01-04

CREATE TABLE IF NOT EXISTS `loyalty_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default loyalty settings
INSERT INTO `loyalty_settings` (`setting_key`, `setting_value`) VALUES
('points_per_dollar_purchase', '1.0'),
('points_per_dollar_funding', '0.5'),
('loyalty_tier_bronze', '0'),
('loyalty_tier_silver', '1000'),
('loyalty_tier_gold', '5000'),
('loyalty_tier_platinum', '10000');
