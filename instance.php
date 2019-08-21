<?php

defined('CMS_BACKEND') or define('CMS_BACKEND', false);

##  I N C L U D E   C O F I G U R A T I O N   F I L E S

	require_once    'config/standard.php';
	require_once    'config/directories.php';

##	B E N C H M A R K

	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CBenchmark.php';
	CBenchmark::instance() -> initialize(CMS_BENCHMARK);
	CBenchmark::instance() -> measurementPoint('processing include');	

##	P H P   E R R O R   R E P O R T I N G

	if(PHP_ERROR_REPORTING) error_reporting(E_ALL);
	ini_set('display_errors', PHP_ERROR_REPORTING);

##	I N C L U D E   C L A S S E S   &   F U N C T O N S	

	require_once	CMS_SERVER_ROOT.DIR_CORE. 'toolkit.php';
	
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CView.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CModel.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CController.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CSheme.php';

	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CSQLConnect.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CURLVariables.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CMessages.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CSysMailer.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CSession.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CCookie.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CLogin.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CLanguage.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CImperator.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CBasic.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CModules.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CHTML.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CTemplate.php';

	CBenchmark::instance() -> measurementPoint('initialize and execute system classes');	

##	M E S S A G E   S Y S T E M

	//	CMessages is a singleton class
	//	Collects messages for logging or print them to the screen for different reason

	$_pMessages		 =	CMessages::instance();
	$_pMessages		->	initialize(CMS_PROTOCOL_REPORTING, CMS_DEBUG_REPORTING);

##	S Y S T E M   M A I L E R

	//	CSysMailer is a singleton class
	//	Sends a mail message to a defined address for important messages

	$_pSysMailer	 =	CSysMailer::instance();
	$_pSysMailer	->	initialize(CFG::SYSMAIL,'System Message - ');

##	L A N G U A G E   S Y S T E M   /   I N I T I A L   F I L E S

	//	CLanguage is a singleton class
	// 	Loads and manage language files

	$_pLanguage		 = 	CLanguage::instance();		
	$_pLanguage		-> 	initialize(CFG::LANGUAGE, CFG::LANGUAGE['default']);		
	$_pLanguage		->	loadLanguageFile(CMS_SERVER_ROOT.DIR_CORE.DIR_LANGUAGES.CFG::LANGUAGE['default'] .'/', CFG::LANGUAGE['default'] );

##  S Q L   C O N N E C T I O N

	//	CSQLConnect is a singleton class

	$_pSQLObject 	 =	CSQLConnect::instance();
	$_pSQLObject 	->	initialize();
	if(!$_pSQLObject-> 	createConnection(CFG::MYSQL))
	{
		// nothing atm
		echo 'db connect error';
	}	

##	C O O K I E   M A N A G E R

	//	CCookie is a singleton class
	// 	Set and get Cookie Data

	$_pCookies		 =	CCookie::instance();
	$_pCookies 		->	initialize(CFG::COOKIES);

##	S E S S I O N   S Y S T E M

	//	CSession is a singleton class
	// 	Session System without session cookies at the beginning

	$_pSession		 = 	CSession::instance();		
	$_pSession		->	updateSession();			

##	U R L   V A R I A B L E S   /   I N I T I A L   R E Q U E S T

	$_pURLVariables	 =	new CURLVariables();

	$_request[] 	 = 	[	"input" => "lang", 		  	 	"validate" => "strip_tags|strip_whitespaces|lowercase|!empty",          "use_default" => true, "default_value" => CFG::LANGUAGE['default'] ]; // language key
	$_request[] 	 = 	[	"input" => "cms-node",  		"validate" => "strip_tags|strip_whitespaces|lowercase|is_digit|!empty", "use_default" => true, "default_value" => 2     ]; // node_id
	$_request[] 	 = 	[	"input" => "cms-ctrl-action",	"validate" => "strip_tags|strip_whitespaces|lowercase|!empty", 			"use_default" => true, "default_value" => []	]; // requested controller action
	$_pURLVariables -> retrieve($_request, true, false); // GET

	$_request		 =	[];
	$_request[] 	 = 	[	"input" => "cms-risa",   	 	"validate" => "strip_tags|strip_whitespaces|lowercase|!empty" ]; 		// requested initial script action
	$_request[] 	 = 	[	"input" => "cms-tlon",   		"validate" => "strip_tags|strip_whitespaces|!empty", 					"use_default" => true, "default_value" => ''    ]; // target login object name
	$_request[] 	 = 	[	"input" => "cms-oid",   		"validate" => "strip_tags|strip_whitespaces|!empty", 					"use_default" => true, "default_value" => ''    ]; // object id
	$_request[] 	 = 	[	"input" => "version", 			"validate" => "strip_tags|strip_whitespaces|lowercase|is_digit|!empty",	"use_default" => true, "default_value" => false ]; // page_version
	$_request[] 	 = 	[	"input" => "cms-xhrequest",		"validate" => "strip_tags|strip_whitespaces|!empty",					"use_default" => true, "default_value" => false ]; // request is by xhr function
	$_request[] 	 = 	[	"input" => "cms-ctrl-action",	"validate" => "strip_tags|strip_whitespaces|lowercase|!empty" ]; 		// requested controller action
	$_pURLVariables -> retrieve($_request, false, true); // POST 

	$_rcaTarget		 =	$_pURLVariables -> getValue("cms-ctrl-action");

	switch($_pURLVariables -> getValue("cms-risa"))
	{
		case 'login':	## User login #################################################################################################################

						$_pLoginVariables	 =	new CURLVariables();
						$_aLoginReqStruct[]  = 	[	"input" => "username", "validate" => "strip_tags|!empty" ];
						$_aLoginReqStruct[]  = 	[	"input" => "password", "validate" => "strip_tags|!empty" ];
						if($_pLoginVariables -> retrieve($_aLoginReqStruct, false, true, true))
						{	##	Login process
							$_pLogin		 =	new CLogin(CFG::LOGIN);
							if( $_pLogin ->	login($_pURLVariables -> getValue("cms-tlon"), $_pLoginVariables -> getArray()) )
							{
								$_rcaTarget[$_pURLVariables -> getValue("cms-oid")] = 'loginSuccess';				
							}
						}
						else
						{	##	Missing data for login
							$_pMessages -> 	addMessage( $_pLanguage -> getString('ERR_CR_LOGINDTAMM') , MSG_WARNING, '', true);							
						}
						break;

		case 'logout':	## User logout ################################################################################################################

						$_pLogin	 =	new CLogin(CFG::LOGIN);
						$_pLogin 	->	logout($_pURLVariables -> getValue("cms-tlon"));
						break;
	}

##	L A N G U A G E   S Y S T E M   /   S E T   R E Q   L A N G

	$_pLanguage		-> 	setActiveLanguage($_pURLVariables -> getValue("lang"));		

##	M O D U L E S   L O A D E R	

	CBenchmark::instance() -> measurementPoint('module loader');	

	$_pModules		 =	CModules::instance();
	$_pModules		->	loadModules();

##	I M P E R A T O R

	CBenchmark::instance() -> measurementPoint('call imperator');	

	$_pageRequest	 =	[
							"node_id"			=>	$_pURLVariables -> getValue("cms-node"),
							"page_language"		=>	$_pLanguage		-> getActiveLanguage(),
							"page_language_def"	=>	CFG::LANG_DEFAULT,
							"page_version"		=>	$_pURLVariables -> getValue("version"),
							"xhrequest"			=>	$_pURLVariables -> getValue("cms-xhrequest")
						];		
						
	$_pImperator	 =	new CImperator( $_pSQLObject -> getConnection(CFG::MYSQL_PRMY) );
	$_pImperator	->	logic( $_pageRequest , $_pModules, $_rcaTarget, CMS_BACKEND);

##	H T M L   D O C U M E N T

	CBenchmark::instance() -> measurementPoint('create html document');	

	$_pHTML			 = 	new CHTML();

##	V I E W 

	$_pHTML -> openDocument($_pImperator -> m_page, $_pImperator, $_pageRequest);


#	$_pHTAccess  = new CHTAccess();
#	$_pHTAccess -> generatePart4Backend();
#	$_pHTAccess -> generatePart4Frontend($_pSQLObject -> getConnection(CFG::MYSQL_PRMY));
#	$_pHTAccess -> writeHTAccess();

?>