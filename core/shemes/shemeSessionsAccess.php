<?php

class shemeSessionsAccess extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_sessions_access');
		$this -> duplicateTable('tb_sessions_access_archiv');
		
		$this -> addColumn('data_id', 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();

		$this -> addColumn('session_id', 'string') -> setLength(65);
		$this -> addColumn('node_id', 'int') -> setAttribute('UNSIGNED');
		$this -> addColumn('time_access', 'bigint')  -> setAttribute('UNSIGNED');
		$this -> addColumn('referer', 'string') -> setLength(250);
	}
}

?>