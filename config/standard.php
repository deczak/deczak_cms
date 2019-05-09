<?php

	declare(strict_types=1);

##  S E R V E R   R O O T   &   U R L

	define('CMS_SERVER_ROOT', '</www/htdcos/>');

	define('CMS_SERVER_URL' , '<protocol>://www.<domain>.<tld>');

##	E N C R Y P T I O N   B A S E K E Y

	define('ENCRYPTION_BASEKEY','4democracy');

	define('ENCRYPTION_METHOD','AES-256-CBC');

##	D A T A B A S E   N A M E S

	define('DATABASE_PRIMARY','1');

##	L O G I N   O B J E C T   N A M E S

	define('LOGIN_OBJECT_USERS','0000');

##	T I M E   F O R M A T S   F O R   D A T E   F U N C T I O N

	define('TIME_FORMAT_SYSMAIL','Y-m-d @ H:i:s');
	define('TIME_FORMAT_USERMAIL','Y-m-d @ H:i:s');

##	E R R O R   R E P O R T I N G   &   D E B U G

	define('PHP_ERROR_REPORTING',true);
	define('CMS_PROTOCOL_REPORTING',true);
	define('CMS_DEBUG_REPORTING',true);
	
##  C O N F I G   C L A S S

	class	CFG 
	{
		##  M Y S Q L   A C C E S S   P A R A M E T E R S

		const	MYSQL   	= 	[
								DATABASE_PRIMARY 	=>	[
														"server" 		=> 	"***",
														"user"			=>	"***",
														"password"		=>	"***",
														"database"		=>	"***",
														"name"			=>	DATABASE_PRIMARY						
														]	#->	,
							#	***				 	=>	[
							#							"server" 		=> 	"***",
							#							"user"			=>	"***",
							#							"password"		=>	"***",
							#							"database"		=>	"***",
							#							"name"			=>	"***"						
							#							]
								];
		
		##  S Y S T E M   M E S S A G E   R E C E I V E R

		const	SYSMAIL		=	[
								"name" 				=> 	"",
								"mail"				=>	""
								]; 

		##  C O O K I E   P A R A M E T E R S							

		const	COOKIES		=	[
								"request_https"		=> 	false
								]; 

		##  L O G I N   O B J E C T S		

		const	LOGIN		=	[
								"fail_limit"		=>	5,
								"objects"			=>	[
														LOGIN_OBJECT_USERS	=>	[
																				"ob_name"		=> 	LOGIN_OBJECT_USERS,
																				"db_name"		=> 	[ DATABASE_PRIMARY ],
																				"table"			=> 	"tb_users",
																				"columns"		=> 	[
																										[ "name" => "login_name" , "data_prc" => "crypt" , "field" => "username" , "is_username" => true ],
																										[ "name" => "login_pass" , "data_prc" => "hash"	 , "field" => "password" ]
																									],
																				"fields"		=> 	[
																										[ "name" => "username" 	 , "type" => "text" 	],
																										[ "name" => "password" 	 , "type" => "password" ]
																									]
																				]
														]
							]; 

		##  L A N G U A G E   P A R A M E T E R S							

		const	LANGUAGE	=	[
								"default"			=> 	"en",
								"supported"			=>	[
														"en" =>	[
																"key"		=>	"en",
																"name"		=>	"English"
																]
															]
								]; 							

	}





?>