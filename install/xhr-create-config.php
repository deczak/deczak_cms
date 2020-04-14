<?php

	include '../core/toolkit.php';

	if(empty($_POST['server-root'])) 		tk::xhrResult(1, 'Document root not set');				else $_POST['server-root'] 		 = trim(strip_tags($_POST['server-root']));
	if(empty($_POST['server-url'])) 		tk::xhrResult(1, 'Web URL address not set');			else $_POST['server-url'] 		 = trim(strip_tags($_POST['server-url']));
	if(empty($_POST['crypt-basekey'])) 		tk::xhrResult(1, 'Base encryption key not set');		else $_POST['crypt-basekey'] 	 = trim(strip_tags($_POST['crypt-basekey']));

	if(empty($_POST['database-server'])) 	tk::xhrResult(1, 'Database server address not set');	else $_POST['database-server'] 	 = trim(strip_tags($_POST['database-server']));
	if(empty($_POST['database-user'])) 		tk::xhrResult(1, 'Database user name not set');			else $_POST['database-user'] 	 = trim(strip_tags($_POST['database-user']));
	if(empty($_POST['database-pass'])) 		tk::xhrResult(1, 'Database password not set');			else $_POST['database-pass'] 	 = trim(strip_tags($_POST['database-pass']));
	if(empty($_POST['database-database'])) 	tk::xhrResult(1, 'Database name not set');				else $_POST['database-database'] = trim(strip_tags($_POST['database-database']));
	
	if($_POST['server-root'][ strlen($_POST['server-root']) - 1] !== '/') $_POST['server-root'] .= '/';
	if($_POST['server-url'][ strlen($_POST['server-url']) - 1] !== '/') $_POST['server-url'] .= '/';

	##	standard.php
	$configFile = file_get_contents('template-config.php');

	$configFile = str_replace('%SERVER_ROOT%',$_POST['server-root'], $configFile);
	$configFile = str_replace('%SERVER_URL%', $_POST['server-url'], $configFile);

	$configFile = str_replace('%DATABASE_SERVER%',$_POST['database-server'], $configFile);
	$configFile = str_replace('%DATABASE_USER%',$_POST['database-user'], $configFile);
	$configFile = str_replace('%DATABASE_PASSWORD%',$_POST['database-pass'], $configFile);
	$configFile = str_replace('%DATABASE_DATABASE%',$_POST['database-database'], $configFile);

	$configFile = str_replace('%BASEKEY%',$_POST['crypt-basekey'], $configFile);

	$configFile = str_replace('%COOKIE_HTTPS%', (TK::isSSL() ? 'true' : 'false'), $configFile);

	file_put_contents('../config/standard.php', $configFile);


	##	configuration.json

	$configFile = file_get_contents('configuration.json');

	$configFile = str_replace('%SYSMAIL_NAME%',$_POST['mail-name'], $configFile);
	$configFile = str_replace('%SYSMAIL_MAIL%',$_POST['mail-mail'], $configFile);

	file_put_contents('../data/configuration.json', $configFile);


	tk::xhrResult(0, 'OK');
?>