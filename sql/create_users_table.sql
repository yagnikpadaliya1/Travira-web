-- ================================================
-- TRAVIRA DB — Customer Users Migration
-- Run this once in phpMyAdmin or MySQL terminal
-- ================================================

-- 1. Create the users table
CREATE TABLE IF NOT EXISTS `users` (
    `user_id`       INT          NOT NULL AUTO_INCREMENT,
    `full_name`     VARCHAR(120) NOT NULL,
    `email`         VARCHAR(180) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`),
    UNIQUE KEY `uq_user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Add user_id column to bookings (nullable — existing guest bookings stay unaffected)
ALTER TABLE `bookings`
    ADD COLUMN `user_id` INT NULL DEFAULT NULL AFTER `package_id`,
    ADD CONSTRAINT `fk_bookings_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`)
        ON DELETE SET NULL;
