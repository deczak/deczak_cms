<?php

	error_reporting(E_ALL);
	ini_set('display_errors', true);
	
	include_once '../core/classes/toolkit.php';
	include_once '../config/directories.php';
	include_once '../config/standard.php';
	include_once '../core/classes/CDatabase.php';
	include_once '../core/classes/CMessages.php';
	include_once '../core/classes/CURLVariables.php';
	include_once '../core/classes/CSheme.php';
	include_once '../core/classes/CModel.php';
	include_once '../core/classes/CView.php';
	include_once '../core/classes/CController.php';
	include_once '../core/classes/CPageRequest.php';
	include_once '../core/classes/CSession.php';
	include_once '../core/classes/CUserRights.php';
	include_once '../core/classes/CLanguage.php';
	include_once '../core/classes/CRouter.php';
	include_once '../core/classes/CHTAccess.php';
	include_once '../core/classes/CModules.php';

	if(empty($_POST['database-server'])) 	tk::xhrResult(1, 'Database server address not set');	else $_POST['database-server'] 	 = trim(strip_tags($_POST['database-server']));
	if(empty($_POST['database-user'])) 		tk::xhrResult(1, 'Database user name not set');			else $_POST['database-user'] 	 = trim(strip_tags($_POST['database-user']));
	if(empty($_POST['database-pass'])) 		tk::xhrResult(1, 'Database password not set');			else $_POST['database-pass'] 	 = trim(strip_tags($_POST['database-pass']));
	if(empty($_POST['database-database'])) 	tk::xhrResult(1, 'Database name not set');				else $_POST['database-database'] = trim(strip_tags($_POST['database-database']));

	if(empty($_POST['user-user'])) 			tk::xhrResult(1, 'User name not set');					else $_POST['user-user'] 	 	 = trim(strip_tags($_POST['user-user']));
	if(empty($_POST['user-pass'])) 			tk::xhrResult(1, 'User password not set');				else $_POST['user-pass'] 		 = trim(strip_tags($_POST['user-pass']));

	$_pMessages		 =	CMessages::instance();
	$_pMessages		->	initialize(CMS_PROTOCOL_REPORTING, CMS_DEBUG_REPORTING);

	$_POST['user-first-name'] 	= '';
	$_POST['user-last-name'] 	= '';
	$_POST['user-mail'] 		= '';

	$_POST['user-user']			= CRYPT::LOGIN_HASH($_POST['user-user']);
	$_POST['user-pass']			= CRYPT::LOGIN_CRYPT($_POST['user-pass'], CFG::GET() -> ENCRYPTION -> BASEKEY);
	$_POST['user-first-name']	= CRYPT::ENCRYPT($_POST['user-first-name'], '1', true);
	$_POST['user-last-name']	= CRYPT::ENCRYPT($_POST['user-last-name'], '1', true);
	$_POST['user-mail']			= CRYPT::ENCRYPT($_POST['user-mail'], '1', true);

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

	$_pLanguage		 = 	CLanguage::instance();		
	$_pLanguage		-> 	initialize($db);

	$defLangList = new stdClass;
	$defLangList -> DEFAULT_IN_URL		=	false;
	$defLangList -> BACKEND			=	[					
										"en" =>	[
												"key"		=>	"en",
												"name"		=>	"English"
												],
										"de" =>	[
												"key"		=>	"de",
												"name"		=>	"Deutsch"
												]							
										]; 	

	$pRouter  = CRouter::instance();
	$pRouter -> initialize(CFG::GET() -> LANGUAGE, CLanguage::instance() -> getLanguages());

	$db -> beginTransaction();

	$seedDir = 'seeds';

	##	Base Data

	$sqlDump = file_get_contents($seedDir.'/1-base.sql');

	$sqlDump = str_replace('%USER_NAME%',$_POST['user-user'], $sqlDump);
	$sqlDump = str_replace('%USER_PASSWORD%',$_POST['user-pass'], $sqlDump);
	$sqlDump = str_replace('%USER_FIRST_NAME%',$_POST['user-first-name'], $sqlDump);
	$sqlDump = str_replace('%USER_LAST_NAME%',$_POST['user-last-name'], $sqlDump);
	$sqlDump = str_replace('%USER_MAIL%',$_POST['user-mail'], $sqlDump);

	$sqlDump = str_replace('%TIMESTAMP%',time(), $sqlDump);

	try
	{
		$db -> getConnection() -> exec($sqlDump);
	}
	catch (PDOException $exception)
	{
		$db -> rollBack();
		tk::xhrResult(1, 'SQL error on query - '. $exception -> getMessage());
	}

	$db -> commit();

	##	Install core modules

	$pUserRights = new CUserRights;

	$pModules = CModules::instance();
	$pModules -> initialize($db, $pUserRights);

	$avaiableModules = $pModules -> getAvailableModules();
	$retryInstallList = [];
	foreach($avaiableModules as $module)
	{
		if($module -> module -> module_type == 'core')
		{
			$installModule  = new stdClass;
			$installModule -> location 	= $module -> module -> module_location;
			$installModule -> type		= $module -> module -> module_type;
	
			if(property_exists($module, 'objects') && $module -> objects !== false)
			{
				foreach($module -> objects as $object)
				{
					if(!property_exists($object, 'controller'))
						continue;

					if(empty($object -> controller))
						break; 

					$moduleCondition  = new CModelCondition();
					$moduleCondition -> where('module_controller', $object -> controller);

					$modelModules  = new modelModules;
					$modelModules -> load($db, $moduleCondition);

					$modulesList = $modelModules -> getResult();

					if(count($modulesList) === 0)
					{
						$retryInstallList[] = $installModule;
						continue 2;
					}
				}
			}

			$errMessage = '';

			$pModules -> install($db, $installModule -> location, $installModule -> type, $errMessage, false);
		}
	}

	foreach($retryInstallList as $module)
	{
		$errMessage = '';
		$pModules -> install($db, $module -> location, $module -> type, $errMessage, false);
	}

	##	Example Data

	$sqlDump = file_get_contents($seedDir.'/2-example.sql');

	if(!empty(trim($sqlDump)))
	{

		$db -> beginTransaction();

		$sqlDump = str_replace('%USER_NAME%',$_POST['user-user'], $sqlDump);
		$sqlDump = str_replace('%USER_PASSWORD%',$_POST['user-pass'], $sqlDump);
		$sqlDump = str_replace('%USER_FIRST_NAME%',$_POST['user-first-name'], $sqlDump);
		$sqlDump = str_replace('%USER_LAST_NAME%',$_POST['user-last-name'], $sqlDump);
		$sqlDump = str_replace('%USER_MAIL%',$_POST['user-mail'], $sqlDump);

		$sqlDump = str_replace('%TIMESTAMP%',time(), $sqlDump);

		try
		{
			$db -> getConnection() -> exec($sqlDump);
		}
		catch (PDOException $exception)
		{
			$db -> rollBack();
			tk::xhrResult(1, 'SQL error on query - '. $exception -> getMessage());
		}

		$db -> commit();

	}

	##	Add administrator base rights

	$modelModules  = new modelModules;
	$modelModules -> load($db, $moduleCondition);
	$modulesList = $modelModules -> getResult();

	$adminRights = [];

	foreach($modulesList as $module)
	{
		//




		$moduleFilepath 	= CMS_SERVER_ROOT . $module -> module_type .'/'. DIR_MODULES . $module -> module_location .'/module.json';

		$moduleJSON	= file_get_contents($moduleFilepath);

		if($moduleJSON === false)
			continue;
		
		$moduleJSON = json_decode($moduleJSON);


		$moduleData = $this -> getMmoduleData($moduleJSON, $module -> module_location, $module -> module_type);

// !?! $right obj oder array

		foreach($moduleData['rights'] as $right)
		{

			$adminRights[ $module -> module_id ][] = $right -> name;



		}

	}

	$adminDataset['group_name'] 	= 'Administrator';
	$adminDataset['group_rights'] 	= json_encode($adminRights);
	$adminDataset['create_time'] 	= time();
	$adminDataset['create_by'] 		= 0;

	$modelRightGroups = new modelRightGroups;
	$modelRightGroups -> insert($db, $adminDataset);
	

	##	finish

	tk::xhrResult(0, 'OK');

?>