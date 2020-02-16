SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

INSERT INTO `tb_modules` (`module_id`, `module_location`, `module_controller`, `module_type`, `module_group`, `is_frontend`, `is_active`, `module_icon`, `module_name`, `create_time`, `create_by`, `update_time`, `update_by`) VALUES
(1, 'mod_loginForm', 'controllerLoginForm', 'core', 'Simple Modules', 1, 1, '&#xf2f6;', 'Login', %TIMESTAMP%, 1, 0, 0),
(2, 'mod_usersBackend', 'controllerUsersBackend', 'core', 'backend', 0, 1, '', 'Backend Users', %TIMESTAMP%, 1, 0, 0),
(3, 'mod_pages', 'controllerPages', 'core', 'backend', 0, 1, '', 'Page Editing', %TIMESTAMP%, 1, 0, 0),
(4, 'mod_SimpleText', 'controllerSimpleText', 'core', 'Simple Modules', 1, 1, '&#xf15c;', 'Text', %TIMESTAMP%, 1, 0, 0),
(5, 'mod_SimpleHeadline', 'controllerSimpleHeadline', 'core', 'Simple Modules', 1, 1, '&#xf1dc;', 'Headline', %TIMESTAMP%, 1, 0, 0),
(6, 'mod_RightGroups', 'controllerRightGroups', 'core', 'backend', 0, 1, '', 'Right Groups', %TIMESTAMP%, 1, 0, 0),
(7, 'mod_simpleSource', 'controllerSimpleSource', 'core', 'Simple Modules', 1, 1, '&#xf121;', 'Code', %TIMESTAMP%, 1, 0, 0),
(8, 'mod_LoginObjects', 'controllerLoginObjects', 'core', 'backend', 0, 1, '', 'Login Objects', %TIMESTAMP%, 1, 0, 0),
(9, 'mod_deniedRemote', 'controllerDeniedRemote', 'core', 'backend', 0, 1, '', 'Denied Remote Address', %TIMESTAMP%, 1, 0, 0),
(10, 'mod_userAgent', 'controllerUserAgent', 'core', 'backend', 0, 1, '', 'User Agents', %TIMESTAMP%, 1, 0, 0),
(11, 'mod_sessions', 'controllerSessions', 'core', 'backend', 0, 1, '', 'Sessions', %TIMESTAMP%, 1, 0, 0),
(12, 'mod_modules', 'controllerModules', 'core', 'backend', 0, 1, '', 'Modules', %TIMESTAMP%, 1, 0, 0),
(13, 'mod_languages', 'controllerLanguages', 'core', 'backend', 0, 1, '&#xf1ab;', 'Languages', %TIMESTAMP%, 1, 0, 0),
(14, 'mod_environment', 'controllerEnvironment', 'core', 'backend', 0, 1, '', 'Environment', %TIMESTAMP%, 1, 0, 0),
(15, 'mod_users', 'controllerUsers', 'core', 'backend', 0, 1, '', 'Users', %TIMESTAMP%, 1, 0, 0),
(16, 'mod_categories', 'controllerCategories', 'core', 'backend', 0, 1, '', 'Categories', %TIMESTAMP%, 1, 0, 0),
(17, 'mod_tags', 'controllerTags', 'core', 'backend', 0, 1, '', 'Tags', %TIMESTAMP%, 1, 0, 0);

INSERT INTO `tb_languages` (`data_id`, `lang_key`, `lang_name`, `lang_name_native`, `lang_hidden`, `lang_locked`, `lang_default`, `lang_frontend`, `lang_backend`, `create_time`, `create_by`, `update_time`, `update_by`) VALUES
(1, 'en', 'English', 'English', 0, 0, 1, 1, 1, %TIMESTAMP%, 1, 0, 0),
(2, 'de', 'German', 'Deutsch', 1, 0, 0, 1, 1, %TIMESTAMP%, 1, 0, 0);

INSERT INTO `tb_login_objects` (`data_id`, `object_id`, `object_databases`, `object_table`, `object_fields`, `object_session_ext`, `object_description`, `is_disabled`, `is_protected`, `create_time`, `create_by`, `update_time`, `update_by`) VALUES
(12, 'ABKND', '["1"]', 'tb_users_backend', '[{"name":"login_name","data_prc":"crypt","type":"text","is_username":"0"},{"name":"login_pass","data_prc":"hash","type":"password","is_username":"1"}]', '{"1":{"name":"user_name_last","data_prc":"crypt"},"2":{"name":"language","data_prc":"text"}}', 'Backend Access', 0, 1, %TIMESTAMP%, 1, 0, 0);

INSERT INTO `tb_page` (`data_id`, `node_id`, `page_id`, `page_version`, `page_template`, `update_reason`, `hidden_state`, `cache_disabled`, `crawler_index`, `crawler_follow`, `menu_follow`, `publish_from`, `publish_until`, `publish_expired`, `page_auth`, `create_time`, `create_by`, `update_time`, `update_by`) VALUES
(1, 2, 1, 1, 'default', '', 0, 0, 1, 1, 1, 0, 0, 0, NULL, %TIMESTAMP%, 1, 0, 0),
(2, 3, 1, 1, 'default', '', 0, 0, 1, 1, 1, 0, 0, 0, '0', %TIMESTAMP%, 1, 0, 0);

INSERT INTO `tb_page_header` (`data_id`, `node_id`, `page_id`, `page_language`, `page_title`, `page_name`, `page_description`, `page_version`) VALUES
(1, 2, 1, 'en', 'Home', 'Home ', 'Home Description', 1),
(2, 3, 1, 'de', 'Startseite', 'Startseite', 'Startseiten Beschreibung', 1);

INSERT INTO `tb_page_path` (`node_id`, `page_id`, `page_language`, `page_path`, `node_rgt`, `node_lft`) VALUES
(1, 0, '0', '', 6, 1),
(2, 1, 'en', '/', 3, 2),
(3, 1, 'de', '/', 5, 4);

INSERT INTO `tb_page_object` (`object_id`, `node_id`, `page_version`, `module_id`, `object_order_by`, `update_reason`, `create_time`, `create_by`, `update_time`, `update_by`) VALUES
(1, 2, 1, 4, 2, '', %TIMESTAMP%, 1, 0, 0),
(2, 2, 1, 5, 1, '', %TIMESTAMP%, 1, 0, 0),
(3, 3, 1, 5, 1, '', %TIMESTAMP%, 1, 0, 0),
(4, 3, 1, 4, 2, '', %TIMESTAMP%, 1, 0, 0);

INSERT INTO `tb_page_object_simple` (`data_id`, `object_id`, `body`, `params`) VALUES
(1, 1, 'For more information and the documentation about the content management system see <a href="https://www.dennczak.de/en/projects/content-managment-system/" style="">this website</a>&nbsp;(external link)', ''),
(2, 2, '<h1>Welcome to your new<p>Content Management System<br></p></h1>', ''),
(3, 3, '<h1>Willkommen zu Ihrem neuen<p>Content Management System</p></h1>', ''),
(4, 4, 'Für weitere Informationen und die Dokumentation zu diesem Content Management System schauen Sie auf <a href="https://www.dennczak.de/projekte/content-managment-system/">diese Website</a> (externer Link)', '');

INSERT INTO `tb_right_groups` (`group_id`, `group_name`, `group_rights`, `create_time`, `create_by`, `update_time`, `update_by`) VALUES
(1, 'administrator', '{"1":["view","create","edit","delete"],"2":["index","view","create","edit","delete"],"3":["index","view","create","edit","delete"],"4":["view","create","edit","delete"],"5":["view","create","edit","delete"],"6":["index","view","create","edit","delete"],"7":["view","create","edit","delete"],"8":["index","view","create","edit","delete"],"9":["index","view","create","edit","delete"],"10":["index","view","create","edit","delete"],"11":["index","view","edit","delete"],"12":["index","view","create","edit","delete"],"13":["index","view","create","edit","delete"],"14":["index","edit"],"15":["index","view","create","edit","delete"],"16":["index","view","create","edit","delete"],"17":["index","view","create","edit","delete"]}', %TIMESTAMP%, 1, 0, 0);

INSERT INTO `tb_users_backend` (`data_id`, `login_name`, `login_pass`, `login_count`, `user_id`, `user_name_first`, `user_name_last`, `user_mail`, `time_login`, `create_time`, `update_time`, `cookie_id`, `recover_key`, `recover_timeout`, `is_locked`,`language`) VALUES
(1, '%USER_NAME%', '%USER_PASSWORD%', 0, '1', '%USER_FIRST_NAME%', '%USER_LAST_NAME%', '%USER_MAIL%', 0, %TIMESTAMP%, 0, '{}', 0, 0, 0,'en');

INSERT INTO `tb_users_groups` (`data_id`, `user_id`, `group_id`) VALUES
(1, '1', 1);