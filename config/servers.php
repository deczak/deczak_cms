<?php

##  S E R V E R   R O O T   &   U R L

	define('CMS_SERVER_ROOT', '</www/htdcos/>');

	define('CMS_SERVER_URL' , '<protocol>://www.<domain>.<tld>');

##  M Y S Q L   A C C E S S   P A R A M S


	$_CFG['MYSQL'][0]		=	[
								"server" 		=> 	"***",
								"user"			=>	"***",
								"password"		=>	"***",
								"database"		=>	"***"
								]; 

	/* Adding more by increasing index
	$_CFG['MYSQL'][1]		=	[
								"server" 		=> 	"***",
								"user"			=>	"***",
								"password"		=>	"***",
								"database"		=>	"***"
								]; 

	*/

?>