<?php

class shemeRightGroups extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_right_groups');
				
		$this -> addColumn('group_id'		, 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();

		$this -> addColumn('group_name'		, 'string') -> setLength(50);
		$this -> addColumn('group_rights'	, 'text');

		$this -> addColumn('create_time', 'bigint') -> setAttribute('UNSIGNED');
		$this -> addColumn('create_by', 'string') -> setLength(25);
		$this -> addColumn('update_time', 'bigint') -> setAttribute('UNSIGNED') -> setDefault('0');
		$this -> addColumn('update_by', 'string') -> setLength(25) -> setDefault('NULL');
	}
}

?>