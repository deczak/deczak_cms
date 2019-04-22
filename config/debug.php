<?php


##	T R I G G E R   F O R   D E B U G   O U T P U T

	$g_bFailureReport		=	true;

##	D E B U G   S E T T I N G S

	if($g_bFailureReport)
	{
		error_reporting(E_ALL);
	}

	ini_set('display_errors', $g_bFailureReport);

?>