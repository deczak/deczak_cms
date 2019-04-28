<?php

##  I N C L U D E   C O F I G U R A T I O N   F I L E S

	require_once    'config/standard.php';
	require_once    'config/servers.php';
	require_once    'config/debug.php';
	require_once    'config/directories.php';

##	I N C L U D E   C L A S S E S   &   F U N C T O N S	

	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CToolkit.php';

	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CSQLConnect.php';	
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CURLVariables.php';	
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CMessages.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CSysMailer.php';

##	M E S S A G E   S Y S T E M

	//	CMessages is a Singleton Class
	//	Collects messages for logging or print them to the screen for different reason

	$_pMessages		 =	CMessages::instance();
	$_pMessages		->	initialize($_bMessageLog);

##	S Y S T E M   M A I L E R

	//	CSysMailer is a Singleton Class
	//	Sends a mail message to a defined address for important messages

	$_pSysMailer	 =	CSysMailer::instance();
	$_pSysMailer	->	initialize($_CFG['SYSMAIL'],'System Message - ');

##  S Q L   C O N N E C T I O N

	$_pSQLObject 	 =	new CSQLConnect;
	if(!$_pSQLObject-> 	createConnection($_CFG['MYSQL'][0]))
	{
		$_pMessages -> 	addMessage('Database connection error: '. $_pSQLObject -> getErrorMsg(), MSG_ERROR, '', true);
		$_pSysMailer-> 	sendMail('Database connection error', 'Error while attempting to connect to the database on '. date('Y-m-d H:i:s',time()), true, 'sql-connection');
	}	

##	U R L   V A R I A B L E S   /   I N I T I A L   R E Q U E S T

	$_pURLVariables	 = new CURLVariables();
	$_request[] 	 = 	[	"input" => "test1", 	/*"output" => "test3",*/	"validate" => "strip_tags|!empty" , "use_default" => true,	"default_value" => "yes"];
#	$_pURLVariables -> retrieve($_request, true, true);











#	CSysMailer::instance() -> sendMail('test subject','test body');
#	CMessages::instance() -> addMessage('test subject');



















	$_pMessages -> printMessages();
?>


<style>
	.message-container { max-width:1024px; margin:5px auto; padding:15px 11px; }
	.message-container:empty { display:none; }
	.message-container > p { margin: 4px 0; }
	.message-container.message-type-1 { border:1px solid red; }
	.message-container.message-type-2 { border:1px solid orange; }
	.message-container.message-type-3 { border:1px solid green; }
	.message-container.message-type-4 { border:1px solid blue; }
</style>