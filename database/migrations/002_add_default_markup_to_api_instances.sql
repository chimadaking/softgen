-- Migration: Add default_markup field to api_instances
-- Run this to add the missing default_markup column for SMS pricing

ALTER TABLE `api_instances` 
ADD COLUMN `default_markup` DECIMAL(10,2) DEFAULT 0.00 AFTER `api_key`;

-- Update comment: This default_markup will be applied to all services synced from this instance
-- Formula: final_price = api_rate + markup (markup can be absolute or percentage-based)

-- Create index for better query performance on api_services
CREATE INDEX idx_api_services_instance_id ON api_services(api_instance_id);
CREATE INDEX idx_api_services_status_visible ON api_services(status, visible);
