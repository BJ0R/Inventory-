-- Database Schema for Inventory Management System
CREATE TABLE `inventory_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `unit` varchar(50) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `date_added` datetime NOT NULL DEFAULT current_timestamp(),
  `ris_number` varchar(50) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ris_number` (`ris_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `inventory_withdrawals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `ws_number` varchar(50) NOT NULL,
  `quantity_withdrawn` decimal(10,2) NOT NULL,
  `balance` decimal(10,2) NOT NULL,
  `date_withdrawn` datetime NOT NULL,
  `remark` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ws_number` (`ws_number`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `inventory_withdrawals_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `inventory_activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `details` text NOT NULL,
  `performed_by` varchar(100) NOT NULL,
  `performed_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `inventory_activity_log_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `status` enum('active','inactive','pending') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `item_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quantity_requested` decimal(10,2) NOT NULL,
  `status` enum('pending','approved','rejected','on_order','partially_fulfilled','ready_for_pickup','completed','cancelled') NOT NULL DEFAULT 'pending',
  `request_date` datetime NOT NULL DEFAULT current_timestamp(),
  `last_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `item_requests_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`),
  CONSTRAINT `item_requests_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert initial admin user
-- USER: admin
-- PASSWORD: 12345678 
INSERT INTO `users` VALUES('1','admin','admin@ispsc.edu.ph','$2y$10$fjTayyiQFQemJ.DxGw4VB.isKCh4rU2U1ParJVHOC4iYzCRQNua.O','admin','active','2025-06-05 20:44:12','2025-06-05 20:44:12');
