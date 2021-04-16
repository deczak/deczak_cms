INSERT INTO `tb_backend_page_path` (`node_id`, `page_id`, `page_language`, `page_path`, `node_lft`, `node_rgt`, `node_level`) VALUES
(1, 0, '0', '', 1, 4, 0),
(2, 1, 'en', '/', 2, 3, 1);

INSERT INTO `tb_backend_page` (`data_id`, `node_id`, `page_id`, `page_version`, `page_template`, `update_reason`, `hidden_state`, `cache_disabled`, `crawler_index`, `crawler_follow`, `menu_follow`, `publish_from`, `publish_until`, `publish_expired`, `page_auth`, `create_time`, `create_by`, `update_time`, `update_by`) VALUES
(1, 2, 1, 1, 'backend', '', 0, 0, 1, 1, 1, 0, 0, 0, NULL, %TIMESTAMP%, 1, 0, 0);

INSERT INTO `tb_backend_page_header` (`data_id`, `node_id`, `page_id`, `page_language`, `page_title`, `page_name`, `page_description`, `page_version`) VALUES
(1, 2, 1, 'en', 'Home', 'Home ', 'Home Description', 1);

INSERT INTO `tb_backend_menu` (`menu_group`, `menu_name`, `menu_icon`, `menu_order_by`) VALUES
(1, 'PLACEHOLDER', '', 1),
(2, 'PLACEHOLDER', '', 2),
(3, 'PLACEHOLDER', '&#xf013;', 3);

INSERT INTO `tb_page` (`data_id`, `node_id`, `page_id`, `page_version`, `page_template`, `update_reason`, `hidden_state`, `cache_disabled`, `crawler_index`, `crawler_follow`, `menu_follow`, `publish_from`, `publish_until`, `publish_expired`, `page_auth`, `create_time`, `create_by`, `update_time`, `update_by`) VALUES
(1, 2, 1, 1, 'default', '', 0, 0, 1, 1, 1, 0, 0, 0, NULL, %TIMESTAMP%, 1, 0, 0);

INSERT INTO `tb_page_header` (`data_id`, `node_id`, `page_id`, `page_language`, `page_title`, `page_name`, `page_description`, `page_version`) VALUES
(1, 2, 1, 'en', 'Home', 'Home ', 'Home Description', 1);

INSERT INTO `tb_page_path` (`node_id`, `page_id`, `page_language`, `page_path`, `node_lft`, `node_rgt`, `node_level`) VALUES
(1, 0, '0', '', 1, 4, 0),
(2, 1, 'en', '/', 2, 3, 1);

INSERT INTO `tb_languages` (`data_id`, `lang_key`, `lang_name`, `lang_name_native`, `lang_hidden`, `lang_locked`, `lang_default`, `lang_frontend`, `lang_backend`, `create_time`, `create_by`, `update_time`, `update_by`) VALUES
(1, 'en', 'English', 'English', 0, 0, 1, 1, 1, %TIMESTAMP%, 1, 0, 0);

INSERT INTO `tb_login_objects` (`data_id`, `object_id`, `object_databases`, `object_fields`, `object_session_ext`, `object_description`, `is_disabled`, `is_protected`, `create_time`, `create_by`, `update_time`, `update_by`) VALUES
(12, 'ABKND', '["primary"]', '[{"name":"login_name","data_prc":"crypt","type":"text","is_username":"0","query_type":"compare","table":"tb_users_backend"},{"name":"login_pass","data_prc":"hash","type":"password","is_username":"1","query_type":"compare","table":"tb_users_backend"}]', '{"1":{"name":"user_name_last","data_prc":"crypt","table":"tb_users_backend","query_type":"compare"},"2":{"name":"language","data_prc":"text","table":"tb_users_backend","query_type":"compare"}}', 'Backend Access', 0, 1, %TIMESTAMP%, 1, 0, 0);

/*
INSERT INTO `tb_right_groups` (`group_id`, `group_name`, `group_rights`, `create_time`, `create_by`, `update_time`, `update_by`) VALUES
(1, 'administrator', '{"1":["view","create","edit","delete"],"2":["index","view","create","edit","delete"],"3":["index","view","create","edit","delete"],"4":["view","create","edit","delete"],"5":["view","create","edit","delete"],"6":["index","view","create","edit","delete"],"7":["view","create","edit","delete"],"8":["index","view","create","edit","delete"],"9":["index","view","create","edit","delete"],"10":["index","view","create","edit","delete"],"11":["index","view","edit","delete"],"12":["index","view","create","edit","delete"],"13":["index","view","create","edit","delete"],"14":["index","edit"],"15":["index","view","create","edit","delete"],"16":["index","view","create","edit","delete"],"17":["index","view","create","edit","delete"],"18":["index","view","edit"]}', %TIMESTAMP%, 1, 0, 0);
*/
INSERT INTO `tb_users_backend` (`data_id`, `login_name`, `login_pass`, `login_count`, `user_id`, `user_name_first`, `user_name_last`, `user_mail`, `time_login`, `create_time`, `update_time`, `cookie_id`, `recover_key`, `recover_timeout`, `is_locked`,`language`) VALUES
(1, '%USER_NAME%', '%USER_PASSWORD%', 0, '1', '%USER_FIRST_NAME%', '%USER_LAST_NAME%', '%USER_MAIL%', 0, %TIMESTAMP%, 0, '{}', 0, 0, 0,'en');

/*
	Administrator ist erste gruppe, diesen insert ebenfalls in xidd verschieben
*/

INSERT INTO `tb_users_groups` (`data_id`, `user_id`, `group_id`) VALUES
(1, '1', 1);

/*
	erster eintrag gehört zu backend user (type 0)
*/

INSERT INTO `tb_users_register` (`data_id`, `user_id`, `user_type`, `user_hash`, `user_name`) VALUES
(1, '1', 0, NULL, NULL);