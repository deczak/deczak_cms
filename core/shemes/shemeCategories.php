<?php

class shemeCategories extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_categories');
		
		$this -> addColumn('category_id', 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();

		$this -> addColumn('category_name', 'string') -> setLength(50);
		$this -> addColumn('category_url', 'string') -> setLength(50);
		$this -> addColumn('category_hidden', 'tinyint') -> setAttribute('UNSIGNED');
		$this -> addColumn('category_disabled', 'tinyint') -> setAttribute('UNSIGNED');
	}
}

?>