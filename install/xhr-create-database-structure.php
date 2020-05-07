<?php


	error_reporting(E_ALL);
	ini_set('display_errors', true);

	include '../core/toolkit.php';
	include '../config/directories.php';
	include '../config/standard.php';
	include '../core/classes/CSheme.php';
	include '../core/classes/CDatabase.php';
	include '../core/classes/CMessages.php';

	if(empty($_POST['database-server'])) 	tk::xhrResult(1, 'Database server address not set');	else $_POST['database-server'] 	 = trim(strip_tags($_POST['database-server']));
	if(empty($_POST['database-user'])) 		tk::xhrResult(1, 'Database user name not set');			else $_POST['database-user'] 	 = trim(strip_tags($_POST['database-user']));
	if(empty($_POST['database-pass'])) 		tk::xhrResult(1, 'Database password not set');			else $_POST['database-pass'] 	 = trim(strip_tags($_POST['database-pass']));
	if(empty($_POST['database-database'])) 	tk::xhrResult(1, 'Database name not set');				else $_POST['database-database'] = trim(strip_tags($_POST['database-database']));




	$_pMessages		 =	CMessages::instance();
	$_pMessages		->	initialize(CMS_PROTOCOL_REPORTING, CMS_DEBUG_REPORTING);
/*
	$db = new mysqli($_POST['database-server'], $_POST['database-user'], $_POST['database-pass'], $_POST['database-database']);

	if (mysqli_connect_errno())
		tk::xhrResult(1, 'SQL error on connection - '. mysqli_connect_error());
*/	


	$databases 	 = 	[];
	$databases[] = 	[
						'server'	=> $_POST['database-server'],
						'database'	=> $_POST['database-database'],
						'user'		=> $_POST['database-user'],
						'password'	=> $_POST['database-pass'],
						'name'		=> 'primary'
					];

	$pDBInstance 	 = CDatabase::instance();
	if(!$pDBInstance -> connect($databases))
		tk::xhrResult(1, 'DB connect error');
	
	$db = $pDBInstance -> getConnection('primary');




	$shemeInstance 	= [];
	$_dirIterator 	= new DirectoryIterator('../core/shemes/');

	foreach($_dirIterator as $_dirItem)
	{
		if($_dirItem -> isDot() || $_dirItem -> getType() === 'dir')
			continue;

		include	'../core/shemes/'. $_dirItem -> getFilename();

		$className = explode('.',$_dirItem -> getFilename())[0];

		$instanceKey = count($shemeInstance);
		$shemeInstance[$instanceKey] = new $className();


		// error on costraints
		#$db -> query("SET FOREIGN_KEY_CHECKS=0");
		#$shemeInstance[$instanceKey] -> dropTable($db);
		#$db -> query("SET FOREIGN_KEY_CHECKS=1");

		$errorMsg = '';
		if(!$shemeInstance[$instanceKey] -> createTable($db, $errorMsg))
		{
			tk::xhrResult(1, $errorMsg);
		}
	}

	foreach($shemeInstance as $instance)
	{
		$instance -> createConstraints($db);
	}

	tk::xhrResult(0, 'OK');

?>