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
		CLog::add('cmsUpdate::detectUpdate -- Call');

		if(!file_exists(CMS_SERVER_ROOT.'VERSION-DB'))
			return true;		

		$versionDB = file_get_contents(CMS_SERVER_ROOT.'VERSION-DB');

		if($versionDB === false)
		{
			// File exists but trying to read it failed

			CLog::add('cmsUpdate::detectUpdate -- Read error on VERSION-DB but file exists', true);
			CLog::add('cmsUpdate::detectUpdate -- Return true to initiate update process');

			// Try to update

			return true;
		}

		$versionFS = file_get_contents(CMS_SERVER_ROOT.'VERSION-FS');

		if($versionFS === false)
		{
			// File exists but trying to read it failed

			CLog::add('cmsUpdate::detectUpdate -- Read error on VERSION-FS. File has to be exists and must be readable!', true);
			CLog::add('cmsUpdate::detectUpdate -- System halted!', true);

			// This File is required

			echo "The System can't find the VERSION-FS file for the update process<br><br>System halted";
			exit;

		}

		if((int)$versionFS !== (int)$versionDB)
			return true;

		return false;
	}

	/**
	 * 	This Function updates the database tables to their has to be state.
	 * 	
	 * 	@return bool true if the updates ends successful, otherwise false
	*/
	public function
	updateDatabase() : bool
	{
		CLog::add('cmsUpdate::updateDatabase -- Call');

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

				CLog::add('cmsUpdate::updateDatabase -- Update Table aborted, update process stopped');

				return false;
			}	
		}
	
		##	Transaction fails when changing the table schema due to PHP behavior
		## 	$dbConnection -> commit();

		##	Update Version File

		$versionFS = file_get_contents(CMS_SERVER_ROOT.'VERSION-FS');
		if($versionFS !== false)
			file_put_contents(CMS_SERVER_ROOT.'VERSION-DB', $versionFS);

		CLog::add('cmsUpdate::updateDatabase -- update Process successful, update VERSION-DB');

		return true;
	}
}
