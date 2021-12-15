<?php

defined('CMS_BACKEND') or define('CMS_BACKEND', false);

##  I N C L U D E   C O F I G U R A T I O N   F I L E S

	require_once	'core/classes/cfg.php';
	require_once    'config/directories.php';
	require_once    'config/standard.php';

	CFG::initialize();	

##	B E N C H M A R K ( not in use )

	#require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CBenchmark.php';
	#CBenchmark::instance() -> initialize(CMS_BENCHMARK);	

##	P H P   E R R O R   R E P O R T I N G

	if(CFG::GET() -> ERROR_SYSTEM -> ERROR_FILE -> ENABLED || PHP_ERROR_DISPLAY)
		error_reporting(E_ALL);
	else
		error_reporting(0);

	ini_set('display_errors', PHP_ERROR_DISPLAY);

##	L O G   S Y S T E M

	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'cmsLog.php';

	cmsLog::initialize();

##	I N C L U D E   C L A S S E S   &   F U N C T O N S	

	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'toolkit.php';
	
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CDatabase.php';

	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CView.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CModel.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CModelCondition.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CModelComplementary.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CController.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CSheme.php';

	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CURLVariables.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CRouter.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CMessages.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CSysMailer.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CHTAccess.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CXMLSitemap.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CSession.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CCookie.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CLogin.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CLanguage.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CDirector.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CImperator.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CModules.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CHTML.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CTemplate.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CPageRequest.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CUserRights.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CNodesSearch.php';

	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'cmsUpdate.php';

##	M E S S A G E   S Y S T E M

	CMessages::initialize();

##	S Y S T E M   M A I L E R

	//	CSysMailer is a singleton class
	//	Sends a mail message to a defined address for important messages

	$_pSysMailer	 =	CSysMailer::instance();
	$_pSysMailer	->	initialize();

##  S Q L   C O N N E C T I O N

	//	CSQLConnect is a singleton class

	$pDBInstance 	 = CDatabase::instance();
	if(!$pDBInstance -> connect(CFG::GET() -> MYSQL -> DATABASE))
	{	##	create connection failed
		CPageRequest::instance() -> setResponseCode(920);
	}	
	CDatabase::setActiveConnectionName(CFG::GET() -> MYSQL -> PRIMARY_DATABASE);

##  S Y S T E M   U P D A T E R

if(CMS_BACKEND)
{
	$cmsUpdate  = new cmsUpdate;
	if($cmsUpdate -> detectUpdate())
		$cmsUpdate -> execUpdate();
}

##	L A N G U A G E   S Y S T E M   /   I N I T I A L   F I L E S

	//	CLanguage is a singleton class
	// 	Loads and manage language files

	CLanguage::initialize($pDBInstance -> getConnection(CFG::GET() -> MYSQL -> PRIMARY_DATABASE));		
	CLanguage::loadLanguageFile(CMS_SERVER_ROOT.DIR_CORE.DIR_LANGUAGES.CLanguage::getDefault() .'/');

##	R O U T I N G

	$pRouter  = CRouter::instance();
	$pRouter -> initialize(CFG::GET() -> LANGUAGE, CLanguage::getLanguages());

	if(CMS_URL_BASE !== false)
		$_SERVER['REQUEST_URI'] = str_replace('/'. CMS_URL_BASE, '', $_SERVER['REQUEST_URI']);
		
	if(CPageRequest::instance() -> getResponseCode() === 200)
	{
		$pRouteRequest = $pRouter -> route($_SERVER['REQUEST_URI']);

		if($pRouteRequest === false)
		{
			$pRouter -> createRoutes($pDBInstance -> getConnection(CFG::GET() -> MYSQL -> PRIMARY_DATABASE));
			header("Location: ". $_SERVER['REQUEST_URI']); 	
			exit;
		}

		$_GET['cms-node'] = $pRouteRequest -> nodeId;
		$_GET['cms-lang'] = $pRouteRequest -> language;

		if($pRouteRequest -> responseCode != 200)
			$_GET['cms-error'] = $pRouteRequest -> responseCode;
	}
	
##	C O O K I E   M A N A G E R

	//	CCookie is a singleton class
	// 	Set and get Cookie Data

	$_pCookies		 =	CCookie::instance();
	$_pCookies 		->	initialize();
		
##	U R L   V A R I A B L E S   /   I N I T I A L   R E Q U E S T

	$_pURLVariables	 =	new CURLVariables();

	$_request[] 	 = 	[	"input" => "cms-lang", 		  	"validate" => "strip_tags|strip_whitespaces|lowercase|!empty",      	"use_default" => true, "default_value" => CLanguage::getDefault() ]; // language key
	$_request[] 	 = 	[	"input" => "cms-node",  		"validate" => "strip_tags|strip_whitespaces|lowercase|is_digit|!empty", "use_default" => true, "default_value" => false     ]; // node_id
	$_request[] 	 = 	[	"input" => "cms-ctrl-action",	"validate" => "strip_tags|strip_whitespaces|lowercase|!empty", 			"use_default" => true, "default_value" => []	]; // requested controller action
	$_request[] 	 = 	[	"input" => "cms-error",			"validate" => "strip_tags|strip_whitespaces|lowercase|!empty", 			"use_default" => true, "default_value" => false	]; // url rewrite error redirect (eg 403,404)
	$_pURLVariables -> retrieve($_request, true, false); // GET

	$_request		 =	[];
	$_request[] 	 = 	[	"input" => "cms-risa",   	 	"validate" => "strip_tags|strip_whitespaces|lowercase|!empty" ]; 		// requested initial script action
	$_request[] 	 = 	[	"input" => "cms-tlon",   		"validate" => "strip_tags|strip_whitespaces|!empty", 					"use_default" => true, "default_value" => ''    ]; // target login object name
	$_request[] 	 = 	[	"input" => "cms-oid",   		"validate" => "strip_tags|strip_whitespaces|is_digit|!empty", 			"use_default" => true, "default_value" => ''    ]; // object id
	$_request[] 	 = 	[	"input" => "cms-node-version",	"validate" => "strip_tags|strip_whitespaces|lowercase|is_digit|!empty",	"use_default" => true, "default_value" => false ]; // page_version
	$_request[] 	 = 	[	"input" => "cms-xhrequest",		"validate" => "strip_tags|strip_whitespaces|!empty",					"use_default" => true, "default_value" => false ]; // request is by xhr function
	$_request[] 	 = 	[	"input" => "cms-ctrl-action",	"validate" => "strip_tags|strip_whitespaces|lowercase|!empty" ]; 		// requested controller action
	$_pURLVariables -> retrieve($_request, false, true); // POST 

	$_rcaTarget		 =	$_pURLVariables -> getValue("cms-ctrl-action");

##	R I G H T S   S Y S T E M

	$pUserRights	 = 	new CUserRights();

##	S E S S I O N   S Y S T E M

	//	CSession is a singleton class
	// 	Session System without session cookies at the beginning

	$_pSession		 = 	CSession::instance();		
	$_pSession		->	updateSession(intval($_pURLVariables -> getValue("cms-node")), $_pURLVariables -> getValue("cms-lang"), $pUserRights);	

##	Requested initial script action

	switch($_pURLVariables -> getValue("cms-risa"))
	{
		case 'login':	## User login #################################################################################################################

						$_pLogin		 = CLogin::instance();
						if( $_pLogin ->	login($pDBInstance -> getConnection(CFG::GET() -> MYSQL -> PRIMARY_DATABASE), $_pURLVariables -> getValue("cms-tlon")) )
						{
							$_rcaTarget[$_pURLVariables -> getValue("cms-oid")] = 'loginSuccess';	
						}
						else
						{	##	Missing data for login
							CMessages::add(CLanguage::string('ERR_CR_LOGINDTAMM') , MSG_WARNING, '', true);							
						}
						break;

		case 'logout':	## User logout ################################################################################################################

						$_pLogin	 =	CLogin::logout($pDBInstance -> getConnection(CFG::GET() -> MYSQL -> PRIMARY_DATABASE), $_pURLVariables -> getValue("cms-tlon"));
						break;
	}

##	L A N G U A G E   S Y S T E M   /   S E T   R E Q   L A N G

	$activeLanguage = $_pURLVariables -> getValue("cms-lang");

	if(CMS_BACKEND && CSession::instance() -> getValue('language') !== NULL)
		$activeLanguage  = CSession::instance() -> getValue('language');

	#if(CMS_BACKEND && $activeLanguage !== NULL && CLanguage::getDefault() !== $activeLanguage)
	if($activeLanguage !== NULL && CLanguage::getDefault() !== $activeLanguage)
		CLanguage::loadLanguageFile(CMS_SERVER_ROOT.DIR_CORE.DIR_LANGUAGES.$activeLanguage .'/', $activeLanguage );

	CLanguage::setActiveLanguage($activeLanguage);		

##	M O D U L E S   L O A D E R	

	CModules::initialize($pDBInstance -> getConnection(CFG::GET() -> MYSQL -> PRIMARY_DATABASE), $pUserRights);

##	I M P E R A T O R

	$pageRequest 	 = 	CPageRequest::instance();
	$pageRequest 	-> 	init(
							$pDBInstance 		-> getConnection(CFG::GET() -> MYSQL -> PRIMARY_DATABASE),
							$_pURLVariables 	-> getValue("cms-node"),
							CLanguage::getActiveLanguage(),
							$_pURLVariables 	-> getValue("cms-node-version"),
							$_pURLVariables 	-> getValue("cms-xhrequest")
							);				

	if($_pURLVariables -> getValue("cms-error") !== false)
		CPageRequest::instance() -> setResponseCode($_pURLVariables -> getValue("cms-error"));
			
	define('URL_LANG_PRREFIX', ((CFG::GET() -> LANGUAGE -> DEFAULT_IN_URL || $pageRequest -> page_language !== CLanguage::getDefault()) ? $pageRequest -> page_language .'/' : '') );

	$pImperator	 =	new CImperator( $pDBInstance -> getConnection(CFG::GET() -> MYSQL -> PRIMARY_DATABASE) );
	$pImperator	->	logic($pageRequest, $_rcaTarget, CMS_BACKEND, $pUserRights);

##	H T M L   D O C U M E N T

	$_pHTML			 = 	new CHTML();

##	V I E W 

	$_pHTML -> openDocument($pImperator, $pageRequest);
