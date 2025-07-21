--
-- Table structure for table `inventory_activity_log`
--
DROP TABLE IF EXISTS `inventory_activity_log`;
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_activity_log`
--
INSERT INTO `inventory_activity_log` VALUES('1','1','added','Item added with quantity: 11','admin','2025-06-06 08:00:42');
INSERT INTO `inventory_activity_log` VALUES('2','1','edited','Quantity changed from 11.00 to 22','admin','2025-06-06 08:00:51');
INSERT INTO `inventory_activity_log` VALUES('3','1','withdrawn','Withdrawn: 11, New balance: 11, WS#: WS-2025-06-0001','admin','2025-06-06 08:02:50');
INSERT INTO `inventory_activity_log` VALUES('4','1','withdrawn','Withdrawn: 2, New balance: 9, WS#: WS-2025-06-0002','admin','2025-06-07 12:01:37');
INSERT INTO `inventory_activity_log` VALUES('5','32','withdrawn','Withdrawn: 40, New balance: 10, WS#: WS-2025-06-0003','admin','2025-06-07 12:06:05');
INSERT INTO `inventory_activity_log` VALUES('6','40','withdrawn','Withdrawn: 5, New balance: 3, WS#: WS-2025-06-0004','admin','2025-06-07 17:33:16');
INSERT INTO `inventory_activity_log` VALUES('7','40','edited','Quantity changed from 3.00 to 22','admin','2025-06-16 21:58:29');
INSERT INTO `inventory_activity_log` VALUES('8','41','added','Item added with quantity: 12','admin','2025-06-17 16:47:35');
INSERT INTO `inventory_activity_log` VALUES('9','41','edited','Quantity changed from 12.00 to 22','admin','2025-06-17 21:18:37');
INSERT INTO `inventory_activity_log` VALUES('10','41','withdrawn','Withdrawn: 22, New balance: 0, WS#: WS-2025-06-0005','admin','2025-06-17 21:18:52');
INSERT INTO `inventory_activity_log` VALUES('11','42','added','Item added with quantity: 100','admin','2025-06-17 21:22:07');
INSERT INTO `inventory_activity_log` VALUES('12','43','added','Item added with quantity: 2','admin','2025-06-17 21:43:09');
INSERT INTO `inventory_activity_log` VALUES('13','44','added','Item added with quantity: 1','admin','2025-06-17 21:44:54');

--
-- Table structure for table `inventory_items`
--
DROP TABLE IF EXISTS `inventory_items`;
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
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_items`
--
INSERT INTO `inventory_items` VALUES('1','RG45','network connection','pcs','9.00','2025-06-06 08:00:42','RIS-2025-06-0001','MIS office');
INSERT INTO `inventory_items` VALUES('32','Bond Paper','A4 size, 500 sheets per ream','reams','10.00','2025-06-07 12:05:22','RIS-2025-06-0002','PaperWorld Inc.');
INSERT INTO `inventory_items` VALUES('33','Ethernet Cable','Cat6, 10 meters','pcs','30.00','2025-06-07 12:05:22','RIS-2025-06-0003','TechNet Hardware');
INSERT INTO `inventory_items` VALUES('34','Stapler','Heavy-duty office stapler','pcs','10.00','2025-06-07 12:05:22','RIS-2025-06-0004','Office Essentials');
INSERT INTO `inventory_items` VALUES('35','USB Flash Drive','32GB storage capacity','pcs','40.00','2025-06-07 12:05:22','RIS-2025-06-0005','GigaStore Ltd.');
INSERT INTO `inventory_items` VALUES('36','Whiteboard Marker','Black, refillable','box','20.00','2025-06-07 12:05:22','RIS-2025-06-0006','EduSupplies Co.');
INSERT INTO `inventory_items` VALUES('37','Filing Cabinet','4-drawer steel cabinet','pcs','5.00','2025-06-07 12:05:22','RIS-2025-06-0007','SteelFurnish Inc.');
INSERT INTO `inventory_items` VALUES('38','Mouse','Wireless optical mouse','pcs','15.00','2025-06-07 12:05:22','RIS-2025-06-0008','PeriTech Devices');
INSERT INTO `inventory_items` VALUES('39','Laptop Charger','65W adapter for Lenovo laptops','pcs','12.00','2025-06-07 12:05:22','RIS-2025-06-0009','PowerEdge Supplies');
INSERT INTO `inventory_items` VALUES('40','Alcohol Dispenser','Automatic spray dispenser','pcs','22.00','2025-06-07 12:05:22','RIS-2025-06-0010','SafeZone Products');
INSERT INTO `inventory_items` VALUES('41','pen','black ballpen','pcs','0.00','2025-06-17 16:47:35','RIS-2025-06-0011','office');
INSERT INTO `inventory_items` VALUES('42','resourcess','commodity','pcs','100.00','2025-06-17 21:22:07','RIS-2025-06-0012','mines');
INSERT INTO `inventory_items` VALUES('43','cleaning detergent','for cleaning','box','2.00','2025-06-17 21:43:09','RIS-2025-06-0013','office');
INSERT INTO `inventory_items` VALUES('44','1','1','box','1.00','2025-06-17 21:44:54','RIS-2025-06-0014','office');

--
-- Table structure for table `inventory_withdrawals`
--
DROP TABLE IF EXISTS `inventory_withdrawals`;
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_withdrawals`
--
INSERT INTO `inventory_withdrawals` VALUES('1','1','WS-2025-06-0001','11.00','11.00','2025-06-06 00:02:00','thank u');
INSERT INTO `inventory_withdrawals` VALUES('2','1','WS-2025-06-0002','2.00','9.00','2025-06-07 04:01:00','thank u');
INSERT INTO `inventory_withdrawals` VALUES('3','32','WS-2025-06-0003','40.00','10.00','2025-06-07 04:05:00','thx');
INSERT INTO `inventory_withdrawals` VALUES('4','40','WS-2025-06-0004','5.00','3.00','2025-06-07 09:33:00','thx');
INSERT INTO `inventory_withdrawals` VALUES('5','41','WS-2025-06-0005','22.00','0.00','2025-06-17 13:18:00','thank u');

--
-- Table structure for table `item_requests`
--
DROP TABLE IF EXISTS `item_requests`;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_requests`
--
INSERT INTO `item_requests` VALUES('1','1','1','2.00','pending','2025-06-06 08:00:58','2025-06-06 08:00:58');
INSERT INTO `item_requests` VALUES('2','1','2','2.00','completed','2025-06-06 08:02:13','2025-06-08 06:34:30');
INSERT INTO `item_requests` VALUES('3','1','7','2.00','approved','2025-06-11 20:41:12','2025-06-17 21:16:28');
INSERT INTO `item_requests` VALUES('4','41','2','2.00','pending','2025-06-17 16:48:11','2025-06-17 16:48:11');

--
-- Table structure for table `users`
--
DROP TABLE IF EXISTS `users`;
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--
INSERT INTO `users` VALUES('1','admin','admin@ispsc.edu.ph','$2y$10$fjTayyiQFQemJ.DxGw4VB.isKCh4rU2U1ParJVHOC4iYzCRQNua.O','admin','active','2025-06-05 20:44:12','2025-06-05 20:44:12');
INSERT INTO `users` VALUES('2','brian','brian@gmail.com','$2y$10$oSv8hJW9y0.7ptkQpcN8zuHTQAx9Z.eeo/9wC9d92vL1a.14pBL4e','user','active','2025-06-06 08:01:46','2025-06-06 08:02:00');
INSERT INTO `users` VALUES('7','allan','allan@gmail.com','$2y$10$79tgJePJajPjb.7X8RW0Gu2/mfeNPMtC5Kif7bYyzKgo9BuL7Qtb.','user','active','2025-06-11 20:37:57','2025-06-17 18:27:47');
INSERT INTO `users` VALUES('10','BJorn','bjorn@gmail.com','$2y$10$czZPhlwpsprZ18ABk3WaqOSWP0KO/FDqqGLEhbzV19GJLZoNKM0Re','user','active','2025-06-17 21:05:53','2025-06-17 21:05:53');
INSERT INTO `users` VALUES('11','bj','BJ@gmail.com','$2y$10$tCfMLOM5fRuF1dU/nnqmkeHI6j1oPwBify8T2EH6WtuVItpJweEBG','user','active','2025-06-17 21:45:36','2025-06-17 21:45:36');

