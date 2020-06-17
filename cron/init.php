<?php

#	error_reporting(E_ALL);
#	ini_set('display_errors', 1);

	require_once    '../config/directories.php';
	require_once    '../config/standard.php';

	require_once	CMS_SERVER_ROOT.DIR_CORE. 'toolkit.php';

	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CDatabase.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CSysMailer.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CModelCondition.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CModel.php';

##	S Y S T E M   M A I L E R

	//	CSysMailer is a singleton class
	//	Sends a mail message to a defined address for important messages

	$_pSysMailer	 =	CSysMailer::instance();
	$_pSysMailer	->	initialize();

##  D B   C O N N E C T I O N

	$pDBInstance 	 = CDatabase::instance();
	if(!$pDBInstance -> connect(CFG::GET() -> MYSQL -> DATABASE))
	{	##	create connection failed
		exit;
	}	

	$pDatabase = $pDBInstance -> getConnection(CFG::GET() -> MYSQL -> PRIMARY_DATABASE);

?>