<?php

class shemePagePath extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_page_path');
		
		$this -> addColumn('node_id', 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();
		$this -> addColumn('page_id', 'int') -> setIndex('INDEX') -> setAttribute('UNSIGNED');
		
		$this -> addColumn('page_language', 'string') -> setLength(5);
		$this -> addColumn('page_path', 'string') -> setLength(250);
		$this -> addColumn('node_rgt', 'int') -> setAttribute('UNSIGNED');
		$this -> addColumn('node_lft', 'int') -> setAttribute('UNSIGNED');
	}
}

?>