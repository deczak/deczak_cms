<?php


class shemeUsersBackend extends CSheme
{
	public function
	__construct()
	{
// vor dem kopieren CSheme anpassen

		parent::__construct();		

		$this -> setTable('tb_users_backend');
		
		$this -> addColumn('data_id'		, 'int');
	#	$this -> addColumn('data_id'		, 'int') -> setAutoIncrement('data_id') -> setKey('data_id') -> setAttribute('data_id', 'UNSIGNED');

		$this -> addColumn('login_name'		, 'text');
		$this -> addColumn('login_pass'		, 'string');
		$this -> addColumn('login_count'	, 'int');
		$this -> addColumn('user_id'		, 'string');
		$this -> addColumn('user_name_first', 'text');
		$this -> addColumn('user_name_last'	, 'text');
		$this -> addColumn('user_mail'		, 'text');
		$this -> addColumn('time_login'		, 'bigint');
		$this -> addColumn('time_create'	, 'bigint');
		$this -> addColumn('time_update'	, 'bigint');
		$this -> addColumn('cookie_id'		, 'text');
		$this -> addColumn('recover_key'	, 'string');
		$this -> addColumn('recover_timeout', 'bigint');
		$this -> addColumn('is_locked'		, 'tinyint');
		
		$this -> addColumn('user_rights'	, 'text') -> isVirtual();

/*
										"table"		=>	"tb_users_backend",
										"collate"	=>	"utf8mb4_unicode_ci",
										"columns"	=>	[
															[ "name" => "data_id"      	, "type" => "int"   	, "length" => "11"  , "null" => "NOT NULL"  , "ai" => true, "key" => "UNIQUE" ],
															[ "name" => "login_name"    , "type" => "text" 		, 					  "null" => "NOT NULL"  ],
															[ "name" => "login_pass" 	, "type" => "string"	, "length" => "135" , "null" => "NOT NULL"  ],
															[ "name" => "login_count" 	, "type" => "int"  		, "length" => "11"  , "null" => "NOT NULL"  , "default" => 0 ],
															[ "name" => "user_id"		, "type" => "string"	, "length" => "25"  , "null" => "NOT NULL"  ],
															[ "name" => "user_name_first","type" => "text"  	, 					  "null" => "NOT NULL"  ],
															[ "name" => "user_name_last", "type" => "text"  	, 					  "null" => "NOT NULL"  ],
															[ "name" => "user_mail" 	, "type" => "text"  	, 					  "null" => "NOT NULL"  ],
															[ "name" => "time_login" 	, "type" => "bigint"  	, "length" => "20"  , "null" => "NOT NULL"  , "default" => 0 ],
															[ "name" => "time_create" 	, "type" => "bigint"  	, "length" => "20"  , "null" => "NOT NULL"  ],
															[ "name" => "time_update" 	, "type" => "bigint"  	, "length" => "20"  , "null" => "NOT NULL"  , "default" => 0  ],
															[ "name" => "cookie_id" 	, "type" => "text"  	, 					  "null" => "NOT NULL"  ],
															[ "name" => "recover_key" 	, "type" => "string"  	, "length" => "65"  , "null" => "NULL"  	],
															[ "name" => "recover_timeout","type" => "bigint"  	, "length" => "20"  , "null" => "NOT NULL"  , "default" => 0  ],
															[ "name" => "is_locked" 	, "type" => "tinyint"  	, "length" => "3"  ,  "null" => "NOT NULL"	, "default" => 1  ],
															[ "name" => "user_rights" 	, "ignore" => true ]	// ignore on query call if index exists, even if set false
*/



	}
}



		

?>