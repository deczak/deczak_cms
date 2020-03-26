<?php

require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelModules.php';	

require_once 'CSingleton.php';

class	CModules extends CSingleton
{
	public 	$modelModules;
	public	$modulesList;
	public	$loadedList;

	private $m_pUserRights;

	public function
	init(&$_sqlConnection, CUserRights &$_pUserRights)
	{
		if($_sqlConnection === false)
			return; 
		$this -> modelModules	 =	new modelModules();
		$this -> modelModules	->	load($_sqlConnection);
		$this -> modulesList 	 =	$this -> modelModules -> getDataInstance();

		$this -> loadedList		 =	[];

		$this -> m_pUserRights	 = $_pUserRights;
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
							$this -> modulesList[$moduleIndex] = (object)array_merge((array)$moduleInstance, (array)$moduleConfig);
							$this -> modulesList[$moduleIndex] -> user_rights = $this -> m_pUserRights -> getModuleRights($_moduleId);

							$_modLocation	= CMS_SERVER_ROOT . DIR_CORE . DIR_MODULES . $moduleInstance -> module_location .'/';	
							CLanguage::instance() -> loadLanguageFile($_modLocation.'lang/', $_pageLanguage);


							$this -> loadedList[] = $this -> modulesList[$moduleIndex];
							return $this -> modulesList[$moduleIndex];
							
			case 'mantle' : include CMS_SERVER_ROOT.DIR_MANTLE.DIR_MODULES. $moduleInstance -> module_location .'/'. $moduleInstance -> module_controller .'.php';

							$moduleConfig 	= file_get_contents( CMS_SERVER_ROOT.DIR_MANTLE.DIR_MODULES. $moduleInstance -> module_location .'/module.json');
							$moduleConfig 	= ($moduleConfig !== false ? json_decode($moduleConfig) : [] );	

							$_modLocation	= CMS_SERVER_ROOT . DIR_MANTLE . DIR_MODULES . $moduleInstance -> module_location .'/';
							CLanguage::instance() -> loadLanguageFile($_modLocation.'lang/', $_pageLanguage);

							$this -> modulesList[$moduleIndex] = (object)array_merge((array)$moduleInstance, (array)$moduleConfig);

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
				$moduleConfig -> module_location = $directory;

				$moduleList_core[]	= $moduleConfig;
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
				$moduleConfig -> module_location = $directory;

				$moduleList_mantle[]	= $moduleConfig;
			}
		}

		$availableList = [];

		foreach($moduleList_core as $dirModuleKey => $dirModuleItem)
		{
			$moduleInstalled = false;

			foreach($this -> modulesList as $listItem)
			{
				if($listItem -> module_controller === $dirModuleItem -> module_controller)
				{
					$moduleInstalled = true;
					break;
				}
			}

			if($moduleInstalled)
				break;

			$dirModuleItem -> module_type = "core";

			$availableList[] = $dirModuleItem;
		}
		foreach($moduleList_mantle as $dirModuleKey => $dirModuleItem)
		{
			$moduleInstalled = false;

			foreach($this -> modulesList as $listItem)
			{
				if($listItem -> module_controller === $dirModuleItem -> module_controller)
				{
					$moduleInstalled = true;
					break;
				}
			}
			if($moduleInstalled)
				continue;

			$dirModuleItem -> module_type 		= "mantle";

			$availableList[] = $dirModuleItem;
		}
		
		return $availableList;
	}

	public function
	install(&$_sqlConnection, $moduleLocation, $moduleType, &$errorMsg)
	{
		// Read module.json

		$moduleFilepath 	= CMS_SERVER_ROOT . $moduleType .'/'. DIR_MODULES . $moduleLocation .'/module.json';

		$moduleConfig	= file_get_contents($moduleFilepath);

		if($moduleConfig === false)
			return false;

		$moduleConfig = json_decode($moduleConfig);



		// Insert module into db

		$modelModules	= new modelModules();

		$moduleData	= [];
		$moduleData['module_location'] 		= $moduleLocation;
		$moduleData['module_type'] 			= $moduleType;

		$moduleData['module_controller'] 	= $moduleConfig -> module_controller;
		$moduleData['module_name'] 			= $moduleConfig -> module_name;
		$moduleData['module_desc'] 			= $moduleConfig -> module_desc;
		$moduleData['module_icon'] 			= $moduleConfig -> module_icon;
		$moduleData['module_group'] 		= $moduleConfig -> module_group;
		$moduleData['is_frontend'] 			= strval($moduleConfig -> module_frontend);
		$moduleData['is_active'] 			= '1';

		$moduleData['create_time'] 			= time();
		$moduleData['create_by'] 			= CSession::instance() -> getValue('user_id');


		if(property_exists($moduleConfig, 'module_extends') && !empty($moduleConfig -> module_extends))
		{
			$moduleData['module_extends'] 		= $moduleConfig -> module_extends;

			##	Update Parent

			$parentCondition  = new CModelCondition();
			$parentCondition -> where('module_controller', $moduleConfig -> module_extends);

			$updateParent = [
							'module_extends_by' => $moduleConfig -> module_controller
							];

			$modelModules -> update($_sqlConnection, $updateParent, $parentCondition);

		}



		$moduleId		= 0;

		$modelModules -> insert($_sqlConnection, $moduleData, $moduleId);

		$moduleData['module_id'] = $moduleId;
		
		//	Create module Tables

		if(property_exists($moduleConfig, 'module_sheme') && is_array($moduleConfig -> module_sheme))
		{
			foreach($moduleConfig -> module_sheme as $shemeItem)
			{
				$shemeFilepath = CMS_SERVER_ROOT . $moduleType .'/'. DIR_MODULES . $moduleLocation .'/'. $shemeItem -> filename .'.php';

				if(!file_exists($shemeFilepath))
				{
					$shemeFilepath = CMS_SERVER_ROOT . $moduleType .'/'. DIR_SHEME . $shemeItem -> filename .'.php';
				}

				include $shemeFilepath;
				
				$sheme  = new $shemeItem -> filename();

				if(!$sheme -> createTable($_sqlConnection, $errorMsg))
				{
					return false;
				}
			}
		}

		if($moduleConfig -> module_frontend == 0)
		{
			$backendObjFilepath = CMS_SERVER_ROOT . DIR_DATA .'/backend/backend-id.json';
			$backendObjectId	= file_get_contents($backendObjFilepath);
			$backendObjectId	= json_decode($backendObjectId);

			$backendFilepath 	= CMS_SERVER_ROOT . DIR_DATA .'/backend/backend.json';
			$backendPages		= file_get_contents($backendFilepath);
			$backendPages		= json_decode($backendPages, true);

			$backendPages[]		= 	[
										"page_name"			=> $moduleConfig -> module_name,
										"page_title"		=> $moduleConfig -> module_name,
										"page_description"	=> "",
										"page_path"			=> $moduleConfig -> module_path,
										"node_id"			=> $backendObjectId -> next_node_id,
										"page_auth"			=> "ABKND",
										"menu_group"		=> $moduleConfig -> module_menu_group,
										"menu_order"		=> "0",
										"objects"			=> [
																	[
																		"module_id"	=> strval($moduleId),
																		"object_id"	=> "0",     					
																		"body"		=> "",
																		"params"	=> ""
																	]
																]
									];

			$backendPages		= json_encode($backendPages);

			file_put_contents($backendFilepath, $backendPages);

			$backendObjectId -> next_node_id = $backendObjectId -> next_node_id + 1;

			$backendObjectId		= json_encode($backendObjectId);

			file_put_contents($backendObjFilepath, $backendObjectId);

			$this -> modelModules	->	load($_sqlConnection);
			$this -> modulesList 	 =	$this -> modelModules -> getDataInstance();

			$_pHTAccess  = new CHTAccess();
			$_pHTAccess -> generatePart4Backend($_sqlConnection);
			$_pHTAccess -> writeHTAccess();
		}

		return true;
	}

	public function
	uninstall(&$_sqlConnection, $_moduleId)
	{
		##	Get module data from db

		$modelCondition  = new CModelCondition();
		$modelCondition	-> where('module_id', $_moduleId);

		$modelModules	 = new modelModules();
		$modelModules 	-> load($_sqlConnection, $modelCondition);	

		$moduleData		 = $modelModules -> getDataInstance()[0];

		if(empty($moduleData))
			return false;

		##	Read backend pages, delete page if module is backend

		if($moduleData -> is_frontend === 0)
		{

			$backendFilepath 	= CMS_SERVER_ROOT . DIR_DATA .'/backend/backend.json';
			$backendPages		= file_get_contents($backendFilepath);
			$backendPages		= json_decode($backendPages);

			for($i = 0; $i < count($backendPages); $i++)
			{
				if(!isset($backendPages[$i] -> objects[0] -> module_id) || $backendPages[$i] -> objects[0] -> module_id !== $_moduleId)
					continue;

				unset($backendPages[$i]);

				break;
			}
			
			$backendPages = json_encode($backendPages);

			file_put_contents($backendFilepath, $backendPages);
		}

		//	Get module config, delete tables

		$moduleFilepath 	= CMS_SERVER_ROOT . $moduleData -> module_type .'/'. DIR_MODULES . $moduleData -> module_location .'/module.json';

		$moduleConfig		= file_get_contents($moduleFilepath);

		if($moduleConfig === false)
			return false;

		$moduleConfig = json_decode($moduleConfig);

		if(property_exists($moduleConfig, 'module_sheme') && is_array($moduleConfig -> module_sheme))
		{
			foreach($moduleConfig -> module_sheme as $shemeItem)
			{
				$shemeFilepath = CMS_SERVER_ROOT . $moduleData -> module_type .'/'. DIR_MODULES . $moduleData -> module_location .'/'. $shemeItem -> filename .'.php';

				if(!file_exists($shemeFilepath))
				{
					$shemeFilepath = CMS_SERVER_ROOT . $moduleData -> module_type .'/'. DIR_SHEME . $shemeItem -> filename .'.php';
				}

				include $shemeFilepath;
				
				$sheme  = new $shemeItem -> filename();

				$sheme -> dropTable($_sqlConnection);
			}
		}

		$modelModules -> delete($_sqlConnection, $modelCondition);	


		//	Remote extends_by if set


		if(property_exists($moduleConfig, 'module_extends') && !empty($moduleConfig -> module_extends))
		{

			$parentCondition  = new CModelCondition();
			$parentCondition -> where('module_controller', $moduleConfig -> module_extends);

			$updateParent = [
							'module_extends_by' => ''
							];

			$modelModules -> update($_sqlConnection, $updateParent, $parentCondition);
		}		
	}

	public function
	existsRights(int $_moduleId, string $_rightsId)
	{
		return $this -> m_pUserRights -> existsRight($_moduleId, $_rightsId);
	}


}
?>