<?php

class shemeUsersBackend extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_users_backend');
		
		$this -> addColumn('data_id'		, 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();

		$this -> addColumn('login_name'		, 'text');
		$this -> addColumn('login_pass'		, 'string') 	-> setLength(135);
		$this -> addColumn('login_count'	, 'int') 		-> setAttribute('UNSIGNED') -> setDefault('0');
		$this -> addColumn('user_id'		, 'string') 	-> setKey('UNIQUE') -> setLength(25);
		$this -> addColumn('user_name_first', 'text');
		$this -> addColumn('user_name_last'	, 'text');
		$this -> addColumn('user_mail'		, 'text');
		$this -> addColumn('time_login'		, 'bigint') 	-> setAttribute('UNSIGNED') -> setDefault('0');
		$this -> addColumn('cookie_id'		, 'text');
		$this -> addColumn('recover_key'	, 'string') 	-> setLength(65);
		$this -> addColumn('recover_timeout', 'bigint') 	-> setAttribute('UNSIGNED') -> setDefault('0');
		$this -> addColumn('is_locked'		, 'tinyint') 	-> setAttribute('UNSIGNED');
		$this -> addColumn('language'		, 'string') 	-> setLength(3);

		$this -> addColumn('create_time'	, 'bigint') 	-> setAttribute('UNSIGNED');
		$this -> addColumn('create_by'		, 'smallint')	-> setAttribute('UNSIGNED');
		$this -> addColumn('update_time'	, 'bigint') 	-> setAttribute('UNSIGNED') -> setDefault('0');
		$this -> addColumn('update_by'		, 'smallint') 	-> setAttribute('UNSIGNED') -> setDefault('0');		
		
		$this -> addColumn('user_rights'	, 'text') -> isVirtual();
	}
}

?>