<?php

class shemeUserGroups extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_users_groups');
		
		$this -> addColumn('data_id', 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();

		$this -> addColumn('user_id', 'string') -> setLength(25);
		$this -> addColumn('group_id', 'int') -> setAttribute('UNSIGNED');
		$this -> addColumn('user_hash', 'string') -> setLength(64) -> setDefault('NULL');
		
		$this -> addColumn('update_time', 'bigint') -> setAttribute('UNSIGNED') -> setDefault('0');
		$this -> addColumn('update_by', 'string') -> setLength(25) -> setDefault('NULL');
	}
}

?>