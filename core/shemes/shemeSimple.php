<?php

class shemeSimple extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_page_object_simple') ;
		
		$this -> addColumn('data_id', 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();
		$this -> addColumn('object_id', 'int') -> setAttribute('UNSIGNED');
		$this -> addColumn('body', 'text');
		$this -> addColumn('params', 'text');


		$this -> addConstraing('simple_object_id', 'object_id', 'tb_page_object', 'object_id', 'CASCADE', 'CASCADE');
	}
}

?>