<?php

class shemeModules extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_modules');
		
		$this -> addColumn('module_id', 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();

		$this -> addColumn('module_location', 'string') -> setLength(50);
		$this -> addColumn('module_controller', 'string') -> setLength(50);
		$this -> addColumn('module_type', 'string') -> setLength(10);
		$this -> addColumn('module_group', 'string') -> setLength(25);
		$this -> addColumn('module_icon', 'string') -> setLength(10);
		$this -> addColumn('module_name', 'string') -> setLength(35);

		$this -> addColumn('is_frontend', 'tinyint') -> setAttribute('UNSIGNED');
		$this -> addColumn('is_active', 'tinyint') -> setAttribute('UNSIGNED');

		$this -> addColumn('create_time', 'bigint') -> setAttribute('UNSIGNED');
		$this -> addColumn('create_by', 'string') -> setLength(25);
		$this -> addColumn('update_time', 'bigint') -> setAttribute('UNSIGNED') -> setDefault('0');
		$this -> addColumn('update_by', 'string') -> setLength(25) -> setDefault('NULL');

	}
}

?>