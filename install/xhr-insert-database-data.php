<?php

	include '../core/toolkit.php';
	include '../config/standard.php';

	if(empty($_POST['database-server'])) 	tk::xhrResult(1, 'Database server address not set');	else $_POST['database-server'] 	 = trim(strip_tags($_POST['database-server']));
	if(empty($_POST['database-user'])) 		tk::xhrResult(1, 'Database user name not set');			else $_POST['database-user'] 	 = trim(strip_tags($_POST['database-user']));
	if(empty($_POST['database-pass'])) 		tk::xhrResult(1, 'Database password not set');			else $_POST['database-pass'] 	 = trim(strip_tags($_POST['database-pass']));
	if(empty($_POST['database-database'])) 	tk::xhrResult(1, 'Database name not set');				else $_POST['database-database'] = trim(strip_tags($_POST['database-database']));

	if(empty($_POST['user-user'])) 			tk::xhrResult(1, 'User name not set');					else $_POST['user-user'] 	 	 = trim(strip_tags($_POST['user-user']));
	if(empty($_POST['user-pass'])) 			tk::xhrResult(1, 'User password not set');				else $_POST['user-pass'] 		 = trim(strip_tags($_POST['user-pass']));

	$_POST['user-first-name'] 	= '';
	$_POST['user-last-name'] 	= '';
	$_POST['user-mail'] 		= '';


	$_POST['user-user']			= CRYPT::LOGIN_HASH($_POST['user-user']);
	$_POST['user-pass']			= CRYPT::LOGIN_CRYPT($_POST['user-pass'], ENCRYPTION_BASEKEY);
	$_POST['user-first-name']	= CRYPT::ENCRYPT($_POST['user-first-name'], '1', true);
	$_POST['user-last-name']	= CRYPT::ENCRYPT($_POST['user-last-name'], '1', true);
	$_POST['user-mail']			= CRYPT::ENCRYPT($_POST['user-mail'], '1', true);

	$db = new mysqli($_POST['database-server'], $_POST['database-user'], $_POST['database-pass'], $_POST['database-database']);

	if (mysqli_connect_errno())
		tk::xhrResult(1, 'SQL error on connection - '. mysqli_connect_error());

	$sqlDump = file_get_contents('database-data.sql');

	$sqlDump = str_replace('%%USER_NAME%%',$_POST['user-user'], $sqlDump);
	$sqlDump = str_replace('%%USER_PASSWORD%%',$_POST['user-pass'], $sqlDump);
	$sqlDump = str_replace('%%USER_FIRST_NAME%%',$_POST['user-first-name'], $sqlDump);
	$sqlDump = str_replace('%%USER_LAST_NAME%%',$_POST['user-last-name'], $sqlDump);
	$sqlDump = str_replace('%%USER_MAIL%%',$_POST['user-mail'], $sqlDump);

	if( $db -> multi_query($sqlDump) === FALSE)
		tk::xhrResult(1, 'SQL error on query - '. $db -> error);


sleep(5); // its required, multi_query returns ok even if the server still not finished with all 

	tk::xhrResult(0, 'OK');

?>