<?php

	include '../core/toolkit.php';
	include '../config/standard.php';
	include '../config/directories.php';
	include '../core/classes/CSheme.php';
	include '../core/classes/CModel.php';
	include '../core/shemes/shemeSitemap.php';
	include '../core/classes/CHTAccess.php';

	if(empty($_POST['server-subpath'])) 	tk::xhrResult(1, 'Sub directory not set');				else $_POST['server-subpath'] 	 = trim(strip_tags($_POST['server-subpath']));

	if(empty($_POST['database-server'])) 	tk::xhrResult(1, 'Database server address not set');	else $_POST['database-server'] 	 = trim(strip_tags($_POST['database-server']));
	if(empty($_POST['database-user'])) 		tk::xhrResult(1, 'Database user name not set');			else $_POST['database-user'] 	 = trim(strip_tags($_POST['database-user']));
	if(empty($_POST['database-pass'])) 		tk::xhrResult(1, 'Database password not set');			else $_POST['database-pass'] 	 = trim(strip_tags($_POST['database-pass']));
	if(empty($_POST['database-database'])) 	tk::xhrResult(1, 'Database name not set');				else $_POST['database-database'] = trim(strip_tags($_POST['database-database']));


	$db = new mysqli($_POST['database-server'], $_POST['database-user'], $_POST['database-pass'], $_POST['database-database']);

	if (mysqli_connect_errno())
		tk::xhrResult(1, 'SQL error on connection - '. mysqli_connect_error());


	$configFile = file_get_contents('0-base');

	$configFile = str_replace('%%SUB_PATH%%',$_POST['server-subpath'], $configFile);

	file_put_contents('../data/htaccess/0-base', $configFile);


	$_pHTAccess  = new CHTAccess();
	$_pHTAccess -> generatePart4Backend();
	$_pHTAccess -> generatePart4Frontend($db);
	$_pHTAccess -> writeHTAccess();

	tk::xhrResult(0, 'OK');
?>