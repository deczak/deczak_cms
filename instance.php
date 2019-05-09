<?php

##  I N C L U D E   C O F I G U R A T I O N   F I L E S

	require_once    'config/standard.php';
	require_once    'config/directories.php';

##	P H P   E R R O R   R E P O R T I N G

	if(PHP_ERROR_REPORTING) error_reporting(E_ALL);
	ini_set('display_errors', PHP_ERROR_REPORTING);

##	I N C L U D E   C L A S S E S   &   F U N C T O N S	

	require_once	CMS_SERVER_ROOT.DIR_CORE. 'toolkit.php';

	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CSQLConnect.php';	
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CURLVariables.php';	
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CMessages.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CSysMailer.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CSession.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CCookie.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CLogin.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CLanguage.php';

##	M E S S A G E   S Y S T E M

	//	CMessages is a Singleton Class
	//	Collects messages for logging or print them to the screen for different reason

	$_pMessages		 =	CMessages::instance();
	$_pMessages		->	initialize(CMS_PROTOCOL_REPORTING, CMS_DEBUG_REPORTING);

##	S Y S T E M   M A I L E R

	//	CSysMailer is a Singleton Class
	//	Sends a mail message to a defined address for important messages

	$_pSysMailer	 =	CSysMailer::instance();
	$_pSysMailer	->	initialize(CFG::SYSMAIL,'System Message - ');

##	L A N G U A G E   S Y S T E M   /   I N I T I A L   F I L E S

	//	CLanguage is a Singleton Class
	// 	Loads and manage language files

	$_pLanguage		 = 	CLanguage::instance();		
	$_pLanguage		-> 	initialize(CFG::LANGUAGE, CFG::LANGUAGE['default']);		
	$_pLanguage		->	loadLanguageFile(CMS_SERVER_ROOT.DIR_CORE.DIR_LANGUAGES.CFG::LANGUAGE['default'] .'/', CFG::LANGUAGE['default'] );

##  S Q L   C O N N E C T I O N

	//	CSQLConnect is a Singleton Class

	$_pSQLObject 	 =	CSQLConnect::instance();
	$_pSQLObject 	->	initialize();
	if(!$_pSQLObject-> 	createConnection(CFG::MYSQL))
	{
		// nothing atm
	}	

##	C O O K I E   M A N A G E R

	//	CCookie is a Singleton Class
	// 	Set and get Cookie Data

	$_pCookies		 =	CCookie::instance();
	$_pCookies 		->	initialize(CFG::COOKIES);

##	S E S S I O N   S Y S T E M

	//	CSession is a Singleton Class
	// 	Session System without session cookies at the beginning

	$_pSession		 = 	CSession::instance();		
	$_pSession		->	updateSession();			

##	U R L   V A R I A B L E S   /   I N I T I A L   R E Q U E S T

	$_pURLVariables	 =	new CURLVariables();

	$_request[] 	 = 	[	"input" => "lang", "validate" => "strip_tags|strip_whitespaces|lowercase|!empty", "use_default" => true, "default_value" => CFG::LANGUAGE['default'] ]; // language key
	$_pURLVariables -> retrieve($_request, true, false); // GET

	$_request[] 	 = 	[	"input" => "risa", "validate" => "strip_tags|strip_whitespaces|lowercase|!empty" ]; // requested initial script action
	$_pURLVariables -> retrieve($_request, false, true); // POST (Requests for GET are still in array and handled for POST too)

	switch($_pURLVariables -> getValue("risa"))
	{
		case 'login':	## User login #################################################################################################################

						$_pLoginVariables	 =	new CURLVariables();
						$_aLoginReqStruct[]  = 	[	"input" => "username", "validate" => "strip_tags|!empty" ];
						$_aLoginReqStruct[]  = 	[	"input" => "password", "validate" => "strip_tags|!empty" ];
						if($_pLoginVariables -> retrieve($_aLoginReqStruct, false, true, true))
						{	##	Login process
							$_pLogin		 =	new CLogin(CFG::LOGIN);
							if( $_pLogin ->	login(LOGIN_OBJECT_USERS, $_pLoginVariables -> getArray()) )
							{
								header("Location: ". CMS_SERVER_URL); // temoporary until the login modul exists
								exit;
					
							}
						}
						else
						{	##	Missing data for login
							$_pMessages -> 	addMessage( $_pLanguage -> getString('ERR_CR_LOGINDTAMM') , MSG_WARNING, '', true);							
						}
						break;

		case 'logout':	## User logout ################################################################################################################

						$_pLogin	 =	new CLogin(CFG::LOGIN);
						$_pLogin 	->	logout(LOGIN_OBJECT_USERS);
						break;
	}

##	L A N G U A G E   S Y S T E M   /   S E T   R E Q   L A N G

	$_pLanguage		-> 	setActiveLanguage($_pURLVariables -> getValue("lang"));		
































?><!DOCTYPE html>
<html>
<head>
	<style>
		.message-container { max-width:1024px; margin:5px auto; padding:15px 11px; }
		.message-container:empty { display:none; }
		.message-container > p { margin: 4px 0; }
		.message-container.message-type-1 { border:1px solid red; }
		.message-container.message-type-2 { border:1px solid orange; }
		.message-container.message-type-3 { border:1px solid green; }
		.message-container.message-type-4 { border:1px solid blue; }

		input, button { height:32px; width:175px;  margin:2px 5px; }
	</style>
</head>
<body>

	<br><br>

	<?php $_pMessages -> printMessages(); ?>

	<form action="" method="post">
		<input type="hidden" name="risa" value="login">
		<input type="text" name="username" value="" placeholder="Username">
		<input type="password" name="password" value="" placeholder="Password">
		<button>Login</button>
	</form>

	<form action="" method="post">
		<input type="hidden" name="risa" value="logout">
		<button>Logout</button>
	</form>

	</body>
</html>