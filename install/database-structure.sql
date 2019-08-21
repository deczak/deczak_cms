SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

DROP TABLE IF EXISTS `tb_page`;
CREATE TABLE `tb_page` (
  `data_id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `node_id` int(10) UNSIGNED NOT NULL,
  `page_version` mediumint(11) NOT NULL DEFAULT '1',
  `page_template` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `time_create` bigint(20) NOT NULL,
  `time_update` bigint(20) DEFAULT NULL,
  `create_by` mediumint(9) NOT NULL,
  `update_by` mediumint(9) DEFAULT NULL,
  `update_reason` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `tb_page_header`;
CREATE TABLE `tb_page_header` (
  `data_id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `node_id` int(10) UNSIGNED NOT NULL,
  `page_language` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `page_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `page_description` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `page_version` mediumint(9) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `tb_page_object`;
CREATE TABLE `tb_page_object` (
  `node_id` int(10) UNSIGNED NOT NULL,
  `page_version` mediumint(9) UNSIGNED NOT NULL DEFAULT '1',
  `module_id` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `object_id` int(11) UNSIGNED NOT NULL,
  `object_order_by` mediumint(9) UNSIGNED NOT NULL,
  `time_create` bigint(20) UNSIGNED DEFAULT NULL,
  `time_update` bigint(20) UNSIGNED DEFAULT NULL,
  `create_by` mediumint(8) UNSIGNED DEFAULT NULL,
  `update_by` mediumint(8) UNSIGNED DEFAULT NULL,
  `update_reason` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `tb_page_object_simple`;
CREATE TABLE `tb_page_object_simple` (
  `data_id` int(11) UNSIGNED NOT NULL,
  `object_id` int(11) UNSIGNED NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `params` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `tb_page_path`;
CREATE TABLE `tb_page_path` (
  `page_id` int(11) NOT NULL,
  `page_language` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_path` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `node_id` int(10) UNSIGNED NOT NULL,
  `node_lft` int(10) UNSIGNED NOT NULL,
  `node_rgt` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `tb_right_groups`;
CREATE TABLE `tb_right_groups` (
  `group_id` int(11) NOT NULL,
  `group_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_rights` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `tb_sessions`;
CREATE TABLE `tb_sessions` (
  `data_id` int(11) NOT NULL,
  `session_id` varchar(65) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_ip` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `time_create` bigint(20) NOT NULL,
  `time_update` bigint(20) NOT NULL DEFAULT '0',
  `time_out` bigint(20) NOT NULL,
  `login_fail_count` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `tb_users`;
CREATE TABLE `tb_users` (
  `data_id` int(11) NOT NULL,
  `login_name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `login_pass` varchar(135) COLLATE utf8mb4_unicode_ci NOT NULL,
  `login_count` int(11) NOT NULL DEFAULT '0',
  `user_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_name_first` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_name_last` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_mail` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `time_login` bigint(20) NOT NULL DEFAULT '0',
  `time_create` bigint(20) NOT NULL,
  `time_update` bigint(20) NOT NULL DEFAULT '0',
  `cookie_id` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `recover_key` varchar(65) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recover_timeout` bigint(20) NOT NULL DEFAULT '0',
  `is_locked` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `tb_users_backend`;
CREATE TABLE `tb_users_backend` (
  `data_id` int(11) NOT NULL,
  `login_name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `login_pass` varchar(135) COLLATE utf8mb4_unicode_ci NOT NULL,
  `login_count` int(11) NOT NULL DEFAULT '0',
  `user_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_name_first` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_name_last` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_mail` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `time_login` bigint(20) NOT NULL DEFAULT '0',
  `time_create` bigint(20) NOT NULL,
  `time_update` bigint(20) NOT NULL DEFAULT '0',
  `cookie_id` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `recover_key` varchar(65) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recover_timeout` bigint(20) NOT NULL DEFAULT '0',
  `is_locked` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `tb_users_groups`;
CREATE TABLE `tb_users_groups` (
  `data_id` int(11) NOT NULL,
  `user_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `tb_page`
  ADD UNIQUE KEY `data_id` (`data_id`),
  ADD KEY `page_id` (`page_id`),
  ADD KEY `page_id_2` (`page_id`),
  ADD KEY `node_id` (`node_id`);

ALTER TABLE `tb_page_header`
  ADD UNIQUE KEY `data_id` (`data_id`),
  ADD KEY `page_id` (`page_id`),
  ADD KEY `node_id` (`node_id`);

ALTER TABLE `tb_page_object`
  ADD PRIMARY KEY (`object_id`),
  ADD KEY `node_id` (`node_id`);

ALTER TABLE `tb_page_object_simple`
  ADD UNIQUE KEY `data_id` (`data_id`),
  ADD KEY `object_id` (`object_id`);

ALTER TABLE `tb_page_path`
  ADD PRIMARY KEY (`node_id`),
  ADD KEY `page_id` (`page_id`),
  ADD KEY `node_id` (`node_id`),
  ADD KEY `node_lft` (`node_lft`),
  ADD KEY `node_rgt` (`node_rgt`);

ALTER TABLE `tb_right_groups`
  ADD UNIQUE KEY `data_id` (`group_id`);

ALTER TABLE `tb_sessions`
  ADD UNIQUE KEY `data_id` (`data_id`);

ALTER TABLE `tb_users`
  ADD UNIQUE KEY `data_id` (`data_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `tb_users_backend`
  ADD UNIQUE KEY `data_id` (`data_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `tb_users_groups`
  ADD UNIQUE KEY `data_id` (`data_id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `user_id` (`user_id`);


ALTER TABLE `tb_page`
  MODIFY `data_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tb_page_header`
  MODIFY `data_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tb_page_object`
  MODIFY `object_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `tb_page_object_simple`
  MODIFY `data_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `tb_page_path`
  MODIFY `node_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `tb_right_groups`
  MODIFY `group_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tb_sessions`
  MODIFY `data_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tb_users`
  MODIFY `data_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tb_users_backend`
  MODIFY `data_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tb_users_groups`
  MODIFY `data_id` int(11) NOT NULL AUTO_INCREMENT;
