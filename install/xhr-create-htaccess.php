<?php

	error_reporting(E_ALL);
	ini_set('display_errors', true);
	
	include_once '../config/directories.php';
	include_once '../config/standard.php';

	include_once '../core/classes/cmsLog.php';
	cmsLog::initialize();

	include_once '../core/classes/toolkit.php';
	include_once '../core/classes/CSheme.php';
	include_once '../core/classes/CModel.php';
	include_once '../core/classes/CSession.php';
	include_once '../core/classes/CRouter.php';
	include_once '../core/classes/CModules.php';
	include_once '../core/classes/CModelCondition.php';
	include_once '../core/classes/CLanguage.php';
	#include '../core/shemes/shemeSitemap.php';
	include_once '../core/classes/CHTAccess.php';
	include_once '../core/classes/CUserRights.php';
	include_once '../core/classes/CDatabase.php';
	include_once '../core/classes/CMessages.php';


	$pUserRights	 = 	new CUserRights();

	if(empty($_POST['server-subpath'])) 	;				else $_POST['server-subpath'] 	 = trim(strip_tags($_POST['server-subpath']));

	if(empty($_POST['database-server'])) 	tk::xhrResult(1, 'Database server address not set');	else $_POST['database-server'] 	 = trim(strip_tags($_POST['database-server']));
	if(empty($_POST['database-user'])) 		tk::xhrResult(1, 'Database user name not set');			else $_POST['database-user'] 	 = trim(strip_tags($_POST['database-user']));
	if(empty($_POST['database-pass'])) 		tk::xhrResult(1, 'Database password not set');			else $_POST['database-pass'] 	 = trim(strip_tags($_POST['database-pass']));
	if(empty($_POST['database-database'])) 	tk::xhrResult(1, 'Database name not set');				else $_POST['database-database'] = trim(strip_tags($_POST['database-database']));



	$_pMessages		 =	CMessages::initialize();
	#$db = new mysqli($_POST['database-server'], $_POST['database-user'], $_POST['database-pass'], $_POST['database-database']);


	$databases 	 = 	[];
	$databases[] = 	[
						'server'	=> $_POST['database-server'],
						'database'	=> $_POST['database-database'],
						'user'		=> $_POST['database-user'],
						'password'	=> $_POST['database-pass'],
						'name'		=> 'primary'
					];

	$pDBInstance 	 = CDatabase::instance();
	if(!$pDBInstance -> connect($databases))
		tk::xhrResult(1, 'DB connect error');
	
	$db = $pDBInstance -> getConnection('primary');





	#if (mysqli_connect_errno())
	#	tk::xhrResult(1, 'SQL error on connection - '. mysqli_connect_error());

	CModules::instance() -> initialize($db, $pUserRights);


	$configFile = file_get_contents('0-base');

	$configFile = str_replace('%SUB_PATH%',$_POST['server-subpath'], $configFile);

	if(!file_exists('../data/htaccess/'))
		mkdir('../data/htaccess/');

	file_put_contents('../data/htaccess/0-base', $configFile);

	$_pLanguage	 = 	CLanguage::instance();
	$_pLanguage	-> 	initialize($db);	


	$pRouter  = CRouter::instance();
	$pRouter -> initialize('de', CLanguage::getLanguages());


	$_pHTAccess  = new CHTAccess();
	$_pHTAccess -> generatePart4Backend($db);
	$_pHTAccess -> generatePart4Frontend($db);
	$_pHTAccess -> writeHTAccess($db);

	tk::xhrResult(0, 'OK');
?>