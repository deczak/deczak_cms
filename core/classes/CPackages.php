<?php

class	CPackages
{
	public function
	__construct()
	{
	}

	public function
	install(CDatabaseConnection &$_dbConnection, string $_filepath) : bool
	{
		$extractToDir = CMS_SERVER_ROOT. DIR_TEMP . hash_file('md5', $_filepath);

		$pZipArchive = new ZipArchive;

		if($pZipArchive -> open($_filepath) !== true)
		{
			// error
			return false;
		}

		if($pZipArchive -> extractTo($extractToDir) !== true)
		{
			// error
			return false;
		}

   		$pZipArchive->close();

		$packageInfoFilepath = $extractToDir . '/package';


		//$_dbConnection -> beginTransaction(); .. later

		
		$pPackagesInstall = new CPackagesInstall;
		if(!$pPackagesInstall -> install($_dbConnection, $packageInfoFilepath, $extractToDir . '/'))
		{
			
			//$_dbConnection -> rollBack();
			return false;
		}


		tk::rrmdir($extractToDir);
		

		//$_dbConnection -> commit();
		return true;
	}

}

class	CPackagesInstall
{
	public function
	__construct()
	{
	}

	public function
	install(CDatabaseConnection &$_dbConnection, string $_filepath, string $_exractedDirPath) : bool
	{
		if(!file_exists($_filepath))
		{
			// error 
			return false;
		}

		## Read package file

		$rawPackageInfo = parse_ini_file($_filepath, true, INI_SCANNER_RAW);

		if($rawPackageInfo === false)
		{
			// error
			return false;
		}

		## Get package info

		$packageInfo = $this -> getPackageInfo($rawPackageInfo);

		if($packageInfo === null)
		{

			// error
			return false;
		}

		## Determine destination

		$destLocation = null;
		switch($packageInfo -> info -> type)
		{
			case 'page-template':

				$destLocation 		= CMS_SERVER_ROOT . DIR_TEMPLATES;
				$destLocationPublic = CMS_SERVER_ROOT . DIR_PUBLIC . DIR_TEMPLATES;

				if(!is_dir($destLocationPublic))
					if(!mkdir($destLocationPublic, 0777, true))
						return false;

				break;

			default:

				// error
				return false;
		}
		
		## Check destination & package file

		if(is_dir($destLocation . $packageInfo -> info -> dir))
		{
			if(file_exists($destLocation . $packageInfo -> info -> dir .'/package'))
			{
				$destRawPackageInfo = parse_ini_file($destLocation . $packageInfo -> info -> dir .'/package', true, INI_SCANNER_RAW);
				$destPackageInfo 	= $this -> getPackageInfo($destRawPackageInfo);

				if((float)number_format((float)preg_replace("/[^-0-9\.]/","", $packageInfo -> info -> version), 2, '.', '') <= (float)number_format((float)preg_replace("/[^-0-9\.]/","", $destPackageInfo -> info -> version), 2, '.', ''))
				{
					// error
					return false;
				}
			}
			else
			{
				// error
				return false;
			}
		}
		else
		{
			if(!mkdir($destLocation . $packageInfo -> info -> dir, 0777, true))
			{
				// error
				return false;
			}
		}

		## Copy package file

		copy(
			$_filepath, 
			$destLocation . $packageInfo -> info -> dir .'/package'
		);

		## Exec pull - check

		for($i = 0; $i < count($packageInfo -> exec -> pull); $i++)
		{
			$job = explode("' '", $packageInfo -> exec -> pull[$i]);

			if(count($job) !== 2)
			{
				// error
				return false;
			}

			foreach($job as &$tmpDo)
			{
				$tmpDo = trim($tmpDo, " '\"");

				if(!$this -> validateExecPath($tmpDo))
				{
					// error
					return false;
				}
			}
			
			$packageInfo -> exec -> pull[$i] = $job;
		}

		## Exec pull - exec

		for($i = 0; $i < count($packageInfo -> exec -> pull); $i++)
		{
			$jobPath = pathinfo($packageInfo -> exec -> pull[$i][1], PATHINFO_DIRNAME);

			if(!is_dir($jobPath) && !mkdir($jobPath, 0777, true))
			{
				// error
				return false;
			}


			$copyDestination 	= $destLocation;
			
 			$fileExtension 		= pathinfo(strtolower($packageInfo -> exec -> pull[$i][0]), PATHINFO_EXTENSION);



			switch($packageInfo -> info -> type)
			{
				case 'page-template':

					switch($fileExtension)
					{
						case 'mp4':
						case 'jpg':
						case 'jpeg':
						case 'webp':
						case 'png':
						case 'gif':
						case 'css':
						case 'less':
						case 'js':
						case 'woff':
						case 'woff2':

							$copyDestination = $destLocationPublic;
							break;
					}		

					break;
			}		


 			$fileLocation 	= pathinfo($packageInfo -> exec -> pull[$i][1], PATHINFO_DIRNAME);

			if(!is_dir($copyDestination . $fileLocation))
				if(!mkdir($copyDestination . $fileLocation, 0777, true))
					return false;

			copy(
				$_exractedDirPath . $packageInfo -> exec -> pull[$i][0], 
				$copyDestination . $packageInfo -> exec -> pull[$i][1]
				);

		}

		return true;
	}

	public function 
	getPackageInfo(array $_rawPackageInfo) : ?object
	{
		$_rawPackageInfo = (object)$_rawPackageInfo;

		if(!property_exists($_rawPackageInfo, 'info'))
		{
			// error
			return null;
		}
		
		$_rawPackageInfo -> info = (object)$_rawPackageInfo -> info;

		if(!property_exists($_rawPackageInfo -> info, 'scheme'))
		{
			// error
			return null;
		}
		
		$packageInfo = null;

		switch($_rawPackageInfo -> info -> scheme)
		{
			case 1:

				$pPackagesInstallS1 = new CPackagesInstallS1;
				$packageInfo = $pPackagesInstallS1 -> getPackagesInfo($_rawPackageInfo);
				break;
		}

		return $packageInfo;
	}

	private function
	validateExecPath(string $_path) : bool
	{
		if(strpos($_path, '//') !== false)		return false;
		if(strpos($_path, '\\') !== false)		return false;
		if(strpos($_path, '../') !== false)		return false;
		if(strpos($_path, '..\\') !== false)	return false;

		return true;
	}
}

class	CPackagesInstallS1 // Package Scheme 1
{
	public function
	__construct()
	{
	}

	private function
	setPackageInfoProp(stdClass $_rawPackageInfo, string $_prop)
	{
		if(!property_exists($_rawPackageInfo, $_prop))
			throw new Exception('prop does not exists');

		return $_rawPackageInfo -> $_prop;
	}

	private function
	setPackageInfoArray(array $_rawPackageInfo, string $_prop)
	{
		if(!isset($_rawPackageInfo[$_prop]))
			throw new Exception('prop does not exists');

		return $_rawPackageInfo[$_prop];
	}

	public function
	getPackagesInfo(stdClass $_rawPackageInfo) : ?object
	{
		
		$packageInfo  = new stdClass;
		$packageInfo -> info  = new stdClass;
		$packageInfo -> info -> scheme =  $_rawPackageInfo -> info -> scheme;

		$packageInfo -> exec  = new stdClass;
		$packageInfo -> exec -> pull = [];
		$packageInfo -> exec -> drop = [];
		$packageInfo -> exec -> move = [];
		$packageInfo -> exec -> dbup = [];

		try
		{
			$packageInfo -> info -> type 		= $this -> setPackageInfoProp($_rawPackageInfo -> info, 'type');
			$packageInfo -> info -> version 	= $this -> setPackageInfoProp($_rawPackageInfo -> info, 'version');
			$packageInfo -> info -> name		= $this -> setPackageInfoProp($_rawPackageInfo -> info, 'name');
			$packageInfo -> info -> description = $this -> setPackageInfoProp($_rawPackageInfo -> info, 'description');

			// dir wird eventuell nicht immer benötigt, wenn nötig durch type händeln
			$packageInfo -> info -> dir	 		= $this -> setPackageInfoProp($_rawPackageInfo -> info, 'dir');

			$packageInfo -> exec -> pull = $this -> setPackageInfoArray($_rawPackageInfo -> exec, 'pull');
		}
		catch (Exception $exception)
		{
					
			// error 
			echo 'Caught exception: ',  $exception -> getMessage(), "<br>";
			return null;
			
		}

		return $packageInfo;
	}
}
