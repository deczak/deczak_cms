<?php

##  S E R V E R   R O O T   &   U R L

	define('CMS_SERVER_ROOT', '<absolute_filesystem_path>');

	define('CMS_SERVER_URL' , 'http://<domain>.<tld>');

##  S U B   D I R E C T O R I E S

	define('DIR_PHP_CLASS'  , 'php_class/');
	define('DIR_PHP_FUNC'   , 'php_func/');
	define('DIR_TEMPLATES'  , 'templates/');
	define('DIR_MODULES'    , 'modules/');

	define('DIR_SYSTEM'     , 'system/');

##  M Y S Q L   A C C E S S   P A R A M S

	$g_aSQLConnectData		=	[
								"server" 		=> 	"***",
								"user"			=>	"***",
								"password"		=>	"***",
								"database"		=>	"***"
								]; 

?>