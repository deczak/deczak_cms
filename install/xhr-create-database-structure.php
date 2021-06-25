<?php

	error_reporting(E_ALL);
	ini_set('display_errors', true);

	include '../core/classes/toolkit.php';
	include '../config/directories.php';
	include '../config/standard.php';
	include '../core/classes/CSheme.php';
	include '../core/classes/CModel.php';
	include '../core/classes/CDatabase.php';
	include '../core/classes/CPackages.php';
	include '../core/classes/CMessages.php';

	if(empty($_POST['database-server'])) 	tk::xhrResult(1, 'Database server address not set');	else $_POST['database-server'] 	 = trim(strip_tags($_POST['database-server']));
	if(empty($_POST['database-user'])) 		tk::xhrResult(1, 'Database user name not set');			else $_POST['database-user'] 	 = trim(strip_tags($_POST['database-user']));
	if(empty($_POST['database-pass'])) 		tk::xhrResult(1, 'Database password not set');			else $_POST['database-pass'] 	 = trim(strip_tags($_POST['database-pass']));
	if(empty($_POST['database-database'])) 	tk::xhrResult(1, 'Database name not set');				else $_POST['database-database'] = trim(strip_tags($_POST['database-database']));

	$_pMessages		 =	CMessages::instance();
	$_pMessages		->	initialize(CMS_PROTOCOL_REPORTING, CMS_DEBUG_REPORTING);

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

	##	loop sheme dir, get all class names and include the files

	$shemeList = [];
	foreach($_dirIterator as $_dirItem)
	{
		if($_dirItem -> isDot() || $_dirItem -> getType() === 'dir')
			continue;

		include_once	'../core/shemes/'. $_dirItem -> getFilename();

		$shemeList[] = explode('.',$_dirItem -> getFilename())[0];
	}

	##	after this, because of extended shemes, create instances to create tables

	foreach($shemeList as $className)
	{
		$instanceKey = count($shemeInstance);
		$shemeInstance[$instanceKey] = new $className();

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

	foreach($shemeInstance as $instance)
	{
		foreach($instance -> m_seedList as $seed)
		{
			$shemeName	 = get_class($instance);
			$pModel  	 = new CModel($shemeName, 'dta'. $shemeName);
			$pModel 	-> insert($db, $seed);
		}
	}

	##	install default template

		$procPath = 'page-templates/';

		if(file_exists($procPath))
		{ 
			$dirIterator 	= new DirectoryIterator($procPath);
			foreach($dirIterator as $dirItem)
			{
				if($dirItem -> isDot() || $dirItem -> getType() === 'dir')
					continue;

				$filename = $dirItem -> getFilename();

				if($filename[0] === '.')
					continue;


				$pFInfo = new finfo(FILEINFO_MIME_TYPE); 

				$fileMime = $pFInfo -> file($procPath . $filename);

				if($fileMime !== 'application/zip')
					continue;

				$pPackages = new CPackages;
				$pPackages -> install($pDBInstance -> getConnection(CFG::GET() -> MYSQL -> PRIMARY_DATABASE), $procPath . $filename);



			}
		}




	tk::xhrResult(0, 'OK');
