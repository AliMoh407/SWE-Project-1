-- MediTrack Database Schema
-- Run this SQL in phpMyAdmin to create all necessary tables

-- Users table (already exists, but including for reference)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` VARCHAR(20) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inventory table
CREATE TABLE IF NOT EXISTS `inventory` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `stock` INT NOT NULL DEFAULT 0,
    `min_stock` INT NOT NULL DEFAULT 0,
    `expiry_date` DATE NOT NULL,
    `price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `controlled` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Activity Logs table
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT,
    `action` VARCHAR(255) NOT NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'Completed',
    `timestamp` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Requests table (for doctor requests)
CREATE TABLE IF NOT EXISTS `requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `doctor_id` INT NOT NULL,
    `item_id` INT NOT NULL,
    `quantity` INT NOT NULL,
    `patient_id` VARCHAR(50) NOT NULL,
    `patient_name` VARCHAR(100) NOT NULL,
    `notes` TEXT,
    `status` VARCHAR(50) NOT NULL DEFAULT 'Pending',
    `priority` VARCHAR(20) NOT NULL DEFAULT 'normal',
    `requested_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `approved_date` DATETIME NULL,
    `approved_by` VARCHAR(100) NULL,
    FOREIGN KEY (`doctor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`item_id`) REFERENCES `inventory`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notifications table
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `type` VARCHAR(50) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `item_id` INT NULL,
    `priority` VARCHAR(20) NOT NULL DEFAULT 'low',
    `read` TINYINT(1) NOT NULL DEFAULT 0,
    `timestamp` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`item_id`) REFERENCES `inventory`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample inventory data
INSERT INTO `inventory` (`name`, `category`, `stock`, `min_stock`, `expiry_date`, `price`, `controlled`) VALUES
('Paracetamol 500mg', 'Pain Relief', 150, 50, '2024-12-31', 2.50, 0),
('Morphine 10mg', 'Pain Relief', 25, 30, '2024-08-15', 15.00, 1),
('Surgical Gloves', 'Medical Equipment', 500, 100, '2025-06-30', 0.50, 0),
('Insulin Pen', 'Diabetes Care', 75, 50, '2024-09-20', 45.00, 0),
('Oxycodone 5mg', 'Pain Relief', 10, 20, '2024-07-10', 8.00, 1),
('Bandages', 'Medical Supplies', 200, 50, '2026-01-01', 1.25, 0);

-- Insert sample activity logs (assuming user_id 1 exists)
INSERT INTO `activity_logs` (`user_id`, `action`, `status`, `timestamp`) VALUES
(1, 'Requested Morphine 10mg', 'Approved', '2024-01-15 14:30:00'),
(2, 'Updated Paracetamol stock', 'Completed', '2024-01-15 13:45:00'),
(1, 'Created new user account', 'Completed', '2024-01-15 12:20:00');

-- Insert sample notifications
INSERT INTO `notifications` (`type`, `title`, `message`, `item_id`, `priority`, `read`, `timestamp`) VALUES
('low_stock', 'Low Stock Alert', 'Morphine 10mg is running low (25 units remaining)', 2, 'high', 0, '2024-01-15 14:30:00'),
('expiry', 'Expiry Warning', 'Oxycodone 5mg expires in 15 days', 5, 'medium', 0, '2024-01-15 13:45:00'),
('request', 'New Request', 'Dr. Sarah Johnson requested Paracetamol 500mg', 1, 'low', 1, '2024-01-15 12:20:00'),
('system', 'System Update', 'Inventory system updated successfully', NULL, 'low', 1, '2024-01-15 10:00:00');

