DROP TABLE IF EXISTS `codes`;

CREATE TABLE `codes` (
  `code_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `days` int(11) DEFAULT NULL COMMENT 'only applicable if type is redeemable',
  `package_id` int(16) DEFAULT NULL,
  `code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `discount` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `redeemed` int(11) NOT NULL DEFAULT 0,
  `date` datetime NOT NULL,
  PRIMARY KEY (`code_id`),
  KEY `type` (`type`),
  KEY `code` (`code`),
  KEY `package_id` (`package_id`),
  CONSTRAINT `codes_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `packages` (`package_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `codes` */

/*Table structure for table `domains` */

DROP TABLE IF EXISTS `domains`;

CREATE TABLE `domains` (
  `domain_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `scheme` varchar(8) NOT NULL DEFAULT '',
  `host` varchar(256) NOT NULL DEFAULT '',
  `type` tinyint(11) DEFAULT 1,
  `date` datetime DEFAULT NULL,
  `custom_index_url` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`domain_id`),
  KEY `user_id` (`user_id`),
  KEY `host` (`host`(191)),
  KEY `type` (`type`),
  CONSTRAINT `domains_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

/*Data for the table `domains` */

/*Table structure for table `links` */

DROP TABLE IF EXISTS `links`;

CREATE TABLE `links` (
  `link_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `biolink_id` int(11) DEFAULT NULL,
  `domain_id` int(11) NOT NULL DEFAULT 0,
  `type` varchar(32) NOT NULL DEFAULT '',
  `subtype` varchar(32) DEFAULT NULL,
  `url` varchar(256) NOT NULL DEFAULT '',
  `location_url` varchar(512) DEFAULT NULL,
  `clicks` int(11) NOT NULL DEFAULT 0,
  `settings` text DEFAULT NULL,
  `order` int(11) NOT NULL DEFAULT 0,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `is_enabled` tinyint(4) NOT NULL DEFAULT 1,
  `date` datetime NOT NULL,
  PRIMARY KEY (`link_id`),
  KEY `project_id` (`project_id`),
  KEY `user_id` (`user_id`),
  KEY `url` (`url`(191)),
  KEY `type` (`type`),
  KEY `subtype` (`subtype`),
  CONSTRAINT `links_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `links_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

/*Data for the table `links` */

insert  into `links`(`link_id`,`project_id`,`user_id`,`biolink_id`,`domain_id`,`type`,`subtype`,`url`,`location_url`,`clicks`,`settings`,`order`,`start_date`,`end_date`,`is_enabled`,`date`) values 
(1,1,1,NULL,0,'biolink','base','hahaha',NULL,3,'{\"title\":\"My Featured Links \\ud83d\\udd25\",\"description\":\"thank you\",\"display_verified\":false,\"image\":\"1591902139.png\",\"background_type\":\"preset\",\"background\":\"one\",\"text_color\":\"#fff\",\"socials_color\":\"#fff\",\"google_analytics\":\"\",\"facebook_pixel\":\"\",\"display_branding\":true,\"branding\":{\"name\":\"\",\"url\":\"\"},\"seo\":{\"title\":\"\",\"meta_description\":\"\"},\"utm\":{\"medium\":\"\",\"source\":\"\"},\"socials\":{\"email\":\"\",\"tel\":\"\",\"whatsapp\":\"\",\"facebook\":\"\",\"facebook-messenger\":\"\",\"instagram\":\"\",\"twitter\":\"\",\"tiktok\":\"\",\"youtube\":\"\",\"soundcloud\":\"\",\"linkedin\":\"\",\"spotify\":\"\",\"pinterest\":\"\"},\"font\":\"lato\"}',0,NULL,NULL,1,'2020-05-15 02:51:06'),
(2,1,1,1,0,'biolink','link','moNd9V4x1i','https://staging.linkinbio.is/',2,'{\"name\":\"Your own link here\",\"text_color\":\"black\",\"background_color\":\"white\",\"outline\":false,\"border_radius\":\"rounded\",\"animation\":false,\"icon\":\"\"}',0,NULL,NULL,1,'2020-05-15 02:51:06');

/*Table structure for table `packages` */

DROP TABLE IF EXISTS `packages`;

CREATE TABLE `packages` (
  `package_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL DEFAULT '',
  `monthly_price` float NOT NULL,
  `annual_price` float NOT NULL,
  `settings` text NOT NULL,
  `status` tinyint(4) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`package_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

/*Data for the table `packages` */

insert  into `packages`(`package_id`,`name`,`monthly_price`,`annual_price`,`settings`,`status`,`date`) values 
(1,'All Features',1,12,'{\"no_ads\":true,\"removable_branding\":true,\"custom_branding\":true,\"custom_colored_links\":true,\"statistics\":true,\"google_analytics\":true,\"facebook_pixel\":true,\"custom_backgrounds\":true,\"verified\":true,\"scheduling\":true,\"seo\":true,\"utm\":true,\"socials\":true,\"fonts\":true,\"projects_limit\":-1,\"biolinks_limit\":-1,\"links_limit\":-1,\"domains_limit\":-1}',1,'2020-05-12 08:12:02');

/*Table structure for table `pages` */

DROP TABLE IF EXISTS `pages`;

CREATE TABLE `pages` (
  `page_id` int(11) NOT NULL AUTO_INCREMENT,
  `pages_category_id` int(11) DEFAULT NULL,
  `url` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `content` longtext COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(16) COLLATE utf8_unicode_ci DEFAULT '',
  `position` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `order` int(11) DEFAULT 0,
  `total_views` int(11) DEFAULT 0,
  `date` datetime DEFAULT NULL,
  `last_date` datetime DEFAULT NULL,
  PRIMARY KEY (`page_id`),
  KEY `pages_pages_category_id_index` (`pages_category_id`),
  KEY `pages_url_index` (`url`),
  CONSTRAINT `pages_pages_categories_pages_category_id_fk` FOREIGN KEY (`pages_category_id`) REFERENCES `pages_categories` (`pages_category_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `pages` */

insert  into `pages`(`page_id`,`pages_category_id`,`url`,`title`,`description`,`content`,`type`,`position`,`order`,`total_views`,`date`,`last_date`) values 
(1,NULL,'privacy','privacy','PRIVACY STATEMENT &#8212;- SECTION 1 &#8211; WHAT DO WE DO WITH YOUR INFORMATION? When you purchase something from our store, as','<!DOCTYPE html>\r\n<html>\r\n<head>\r\n</head>\r\n<body>\r\n<p>We reserve the right to modify this privacy policy at any time, so please review it frequently. Changes and clarifications will take effect immediately upon their posting on the website. If we make material changes to this policy, we will notify you here that it has been updated, so that you are aware of what information we collect, how we use it, and under what circumstances, if any, we use and/or disclose it.</p>\r\n<p>If our store is acquired or merged with another company, your information may be transferred to the new owners so that we may continue to sell products to you.</p>\r\n</body>\r\n</html>','internal','bottom',0,17,'2020-05-14 19:59:35','2020-05-14 19:59:35');

/*Table structure for table `pages_categories` */

DROP TABLE IF EXISTS `pages_categories`;

CREATE TABLE `pages_categories` (
  `pages_category_id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `title` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `icon` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`pages_category_id`),
  KEY `url` (`url`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

/*Data for the table `pages_categories` */

/*Table structure for table `payments` */

DROP TABLE IF EXISTS `payments`;

CREATE TABLE `payments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `package_id` int(11) DEFAULT NULL,
  `processor` enum('PAYPAL','STRIPE') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('ONE-TIME','RECURRING') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plan` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subscription_id` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payer_id` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `currency` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_user_id` (`user_id`),
  KEY `package_id` (`package_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `packages` (`package_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `payments_users_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `payments` */

/*Table structure for table `projects` */

DROP TABLE IF EXISTS `projects`;

CREATE TABLE `projects` (
  `project_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL DEFAULT '',
  `date` datetime NOT NULL,
  PRIMARY KEY (`project_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

/*Data for the table `projects` */

insert  into `projects`(`project_id`,`user_id`,`name`,`date`) values 
(1,1,'hello world','2020-05-15 02:50:48');

/*Table structure for table `redeemed_codes` */

DROP TABLE IF EXISTS `redeemed_codes`;

CREATE TABLE `redeemed_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `code_id` (`code_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `redeemed_codes_ibfk_1` FOREIGN KEY (`code_id`) REFERENCES `codes` (`code_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `redeemed_codes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `redeemed_codes` */

/*Table structure for table `settings` */

DROP TABLE IF EXISTS `settings`;

CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(64) NOT NULL DEFAULT '',
  `value` longtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4;

/*Data for the table `settings` */

insert  into `settings`(`id`,`key`,`value`) values 
(1,'ads','{\"header\":\"\",\"footer\":\"\",\"header_biolink\":\"\",\"footer_biolink\":\"\"}'),
(2,'captcha','{\"recaptcha_is_enabled\":\"0\",\"recaptcha_public_key\":\"\",\"recaptcha_private_key\":\"\"}'),
(3,'cron','{\"key\":\"b8c3a92fb57a5df9a2aa06a3e2b13383\"}'),
(4,'default_language','english'),
(5,'email_confirmation','1'),
(6,'register_is_enabled','1'),
(7,'email_notifications','{\"emails\":\"\",\"new_user\":\"\",\"new_payment\":\"\"}'),
(8,'facebook','{\"is_enabled\":\"1\",\"app_id\":\"668001737095866\",\"app_secret\":\"420f1f69122c3ce112e5c1724e716df5\"}'),
(9,'favicon','9fa8a623783fd2d277c53e1d216068ce.ico'),
(10,'logo',''),
(11,'package_custom','{\"package_id\":\"custom\",\"name\":\"Custom\",\"status\":1}\r\n'),
(12,'package_free','{\"package_id\":\"free\",\"name\":\"Free\",\"days\":null,\"status\":1,\"settings\":{\"additional_global_domains\":true,\"custom_url\":true,\"deep_links\":true,\"no_ads\":true,\"removable_branding\":true,\"custom_branding\":false,\"custom_colored_links\":true,\"statistics\":true,\"google_analytics\":false,\"facebook_pixel\":false,\"custom_backgrounds\":false,\"verified\":true,\"scheduling\":false,\"seo\":false,\"socials\":false,\"fonts\":false,\"projects_limit\":1,\"biolinks_limit\":1,\"links_limit\":10}}'),
(13,'package_trial','{\"package_id\":\"trial\",\"name\":\"Trial\",\"days\":7,\"is_enabled\":1,\"settings\":{\"no_ads\":false,\"removable_branding\":false,\"custom_branding\":false,\"custom_colored_links\":true,\"statistics\":true,\"google_analytics\":true,\"facebook_pixel\":true,\"custom_backgrounds\":false,\"verified\":false,\"scheduling\":false,\"seo\":false,\"socials\":false,\"fonts\":false,\"projects_limit\":1,\"biolinks_limit\":1,\"links_limit\":10}}'),
(14,'payment','{\"is_enabled\":\"0\",\"type\":\"both\",\"brand_name\":\"BioLinks\",\"currency\":\"USD\", \"codes_is_enabled\": false}\r\n'),
(15,'paypal','{\"is_enabled\":\"0\",\"mode\":\"sandbox\",\"client_id\":\"\",\"secret\":\"\"}'),
(16,'smtp','{\"host\":\"\",\"from\":\"\",\"encryption\":\"tls\",\"port\":\"587\",\"auth\":\"0\",\"username\":\"\",\"password\":\"\"}'),
(17,'custom','{\"head_js\":\"\",\"head_css\":\"\"}'),
(18,'socials','{\"facebook\":\"\",\"instagram\":\"\",\"twitter\":\"\",\"youtube\":\"\"}'),
(19,'stripe','{\"is_enabled\":\"0\",\"publishable_key\":\"\",\"secret_key\":\"\",\"webhook_secret\":\"\"}\r\n'),
(20,'default_timezone','UTC'),
(21,'title','phpBiolinks.com'),
(22,'privacy_policy_url',''),
(23,'terms_and_conditions_url',''),
(24,'index_url',''),
(25,'business','{\"invoice_is_enabled\":\"0\",\"name\":\"\",\"address\":\"\",\"city\":\"\",\"county\":\"\",\"zip\":\"\",\"country\":\"\",\"email\":\"\",\"phone\":\"\",\"tax_type\":\"\",\"tax_id\":\"\",\"custom_key_one\":\"\",\"custom_value_one\":\"\",\"custom_key_two\":\"\",\"custom_value_two\":\"\"}'),
(26,'links','{\"shortener_is_enabled\":true, \"domains_is_enabled\": true, \"blacklisted_domains\":[\"\"],\"blacklisted_keywords\":[],\"phishtank_is_enabled\":\"0\",\"phishtank_api_key\":\"\",\"google_safe_browsing_is_enabled\":\"0\",\"google_safe_browsing_api_key\":\"\"}'),
(27,'license','{\"license\":\"675605b1-c8e1-4c49-8c0c-3fa9be19f584\",\"type\":\"Extended License\"}'),
(28,'google','{\"is_enabled\":\"1\",\"app_id\":\"208820966719-kcbq52tl97ad720qtk9605umg13j9vl7.apps.googleusercontent.com\",\"app_secret\":\"Fz6lI6FAEVyoAliJhI0bGS-T\"}');

/*Table structure for table `track_links` */

DROP TABLE IF EXISTS `track_links`;

CREATE TABLE `track_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `link_id` int(11) NOT NULL,
  `dynamic_id` varchar(32) NOT NULL DEFAULT '',
  `ip` varchar(128) NOT NULL DEFAULT '',
  `country_code` varchar(8) DEFAULT NULL,
  `os_name` varchar(16) DEFAULT NULL,
  `browser_name` varchar(32) DEFAULT NULL,
  `referrer` varchar(512) DEFAULT NULL,
  `device_type` varchar(16) DEFAULT NULL,
  `browser_language` varchar(16) DEFAULT NULL,
  `count` int(11) NOT NULL DEFAULT 1,
  `date` datetime NOT NULL,
  `last_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dynamic_id` (`dynamic_id`),
  KEY `link_id` (`link_id`),
  KEY `track_links_date_index` (`date`),
  CONSTRAINT `track_links_ibfk_1` FOREIGN KEY (`link_id`) REFERENCES `links` (`link_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

/*Data for the table `track_links` */

insert  into `track_links`(`id`,`link_id`,`dynamic_id`,`ip`,`country_code`,`os_name`,`browser_name`,`referrer`,`device_type`,`browser_language`,`count`,`date`,`last_date`) values 
(1,1,'774bf4fddc49a8fb8ea1f3b346c009b3','210.121.187.8','KR','Windows','Chrome',NULL,'desktop','en-US',5,'2020-05-15 02:51:15','2020-05-15 02:52:28'),
(2,2,'31e1324fdaec0b6bdd778e82c6079d91','210.121.187.8','KR','Windows','Chrome','https://staging.linkinbio.is/hahaha','desktop','en-US',2,'2020-05-15 02:51:17','2020-05-15 02:51:33'),
(7,1,'0d9f8b820a7e6b60a609e0c9e6552261','66.102.6.40','US','Linux','Chrome',NULL,'desktop','',1,'2020-05-15 02:52:22','2020-05-15 02:52:22'),
(9,1,'84ecbbe2bc804c9a7dd516d92a038e27','66.249.93.76',NULL,'Linux','Chrome',NULL,'desktop','',1,'2020-05-15 22:58:49','2020-05-15 22:58:49'),
(10,2,'21c674f7fd57c7201d6b171fca96116a','119.160.65.32','PK','Windows','Firefox','https://staging.linkinbio.is/admin/links','desktop','en-US',4,'2020-05-15 22:58:54','2020-05-15 22:59:53');

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token_code` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `twofa_secret` varchar(16) DEFAULT NULL,
  `email_activation_code` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `lost_password_code` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `facebook_id` bigint(20) DEFAULT NULL,
  `type` int(11) NOT NULL DEFAULT 0,
  `active` int(11) NOT NULL DEFAULT 0,
  `package_id` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `package_expiration_date` datetime DEFAULT NULL,
  `package_settings` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `package_trial_done` tinyint(4) DEFAULT 0,
  `payment_subscription_id` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `language` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT 'english',
  `timezone` varchar(32) DEFAULT 'UTC',
  `date` datetime DEFAULT NULL,
  `ip` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` varchar(32) DEFAULT NULL,
  `last_activity` datetime DEFAULT NULL,
  `last_user_agent` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `total_logins` int(11) DEFAULT 0,
  `google_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  KEY `package_id` (`package_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;

/*Data for the table `users` */

insert  into `users`(`user_id`,`email`,`password`,`name`,`token_code`,`twofa_secret`,`email_activation_code`,`lost_password_code`,`facebook_id`,`type`,`active`,`package_id`,`package_expiration_date`,`package_settings`,`package_trial_done`,`payment_subscription_id`,`language`,`timezone`,`date`,`ip`,`country`,`last_activity`,`last_user_agent`,`total_logins`,`google_id`) values 
(1,'support@linkinbio.xyz','$2y$10$uFNO0pQKEHSFcus1zSFlveiPCB3EvG9ZlES7XKgJFTAl5JbRGFCWy','AdminUser','',NULL,'','',NULL,1,1,'free','2050-07-15 11:26:19','{\"no_ads\":true,\"removable_branding\":true,\"custom_branding\":false,\"custom_colored_links\":true,\"statistics\":true,\"google_analytics\":false,\"facebook_pixel\":false,\"custom_backgrounds\":false,\"verified\":true,\"scheduling\":false,\"seo\":false,\"socials\":false,\"fonts\":false,\"projects_limit\":1,\"biolinks_limit\":1,\"links_limit\":10}',1,'','english','UTC','2019-06-01 12:00:00','127.0.0.1','','2030-06-01 04:36:10','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.97 Safari/537.36',32,NULL),
(2,'zakirahmad@gmail.com','$2y$10$elxQ6gGe2skZnKQGsQTYu.hpjQdgMyEXfoBYwhA08Qwo77.xzSDMG','Zakir Ahmad','',NULL,NULL,NULL,3503332133014432,0,1,'free','2020-05-14 20:18:08','{\"no_ads\":true,\"removable_branding\":true,\"custom_branding\":false,\"custom_colored_links\":true,\"statistics\":true,\"google_analytics\":false,\"facebook_pixel\":false,\"custom_backgrounds\":false,\"verified\":true,\"scheduling\":false,\"seo\":false,\"socials\":false,\"fonts\":false,\"projects_limit\":1,\"biolinks_limit\":1,\"links_limit\":10}',0,NULL,'english','UTC','2020-05-14 20:18:08','119.160.66.183',NULL,NULL,'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:75.0) Gecko/20100101 Firefox/75.0',1,NULL),
(3,'devninjasuper0228@gmail.com','$2y$10$ioVOf7kkfxGAWo8EpsJZtewSW.lcCWWaym8TqAjKpNuIpuiV.PEB2','ikram0228',NULL,NULL,'44ff4e3f1416f46ce2c9ba47a6212d4f',NULL,NULL,0,0,'free','2020-05-15 06:16:09','{\"no_ads\":true,\"removable_branding\":true,\"custom_branding\":false,\"custom_colored_links\":true,\"statistics\":true,\"google_analytics\":false,\"facebook_pixel\":false,\"custom_backgrounds\":false,\"verified\":true,\"scheduling\":false,\"seo\":false,\"socials\":false,\"fonts\":false,\"projects_limit\":1,\"biolinks_limit\":1,\"links_limit\":10}',0,NULL,'english','UTC','2020-05-15 06:16:09','210.121.187.8',NULL,NULL,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36',0,NULL),
(5,'romancoder204@gmail.com','$2y$10$FHvrJf/I.DFUZphFqOJdke2AyUEaihkJliaOUypa/hyk6366LEWae','ikram222','',NULL,'517e36cad02223e4c319e54eda8f1605',NULL,NULL,1,1,'1','2030-05-15 06:50:03','{\"no_ads\":true,\"removable_branding\":true,\"custom_branding\":true,\"custom_colored_links\":true,\"statistics\":true,\"google_analytics\":true,\"facebook_pixel\":true,\"custom_backgrounds\":true,\"verified\":true,\"scheduling\":true,\"seo\":true,\"utm\":true,\"socials\":true,\"fonts\":true,\"projects_limit\":-1,\"biolinks_limit\":-1,\"links_limit\":-1,\"domains_limit\":-1}',0,NULL,'english','UTC','2020-05-15 06:50:03','210.121.187.8','KR',NULL,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36',3,NULL),
(8,'lambodos0228@gmail.com','$2y$10$BI60gll0Yfbjw8n33KeShOMDBQFtk07uIb/88tJUqqdmaYfDaDoKW','Lambo','',NULL,NULL,NULL,NULL,0,1,'free','2030-06-01 14:29:03','{\"no_ads\":true,\"removable_branding\":true,\"custom_branding\":false,\"custom_colored_links\":true,\"statistics\":true,\"google_analytics\":false,\"facebook_pixel\":false,\"custom_backgrounds\":false,\"verified\":true,\"scheduling\":false,\"seo\":false,\"socials\":false,\"fonts\":false,\"projects_limit\":1,\"biolinks_limit\":1,\"links_limit\":10}',0,NULL,'english','UTC','2030-06-01 14:29:03','127.0.0.1',NULL,NULL,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.61 Safari/537.36',1,9223372036854775807);

/*Table structure for table `users_logs` */

DROP TABLE IF EXISTS `users_logs`;

CREATE TABLE `users_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(64) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `ip` varchar(64) DEFAULT NULL,
  `public` int(11) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `users_logs_user_id` (`user_id`),
  CONSTRAINT `users_logs_users_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4;

/*Data for the table `users_logs` */

insert  into `users_logs`(`id`,`user_id`,`type`,`date`,`ip`,`public`) values 
(1,1,'login.success','2020-05-12 08:08:34','97.90.22.4',1),
(2,1,'login.success','2020-05-12 08:13:09','97.90.22.4',1),
(3,1,'login.wrong_password','2020-05-12 19:44:22','119.160.66.163',1),
(4,1,'login.wrong_password','2020-05-12 19:44:30','119.160.66.163',1),
(5,1,'login.wrong_password','2020-05-12 19:44:37','119.160.66.163',1),
(6,1,'login.wrong_password','2020-05-12 19:44:43','119.160.66.163',1),
(7,1,'login.success','2020-05-13 06:44:48','97.90.22.4',1),
(8,1,'login.wrong_password','2020-05-14 19:39:34','119.160.66.183',1),
(9,1,'login.success','2020-05-14 19:42:01','119.160.66.183',1),
(10,2,'register.facebook_register','2020-05-14 20:18:08','119.160.66.183',1),
(11,2,'login.success','2020-05-14 20:18:08','119.160.66.183',1),
(12,1,'login.success','2020-05-15 00:33:21','119.160.66.183',1),
(13,1,'login.success','2020-05-15 02:50:31','210.121.187.8',1),
(14,3,'register.register','2020-05-15 06:16:09','210.121.187.8',1),
(16,5,'register.register','2020-05-15 06:50:03','185.92.26.49',1),
(17,5,'login.success','2020-05-15 07:06:53','210.121.187.8',1),
(18,5,'login.success','2020-05-15 09:30:49','210.121.187.8',1),
(19,5,'login.success','2020-05-15 13:05:27','210.121.187.8',1),
(20,1,'login.success','2020-05-15 20:44:36','119.160.65.32',1),
(21,1,'login.success','2020-05-15 22:56:48','119.160.65.32',1),
(22,1,'login.success','2030-06-01 04:36:10','127.0.0.1',1),
(25,1,'login.success','2030-06-01 13:58:21','127.0.0.1',1),
(26,1,'login.success','2030-06-01 13:58:40','127.0.0.1',1),
(29,8,'register.google_register','2030-06-01 14:29:03','127.0.0.1',1),
(30,8,'login.success','2030-06-01 14:29:03','127.0.0.1',1),
(31,1,'login.success','2030-06-03 11:22:14','127.0.0.1',1),
(32,1,'login.success','2030-06-04 04:01:13','127.0.0.1',1),
(33,1,'login.success','2030-06-11 15:24:06','127.0.0.1',1),
(34,1,'login.success','2030-06-11 15:33:37','127.0.0.1',1),
(35,1,'login.success','2030-06-11 15:36:01','127.0.0.1',1),
(36,1,'login.success','2030-06-11 15:43:35','127.0.0.1',1),
(37,1,'login.success','2030-06-11 16:29:59','127.0.0.1',1),
(38,1,'login.wrong_password','2030-06-11 16:54:17','127.0.0.1',1),
(39,1,'login.success','2030-06-11 16:54:25','127.0.0.1',1),
(40,1,'login.success','2030-06-11 16:54:37','127.0.0.1',1),
(41,1,'login.success','2030-06-11 16:55:05','127.0.0.1',1),
(42,1,'login.success','2030-06-11 17:31:10','127.0.0.1',1),
(43,1,'login.success','2030-06-11 17:43:59','127.0.0.1',1),
(44,1,'login.success','2030-06-11 17:45:32','127.0.0.1',1),
(45,1,'login.success','2030-06-11 17:45:40','127.0.0.1',1),
(46,1,'login.success','2030-06-11 17:47:17','127.0.0.1',1),
(47,1,'login.success','2030-06-11 17:47:52','127.0.0.1',1),
(48,1,'login.success','2030-06-11 17:50:06','127.0.0.1',1),
(49,1,'login.success','2030-06-11 17:51:53','127.0.0.1',1),
(50,1,'login.success','2030-06-11 18:03:26','127.0.0.1',1),
(51,1,'login.success','2030-06-11 18:27:22','127.0.0.1',1),
(52,1,'login.success','2030-06-11 19:01:36','127.0.0.1',1);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
