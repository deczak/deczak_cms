SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

INSERT INTO `tb_modules` (`module_id`, `module_location`, `module_controller`, `module_type`, `module_group`, `is_frontend`, `is_active`, `module_icon`, `module_name`, `create_time`, `create_by`, `update_time`, `update_by`) VALUES
(1, 'mod_loginForm', 'controllerLoginForm', 'core', 'Simple Modules', 1, 1, '&#xf2f6;', 'Login', 0, 1, 0, 0),
(2, 'mod_usersBackend', 'controllerUsersBackend', 'core', 'backend', 0, 1, '', 'Backend Users', 0, 1, 0, 0),
(3, 'mod_pages', 'controllerPages', 'core', 'backend', 0, 1, '', 'Page Editing', 0, 1, 0, 0),
(4, 'mod_SimpleText', 'controllerSimpleText', 'core', 'Simple Modules', 1, 1, '&#xf15c;', 'Text', 0, 1, 0, 0),
(5, 'mod_SimpleHeadline', 'controllerSimpleHeadline', 'core', 'Simple Modules', 1, 1, '&#xf1dc;', 'Headline', 0, 1, 0, 0),
(6, 'mod_RightGroups', 'controllerRightGroups', 'core', 'backend', 0, 1, '', 'Right Groups', 0, 1, 0, 0),
(7, 'mod_simpleSource', 'controllerSimpleSource', 'core', 'Simple Modules', 1, 1, '&#xf121;', 'Code', 0, 1, 0, 0),
(8, 'mod_LoginObjects', 'controllerLoginObjects', 'core', 'backend', 0, 1, '', 'Login Objects', 0, 1, 0, 0),
(9, 'mod_deniedRemote', 'controllerDeniedRemote', 'core', 'backend', 0, 1, '', 'Denied Remote Address', 0, 1, 0, 0),
(10, 'mod_userAgent', 'controllerUserAgent', 'core', 'backend', 0, 1, '', 'User Agents', 0, 1, 0, 0),
(11, 'mod_sessions', 'controllerSessions', 'core', 'backend', 0, 1, '', 'Sessions', 0, 1, 0, 0),
(12, 'mod_modules', 'controllerModules', 'core', 'backend', 0, 1, '', 'Modules', 0, 1, 0, 0);
(13, 'mod_languages', 'controllerLanguages', 'core', 'backend', 0, 1, '&#xf1ab;', 'Languages', 0, 1, 0, 0);

INSERT INTO `tb_languages` (`data_id`, `lang_key`, `lang_name`, `lang_name_native`, `lang_hidden`, `lang_locked`, `lang_default`, `lang_fontend`, `lang_backend`, `create_time`, `create_by`, `update_time`, `update_by`) VALUES
(1, 'en', 'English', 'English', 0, 0, 1, 1, 1, 0, 0, 0, 1),
(1, 'de', 'German', 'Deutsch', 0, 0, 1, 1, 1, 0, 0, 0, 1);

INSERT INTO `tb_login_objects` (`data_id`, `object_id`, `object_databases`, `object_table`, `object_fields`, `object_session_ext`, `object_description`, `is_disabled`, `is_protected`, `create_time`, `create_by`, `update_time`, `update_by`) VALUES
(12, 'ABKND', '["1"]', 'tb_users_backend', '[{"name":"login_name","data_prc":"crypt","type":"text","is_username":"0"},{"name":"login_pass","data_prc":"hash","type":"password","is_username":"1"}]', '{"1":{"name":"user_name_last","data_prc":"crypt"},"2":{"name":"language","data_prc":"text"}}', 'Backend Access', 0, 1, 1569299590, 22, 1569903496, 0);


INSERT INTO `tb_page` (`data_id`, `page_id`, `node_id`, `page_version`, `page_template`, `create_time`, `update_time`, `create_by`, `update_by`, `update_reason`) VALUES
(1, 1, 2, 1, 'default', 0, 0, 0, 0, ''),
(2, 2, 3, 1, 'default', 0, 0, 0, 0, ''),
(3, 3, 4, 1, 'default', 0, 0, 0, 0, ''),
(4, 4, 5, 1, 'default', 0, 0, 0, 0, ''),
(5, 5, 6, 1, 'default', 0, 0, 0, 0, '');

INSERT INTO `tb_page_header` (`data_id`, `page_id`, `node_id`, `page_language`, `page_title`, `page_name`, `page_description`, `page_version`) VALUES
(1, 1, 2, 'en', 'Home', 'Home ', 'Home Description', 1),
(2, 2, 3, 'en', 'Page A', 'Page A', '', 1),
(3, 3, 4, 'en', 'Sub page of A', 'Sub page of A', '', 1),
(4, 4, 5, 'en', 'Page B', 'Page B', '', 1),
(5, 5, 6, 'en', 'Sub page of B', 'Sub page of B', '', 1);

INSERT INTO `tb_page_object` (`node_id`, `page_version`, `module_id`, `object_id`, `object_order_by`, `create_time`, `update_time`, `create_by`, `update_by`, `update_reason`) VALUES
(4, 1, '5', 1, 1, 0, 0, 0, 0, ''),
(2, 1, '4', 3, 2, 0, 0, 0, 0, ''),
(2, 1, '5', 4, 1, 0, 0, 0, 0, ''),
(3, 1, '5', 6, 1, 0, 0, 0, 0, ''),
(2, 1, '4', 8, 4, 0, 0, 0, 0, ''),
(2, 1, '5', 9, 3, 0, 0, 0, 0, ''),
(2, 1, '5', 10, 5, 0, 0, 0, 0, ''),
(2, 1, '4', 11, 6, 0, 0, 0, 0, '');

INSERT INTO `tb_page_object_simple` (`data_id`, `object_id`, `body`, `params`) VALUES
(1, 1, '<h1>This is a headline<br></h1>', ''),
(3, 3, '<span title="">If everything went well, you should now have a working website in front of you.</span> <span title="">The layout of this page is a simple standard template that you can edit with an HTML editor.</span> <span title="">At the top left you see the title of the entire pages which is identical on all pages.</span> <span title="" class="">Below that is the breadcrumb menu.</span> <span title="">And on the right the main menu with language selection.</span><br><br><span title="" class="">At the beginning only the english language is activated.</span> <span title="" class="">Although the system supports multilingualism, this is somewhat impractical in page creation and therefore disabled.<br></span></span><br><span class="tlid-translation translation" lang="en"><span title="" class=""><span class="tlid-translation translation" lang="en"><span title="">At the beginning there are 2 standard modules</span><br></span></span></span><ul><li><span class="tlid-translation translation" lang="en"><span title="" class=""><span class="tlid-translation translation" lang="en"><span class="tlid-translation translation" lang="en"><span title="" class=""><span class="tlid-translation translation" lang="en"><span title="" class="">Headline module</span></span></span></span></span></span></span></li></ul><ul><li><span class="tlid-translation translation" lang="en"><span title="" class=""><span class="tlid-translation translation" lang="en"><span class="tlid-translation translation" lang="en"><span title="" class=""><span class="tlid-translation translation" lang="en"><span title="" class=""></span></span></span></span><span title="">Text module</span></span></span></span></li></ul><span class="tlid-translation translation" lang="en"><span title="" class=""><span class="tlid-translation translation" lang="en"> <span title="" class="">The login module can be inserted, but it has no function because a lot has to be changed here.</span></span></span>', ''),
(4, 4, '<h1>Welcome to your new CMS<br></h1>', ''),
(6, 6, '<h1>This is a headline<br></h1>', ''),
(8, 8, '<span title="">The area for the administration is called Backend by me.</span> <span title="">If you see a sign up there is a blank page, it will probably be the dashboard.</span> <span title="">There later information should be found, for example about the current status of the CMS.</span><br><br><span title="">There are currently three areas available there:</span><br><br></span><ul><li><span class="tlid-translation translation" lang="en"><span title="" class=""><b>Right groups</b><br><br>Here you can create rights groups that are currently only relevant for backend users.</span> <span title="">The rights of the modules are always opt-in.</span> <span title="" class="">If a right is not granted, the user can not use it.<br><br></span></span></li><li><span class="tlid-translation translation" lang="en"><span title="" class=""><b>Sites</b><br><br>Here you can create, edit or delete pages.</span> <span title="">What is currently not possible is moving pages.</span> <span title="">You can not switch to another language right now either. </span></span><span class="tlid-translation translation" lang="en"><span title=""><span class="tlid-translation translation" lang="en"><span title="">Small note: Do not refresh the page after creating a page.</span> <span title="" class="">Otherwise you will create another page.<br><br></span></span></span></span></li><li><span class="tlid-translation translation" lang="en"><span title="" class=""><b>Backend users</b><br><br>Here you can create users who are allowed to log in to the backend.</span> <span title="" class="">In this case, appropriate rights groups must be assigned.</span> <span title="" class="">A user can be assigned to multiple groups.<br><br></span></li></ul>', ''),
(9, 9, '<h1>The Backend<br></h1>', ''),
(10, 10, '<h1>Page editing<br></h1>', ''),
(11, 11, '<span title="">In page editing you will see a small dotted frame, this is your content area.</span> <span title="">Currently only one area is possible.</span> <span title="">The plus buttons open a menu with which you can insert modules.</span> <span title="">Please ignore the login module.</span> <span title="">The whole is not perfect, with the headline module it could happen that you see the placeholder text even though you entered something.</span> <span title="">Theoretically, you can use HTML in the modules, but this is not intended and will be changed later.</span> <span title="">For HTML & Co a separate module should be used later.</span><br><br>Every changes requires a click on the floppy disk icon to save those!<br><br><span title="" class="">And if you look closely, you\'ll see a yellow bar on the left that you can click on.</span> <span title="" class="">What you see then is not pretty and can not do much.</span> <span title="">But at least you can change the page name and description etc.</span> <span title="" class="">Page name is what you see in the URL, page title is what you see in the tab as page name.</span> <span title="" class="">These two details are mandatory (currently no feedback if not valid)<br><br><br></span>', '');

INSERT INTO `tb_page_path` (`page_id`, `page_language`, `page_path`, `node_id`, `node_lft`, `node_rgt`) VALUES
(0, '0', '', 1, 1, 12),
(1, 'en', '/', 2, 2, 11),
(2, 'en', 'page-a/', 3, 3, 6),
(3, 'en', 'sub-page-of-a/', 4, 4, 5),
(4, 'en', 'page-b/', 5, 7, 10),
(5, 'en', 'sub-page-of-b/', 6, 8, 9);

INSERT INTO `tb_right_groups` (`group_id`, `group_name`, `group_rights`, `create_time`, `create_by`, `update_time`, `update_by`) VALUES
(1, 'administrator', '{"1":["view","create","edit","delete"],"2":["index","view","create","edit","delete"],"3":["index","view","create","edit","delete"],"4":["view","create","edit","delete"],"5":["view","create","edit","delete"],"6":["index","view","create","edit","delete"],"7":["view","create","edit","delete"],"8":["index","view","create","edit","delete"],"9":["index","view","create","edit","delete"],"10":["index","view","create","edit","delete"],"11":["index","view","edit","delete"],"12":["index","view","create","edit","delete"],"35":["index","view","create","edit","delete"]}', 0, 22, 0, 0);

INSERT INTO `tb_users_backend` (`data_id`, `login_name`, `login_pass`, `login_count`, `user_id`, `user_name_first`, `user_name_last`, `user_mail`, `time_login`, `create_time`, `update_time`, `cookie_id`, `recover_key`, `recover_timeout`, `is_locked`,`language`) VALUES
(1, '%USER_NAME%', '%USER_PASSWORD%', 0, '1', '%USER_FIRST_NAME%', '%USER_LAST_NAME%', '%USER_MAIL%', 0, 0, 0, '{}', 0, 0, 0,'en');

INSERT INTO `tb_users_groups` (`data_id`, `user_id`, `group_id`) VALUES
(1, '1', 1);