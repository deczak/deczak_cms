SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

INSERT INTO `tb_page` (`data_id`, `page_id`, `node_id`, `page_version`, `page_template`, `time_create`, `time_update`, `create_by`, `update_by`, `update_reason`) VALUES
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

INSERT INTO `tb_page_object` (`node_id`, `page_version`, `module_id`, `object_id`, `object_order_by`, `time_create`, `time_update`, `create_by`, `update_by`, `update_reason`) VALUES
(4, 1, 'M5', 1, 1, 0, 0, 0, 0, ''),
(2, 1, 'M4', 3, 2, 0, 0, 0, 0, ''),
(2, 1, 'M5', 4, 1, 0, 0, 0, 0, ''),
(3, 1, 'M5', 6, 1, 0, 0, 0, 0, ''),
(2, 1, 'M4', 8, 4, 0, 0, 0, 0, ''),
(2, 1, 'M5', 9, 3, 0, 0, 0, 0, ''),
(2, 1, 'M5', 10, 5, 0, 0, 0, 0, ''),
(2, 1, 'M4', 11, 6, 0, 0, 0, 0, '');

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

INSERT INTO `tb_right_groups` (`group_id`, `group_name`, `group_rights`) VALUES
(1, 'administrator', '{"M1":["create","edit","delete"],"M2":["create","edit","delete","view"],"M3":["create","edit","delete","deletetree","view"],"M4":["create","edit","delete"],"M5":["create","edit","delete"],"M6":["create","edit","delete","view"]}');

INSERT INTO `tb_users_backend` (`data_id`, `login_name`, `login_pass`, `login_count`, `user_id`, `user_name_first`, `user_name_last`, `user_mail`, `time_login`, `time_create`, `time_update`, `cookie_id`, `recover_key`, `recover_timeout`, `is_locked`) VALUES
(1, 'b2p3amNlc3lIOEE5cHliNDdLbHVodz09', '5685477bbe00722fd06506b4f0803cd649644a29711815557e54007ec39cf5fc3293add371ab7609c96c731a999aa886c5520e5d31bc4b5e79b4c325da19e88f2394', 0, '1', 'WExNMk5DTUNjczVYa0pERlFWMUZrdz09', 'WExNMk5DTUNjczVYa0pERlFWMUZrdz09', 'WExNMk5DTUNjczVYa0pERlFWMUZrdz09', 0, 0, 0, '{}', NULL, 0, 0);

INSERT INTO `tb_users_groups` (`data_id`, `user_id`, `group_id`) VALUES
(1, '1', 1);