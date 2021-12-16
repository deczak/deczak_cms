<?php

/**
 * 	Update Class for the Content Managment System. This contains all stuff that are parts of the core system as those
 *  getting updated regular by git. 
 * 
 * 	FS = File System, DB = Database
 * 
 * 	This class is not finished as it grows with the time on his features. It works only if the files are updated by git
 * 
 * 	currently state
 * 
 * 		- detect the difference between FS and DB version
 * 
 * 		- update database if the difference has been detected
 * 
 * 	todo
 * 
 * 		- update column parameters, column index, key, constraints on tables
 *
 */
class cmsUpdate
{
	public function
	__construct()
	{
	}

	/**
	 * 	This function detects a CMS update on FS versions check in compare to the DB version
	 * 
	 * 	@return bool true if a FS update is detected, otherwise false
	*/
	public function
	detectUpdate() : bool
	{
		cmsLog::add('cmsUpdate::detectUpdate -- Call');

		if(!file_exists(CMS_SERVER_ROOT.'VERSION-DB'))
			return true;		

		$versionDB = file_get_contents(CMS_SERVER_ROOT.'VERSION-DB');

		if($versionDB === false)
		{
			// File exists but trying to read it failed

			cmsLog::add('cmsUpdate::detectUpdate -- Read error on VERSION-DB but file exists', true);
			cmsLog::add('cmsUpdate::detectUpdate -- Return true to initiate update process');

			// Try to update

			return true;
		}

		$versionFS = file_get_contents(CMS_SERVER_ROOT.'VERSION-FS');

		if($versionFS === false)
		{
			// File exists but trying to read it failed

			cmsLog::add('cmsUpdate::detectUpdate -- Read error on VERSION-FS. File has to be exists and must be readable!', true);
			cmsLog::add('cmsUpdate::detectUpdate -- System halted!', true);

			// This File is required

			echo "The System can't find the VERSION-FS file for the update process<br><br>System halted";
			exit;
		}

		if((int)$versionFS !== (int)$versionDB)
			return true;

		return false;
	}

	/**
	 * 	This function updates the database tables to their has to be state.
	 * 	
	 * 	@return bool true if the updates ends successful, otherwise false
	 */
	public function
	updateDatabase() : bool
	{
		cmsLog::add('cmsUpdate::updateDatabase -- Call');

		$pDBInstance  = CDatabase::instance();
		$dbConnection = $pDBInstance -> getConnection(CFG::GET() -> MYSQL -> PRIMARY_DATABASE);
		$shemeList    = [];

		##	Find core sheme files

		$shemeDirIterator = new DirectoryIterator(CMS_SERVER_ROOT.'core/shemes/');
		foreach($shemeDirIterator as $dirItem)
		{
			if(!$dirItem -> isFile())
				continue;


			if($dirItem -> getExtension() !== 'php')
				continue;

			include_once	CMS_SERVER_ROOT.'core/shemes/'. $dirItem -> getFilename();

			$shemeList[] = $dirItem -> getBasename('.php');
		}

		##	Transaction fails when changing the table schema due to PHP behavior
		##	$dbConnection -> beginTransaction();
	
		##	Update sheme files

		foreach($shemeList as $sheme)
		{
			$shemeInstance = new $sheme;
			if(!$shemeInstance -> updateTable($dbConnection))
			{
				##	Transaction fails when changing the table schema due to PHP behavior
				##	$dbConnection -> rollBack();

				cmsLog::add('cmsUpdate::updateDatabase -- Update Table aborted, update process stopped');

				return false;
			}	
		}
	
		##	Transaction fails when changing the table schema due to PHP behavior
		## 	$dbConnection -> commit();

		##	Update Version File

		$versionFS = file_get_contents(CMS_SERVER_ROOT.'VERSION-FS');
		if($versionFS !== false)
			file_put_contents(CMS_SERVER_ROOT.'VERSION-DB', $versionFS);

		cmsLog::add('cmsUpdate::updateDatabase -- update Process successful, update VERSION-DB');

		return true;
	}

	public function 
	execUpdate()
	{
		$this -> updateDatabase();
		$this -> updateConfiguration();
		$this -> updateBEMenu();
	}





	/**
	 * 	This function updates the /data/configuration.json file with new added settings
	 * 
	 * 	@return bool true if the updates ends successful, otherwise false
	 */
	public function
	updateConfiguration() : bool
	{
		cmsLog::add('cmsUpdate::updateConfiguration -- Call');

		$defConfigInfo = file_get_contents('../data/configuration-default.json');

		if($defConfigInfo === false)
		{
			cmsLog::add('cmsUpdate::updateConfiguration -- aborted, can not read default configuration');
			return false;
		}

		$defConfigInfo = json_decode($defConfigInfo);

		if($defConfigInfo === null)
		{
			cmsLog::add('cmsUpdate::updateConfiguration -- aborted, default configuration not valid');
			return false;
		}

		$actConfigInfo = file_get_contents('../data/configuration.json');

		if($defConfigInfo === false)
		{
			cmsLog::add('cmsUpdate::updateConfiguration -- aborted, can not read configuration');
			return false;
		}

		$actConfigInfo = json_decode($actConfigInfo);

		if($defConfigInfo === null)
		{
			cmsLog::add('cmsUpdate::updateConfiguration -- aborted, configuration is not valid');
			return false;
		}

		tk::object_merge($defConfigInfo, $actConfigInfo);

		$actConfigInfo = json_encode($defConfigInfo);
		file_put_contents('../data/configuration.json', $actConfigInfo);

		return true;
	}

	/**
	 * 	This function updates the backend menu for core modules
	 * 
	 * 	@return bool true if the updates ends successful, otherwise false
	 */
	public function
	updateBEMenu() : bool
	{
		cmsLog::add('cmsUpdate::updateBEMenu -- Call');

		$pDBInstance  	 = CDatabase::instance();
		$dbConnection 	 = $pDBInstance -> getConnection(CFG::GET() -> MYSQL -> PRIMARY_DATABASE);

		# Update backend menu groups

		include_once	CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelBackendMenu.php';

		$modelBackendMenu	 = new modelBackendMenu();
		$modelBackendMenu	-> load($dbConnection);
		$backendMenuList 	 = $modelBackendMenu -> getResult();

		$shemeBackendMenu 	 = new shemeBackendMenu;

		$seedList = $shemeBackendMenu -> getSeedList();
		foreach($seedList as $seed)
		{
			$groupExists = false;

			foreach($backendMenuList as $beML)
			{
				if($beML -> menu_group === $seed['menu_group'])
				{
					$groupExists = true;
					break;
				}
			}

			if($groupExists)
			{
				$condition		     = new CModelCondition();
				$condition			-> where('id', $beML -> id);

				$modelBackendMenu	-> update(
					$dbConnection,
					$seed, 
					$condition);
			}
			else
			{
				$modelBackendMenu	-> insert(
					$dbConnection,
					$seed);
			}
		}

		# Find backend core modules and loop them

		$condition		 = new CModelCondition();
		$condition		-> where('module_type', 'core')
						-> where('is_frontend', '0');

		$modelModules	 = new modelModules();
		$modelModules	-> load($dbConnection, $condition);
		$modulesList 	 = $modelModules -> getResult();

		foreach($modulesList as $module)
		{
			# Read backend module.json

			$moduleConfig 	= file_get_contents( CMS_SERVER_ROOT.DIR_CORE.DIR_MODULES. $module -> module_location .'/module.json');
			$moduleConfig 	= ($moduleConfig !== false ? json_decode($moduleConfig) : [] );	

			$pModulesInstall = new CModulesInstall;
			$moduleData = $pModulesInstall -> getModuleData($moduleConfig, $module -> module_location, $module -> module_type);
	
			$moduleData = json_decode(json_encode($moduleData));

			# Check for page -> menu group info

			if(property_exists($moduleData, 'page') && property_exists($moduleData -> page, 'menu_group') && !empty($moduleData -> page -> menu_group))
			{
				# Get Node-ID by Module-ID

				$condition		 = new CModelCondition();
				$condition		-> where('module_id', $module -> module_id);

				$modelBackendPageObject	 = new modelBackendPageObject();
				$modelBackendPageObject	-> load($dbConnection, $condition);

				$beObjektInfo 	 = $modelBackendPageObject -> getResult();
				$beObjektInfo	 = (!empty($beObjektInfo) ? reset($beObjektInfo) : null);

				if(empty($beObjektInfo))
					continue;

				# Set menu group info by Node-ID

				$condition		     = new CModelCondition();
				$condition			-> where('tb_backend_page.node_id', $beObjektInfo -> node_id);

				$modelBackendPage	 = new modelBackendPage();
				$modelBackendPage	-> updateRestricted(
					$dbConnection,
					[
						'menu_group' => $moduleData -> page -> menu_group
					], 
					$condition);
			}
		}

		return true;
	}
}
