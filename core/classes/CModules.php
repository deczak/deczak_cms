<?php

require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelModules.php';	
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPage.php';	
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPageObject.php';	
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPagePath.php';	
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPageHeader.php';	
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPageHeader.php';	

require_once 'CSingleton.php';

/**
 * 	This class gathers and loads information about modules. Also contains function for installing and removing modules.
 * 
 * 	This is a singleton class.
 */
class CModules extends CSingleton
{
	public 	$modelModules;
	public	$modulesList;
	public	$loadedList;
	private $m_pUserRights;

	/**
	 * 	Initialize function to setup the log system
	 * 
	 * 	@param CDatabaseConnection $_dbConnection Database Connection object
	 * 	@param CUserRights $_pUserRights User Rights object
	 * 	@return CModules instance
	 */
	public static function 
	initialize(?CDatabaseConnection &$_dbConnection, CUserRights &$_pUserRights)
	{
		$instance  = static::instance();

		if($_dbConnection === null)
			return; 

		$instance -> modelModules	 =	new modelModules();
		$instance -> modelModules	->	load($_dbConnection);
		$instance -> modulesList 	 =	&$instance -> modelModules -> getResult();

		$instance -> loadedList		 =	[];

		$instance -> m_pUserRights	 = &$_pUserRights;

		cmsLog::add('CModules::initialize -- Initialized CModules Instance');

		$instance -> registerSystemFunction();

		return $instance;
	}

	/**
	 *	Loads the Module by given moduleID if not already loaded. This Function includes the Module controller File and read the Language and json Files
	 *
	 *	@param int $_moduleId The ID from the requested module
	 *	@param string $_pageLanguage The 2-letter language code for the requested language.
	 *	@return object Returns a valid module object with his data, otherwise NULL
	 */
	public function
	loadModule(int $_moduleId, string $_pageLanguage) : ?object
	{
		$moduleInstance = NULL;
		$moduleIndex = false;

		cmsLog::add('CModules::loadModule -- Load defined Module with ID '. $_moduleId .' and language '.$_pageLanguage);

		if(!$this -> getModule($_moduleId, $moduleInstance, $moduleIndex))
		{
			cmsLog::add('CModules::loadModule -- Aborted, could not find module with ID '. $_moduleId, true);
			return null;
		}

		if($moduleInstance -> is_active === 0)
		{
			return null;
		}

		//if(class_exists($moduleInstance -> module_controller))
		//{ echo 'class_exists';
		//	return $moduleInstance;
		//}

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
			case 'core'   :	include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODULES. $moduleInstance -> module_location .'/'. $moduleInstance -> module_controller .'.php';

							$moduleConfig 	= file_get_contents( CMS_SERVER_ROOT.DIR_CORE.DIR_MODULES. $moduleInstance -> module_location .'/module.json');
							$moduleConfig 	= ($moduleConfig !== false ? json_decode($moduleConfig) : [] );	

							$pModulesInstall = new CModulesInstall;
							$moduleData = $pModulesInstall -> getModuleData($moduleConfig, $moduleInstance -> module_location, $moduleInstance -> module_type);

							if($moduleData === false)
							{
								cmsLog::add('CModules::loadModule -- Unable to retrieve modul info for module-ID '. $_moduleId, true);
								return null;
							}

							$moduleData = json_decode(json_encode($moduleData));

							$this -> modulesList[$moduleIndex] = (object)array_merge((array)$moduleInstance, (array)$moduleData);
							$this -> modulesList[$moduleIndex] -> user_rights = $this -> m_pUserRights -> getModuleRights($_moduleId);
							$this -> modulesList[$moduleIndex] -> modules_path = CMS_SERVER_ROOT.DIR_CORE.DIR_MODULES;
							$this -> modulesList[$moduleIndex] -> module_path = CMS_SERVER_ROOT.DIR_CORE.DIR_MODULES. $moduleInstance -> module_location .'/';

							$_modLocation	= CMS_SERVER_ROOT . DIR_CORE . DIR_MODULES . $moduleInstance -> module_location .'/';	
							CLanguage::loadLanguageFile($_modLocation.'lang/', $_pageLanguage);

							$this -> loadedList[] = $this -> modulesList[$moduleIndex];
							return $this -> modulesList[$moduleIndex];
							
			case 'mantle' : include_once CMS_SERVER_ROOT.DIR_MANTLE.DIR_MODULES. $moduleInstance -> module_location .'/'. $moduleInstance -> module_controller .'.php';

							$moduleConfig 	= file_get_contents( CMS_SERVER_ROOT.DIR_MANTLE.DIR_MODULES. $moduleInstance -> module_location .'/module.json');
							$moduleConfig 	= ($moduleConfig !== false ? json_decode($moduleConfig) : [] );	

							$pModulesInstall = new CModulesInstall;
							$moduleData = $pModulesInstall -> getModuleData($moduleConfig, $moduleInstance -> module_location, $moduleInstance -> module_type);

							if($moduleData === false)
							{
								cmsLog::add('CModules::loadModule -- Unable to retrieve modul info for module-ID '. $_moduleId, true);
								return null;
							}

							$moduleData = json_decode(json_encode($moduleData));

							$this -> modulesList[$moduleIndex] = (object)array_merge((array)$moduleInstance, (array)$moduleData);
							$this -> modulesList[$moduleIndex] -> user_rights = $this -> m_pUserRights -> getModuleRights($_moduleId);
							$this -> modulesList[$moduleIndex] -> modules_path = CMS_SERVER_ROOT.DIR_MANTLE.DIR_MODULES;
							$this -> modulesList[$moduleIndex] -> module_path = CMS_SERVER_ROOT.DIR_MANTLE.DIR_MODULES. $moduleInstance->module_location .'/';

							$_modLocation	= CMS_SERVER_ROOT . DIR_MANTLE . DIR_MODULES . $moduleInstance -> module_location .'/';
							CLanguage::loadLanguageFile($_modLocation.'lang/', $_pageLanguage);

							$this -> loadedList[] = $this -> modulesList[$moduleIndex];
							return $this -> modulesList[$moduleIndex];
		}

		return null;
	}

	/**
	 * 	Get a module info from the list of installed modules
	 * 
	 * 	@param int The module ID
	 * 	@param object A reference where the module info will be written
	 * 	@param int A reference where the list index will be written
	 * 	@return bool Return true if a module found, otherwise false
	 */
	public function
	getModule(int $_moduleId, ?object &$_moduleIinstance, int &$_moduleIndex) : bool
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

	/**
	 * 	Return a module info by his controller name
	 * 	
	 * 	@param string $_moduleController The controller name (the class name).
	 * 	@param mixed $_moduleIinstance A reference to the destination where the info will be written.
	 * 	@return bool true if controller was found and written to the reference, otherwise false.
	 */
	public function
	getModuleByController(string $_moduleController, &$_moduleIinstance) : bool
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

	/**
	 * 	Returns the list of all installes modules
	 * 
	 * 	@param bool $_onlyFrontend if true, it returns only frontend modules
	 * 	@return array A list of modules
	 */
	public function
	getModules(bool $_onlyFrontend = false) : array
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
	
	/**
	 * 	Returns a List avaiable modules for core and mantle that is not installed
	 * 
	 * 	@return array A list of not installed modules
	 */
	public function
	getAvailableModules() : array
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

				// Determine Scheme

				$pModulesInstall = new CModulesInstall;

				$moduleData = $pModulesInstall -> getModuleData($moduleConfig, $directory, 'core');

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

				// Determine Scheme

				$pModulesInstall = new CModulesInstall;

				$moduleData = $pModulesInstall -> getModuleData($moduleConfig, $directory, 'mantle');

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
				#tk::dbug($dirModuleItem);

				// aaaooohhh well ... this should not be
				continue;
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

	/**
	 * 	Installs a module
	 * 
	 *	@param CDatabaseConnection $_dbConnection Database Connection object
	 *	@param string $moduleLocation Folder name of that module
	 * 	@param string $moduleType Module type, core or mantle
	 * 	@param string $errorMsg A reference to a string where the error message will be written
	 * 	@param bool $updateRoutes true to update the routing system, otherwise false
	 * 	@return bool true if successful, otherwise false
	 */
	public function
	install(CDatabaseConnection &$_dbConnection, string $moduleLocation, string $moduleType, string &$errorMsg, bool $updateRoutes = true) : bool
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

		CModules::generateResources();

		if($updateRoutes)
		{
			$_pHTAccess  = new CHTAccess();
			$_pHTAccess -> generatePart4Backend($_dbConnection);
			$_pHTAccess -> writeHTAccess($_dbConnection);
		}
		return true;
	}

	/**
	 * 	Uninstall a module
	 * 
	 *	@param CDatabaseConnection $_dbConnection Database Connection object
	 *	@param int $_moduleId Module ID that will be uninstalled
	 * 	@param string $errorMsg A reference to a string where the error message will be written
	 * 	@return bool true if successful, otherwise false
	 */
	public function
	uninstall(CDatabaseConnection &$_dbConnection, int $_moduleId, &$errorMsg) : bool
	{
		$_dbConnection -> beginTransaction();

		$pModulesInstall = new CModulesInstall;
		if(!$pModulesInstall -> uninstall($_dbConnection, $_moduleId, $errorMsg))
		{
			$_dbConnection -> rollBack();
			return false;
		}

		$_dbConnection -> commit();

		$this -> modelModules	->	load($_dbConnection);
		$this -> modulesList 	 =	$this -> modelModules -> getResult();

		CModules::generateResources();

		return true;
	}

	/**
	 * 	Check the user his rights to the module rights
	 * 
	 *	@param int $_moduleId Module ID that will be uninstalled
	 * 	@param string $_rightsId Module right name
	 * 	@return bool true if the user owns the module right, otherwise false
	 */ 
	public function
	existsRights(int $_moduleId, string $_rightsId) : bool
	{
		return $this -> m_pUserRights -> existsRight($_moduleId, $_rightsId);
	}

	/**
	 * 	Static wrapper function for resource generation 
	 */
	public static function
	generateResources(bool $_onlyFrontend = false)
	{
		$instance  = static::instance();
		$modulesResources = new CModulesResources;
		$modulesResources -> generateResources($instance->getModules($_onlyFrontend));
	}

	/**
	 * 	Register the Modules with system function to cmsSystemModules
	 */
	private function registerSystemFunction() : void
	{
		foreach($this -> modulesList as $module)
		{
			if(!$module -> is_systemFunction)
				continue;

			switch($module -> module_type) 
			{
				case 'core'   :	include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODULES. $module -> module_location .'/'. $module -> module_controller .'.php';

								$moduleConfig 	= file_get_contents( CMS_SERVER_ROOT.DIR_CORE.DIR_MODULES. $module -> module_location .'/module.json');
								$moduleConfig 	= ($moduleConfig !== false ? json_decode($moduleConfig) : [] );	

								$pModulesInstall = new CModulesInstall;
								$moduleData = $pModulesInstall -> getModuleData($moduleConfig, $module -> module_location, $module -> module_type);

								if($moduleData === false)
								{
									cmsLog::add('CModules::registerSystemFunction -- Unable to retrieve modul info for module-ID '. $module -> module_id, true);
									continue 2;
								}

								$moduleData = json_decode(json_encode($moduleData));

								$moduleInfo = (object)$moduleData;
								$moduleInfo -> user_rights = $this -> m_pUserRights -> getModuleRights($module -> module_id);
								
				case 'mantle' : include_once CMS_SERVER_ROOT.DIR_MANTLE.DIR_MODULES. $module -> module_location .'/'. $module -> module_controller .'.php';

								$moduleConfig 	= file_get_contents( CMS_SERVER_ROOT.DIR_MANTLE.DIR_MODULES. $module -> module_location .'/module.json');
								$moduleConfig 	= ($moduleConfig !== false ? json_decode($moduleConfig) : [] );	

								$pModulesInstall = new CModulesInstall;
								$moduleData = $pModulesInstall -> getModuleData($moduleConfig, $module -> module_location, $module -> module_type);

								if($moduleData === false)
								{
									cmsLog::add('CModules::registerSystemFunction -- Unable to retrieve modul info for module-ID '. $module -> module_id, true);
									continue 2;
								}

								$moduleData = json_decode(json_encode($moduleData));

								$moduleInfo = (object)$moduleData;
								$moduleInfo -> user_rights = $this -> m_pUserRights -> getModuleRights($module -> module_id);










			}

			if(!empty($moduleInfo))
			{
				$empty = new stdClass;

				$moduleInstance = new $module -> module_controller($module, $empty);

				if(!method_exists($moduleInstance, 'registerSystemFunction'))
					continue;

				$moduleInstance -> registerSystemFunction(cmsSystemModules::instance());
			}
		}
	}
}


/**
 * 	This class un-/installs the modules 
 */
class CModulesInstall 
{
	public function
	__construct()
	{
	}

	/**
	 * 	Installs a module
	 * 
	 *	@param CDatabaseConnection $_dbConnection Database Connection object
	 *	@param string $moduleLocation Folder name of that module
	 * 	@param string $moduleType Module type, core or mantle
	 * 	@param string $errorMsg A reference to a string where the error message will be written
	 * 	@return bool true if successful, otherwise false
	 */
	public function
	install(CDatabaseConnection &$_dbConnection, string $moduleLocation, string $moduleType, &$errorMsg) : bool
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

		// Determine Scheme

		$moduleData = $this -> getModuleData($moduleConfig, $moduleLocation, $moduleType);

		if($moduleData === false)
		{
			$errorMsg = 'Invalid module config format';
			return false;
		}

		## insert module, schemes

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

			if($moduleData['module']['schemes'] !== false)
			{
				foreach($moduleData['module']['schemes'] as $scheme)
				{
					$schemeFilepath = CMS_SERVER_ROOT . $moduleType .'/'. DIR_MODULES . $moduleLocation .'/'. $scheme -> filename .'.php';

					if(!file_exists($schemeFilepath))
					{
						$schemeFilepath = CMS_SERVER_ROOT . $moduleType .'/'. DIR_SCHEME . $scheme -> filename .'.php';
					}

					include $schemeFilepath;
					
					$scheme  = new $scheme -> filename();

					if(!$scheme -> createTable($_dbConnection))
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
				## Check if page is start page

				if(empty($moduleData['page']['page_path']))
				{
					## Check if page exists, otherwise break instead of create startpage

					$modelCondition = new CModelCondition;
					$modelCondition -> where('page_path', '/');
					$modelCondition -> where('page_language', 'en');

					$modelBackendPagePath = new modelBackendPagePath;
					$modelBackendPagePath -> load($_dbConnection, $modelCondition);

					if(count($modelBackendPagePath -> getResult()) !== 1)
					{
						return false;
					}

					$moduleData['page']['node_id'] = reset($modelBackendPagePath -> getResult()) -> node_id;
				}
				else
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
			}
			else
			{
				## Check if page is start page

				if(empty($moduleData['page']['path']))
				{
					## Check if page exists, otherwise break instead of create startpage

					/*
						lang does not exists
					*/

					$modelCondition = new CModelCondition;
					$modelCondition -> where('page_path', $moduleData['page']['page_path']);
					$modelCondition -> where('page_language', $moduleData['page']['page_language'] );

					$modelPagePath = new modelPagePath;
					$modelPagePath -> load($_dbConnection, $modelCondition);

					if(count($modelPagePath -> getResult()) !== 1)
					{
						$errorMsg = 'Could not find node for requested page path';
						return false;
					}

					$moduleData['page']['node_id'] = reset($modelBackendPagePath -> getResult()) -> node_id;
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
		}

		## insert objects

		if(isset($moduleData['objects']) && $moduleData['objects'] !== false)
		{
			foreach($moduleData['objects'] as $index => $object)
			{
				switch($moduleConfig -> scheme)
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

				$moduleCondition  = new CModelCondition();
				$moduleCondition -> where('module_id', $object['module_id']);

				$modelModules  = new modelModules;
				$modelModules -> load($_dbConnection, $moduleCondition);

				$modulesList = $modelModules -> getResult();

				$moduleInfo = reset($modulesList); 

				$contentId = 0;

				if(isset($moduleData['module']['is_frontend']) && $moduleData['module']['is_frontend'] === '0')
				{
					//$modelBackendPageObject = new modelBackendPageObject;
					//$contentId = $modelBackendPageObject -> insert($_dbConnection, $object);

					$modelBackendPageObject = modelBackendPageObject::new($object, $_dbConnection);
					$modelBackendPageObject->save();

					$contentId = $modelBackendPageObject->content_id;
				}
				else
				{
					//$modelPageObject = new modelPageObject;
					//$contentId = $modelPageObject -> insert($_dbConnection, $object);

					$modelPageObject = modelPageObject::new($object, $_dbConnection);
					$modelPageObject->save();

					$contentId = $modelPageObject->content_id;
				}

				if($moduleInfo -> module_group === 'backend')
					continue;

				$objectData = new stdClass();
				$objectData -> page_version 	= '1';
				$objectData -> module_id 		= $object['module_id'];
				$objectData -> object_id 		= (int)$contentId;
				#$objectData -> content_id 		= $contentId;
				$objectData -> object_order_by 	= $index + 1;
				$objectData -> node_id 			= $moduleData['page']['node_id'];
				$objectData -> create_time 		= time();
				$objectData -> create_by 		= CSession::instance() -> getValue('user_id');

 				$controller = $moduleInfo -> module_controller;
				$controllerFilepath = CMS_SERVER_ROOT . $moduleInfo -> module_type .'/'. DIR_MODULES . $moduleInfo -> module_location .'/'. $controller .'.php';

				if(!file_exists($controllerFilepath))
				{
					continue;
				}

				$objectModuleFilepath 	= CMS_SERVER_ROOT . $moduleInfo -> module_type .'/'. DIR_MODULES . $moduleInfo -> module_location .'/module.json';

				$objectModuleConfig	= file_get_contents($objectModuleFilepath);

				if($objectModuleConfig === false)
				{
					continue;
				}

				$objectModuleConfig = json_decode($objectModuleConfig);

				// Determine Scheme

				$objectModuleData = $this -> getModuleData($objectModuleConfig, $moduleInfo -> module_location, $moduleInfo -> module_type);

				if($objectModuleData === false)
				{
					continue;
				}

				$moduleABC = json_decode(json_encode($objectModuleData));

				$isBackendCall = false;
				if($moduleData['module']['module_group'] === 'backend')
					$isBackendCall  = true;

				require_once $controllerFilepath;
				$objectInstance = new $controller($moduleABC, $objectData, $isBackendCall);

				$objectInstance -> setInstallMode();


				$xhrInfo = new stdClass;
				$xhrInfo -> isXHR 	 = true;
				$xhrInfo -> action 	 = 'cms-insert-module';
				$xhrInfo -> objectId = (int)$objectData -> object_id;


				$logicResult = [];

				$objectInstance -> logic(
											$_dbConnection, 
											[ (int)$objectData -> object_id => 'create' ],
											$xhrInfo, 
											$logicResult, 
											true
											);

				$tmpPost = $_POST;							

				$_POST = [];
				$_POST['cms-object-id'] = $contentId;

				$object['data'] = (array)$object['data'];

				if(is_array($object['data']) && !empty($object['data']))
				foreach($object['data'] as $dataKey => $dataValue)
				{
					$_POST[$dataKey] = $dataValue;
				}

				$xhrInfo -> action 	 = 'edit';

				$objectInstance -> logic(
											$_dbConnection, 
											[ (int)$objectData -> object_id => 'edit' ],
											$xhrInfo, 
											$logicResult, 
											true
											);
				$_POST = $tmpPost;
			}
		}

		return true;
	}

	/**
	 * 	Uninstall a module
	 * 
	 *	@param CDatabaseConnection $_dbConnection Database Connection object
	 *	@param int $_moduleId Module ID that will be uninstalled
	 * 	@param string $errorMsg A reference to a string where the error message will be written
	 * 	@return bool true if successful, otherwise false
	 */
	public function
	uninstall(CDatabaseConnection &$_dbConnection, int $_moduleId, &$errorMsg) : bool
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

		// Determine Scheme

		$moduleData = $this -> getModuleData($moduleConfig, $moduleInfo -> module_location, $moduleInfo -> module_type);

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

			$modelBackendPageObject = modelBackendPageObject::
				  where('module_id', '=', $_moduleId)
				->get();
			/*
			$objectCondition  = new CModelCondition;
			$objectCondition -> where('module_id', $_moduleId);

			$modelBackendPageObject  = new modelBackendPageObject;
			$modelBackendPageObject -> load($_dbConnection, $objectCondition);
			*/

			$nodeList = [];
			#foreach($modelBackendPageObject -> getResult() as $object)
			foreach($modelBackendPageObject as $object)
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

		## insert module, schemes

		if($moduleData['module'] !== false)
		{
			if($moduleData['module']['schemes'] !== false)
			{
				foreach($moduleData['module']['schemes'] as $scheme)
				{
					$schemeFilepath = CMS_SERVER_ROOT . $moduleInfo -> module_type .'/'. DIR_MODULES . $moduleInfo -> module_location .'/'. $scheme -> filename .'.php';

					if(!file_exists($schemeFilepath))
					{
						$schemeFilepath = CMS_SERVER_ROOT . $moduleInfo -> module_type .'/'. DIR_SCHEME . $scheme -> filename .'.php';
					}

					include $schemeFilepath;
					
					$scheme  = new $scheme -> filename();

					if(!$scheme -> dropTable($_dbConnection))
					{
						return false;
					}
				}
			}
		}
			
		return $modelModules -> delete($_dbConnection, $moduleCondition);
	}

	/**
	 *	Get the module data based on their json scheme format in a normalized structure
	 *	@param stdClass $_moduleConfig the module config data
	 *	@param string $moduleLocation Folder name of that module
	 * 	@param string $moduleType Module type, core or mantle
	 * 	@return mixed normalized module info or false if failed
	 */
	public function
	getModuleData(stdClass $_moduleConfig, $moduleLocation, $moduleType)
	{
		$moduleData = false;

		if(!property_exists($_moduleConfig, 'scheme'))
			$_moduleConfig -> scheme = 1;



		switch($_moduleConfig -> scheme)
		{
			case 1:

				$pModulesInstallS1 = new CModulesInstallS1;
				$moduleData = $pModulesInstallS1 -> getModuleData($_moduleConfig, $moduleLocation, $moduleType);

				break;

			case 2:

				$pModulesInstallS2 = new CModulesInstallS2;
				$moduleData = $pModulesInstallS2 -> getModuleData($_moduleConfig, $moduleLocation, $moduleType);
			
				break;
		}

		return $moduleData;
	}
}

/**
 * 	This is a helper class to translate the module config into normalized module info
 */
class CModulesInstallS1 // Module Scheme 1
{
	public function
	__construct()
	{
	}

	/**
	 * 	Get the Module Info by module data
	 * 
	 * 	@param stdClass $_moduleConfig Module config as object
	 * 	@param string $moduleLocation Dir name of that module
	 * 	@param string $moduleType Module Type of that module, mantle or core
	 */
	public function
	getModuleData(stdClass $_moduleConfig, string $moduleLocation, string $moduleType)
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
		$moduleData['module']['is_systemFunction']	= $_moduleConfig -> system_module ?? '0';
		$moduleData['module']['is_frontend']		= strval($_moduleConfig -> module_frontend);
		$moduleData['module']['is_active']			= '1';
		$moduleData['module']['create_time']		= $timestamp;
		$moduleData['module']['create_by']			= $userId;
		$moduleData['module']['module_version']		= $_moduleConfig -> module_version ?? 1;

		if(property_exists($_moduleConfig, 'module_extends') && !empty($_moduleConfig -> module_extends))
			$moduleData['module']['extends'] = $_moduleConfig -> module_extends;
		else
			$moduleData['module']['extends'] = false;

		if(property_exists($_moduleConfig, 'module_scheme') && !empty($_moduleConfig -> module_scheme))
			$moduleData['module']['schemes'] = $_moduleConfig -> module_scheme;
		else
			$moduleData['module']['schemes'] = false;

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
			$moduleData['page']['page_language']	= 'en';

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

		if(property_exists($_moduleConfig, 'include'))
		{
			$moduleData['includes'] = $_moduleConfig -> include;
		}
		else
		{
			$moduleData['includes'] = [];
		}

		if(property_exists($_moduleConfig, 'query_url_name'))
		{
			$moduleData['query_url_name'] = $_moduleConfig -> query_url_name;
		}

		if(property_exists($_moduleConfig, 'query_url_var'))
		{
			$moduleData['query_url_var'] = $_moduleConfig -> query_url_var;
		}

		if(property_exists($_moduleConfig, 'query_value_var'))
		{
			$moduleData['query_value_var'] = $_moduleConfig -> query_value_var;
		}

		return $moduleData;
	}
}

/**
 * 	This is a helper class to translate the module config into normalized module info
 */
class CModulesInstallS2 // Module Scheme 2
{
	public function
	__construct()
	{
	}

	/**
	 * 	Get the Module Info by module data
	 * 
	 * 	@param stdClass $_moduleConfig Module config as object
	 * 	@param string $moduleLocation Dir name of that module
	 * 	@param string $moduleType Module Type of that module, mantle or core
	 */
	public function
	getModuleData(stdClass $_moduleConfig, string $moduleLocation, string $moduleType)
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
			$moduleData['module']['is_systemFunction']	= $_moduleConfig -> module -> system_module ?? '0';
			$moduleData['module']['is_frontend']		= strval($_moduleConfig -> module -> frontend);
			$moduleData['module']['is_active']			= '1';
			$moduleData['module']['create_time']		= $timestamp;
			$moduleData['module']['create_by']			= $userId;
			$moduleData['module']['module_version']		= $_moduleConfig -> version ?? 1;

			if(property_exists($_moduleConfig -> module, 'extends') && !empty($_moduleConfig -> module -> extends))
				$moduleData['module']['extends'] = $_moduleConfig -> module -> extends;
			else
				$moduleData['module']['extends'] = false;	

			if(property_exists($_moduleConfig -> module, 'schemes') && !empty($_moduleConfig -> module -> schemes))
				$moduleData['module']['schemes'] = $_moduleConfig -> module -> schemes;
			else
				$moduleData['module']['schemes'] = false;
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
			$moduleData['page']['page_language']	= 'en';

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
					'data'				=>	$object -> data ?? '',
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

		if(property_exists($_moduleConfig, 'includes'))
		{
			$moduleData['includes'] = $_moduleConfig -> includes;
		}
		else
		{
			$moduleData['includes'] = [];
		}

		return $moduleData;
	}
}


/**
 * 	This class create modules releated resources of CSS and JS Files based on module.json information.
 */
class CModulesResources
{
	public function
	__construct()
	{
	}	

	/**
	 * 	Generates the CSS and JS Files based on the modules delivered resources. This does not include module templates related data.
	 * 
	 * 	@param array $_modulesList A list of modules by CModules::getModules
	 */
	public function
	generateResources(array $_modulesList)
	{
		if(!file_exists(CMS_SERVER_ROOT.DIR_PUBLIC .'css'))		{ mkdir(CMS_SERVER_ROOT.DIR_PUBLIC .'css'); chmod(CMS_SERVER_ROOT.DIR_PUBLIC .'css', 0777); }
		if(!file_exists(CMS_SERVER_ROOT.DIR_PUBLIC .'js'))		{ mkdir(CMS_SERVER_ROOT.DIR_PUBLIC .'js');  chmod(CMS_SERVER_ROOT.DIR_PUBLIC .'js', 0777); }

		if(!file_exists(CMS_SERVER_ROOT.DIR_BACKEND .'css'))	{ mkdir(CMS_SERVER_ROOT.DIR_BACKEND .'css'); chmod(CMS_SERVER_ROOT.DIR_BACKEND .'css', 0777); }
		if(!file_exists(CMS_SERVER_ROOT.DIR_BACKEND .'js'))		{ mkdir(CMS_SERVER_ROOT.DIR_BACKEND .'js');  chmod(CMS_SERVER_ROOT.DIR_BACKEND .'js', 0777); }

		$hFileCSSFrontend 	 = fopen(CMS_SERVER_ROOT.DIR_PUBLIC .'css/cms.css', "a");
		$hFileCSSBackend 	 = fopen(CMS_SERVER_ROOT.DIR_BACKEND .'css/cms.css', "a");

		$hFileJSFrontend 	 = fopen(CMS_SERVER_ROOT.DIR_PUBLIC .'js/cms.js', "a");
		$hFileJSBackend 	 = fopen(CMS_SERVER_ROOT.DIR_BACKEND .'js/cms.js', "a");

		if(flock($hFileCSSFrontend, LOCK_EX) && flock($hFileCSSBackend, LOCK_EX) && flock($hFileJSFrontend, LOCK_EX) && flock($hFileJSBackend, LOCK_EX))
		{	
			ftruncate($hFileCSSFrontend, 0);
			ftruncate($hFileCSSBackend, 0);
			ftruncate($hFileJSFrontend, 0);
			ftruncate($hFileJSBackend, 0);
		
			foreach($_modulesList as $module)
			{
				switch($module -> module_type) 
				{
					case 'core':	

						$moduleLocation = CMS_SERVER_ROOT.DIR_CORE.DIR_MODULES. $module -> module_location .'/';

						$moduleConfig 	= file_get_contents($moduleLocation .'module.json');
						$moduleConfig 	= ($moduleConfig !== false ? json_decode($moduleConfig) : [] );	

						$pModulesInstall = new CModulesInstall;
						$moduleData = $pModulesInstall -> getModuleData($moduleConfig, $module -> module_location, $module -> module_type);

						if($moduleData === false)
							continue 2;
						
						$moduleData = json_decode(json_encode($moduleData));

						break;
									
					case 'mantle':

						$moduleLocation = CMS_SERVER_ROOT.DIR_MANTLE.DIR_MODULES. $module -> module_location .'/';

						$moduleConfig 	= file_get_contents($moduleLocation .'module.json');
						$moduleConfig 	= ($moduleConfig !== false ? json_decode($moduleConfig) : [] );	

						$pModulesInstall = new CModulesInstall;
						$moduleData = $pModulesInstall -> getModuleData($moduleConfig, $module -> module_location, $module -> module_type);

						if($moduleData === false)
							continue 2;
						
						$moduleData = json_decode(json_encode($moduleData));
						break;

					default: 
						continue 2;
				}

				## collect all files in the css directory

				if(is_dir($moduleLocation.'css/'))
				{
					$resIterator 	= new DirectoryIterator($moduleLocation.'css/');
					foreach($resIterator as $iterItem)
					{
						if($iterItem -> isDot() || $iterItem -> getType() !== 'file')
							continue;

						if($iterItem -> getBasename()[0] === '.') // skip files with leading dot
							continue;

						$filepath 		= $iterItem -> getPathname();
						$resFileData	= file_get_contents($filepath);

						if($resFileData === false)
							continue 2;

						switch($module -> module_group) 
						{
							case 'backend':	

								fwrite($hFileCSSBackend, "\r\n" . $resFileData);		
								break;

							default:

								fwrite($hFileCSSFrontend, "\r\n" . $resFileData);
						}
					}
				}

				## collect all files in the js directory

				if(is_dir($moduleLocation.'js/'))
				{
					$resIterator 	= new DirectoryIterator($moduleLocation.'js/');
					foreach($resIterator as $iterItem)
					{
						if($iterItem -> isDot() || $iterItem -> getType() !== 'file')
							continue;

						if($iterItem -> getBasename()[0] === '.') // skip files with leading dot
							continue;

						$filepath 		= $iterItem -> getPathname();
						$resFileData	= file_get_contents($filepath);
						
						if($resFileData === false)
							continue 2;

						switch($module -> module_group) 
						{
							case 'backend':	

								fwrite($hFileJSBackend, "\r\n" . $resFileData);		
								break;

							default:

								fwrite($hFileJSFrontend, "\r\n" . $resFileData);
						}
					}
				}

				## collect files those are names in the module.json

				if(empty($moduleData -> includes))
					continue;

				foreach($moduleData -> includes as $include)
				{
					if(!$include -> collect)
						continue;

					$resFileData = file_get_contents($moduleLocation . $include -> file);

					switch($include -> type)
					{
						case 'script':

							if($include -> frontend)
								fwrite($hFileJSFrontend, "\r\n" . $resFileData);	

							if($include -> backend)
								fwrite($hFileJSBackend, "\r\n" . $resFileData);	

							break;

						case 'style':

							if($include -> frontend)
								fwrite($hFileCSSFrontend, "\r\n" . $resFileData);	

							if($include -> backend)
								fwrite($hFileCSSBackend, "\r\n" . $resFileData);	

							break;
					}
				}
			}

			fflush($hFileCSSFrontend); 
			fflush($hFileCSSBackend); 
			fflush($hFileJSFrontend); 
			fflush($hFileJSBackend); 

			flock($hFileCSSFrontend, LOCK_UN); 
			flock($hFileCSSBackend, LOCK_UN); 
			flock($hFileJSFrontend, LOCK_UN); 
			flock($hFileJSBackend, LOCK_UN); 
		}
	
		fclose($hFileCSSFrontend);
		fclose($hFileCSSBackend);
		fclose($hFileJSFrontend);
		fclose($hFileJSBackend);
	}
}
