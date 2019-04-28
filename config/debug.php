<?php


##	T R I G G E R   F O R   D E B U G   O U T P U T

	$_bFailureReport	=	true;		// Enable PHP error reporting

	$_bMessageLog		=	true;		// Enable Messages-Class Log Mode

##	D E B U G   S E T T I N G S

	if($_bFailureReport)
	{
		error_reporting(E_ALL);
	}

	ini_set('display_errors', $_bFailureReport);

?>