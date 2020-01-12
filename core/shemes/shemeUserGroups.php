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
	}
}

?>