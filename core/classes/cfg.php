<?php

require_once 'CSingleton.php';


class	CONFIG_BASE
{
	public function
	__get($name)
	{
		return $this -> $name; 
	}	
}

class	CFG extends CSingleton
{
	##	inside
	protected	$LANGUAGE;
	protected	$ENCRYPTION;
	protected	$MYSQL;
	protected	$LOGIN;
	protected	$CRONJOB;

	##	configuration file
	protected	$ERROR_PAGES;
	protected	$USER_SYSTEM;
	protected	$ERROR_SYSTEM;
	protected	$BACKEND;
	protected	$SESSION;
	protected	$SYSTEM_MAILER;
	protected	$TEMPLATE;
	protected	$FRONTEND;

	public static function
	initialize()
	{
		$instance  = static::instance();

		$instance -> LANGUAGE 		= new CONFIG_LANGUAGE();
		$instance -> ENCRYPTION	 	= new CONFIG_ENCRYPTION();
		$instance -> MYSQL 			= new CONFIG_MYSQL();
		$instance -> LOGIN 			= new CONFIG_LOGIN();
		$instance -> CRONJOB 		= new CONFIG_CRONJOB();
	
		$configuration = file_get_contents(CMS_SERVER_ROOT.DIR_DATA.'configuration.json');
		$configuration = json_decode($configuration);

		$instance -> ERROR_PAGES	= $configuration -> ERROR_PAGES;
		$instance -> USER_SYSTEM	= $configuration -> USER_SYSTEM;
		$instance -> ERROR_SYSTEM	= $configuration -> ERROR_SYSTEM;
		$instance -> BACKEND		= $configuration -> BACKEND;
		$instance -> SESSION 		= $configuration -> SESSION;
		$instance -> SYSTEM_MAILER	= $configuration -> SYSTEM_MAILER;
		$instance -> TEMPLATE		= $configuration -> TEMPLATE;
		$instance -> FRONTEND		= $configuration -> FRONTEND;
	}

	public function
	__get($name)
	{
		return $this -> $name; 
	}
}

