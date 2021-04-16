INSERT INTO `tb_page` (`data_id`, `node_id`, `page_id`, `page_version`, `page_template`, `update_reason`, `hidden_state`, `cache_disabled`, `crawler_index`, `crawler_follow`, `menu_follow`, `publish_from`, `publish_until`, `publish_expired`, `page_auth`, `create_time`, `create_by`, `update_time`, `update_by`) VALUES
(2, 3, 1, 1, 'default', '', 0, 0, 1, 1, 1, 0, 0, 0, '0', %TIMESTAMP%, 1, 0, 0);

INSERT INTO `tb_page_path` (`node_id`, `page_id`, `page_language`, `page_path`, `node_rgt`, `node_lft`) VALUES
(3, 1, 'de', '/', 5, 4);

INSERT INTO `tb_page_header` (`data_id`, `node_id`, `page_id`, `page_language`, `page_title`, `page_name`, `page_description`, `page_version`) VALUES
(2, 3, 1, 'de', 'Startseite', 'Startseite', 'Startseiten Beschreibung', 1);

INSERT INTO `tb_page_object` (`object_id`, `node_id`, `page_version`, `module_id`, `content_id`, `object_order_by`, `update_reason`, `create_time`, `create_by`, `update_time`, `update_by`) VALUES
(1, 2, 1, 4, 1, 2, '', %TIMESTAMP%, 1, 0, 0),
(2, 2, 1, 5, 1, 1, '', %TIMESTAMP%, 1, 0, 0),
(3, 3, 1, 5, 1, 1, '', %TIMESTAMP%, 1, 0, 0),
(4, 3, 1, 4, 1, 2, '', %TIMESTAMP%, 1, 0, 0);

INSERT INTO `tb_page_object_simple` (`data_id`, `object_id`, `body`, `params`) VALUES
(1, 1, '<p>For more information and the documentation about the content management system see <a href="https://www.dennczak.de/en/projects/content-management-system/" style="">this website</a>&nbsp;(external link)</p>', ''),
(2, 2, '<h1>Welcome to your new<p>Content Management System<br></p></h1>', ''),
(3, 3, '<h1>Willkommen zu Ihrem neuen<p>Content Management System</p></h1>', ''),
(4, 4, '<p>Für weitere Informationen und die Dokumentation zu diesem Content Management System schauen Sie auf <a href="https://www.dennczak.de/projekte/content-management-system/">diese Website</a> (externer Link)</p>', '');
