<?php

	declare(strict_types=1);

##  S E R V E R   R O O T   &   U R L

	define('CMS_SERVER_ROOT', '</www/htdocs/>');

	define('CMS_SERVER_URL' , '<protocol>://www.<domain>.<tld>/');
	define('CMS_SERVER_URL_BACKEND' , '<protocol>://www.<domain>.<tld>/backend/');

	define('CMS_URL_BASE', false);

##	L O G I N   O B J E C T   N A M E S

	define('LOGIN_OBJECT_BACKEND','ABKND');

##	E R R O R   R E P O R T I N G   &   D E B U G

	define('PHP_ERROR_REPORTING',true);
	define('CMS_PROTOCOL_REPORTING',false);
	define('CMS_DEBUG_REPORTING',false);
	define('CMS_BENCHMARK',false);

##	B A C K E N D   N A M E

	define('CMS_BACKEND_NAME','backend');
	define('CMS_BACKEND_PUBLIC','backend');
	define('CMS_BACKEND_TEMPLATE','backend');
	define('CMS_BACKEND_STARTBUTTON','<b>'. CMS_BACKEND_NAME .'</b>');
	
##  C O N F I G   C L A S S

class CONFIG_TEMPLATE extends CONFIG_BASE
{
	protected 	$DEFAULT_TEMPLATE		=	'default';
	protected	$ERROR_TEMPLATE			= 	'default';
}
	
class CONFIG_CRONJOB extends CONFIG_BASE
{
	protected	$CRON_DIRECTORY_PUBLIC	= 	true;
}

class CONFIG_LOGIN extends CONFIG_BASE
{
	protected	$COOKIE_HTTPS			=	false;			## -> eigener zweig
}
	
class CONFIG_MYSQL extends CONFIG_BASE
{
	protected	$TABLE_COLLATE		=	"utf8mb4_unicode_ci";
	protected	$TABLE_CHARSET		=	"utf8mb4";
	protected	$TABLE_ENGINE		=	"innoDB";

	protected	$PRIMARY_DATABASE	= 	'1';

	protected	$DATABASE 			= 	[
											[
											"server" 		=> 	"***",
											"user"			=>	"***",
											"password"		=>	"***",
											"database"		=>	"***",
											"name"			=>	'1'						
											]
										];		
}
	
class CONFIG_ENCRYPTION extends CONFIG_BASE
{
	protected 	$BASEKEY			=	'4democracy';
	protected 	$METHOD				=	'AES-256-CBC';
}

class CONFIG_LANGUAGE extends CONFIG_BASE
{
	protected	$DEFAULT_IN_URL		=	false;
	protected 	$BACKEND			=	[					
										"en" =>	[
												"key"		=>	"en",
												"name"		=>	"English"
												],
										"de" =>	[
												"key"		=>	"de",
												"name"		=>	"Deutsch"
												]							
										]; 										
}

##	Bratwurstbratgerät

require_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CSingleton.php';

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
	protected	$TEMPLATE;
	protected	$CRONJOB;
	
	##	configuration file
	protected	$ERROR_PAGES;
	protected	$USER_SYSTEM;
	protected	$BACKEND;
	protected	$SESSION;
	protected	$SYSTEM_MAILER;

	public function
	initialize()
	{
		$this -> LANGUAGE 		= new CONFIG_LANGUAGE();
		$this -> ENCRYPTION	 	= new CONFIG_ENCRYPTION();
		$this -> MYSQL 			= new CONFIG_MYSQL();
		$this -> LOGIN 			= new CONFIG_LOGIN();
		$this -> TEMPLATE 		= new CONFIG_TEMPLATE();
		$this -> CRONJOB 		= new CONFIG_CRONJOB();

		$configuration = file_get_contents(CMS_SERVER_ROOT.DIR_DATA.'configuration.json');
		$configuration = json_decode($configuration);

		$this -> ERROR_PAGES	= $configuration -> ERROR_PAGES;
		$this -> USER_SYSTEM	= $configuration -> USER_SYSTEM;
		$this -> BACKEND		= $configuration -> BACKEND;
		$this -> SESSION 		= $configuration -> SESSION;
		$this -> SYSTEM_MAILER 	= $configuration -> SYSTEM_MAILER;
	}

	public function
	__get($name)
	{
		return $this -> $name; 
	}
}

CFG::instance() -> initialize();	

?>