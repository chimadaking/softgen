-- Migration: Add default_markup field to api_instances table
-- Date: 2026-01-04
-- Description: Adds default_markup column to store instance-level markup percentage

-- Check and add default_markup column safely
SET @dbname = DATABASE();
SET @tablename = 'api_instances';
SET @columnname = 'default_markup';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' DECIMAL(10,2) DEFAULT 0.00 COMMENT ''Default markup (fractional: 0.50 = 50%)'' AFTER api_key')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
