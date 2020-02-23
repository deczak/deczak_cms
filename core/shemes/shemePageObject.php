<?php

class shemePageObject extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_page_object');
		
		$this -> addColumn('object_id', 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();
		$this -> addColumn('node_id', 'int') -> setIndex('INDEX') -> setAttribute('UNSIGNED');
		
		$this -> addColumn('page_version', 'mediumint') -> setAttribute('UNSIGNED') -> setDefault('1');
		$this -> addColumn('module_id', 'int') -> setAttribute('UNSIGNED');
		$this -> addColumn('object_order_by', 'mediumint') -> setAttribute('UNSIGNED');	
			
		$this -> addColumn('update_reason', 'string') -> setLength(250) -> setDefault('NULL');
		

		$this -> addColumn('create_time', 'bigint') -> setAttribute('UNSIGNED');
		$this -> addColumn('create_by', 'string') -> setLength(25);
		$this -> addColumn('update_time', 'bigint') -> setAttribute('UNSIGNED') -> setDefault('0');
		$this -> addColumn('update_by', 'string') -> setLength(25) -> setDefault('NULL');


		$this -> addColumn('instance', 'int') -> isVirtual();
		$this -> addColumn('body', 'string') -> isVirtual();
		$this -> addColumn('params'	, 'array') -> isVirtual();

		$this -> addConstraing('object_node_id', 'node_id', 'tb_page', 'node_id', 'CASCADE', 'CASCADE');

	}
}

?>