<?php

class shemeRedirect extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_redirect');
		
		$this -> addColumn('redirect_id', 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();
		$this -> addColumn('node_id', 'int') -> setIndex('INDEX') -> setAttribute('UNSIGNED');
		$this -> addColumn('redirect_target', 'string') -> setLength(250);
		$this -> addColumn('redirect_desc', 'string') -> setLength(250) -> setDefault('NULL');

		$this -> addColumn('create_time', 'bigint') -> setAttribute('UNSIGNED');
		$this -> addColumn('create_by', 'string') -> setLength(25);

		$this -> addConstraing('redirect_node_id', 'node_id', 'tb_page', 'node_id', 'CASCADE', 'CASCADE');
	}
}

?>