-- SQL for creating activity_logs table
-- Run this on your live database

CREATE TABLE `activity_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `action` VARCHAR(255) NOT NULL,
  `model_type` VARCHAR(255) NULL,
  `model_id` BIGINT UNSIGNED NULL,
  `user_type` VARCHAR(255) NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `description` VARCHAR(255) NOT NULL,
  `old_values` TEXT NULL,
  `new_values` TEXT NULL,
  `metadata` TEXT NULL,
  `ip_address` VARCHAR(255) NULL,
  `user_agent` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `activity_logs_model_type_model_id_index` (`model_type`, `model_id`),
  INDEX `activity_logs_user_type_user_id_index` (`user_type`, `user_id`),
  INDEX `activity_logs_action_index` (`action`),
  INDEX `activity_logs_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

