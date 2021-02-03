<?php

require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelModules.php';	
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPage.php';	
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPageObject.php';	
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPagePath.php';	
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPageHeader.php';	

require_once 'CSingleton.php';

class	CModules extends CSingleton
{
	public 	$modelModules;
	public	$modulesList;
	public	$loadedList;

	private $m_pUserRights;

	public function
	initialize(CDatabaseConnection &$_dbConnection, CUserRights &$_pUserRights)
	{
		if($_dbConnection === null)
			return; 

		$this -> modelModules	 =	new modelModules();
		$this -> modelModules	->	load($_dbConnection);
		$this -> modulesList 	 =	&$this -> modelModules -> getResult();

		$this -> loadedList		 =	[];

		$this -> m_pUserRights	 = &$_pUserRights;
	}

	/**
	 *	Loads the Module by given moduleID if not already loaded 
	 */
	public function
	loadModule(int $_moduleId, string $_pageLanguage)
	{
		$moduleInstance = NULL;
		$moduleIndex = false;



		if(!$this -> getModule($_moduleId, $moduleInstance, $moduleIndex))
		{
			return false;
		}

		if($moduleInstance -> is_active === 0)
		{
			return false;
		}

		if(class_exists($moduleInstance -> module_controller))
		{
			return $moduleInstance;
		}

		##	Check if this moduel extends another module, if yes, call loadModule for required includes

		$this -> modulesList[$moduleIndex] -> parentModule = NULL;

		if(!empty($moduleInstance -> module_extends))
		{
			$parentModule = NULL;
			$this -> getModuleByController($moduleInstance -> module_extends, $parentModule);
			$this -> modulesList[$moduleIndex] -> parentModule = $this -> loadModule($parentModule -> module_id, $_pageLanguage);		
		}

		##

		switch($moduleInstance -> module_type) 
		{
			case 'core'   :	include CMS_SERVER_ROOT.DIR_CORE.DIR_MODULES. $moduleInstance -> module_location .'/'. $moduleInstance -> module_controller .'.php';

							$moduleConfig 	= file_get_contents( CMS_SERVER_ROOT.DIR_CORE.DIR_MODULES. $moduleInstance -> module_location .'/module.json');



							$moduleConfig 	= ($moduleConfig !== false ? json_decode($moduleConfig) : [] );	







		$pModulesInstall = new CModulesInstall;

		$moduleData = $pModulesInstall -> getMmoduleData($moduleConfig, $moduleInstance -> module_location, $moduleInstance -> module_type);




		if($moduleData === false)
		{
			return false;
		}


		$moduleData = json_decode(json_encode($moduleData));








							$this -> modulesList[$moduleIndex] = (object)array_merge((array)$moduleInstance, (array)$moduleData);
							$this -> modulesList[$moduleIndex] -> user_rights = $this -> m_pUserRights -> getModuleRights($_moduleId);

							$_modLocation	= CMS_SERVER_ROOT . DIR_CORE . DIR_MODULES . $moduleInstance -> module_location .'/';	
							CLanguage::instance() -> loadLanguageFile($_modLocation.'lang/', $_pageLanguage);


							$this -> loadedList[] = $this -> modulesList[$moduleIndex];
							return $this -> modulesList[$moduleIndex];
							
			case 'mantle' : include CMS_SERVER_ROOT.DIR_MANTLE.DIR_MODULES. $moduleInstance -> module_location .'/'. $moduleInstance -> module_controller .'.php';

							$moduleConfig 	= file_get_contents( CMS_SERVER_ROOT.DIR_MANTLE.DIR_MODULES. $moduleInstance -> module_location .'/module.json');
							$moduleConfig 	= ($moduleConfig !== false ? json_decode($moduleConfig) : [] );	










		$pModulesInstall = new CModulesInstall;

		$moduleData = $pModulesInstall -> getMmoduleData($moduleConfig, $moduleInstance -> module_location, $moduleInstance -> module_type);

		if($moduleData === false)
		{
			return false;
		}


		$moduleData = json_decode(json_encode($moduleData));











							$_modLocation	= CMS_SERVER_ROOT . DIR_MANTLE . DIR_MODULES . $moduleInstance -> module_location .'/';
							CLanguage::instance() -> loadLanguageFile($_modLocation.'lang/', $_pageLanguage);

							$this -> modulesList[$moduleIndex] = (object)array_merge((array)$moduleInstance, (array)$moduleData);

							$this -> modulesList[$moduleIndex] -> user_rights = $this -> m_pUserRights -> getModuleRights($_moduleId);


							$this -> loadedList[] = $this -> modulesList[$moduleIndex];
							return $this -> modulesList[$moduleIndex];
		}

		return false;
	}

	public function
	getModule(int $_moduleId, &$_moduleIinstance, int &$_moduleIndex)
	{
		$modulesCount = count($this -> modulesList);

		for($i = 0; $i < $modulesCount; $i++)
		{
			if($this -> modulesList[$i] -> module_id === $_moduleId)
			{

				$_moduleIndex = $i;

				$_moduleIinstance = $this -> modulesList[$i];
				return true;
			}
		}

		return false;
	}

	public function
	getModuleByController(string $_moduleController, &$_moduleIinstance)
	{
		$modulesCount = count($this -> modulesList);

		for($i = 0; $i < $modulesCount; $i++)
		{
			if($this -> modulesList[$i] -> module_controller === $_moduleController)
			{
				$_moduleIndex = $i;

				$_moduleIinstance = $this -> modulesList[$i];
				return true;
			}
		}

		return false;
	}

	public function
	&getModules(bool $_onlyFrontend = false)
	{
		if($_onlyFrontend)
		{
			$modulesList = [];

			foreach($this -> modulesList as $module)
			{
				if(!$module -> is_frontend)
					continue;

				if(!property_exists($module, 'user_rights'))
					$module -> user_rights = $this -> m_pUserRights -> getModuleRights($module -> module_id);

				if(!$this -> m_pUserRights -> existsRight($module -> module_id, 'create'))
					continue;

				$modulesList[] = $module;
			}

			return $modulesList;
		}

		return $this -> modulesList;
	}
	
	public function
	getAvailableModules()
	{
		$moduleList_core 	= [];
		$moduleList_mantle 	= [];

		$procPath = CMS_SERVER_ROOT . DIR_CORE . DIR_MODULES;

		if(file_exists($procPath))
		{ 
			$_dirIterator 	= new DirectoryIterator($procPath);
			foreach($_dirIterator as $_dirItem)
			{
				if($_dirItem -> isDot() || $_dirItem -> getType() !== 'dir')
					continue;

				$directory = $_dirItem -> getFilename();

				if($directory[0] === '.')
					continue;

				$moduleFilepath = $procPath . $directory .'/module.json'; 

				$moduleConfig	= file_get_contents($moduleFilepath);

				if($moduleConfig === false)
					continue;

				$moduleConfig = json_decode($moduleConfig);



				// Determine Sheme

				$pModulesInstall = new CModulesInstall;


				$moduleData = $pModulesInstall -> getMmoduleData($moduleConfig, $directory, 'core');

				if($moduleData === false)
				{
					continue;
				}

				$moduleData = json_decode(json_encode($moduleData));




				$moduleData -> module -> module_location = $directory;

				$moduleList_core[]	= $moduleData;
			}
		}

		$procPath = CMS_SERVER_ROOT . DIR_MANTLE . DIR_MODULES;

		if(file_exists($procPath))
		{ 
			$_dirIterator 	= new DirectoryIterator($procPath);
			foreach($_dirIterator as $_dirItem)
			{

				if($_dirItem -> isDot() || $_dirItem -> getType() !== 'dir')
					continue;

				$directory = $_dirItem -> getFilename();

				if($directory[0] === '.')
					continue;

				$moduleFilepath = $procPath . $directory .'/module.json'; 




				$moduleConfig	= file_get_contents($moduleFilepath);

				if($moduleConfig === false)
					continue;

				$moduleConfig = json_decode($moduleConfig);








				// Determine Sheme

				$pModulesInstall = new CModulesInstall;


				$moduleData = $pModulesInstall -> getMmoduleData($moduleConfig, $directory, 'mantle');

				if($moduleData === false)
				{
					continue;
				}


				$moduleData = json_decode(json_encode($moduleData));








				$moduleData -> module -> module_location = $directory;

				$moduleList_mantle[]	= $moduleData;
			}
		}

		$availableList = [];

		foreach($moduleList_core as $dirModuleKey => $dirModuleItem)
		{
			$moduleInstalled = false;


			if(!isset($dirModuleItem -> module -> module_controller))
			{
				tk::dbug($dirModuleItem);

				// aaaooohhh well ...
			}

			foreach($this -> modulesList as $listItem)
			{
				if($listItem -> module_controller === $dirModuleItem -> module -> module_controller)
				{
					$moduleInstalled = true;
					break;
				}
			}

			if($moduleInstalled)
				continue;

		#	$dirModuleItem -> module_type = "core";

			$availableList[] = $dirModuleItem;
		}

		foreach($moduleList_mantle as $dirModuleKey => $dirModuleItem)
		{
			$moduleInstalled = false;

			foreach($this -> modulesList as $listItem)
			{
				if($listItem -> module_controller === $dirModuleItem -> module -> module_controller)
				{
					$moduleInstalled = true;
					break;
				}
			}
			if($moduleInstalled)
				continue;

		#	$dirModuleItem -> module_type 		= "mantle";

			$availableList[] = $dirModuleItem;
		}


		return $availableList;
	}

	public function
	install(CDatabaseConnection &$_dbConnection, $moduleLocation, $moduleType, &$errorMsg, bool $updateRoutes = true)
	{
		$_dbConnection -> beginTransaction();

		$pModulesInstall = new CModulesInstall;
		if(!$pModulesInstall -> install($_dbConnection, $moduleLocation, $moduleType, $errorMsg))
		{
			$_dbConnection -> rollBack();
			return false;
		}

		$_dbConnection -> commit();

		$this -> modelModules	->	load($_dbConnection);
		$this -> modulesList 	 =	$this -> modelModules -> getResult();

		if($updateRoutes)
		{
			$_pHTAccess  = new CHTAccess();
			$_pHTAccess -> generatePart4Backend($_dbConnection);
			$_pHTAccess -> writeHTAccess($_dbConnection);
		}
		return true;
	}

	public function
	uninstall(CDatabaseConnection &$_dbConnection, $_moduleId, &$errorMsg)
	{
		$_dbConnection -> beginTransaction();

		$pModulesInstall = new CModulesInstall;
		if(!$pModulesInstall -> uninstall($_dbConnection, $_moduleId, $errorMsg))
		{
			$_dbConnection -> rollBack();
			return false;
		}

		$_dbConnection -> commit();
		return true;
	}

	public function
	existsRights(int $_moduleId, string $_rightsId)
	{
		return $this -> m_pUserRights -> existsRight($_moduleId, $_rightsId);
	}
}

class CModulesInstall 
{
	public function
	__construct()
	{
	}

	public function
	install(CDatabaseConnection &$_dbConnection, $moduleLocation, $moduleType, &$errorMsg)
	{
		// Read module.json

		$moduleFilepath 	= CMS_SERVER_ROOT . $moduleType .'/'. DIR_MODULES . $moduleLocation .'/module.json';

		$moduleConfig	= file_get_contents($moduleFilepath);

		if($moduleConfig === false)
		{
			$errorMsg = 'Could not find module config';
			return false;
		}

		$moduleConfig = json_decode($moduleConfig);

		// Determine Sheme

		$moduleData = $this -> getMmoduleData($moduleConfig, $moduleLocation, $moduleType);

		if($moduleData === false)
		{
			$errorMsg = 'Invalid module config format';
			return false;
		}

		## insert module, shemes

		if($moduleData['module'] !== false)
		{
			$modelModules	= new modelModules();

			if($moduleData['module']['extends'] !== false)
			{
				##	Update Parent

				$parentCondition  = new CModelCondition();
				$parentCondition -> where('module_controller', $moduleData['module']['extends']);

				$updateParent = [
								'module_extends_by' => $moduleData['module']['controller']
								];

				$modelModules -> update($_dbConnection, $updateParent, $parentCondition);
			}

			$moduleData['module']['module_id'] = $modelModules -> insert($_dbConnection, $moduleData['module']);

			if($moduleData['module']['shemes'] !== false)
			{
				foreach($moduleData['module']['shemes'] as $sheme)
				{
					$shemeFilepath = CMS_SERVER_ROOT . $moduleType .'/'. DIR_MODULES . $moduleLocation .'/'. $sheme -> filename .'.php';

					if(!file_exists($shemeFilepath))
					{
						$shemeFilepath = CMS_SERVER_ROOT . $moduleType .'/'. DIR_SHEME . $sheme -> filename .'.php';
					}

					include $shemeFilepath;
					
					$sheme  = new $sheme -> filename();

					if(!$sheme -> createTable($_dbConnection))
					{
						return false;
					}
				}
			}
		}
		else
		{
			$errorMsg = 'Could not find module information';
			return false;
		}

		## insert page if requested

		if($moduleData['page'] !== false)
		{
			
			if(isset($moduleData['module']['is_frontend']) && $moduleData['module']['is_frontend'] === '0')
			{
				$modelBackendPage  = new modelBackendPage;
				$nodeId = $modelBackendPage -> insert($_dbConnection, $moduleData['page']);

				if($nodeId === false)
				{
					$errorMsg = 'Error on insert page for module';
					return false;
				}

				$moduleData['page']['node_id'] = $nodeId;
			}
			else
			{
				$modelPage  = new modelPage;
				$nodeId = $modelPage -> insert($_dbConnection, $moduleData['page']);

				if($nodeId === false)
				{
					$errorMsg = 'Error on insert page for module';
					return false;
				}

				$moduleData['page']['node_id'] = $nodeId;
			}
		}

		if(isset($moduleData['objects']) && $moduleData['objects'] !== false)
		{
			foreach($moduleData['objects'] as $object)
			{
				switch($moduleConfig -> sheme)
				{
					case 1:

						$object['node_id']		= $moduleData['page']['node_id'];
						$object['module_id']	= $moduleData['module']['module_id'];

						break;

					case 2:

						$object['node_id']		= $moduleData['page']['node_id'];

						$moduleCondition  = new CModelCondition();
						$moduleCondition -> where('module_controller', $object['controller']);

						$modelModules  = new modelModules;
						$modelModules -> load($_dbConnection, $moduleCondition);

						$modulesList = $modelModules -> getResult();

						if(count($modulesList) !== 1)
						{
							continue 2;
						}
						else
						{
							$moduleInfo = reset($modulesList); 
							$object['module_id']	= $moduleInfo -> module_id;
						}

						break;
				}

				if(isset($moduleData['module']['is_frontend']) && $moduleData['module']['is_frontend'] === '0')
				{
					$modelBackendPageObject = new modelBackendPageObject;
					$modelBackendPageObject -> insert($_dbConnection, $object);
				}
				else
				{
					$modelPageObject = new modelPageObject;
					$modelPageObject -> insert($_dbConnection, $object);
				}
			}
		}

		return true;
	}

	public function
	uninstall(CDatabaseConnection &$_dbConnection, $_moduleId, &$errorMsg)
	{
		##	get module info

		$moduleCondition  = new CModelCondition();
		$moduleCondition -> where('module_id', $_moduleId);

		$modelModules  = new modelModules;
		$modelModules -> load($_dbConnection, $moduleCondition);

		$modulesList = $modelModules -> getResult();

		if(count($modulesList) !== 1)
		{
			$errorMsg = 'Could not find module';
			return false;
		}	
							
		$moduleInfo = reset($modulesList); 

		// Read module.json

		$moduleFilepath 	= CMS_SERVER_ROOT . $moduleInfo -> module_type .'/'. DIR_MODULES . $moduleInfo -> module_location .'/module.json';

		$moduleConfig	= file_get_contents($moduleFilepath);

		if($moduleConfig === false)
		{
			$errorMsg = 'Could not find module config';
			return false;
		}

		$moduleConfig = json_decode($moduleConfig);

		// Determine Sheme

		$moduleData = $this -> getMmoduleData($moduleConfig, $moduleInfo -> module_location, $moduleInfo -> module_type);

		if($moduleData === false)
		{
			$errorMsg = 'Invalid module config format';
			return false;
		}

		##	delete pages/object

		$deleteState = false;
		if($moduleInfo -> is_frontend)
		{
			$objectCondition  = new CModelCondition;
			$objectCondition -> where('module_id', $_moduleId);

			$modelPageObject  = new modelPageObject;
			$deleteState = $modelPageObject -> delete($_dbConnection, $objectCondition);
		}
		else
		{
			$objectCondition  = new CModelCondition;
			$objectCondition -> where('module_id', $_moduleId);

			$modelBackendPageObject  = new modelBackendPageObject;
			$modelBackendPageObject -> load($_dbConnection, $objectCondition);

			$nodeList = [];
			foreach($modelBackendPageObject -> getResult() as $object)
				$nodeList[] = $object -> node_id;
			$nodeList = array_unique($nodeList);

			foreach($nodeList as $nodeId)
			{
				$pageCondition  = new CModelCondition();
				$pageCondition -> where('module_id', $nodeId);

				$modelBackendPage  = new modelBackendPage;
				$deleteState = $modelBackendPage -> delete($_dbConnection, $pageCondition);

				if(!$deleteState)
					break;
			}
		}

		if(!$deleteState)
		{
			$errorMsg = 'content deletion failed';
			return false;
		}

		## insert module, shemes

		if($moduleData['module'] !== false)
		{
			if($moduleData['module']['shemes'] !== false)
			{
				foreach($moduleData['module']['shemes'] as $sheme)
				{
					$shemeFilepath = CMS_SERVER_ROOT . $moduleInfo -> module_type .'/'. DIR_MODULES . $moduleInfo -> module_location .'/'. $sheme -> filename .'.php';

					if(!file_exists($shemeFilepath))
					{
						$shemeFilepath = CMS_SERVER_ROOT . $moduleInfo -> module_type .'/'. DIR_SHEME . $sheme -> filename .'.php';
					}

					include $shemeFilepath;
					
					$sheme  = new $sheme -> filename();

					if(!$sheme -> dropTable($_dbConnection))
					{
						return false;
					}
				}
			}
		}
			
		return $modelModules -> delete($_dbConnection, $moduleCondition);
	}

	public function
	getMmoduleData(stdClass $_moduleConfig, $moduleLocation, $moduleType)
	{
		$moduleData = false;

		if(!property_exists($_moduleConfig, 'sheme'))
			$_moduleConfig -> sheme = 1;



		switch($_moduleConfig -> sheme)
		{
			case 1:

				$pModulesInstallS1 = new CModulesInstallS1;
				$moduleData = $pModulesInstallS1 -> getMmoduleData($_moduleConfig, $moduleLocation, $moduleType);

				break;

			case 2:

				$pModulesInstallS2 = new CModulesInstallS2;
				$moduleData = $pModulesInstallS2 -> getMmoduleData($_moduleConfig, $moduleLocation, $moduleType);
			
				break;
		}

		return $moduleData;
	}
}

class CModulesInstallS1 // Module Sheme 1
{
	public function
	__construct()
	{
	}

	public function
	getMmoduleData(stdClass $_moduleConfig, $moduleLocation, $moduleType)
	{
		$timestamp		= time();
		$userId			= CSession::instance() -> getValue('user_id');

		if(!property_exists($_moduleConfig, 'module_controller') || empty($_moduleConfig -> module_controller))
			return false;


		$moduleData		= [];

		$moduleData['module']['module_location']	= $moduleLocation;
		$moduleData['module']['module_type']		= $moduleType;
		$moduleData['module']['module_controller']	= $_moduleConfig -> module_controller;
		$moduleData['module']['module_name']		= $_moduleConfig -> module_name;
		$moduleData['module']['module_desc']		= $_moduleConfig -> module_desc;
		$moduleData['module']['module_icon']		= $_moduleConfig -> module_icon;
		$moduleData['module']['module_group']		= $_moduleConfig -> module_group;
		$moduleData['module']['is_frontend']		= strval($_moduleConfig -> module_frontend);
		$moduleData['module']['is_active']			= '1';
		$moduleData['module']['create_time']		= $timestamp;
		$moduleData['module']['create_by']			= $userId;

		if(property_exists($_moduleConfig, 'module_extends') && !empty($_moduleConfig -> module_extends))
			$moduleData['module']['extends'] = $_moduleConfig -> module_extends;
		else
			$moduleData['module']['extends'] = false;

		if(property_exists($_moduleConfig, 'module_sheme') && !empty($_moduleConfig -> module_sheme))
			$moduleData['module']['shemes'] = $_moduleConfig -> module_sheme;
		else
			$moduleData['module']['shemes'] = false;

		if($_moduleConfig -> module_frontend == 0)
		{
			$moduleData['page']['page_title']		= $_moduleConfig -> module_name;
			$moduleData['page']['page_name']		= $_moduleConfig -> module_name;
			$moduleData['page']['page_description']	= '';
			$moduleData['page']['page_path']		= $_moduleConfig -> module_path;
			$moduleData['page']['page_auth']		= 'ABKND';
			$moduleData['page']['menu_group']		= $_moduleConfig -> module_menu_group;

			$moduleData['page']['hidden_state']		= '0';
			$moduleData['page']['menu_follow']		= '0';
			$moduleData['page']['crawler_follow']	= '0';
			$moduleData['page']['crawler_index']	= '0';
			$moduleData['page']['cache_disabled']	= '0';
			$moduleData['page']['publish_from']		= '0';
			$moduleData['page']['publish_until']	= '0';
			$moduleData['page']['publish_expired']	= '0';
			$moduleData['page']['page_template']	= 'backend';

			$moduleData['page']['cms-edit-page-lang']	= 'en';	// atm en only
			$moduleData['page']['cms-edit-page-node']	= '2';	// child for en start node

			$moduleData['page']['create_time']			= $timestamp;
			$moduleData['page']['create_by']			= $userId;

			$moduleData['objects'][] = [

				'object_order_by'	=>	'1',
				'create_time'		=>	$timestamp,
				'create_by'			=>	$userId

			];
		}
		else
		{
			$moduleData['page'] = false;
			$moduleData['objects'] = false;
		}

		if(property_exists($_moduleConfig, 'module_rights'))
		{
			$moduleData['rights'] = $_moduleConfig -> module_rights;
		}
		else
		{
			$moduleData['rights'] = [];
		}

		if(property_exists($_moduleConfig, 'module_subs'))
		{
			$moduleData['sections'] = $_moduleConfig -> module_subs;
		}
		else
		{
			$moduleData['sections'] = [];
		}

		return $moduleData;
	}
}

class CModulesInstallS2 // Module Sheme 2
{
	public function
	__construct()
	{
	}

	public function
	getMmoduleData(stdClass $_moduleConfig, $moduleLocation, $moduleType)
	{
		$timestamp		= time();
		$userId			= CSession::instance() -> getValue('user_id');

		$moduleData		= [];

		if(property_exists($_moduleConfig, 'module') || !empty($_moduleConfig -> module))
		{
			$moduleData['module']['module_location']	= $moduleLocation;
			$moduleData['module']['module_type']		= $moduleType;
			$moduleData['module']['module_controller']	= $_moduleConfig -> module -> controller;
			$moduleData['module']['module_name']		= $_moduleConfig -> module -> name;
			$moduleData['module']['module_desc']		= $_moduleConfig -> module -> desc;
			$moduleData['module']['module_icon']		= $_moduleConfig -> module -> icon;
			$moduleData['module']['module_group']		= $_moduleConfig -> module -> group;
			$moduleData['module']['is_frontend']		= strval($_moduleConfig -> module -> frontend);
			$moduleData['module']['is_active']			= '1';
			$moduleData['module']['create_time']		= $timestamp;
			$moduleData['module']['create_by']			= $userId;

			if(property_exists($_moduleConfig -> module, 'extends') && !empty($_moduleConfig -> module -> extends))
				$moduleData['module']['extends'] = $_moduleConfig -> module -> extends;
			else
				$moduleData['module']['extends'] = false;	

			if(property_exists($_moduleConfig -> module, 'shemes') && !empty($_moduleConfig -> module -> shemes))
				$moduleData['module']['shemes'] = $_moduleConfig -> module -> shemes;
			else
				$moduleData['module']['shemes'] = false;
		}
		else
			$moduleData['module'] = false;

		if(property_exists($_moduleConfig, 'page') || !empty($_moduleConfig -> page))
		{
			$moduleData['page']['page_title']		= $_moduleConfig -> page -> name;
			$moduleData['page']['page_name']		= $_moduleConfig -> page -> name;
			$moduleData['page']['page_description']	= '';
			$moduleData['page']['page_path']		= $_moduleConfig -> page -> path;
			$moduleData['page']['page_auth']		= 'ABKND';
			$moduleData['page']['menu_group']		= $_moduleConfig -> page -> menu_group;

			$moduleData['page']['hidden_state']		= '0';
			$moduleData['page']['menu_follow']		= '0';
			$moduleData['page']['crawler_follow']	= '0';
			$moduleData['page']['crawler_index']	= '0';
			$moduleData['page']['cache_disabled']	= '0';
			$moduleData['page']['publish_from']		= '0';
			$moduleData['page']['publish_until']	= '0';
			$moduleData['page']['publish_expired']	= '0';
			$moduleData['page']['page_template']	= 'backend';

			$moduleData['page']['cms-edit-page-lang']	= 'en';	// atm en only
			$moduleData['page']['cms-edit-page-node']	= '2';	// child for en start node

			$moduleData['page']['create_time']			= $timestamp;
			$moduleData['page']['create_by']			= $userId;
		}	
		else
			$moduleData['page'] = false;

		if($moduleData['page'] === false && property_exists($_moduleConfig, 'objectList') && !empty($_moduleConfig -> objectList))
			return false;

		if(property_exists($_moduleConfig, 'objectList') && !empty($_moduleConfig -> objectList))
		{
			if(is_array($_moduleConfig -> objectList))
			foreach($_moduleConfig -> objectList as $object)
			{
				$moduleData['objects'][] = [

					'controller'		=>	$object -> controller,
					'body'				=>	$object -> body ?? '',
					'param'				=>	$object -> param ?? '',
					'object_order_by'	=>	'1',
					'create_time'		=>	$timestamp,
					'create_by'			=>	$userId

				];
			}
		}

		if(property_exists($_moduleConfig, 'rights'))
		{
			$moduleData['rights'] = $_moduleConfig -> rights;
		}
		else
		{
			$moduleData['rights'] = [];
		}

		if(property_exists($_moduleConfig, 'sections'))
		{
			$moduleData['sections'] = $_moduleConfig -> sections;
		}
		else
		{
			$moduleData['sections'] = [];
		}


		return $moduleData;
	}
}

?>