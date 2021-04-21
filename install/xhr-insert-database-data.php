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
	include_once '../core/models/modelRightGroups.php';
	include_once '../core/models/modelUsersBackend.php';
	include_once '../core/models/modelUsersRegister.php';
	include_once '../core/models/modelUserGroups.php';
	include_once '../core/models/modelLanguages.php';
	include_once '../core/models/modelPage.php';

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
	$_POST['user-pass']			= CRYPT::LOGIN_CRYPT($_POST['user-pass'], CFG::GET() -> ENCRYPTION -> BASEKEY);

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

##	Base Data

	$db -> beginTransaction();

	$seedDir = 'seeds';

	$sqlDump = file_get_contents($seedDir.'/1-base.sql');

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

##	Insert Languages and base nodes

	$language = [];
	$language['lang_key'] 			= 'en';
	$language['lang_name'] 			= 'English';
	$language['lang_name_native'] 	= 'English';
	$language['lang_hidden'] 		= 0;
	$language['lang_locked'] 		= 0;
	$language['lang_default'] 		= 1;
	$language['lang_frontend'] 		= 1;
	$language['lang_backend'] 		= 1;
	$language['create_time'] 		= time();
	$language['create_by'] 			= 0;
	$modelLanguages = new modelLanguages;
	$modelLanguages -> insert($db, $language);

	$rootPage = [];
	$rootPage['cms-edit-page-lang'] = 'en';
	$rootPage['cms-edit-page-node'] = '1';		// parent node-id
	$rootPage['page_id'] 			= '1';
	$rootPage['page_name'] 			= 'Home';
	$rootPage['page_template'] 		= 'default';
	$rootPage['create_time']		=	time();
	$rootPage['create_by']			= 0;
	$modelPage  = new modelPage();
	$modelPage -> insert($db, $rootPage);

	$rootPage = [];
	$rootPage['cms-edit-page-lang'] = 'en';
	$rootPage['cms-edit-page-node'] = '1';		// parent node-id
	$rootPage['page_id'] 			= '1';
	$rootPage['page_name'] 			= 'Home';
	$rootPage['page_template'] 		= 'backend';
	$rootPage['crawler_index'] 		= '0';
	$rootPage['crawler_follow'] 	= '0';
	$rootPage['create_time']		=	time();
	$rootPage['create_by']			= 0;
	$modelBackendPage  = new modelBackendPage();
	$modelBackendPage -> insert($db, $rootPage);

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
	$modelModules -> load($db);
	$modulesList   = $modelModules -> getResult();
	$adminRights   = [];
	foreach($modulesList as $module)
	{
		$moduleFilepath 	= CMS_SERVER_ROOT . $module -> module_type .'/'. DIR_MODULES . $module -> module_location .'/module.json';
		$moduleJSON	= file_get_contents($moduleFilepath);
		if($moduleJSON === false)
			continue;
		$moduleJSON = json_decode($moduleJSON);
		$pModulesInstall = new CModulesInstall;
		$moduleData = $pModulesInstall -> getMmoduleData($moduleJSON, $module -> module_location, $module -> module_type);
		foreach($moduleData['rights'] as $right)
		{
			$adminRights[ $module -> module_id ][] = $right -> name;
		}
	}
	$userGroup = [];
	$userGroup['group_name'] 	= 'Administrator';
	$userGroup['group_rights'] 	= json_encode($adminRights);
	$userGroup['create_time'] 	= time();
	$userGroup['create_by'] 		= 0;
	$modelRightGroups = new modelRightGroups;
	$userGroupId = $modelRightGroups -> insert($db, $userGroup);
	
##	Create initial backend user

	$initialUser = [];
	$initialUser['login_name'] 		= $_POST['user-user'];
	$initialUser['login_pass'] 		= $_POST['user-pass'];
	$initialUser['user_id'] 		= '1';
	$initialUser['user_name_first'] = $_POST['user-first-name'];
	$initialUser['user_name_last'] 	= $_POST['user-last-name'];
	$initialUser['user_mail'] 		= $_POST['user-mail'];
	$initialUser['cookie_id'] 		= '{}';
	$initialUser['is_locked'] 		= '0';
	$initialUser['language'] 		= 'en';
	$initialUser['create_time'] 	= time();
	$initialUser['create_by'] 		= 0;
	$modelUsersBackend = new modelUsersBackend;
	$initialUserId = $modelUsersBackend -> insert($db, $initialUser);

	$userRegister = [];
	$userRegister['user_id'] 		= $initialUserId;
	$userRegister['user_type'] 		= '0';
	$modelUsersRegister = new modelUsersRegister;
	$modelUsersRegister -> insert($db, $userRegister);

##	Assign user to Admnistrator group

	$userRight = [];
	$userRight['user_id'] 	= $initialUserId;
	$userRight['group_id'] 	= $userGroupId;
	$modelUserGroups = new modelUserGroups;
	$modelUserGroups -> insert($db, $userRight);

##	fin

	tk::xhrResult(0, 'OK');
