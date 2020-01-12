<?php

	error_reporting(E_ALL);
	ini_set('display_errors', true);

	require_once    '../config/directories.php';
	require_once    '../config/standard.php';

	require_once	CMS_SERVER_ROOT.DIR_CORE. 'toolkit.php';

	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CSQLConnect.php';
	require_once	CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CSysMailer.php';

##	S Y S T E M   M A I L E R

	//	CSysMailer is a singleton class
	//	Sends a mail message to a defined address for important messages

	$_pSysMailer	 =	CSysMailer::instance();
	$_pSysMailer	->	initialize();

##  S Q L   C O N N E C T I O N

	$pSQLObject 	 =	CSQLConnect::instance();
	$pSQLObject 	->	initialize();
	if(!$pSQLObject-> 	createConnection())
	{	##	create connection failed
		exit;
	}	

	$sqlInstance = $pSQLObject -> getConnection(CONFIG::GET() -> MYSQL -> PRIMARY_DATABASE);

##  Other

	function
	sqlImplosion(array $_srcArray, $_quote = "'")
	{
		$returnData = [];
		foreach($_srcArray as $value)
			$returnData[] = $_quote . $value . $_quote;
		return implode(',', $returnData);
	}
?>