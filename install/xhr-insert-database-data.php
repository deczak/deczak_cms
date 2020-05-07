<?php
	include '../core/toolkit.php';
	include '../config/directories.php';
	include '../config/standard.php';
	include '../core/classes/CDatabase.php';
	include '../core/classes/CMessages.php';

	if(empty($_POST['database-server'])) 	tk::xhrResult(1, 'Database server address not set');	else $_POST['database-server'] 	 = trim(strip_tags($_POST['database-server']));
	if(empty($_POST['database-user'])) 		tk::xhrResult(1, 'Database user name not set');			else $_POST['database-user'] 	 = trim(strip_tags($_POST['database-user']));
	if(empty($_POST['database-pass'])) 		tk::xhrResult(1, 'Database password not set');			else $_POST['database-pass'] 	 = trim(strip_tags($_POST['database-pass']));
	if(empty($_POST['database-database'])) 	tk::xhrResult(1, 'Database name not set');				else $_POST['database-database'] = trim(strip_tags($_POST['database-database']));

	if(empty($_POST['user-user'])) 			tk::xhrResult(1, 'User name not set');					else $_POST['user-user'] 	 	 = trim(strip_tags($_POST['user-user']));
	if(empty($_POST['user-pass'])) 			tk::xhrResult(1, 'User password not set');				else $_POST['user-pass'] 		 = trim(strip_tags($_POST['user-pass']));



	$_pMessages		 =	CMessages::instance();
	$_pMessages		->	initialize(CMS_PROTOCOL_REPORTING, CMS_DEBUG_REPORTING);


	$_POST['user-first-name'] 	= '';
	$_POST['user-last-name'] 	= '';
	$_POST['user-mail'] 		= '';


	$_POST['user-user']			= CRYPT::LOGIN_HASH($_POST['user-user']);
	$_POST['user-pass']			= CRYPT::LOGIN_CRYPT($_POST['user-pass'], CFG::GET() -> ENCRYPTION -> BASEKEY);
	$_POST['user-first-name']	= CRYPT::ENCRYPT($_POST['user-first-name'], '1', true);
	$_POST['user-last-name']	= CRYPT::ENCRYPT($_POST['user-last-name'], '1', true);
	$_POST['user-mail']			= CRYPT::ENCRYPT($_POST['user-mail'], '1', true);



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





	$sqlDump = file_get_contents('database-data.sql');

	$sqlDump = str_replace('%USER_NAME%',$_POST['user-user'], $sqlDump);
	$sqlDump = str_replace('%USER_PASSWORD%',$_POST['user-pass'], $sqlDump);
	$sqlDump = str_replace('%USER_FIRST_NAME%',$_POST['user-first-name'], $sqlDump);
	$sqlDump = str_replace('%USER_LAST_NAME%',$_POST['user-last-name'], $sqlDump);
	$sqlDump = str_replace('%USER_MAIL%',$_POST['user-mail'], $sqlDump);

	$sqlDump = str_replace('%TIMESTAMP%',time(), $sqlDump);

//	if( $db -> multi_query($sqlDump) === FALSE)
//		tk::xhrResult(1, 'SQL error on query - '. $db -> error);

try {
    $db -> getConnection() -> exec($sqlDump);
}
catch (PDOException $exception)
{
	tk::xhrResult(1, 'SQL error on query - '. $exception -> getMessage());
 
}

#sleep(5); // its required, multi_query returns ok even if the server still not finished with all 

	tk::xhrResult(0, 'OK');

?>