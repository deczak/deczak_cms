<?php

	declare(strict_types=1);

##  S E R V E R   R O O T   &   U R L

	define('CMS_SERVER_ROOT', '%SERVER_ROOT%');

	define('CMS_SERVER_URL' , '%SERVER_URL%');
	define('CMS_SERVER_URL_BACKEND' , '%SERVER_URL%backend/');

##	L O G I N   O B J E C T   N A M E S

	define('LOGIN_OBJECT_USERS','0000');
	define('LOGIN_OBJECT_BACKEND','ABKND');

##	T I M E   F O R M A T S   F O R   D A T E   F U N C T I O N

	define('TIME_FORMAT_SYSMAIL','Y-m-d @ H:i:s');
	define('TIME_FORMAT_USERMAIL','Y-m-d @ H:i:s');
	define('TIME_FORMAT_BACKENDVIEW','Y-m-d @ H:i:s');

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

class CONFIG_SYSTEM_MAILER extends CONFIG_BASE
{
	protected	$RECEIVER_NAME			=	'%SYSMAIL_NAME%';
	protected	$RECEIVER_ADDRESS		=	'%SYSMAIL_MAIL%';
	protected	$SUBJECT_PREFIX			=	'';
	protected	$LOCK_TIMEOUT			=	3600;
	
	protected	$SMIME_CERT_CRYPT_FILE	=	"";		// PEM (*.crt)
	protected	$SMIME_CERT_SIGN_FILE	=	"";		// PEM (*.crt)
	protected	$SMIME_PRIVKEY_FILE		=	"";		// PEM + PKCS#8 (*.pem)
	protected	$SMIME_PRIVKEY_PASS		=	"";		// Key Pass
}
	
class CONFIG_CRONJOB extends CONFIG_BASE
{
	protected	$REPORT_WEEKLYACCESS	=	true;
	protected	$CRON_DIRECTORY_PUBLIC	= 	true;
}

class CONFIG_SESSION extends CONFIG_BASE
{
	protected	$SPAM_ACCESS_TIMEOUT	=	60;
	protected	$SPAM_ACCESS_LIMIT		=	5;
	protected	$COOKIE_HTTPS			=	%COOKIE_HTTPS%;

	protected	$TIMEOUT				= 	60 * 60 * 4;
}
	
class CONFIG_LOGIN extends CONFIG_BASE
{
	protected	$FAIL_LIMIT		=	5;
}
	
class CONFIG_MYSQL extends CONFIG_BASE
{
	protected	$TABLE_COLLATE		=	"utf8mb4_unicode_ci";
	protected	$TABLE_CHARSET		=	"utf8mb4";
	protected	$TABLE_ENGINE		=	"innoDB";

	protected	$PRIMARY_DATABASE	= 	'1';

	protected	$DATABASE 			= 	[
											[
											"server" 		=> 	"%DATABASE_SERVER%",
											"user"			=>	"%DATABASE_USER%",
											"password"		=>	"%DATABASE_PASSWORD%",
											"database"		=>	"%DATABASE_DATABASE%",
											"name"			=>	'1'						
											]
										];		
}
	
class CONFIG_ENCRYPTION extends CONFIG_BASE
{
	protected 	$BASEKEY			=	'%BASEKEY%';
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

class	CONFIG extends CSingleton
{
	protected	$LANGUAGE;
	protected	$ENCRYPTION;
	protected	$MYSQL;
	protected	$LOGIN;
	protected	$SESSION;
	protected	$SYSTEM_MAILER;
	protected	$TEMPLATE;
	protected	$CRONJOB;

	public function
	initialize()
	{
		$this -> LANGUAGE 		= new CONFIG_LANGUAGE();
		$this -> ENCRYPTION	 	= new CONFIG_ENCRYPTION();
		$this -> MYSQL 			= new CONFIG_MYSQL();
		$this -> LOGIN 			= new CONFIG_LOGIN();
		$this -> SESSION 		= new CONFIG_SESSION();
		$this -> SYSTEM_MAILER 	= new CONFIG_SYSTEM_MAILER();
		$this -> TEMPLATE 		= new CONFIG_TEMPLATE();
		$this -> CRONJOB 		= new CONFIG_CRONJOB();
	}

	public function
	__get($name)
	{
		return $this -> $name; 
	}
}

$config 	= CONFIG::instance();
$config    -> initialize();	

?>