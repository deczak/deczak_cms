<?php


	include '../core/toolkit.php';
	include '../config/directories.php';
	include '../config/standard.php';
	include '../core/classes/CSheme.php';

	if(empty($_POST['database-server'])) 	tk::xhrResult(1, 'Database server address not set');	else $_POST['database-server'] 	 = trim(strip_tags($_POST['database-server']));
	if(empty($_POST['database-user'])) 		tk::xhrResult(1, 'Database user name not set');			else $_POST['database-user'] 	 = trim(strip_tags($_POST['database-user']));
	if(empty($_POST['database-pass'])) 		tk::xhrResult(1, 'Database password not set');			else $_POST['database-pass'] 	 = trim(strip_tags($_POST['database-pass']));
	if(empty($_POST['database-database'])) 	tk::xhrResult(1, 'Database name not set');				else $_POST['database-database'] = trim(strip_tags($_POST['database-database']));

	$db = new mysqli($_POST['database-server'], $_POST['database-user'], $_POST['database-pass'], $_POST['database-database']);

	if (mysqli_connect_errno())
		tk::xhrResult(1, 'SQL error on connection - '. mysqli_connect_error());
	
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

		$shemeInstance[$instanceKey] -> dropTable($db);

		$errorMsg = '';
		if(!$shemeInstance[$instanceKey] -> createTable($db, $errorMsg))
		{
			tk::xhrResult(1, $errorMsg);
		}
	}

	foreach($shemeInstance as $instance)
	{
		$instance -> createTableConstraints($db);
	}

	tk::xhrResult(0, 'OK');

?>