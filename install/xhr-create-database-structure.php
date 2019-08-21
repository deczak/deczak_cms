<?php

	include '../core/toolkit.php';

	if(empty($_POST['database-server'])) 	tk::xhrResult(1, 'Database server address not set');	else $_POST['database-server'] 	 = trim(strip_tags($_POST['database-server']));
	if(empty($_POST['database-user'])) 		tk::xhrResult(1, 'Database user name not set');			else $_POST['database-user'] 	 = trim(strip_tags($_POST['database-user']));
	if(empty($_POST['database-pass'])) 		tk::xhrResult(1, 'Database password not set');			else $_POST['database-pass'] 	 = trim(strip_tags($_POST['database-pass']));
	if(empty($_POST['database-database'])) 	tk::xhrResult(1, 'Database name not set');				else $_POST['database-database'] = trim(strip_tags($_POST['database-database']));

	$db = new mysqli($_POST['database-server'], $_POST['database-user'], $_POST['database-pass'], $_POST['database-database']);

	if (mysqli_connect_errno())
		tk::xhrResult(1, 'SQL error on connection - '. mysqli_connect_error());

	$sqlDump = file_get_contents('database-structure.sql');

	if( $db -> multi_query($sqlDump) === FALSE)
		tk::xhrResult(1, 'SQL error on query - '. $db -> error);

	sleep(5); // its required, multi_query returns ok even if the server still not finished with all 

	tk::xhrResult(0, 'OK');

?>