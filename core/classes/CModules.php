<?php

require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelModules.php';	

require_once 'CSingleton.php';

class	CModules extends CSingleton
{
	public 	$modelModules;
	public	$modulesList;
	public	$loadedList;

	public function
	init(&$_sqlConnection)
	{
		$this -> modelModules	 =	new modelModules();
		$this -> modelModules	->	load($_sqlConnection);
		$this -> modulesList 	 =	$this -> modelModules -> getDataInstance();

		$this -> loadedList		 =	[];
	}

	/**
	 *	Loads the Module by given moduleID if not already loaded 
	 */
	public function
	loadModule(int $_moduleId)
	{
		$moduleInstance = NULL;

		if(!$this -> getModule($_moduleId, $moduleInstance))
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

		switch($moduleInstance -> module_type) 
		{
			case 'core'   :	include CMS_SERVER_ROOT.DIR_CORE.DIR_MODULES. $moduleInstance -> module_location .'/'. $moduleInstance -> module_controller .'.php';

							$moduleConfig 	= file_get_contents( CMS_SERVER_ROOT.DIR_CORE.DIR_MODULES. $moduleInstance -> module_location .'/module.json');
							$moduleConfig 	= ($moduleConfig !== false ? json_decode($moduleConfig) : [] );	
							$moduleInstance = (object)array_merge((array)$moduleInstance, (array)$moduleConfig);
							$this -> loadedList[] = $moduleInstance;
							return $moduleInstance;
							
			case 'mantle' : include CMS_SERVER_ROOT.DIR_MANTLE.DIR_MODULES. $moduleInstance -> module_location .'/'. $moduleInstance -> module_controller .'.php';

							$moduleConfig 	= file_get_contents( CMS_SERVER_ROOT.DIR_MANTLE.DIR_MODULES. $moduleInstance -> module_location .'/module.json');
							$moduleConfig 	= ($moduleConfig !== false ? json_decode($moduleConfig) : [] );	
							$moduleInstance = (object)array_merge((array)$moduleInstance, (array)$moduleConfig);
							$this -> loadedList[] = $moduleInstance;
							return $moduleInstance;
		}

		return false;
	}

	public function
	getModule(int $_moduleId, &$_moduleIinstance)
	{
		$modulesCount = count($this -> modulesList);

		for($i = 0; $i < $modulesCount; $i++)
		{
			if($this -> modulesList[$i] -> module_id === $_moduleId)
			{
				$_moduleIinstance = $this -> modulesList[$i];
				return true;
			}
		}

		return false;
	}

	public function
	&getModules()
	{
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
				break;

			$dirModuleItem -> module_type 		= "mantle";

			$availableList[] = $dirModuleItem;
		}
		
		return $availableList;
	}

	public function
	install(&$_sqlConnection, $moduleLocation, $moduleType)
	{
		// Read module.json

		$moduleFilepath 	= CMS_SERVER_ROOT . $moduleType .'/'. DIR_MODULES . $moduleLocation .'/module.json';

		$moduleConfig	= file_get_contents($moduleFilepath);


		if($moduleConfig === false)
			return false;

		$moduleConfig = json_decode($moduleConfig);

		// Insert module into db

		$moduleData	= [];
		$moduleData['module_location'] 		= $moduleLocation;
		$moduleData['module_type'] 			= $moduleType;

		$moduleData['module_controller'] 	= $moduleConfig -> module_controller;
		$moduleData['module_name'] 			= $moduleConfig -> module_name;
		$moduleData['module_icon'] 			= $moduleConfig -> module_icon;
		$moduleData['module_group'] 		= $moduleConfig -> module_group;
		$moduleData['is_frontend'] 			= strval($moduleConfig -> module_frontend);
		$moduleData['is_active'] 			= '1';

		$moduleData['create_time'] 			= time();
		$moduleData['create_by'] 			= CSession::instance() -> getValue('user_id');

		$moduleId		= 0;

		$modelModules	= new modelModules();
		$modelModules -> insert($_sqlConnection, $moduleData, $moduleId);

		$moduleData['module_id'] = $moduleId;
		
		//	Create module Tables

		if(property_exists($moduleConfig, 'module_sheme') && is_array($moduleConfig -> module_sheme))
		{
			foreach($moduleConfig -> module_sheme as $shemeItem)
			{

				include CMS_SERVER_ROOT . $moduleType .'/'. DIR_MODULES . $moduleLocation .'/'. $shemeItem -> filename .'.php';


				$errorMsg = '';
				
				$sheme  = new $shemeItem -> filename();

				if(!$sheme -> createTable($_sqlConnection, $errorMsg))
				{

					// TODO :: createTable failed, error in $errorMsg (todo: add error into this variable)

				}
			}
		}


		if($moduleConfig -> module_frontend == 0)
		{


			$backendObjFilepath = CMS_SERVER_ROOT . DIR_DATA .'/backend-id.json';
			$backendObjectId	= file_get_contents($backendObjFilepath);
			$backendObjectId	= json_decode($backendObjectId);


			$backendFilepath 	= CMS_SERVER_ROOT . DIR_DATA .'/backend.json';
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
			$_pHTAccess -> generatePart4Backend();
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

			$backendFilepath 	= CMS_SERVER_ROOT . DIR_DATA .'/backend.json';
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
				include CMS_SERVER_ROOT . $moduleData -> module_type .'/'. DIR_MODULES . $moduleData -> module_location .'/'. $shemeItem -> filename .'.php';

				$sheme  = new $shemeItem -> filename();

				$sheme -> dropTable($_sqlConnection);
			}
		}

		$modelModules -> delete($_sqlConnection, $modelCondition);	
	}


}
?>