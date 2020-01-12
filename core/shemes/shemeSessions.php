<?php

class shemeSessions extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_sessions');
		$this -> duplicateTable('tb_sessions_archiv');
		
		$this -> addColumn('data_id', 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();

		$this -> addColumn('session_id', 'string') -> setLength(65);
		$this -> addColumn('user_agent', 'text');
		$this -> addColumn('user_ip', 'string') -> setLength(40);
		$this -> addColumn('time_create', 'bigint') -> setAttribute('UNSIGNED');
		$this -> addColumn('time_update', 'bigint') -> setAttribute('UNSIGNED') -> setDefault('0');;
		$this -> addColumn('time_out', 'bigint') -> setAttribute('UNSIGNED');
		$this -> addColumn('login_fail_count', 'int') -> setAttribute('UNSIGNED') -> setDefault('0');;
	}
}

?>