<?php

class shemeCategoriesAllocation extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_categories_allocation');
		
		$this -> addColumn('allocation_id', 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();
		$this -> addColumn('category_id', 'int') -> setIndex('INDEX') -> setAttribute('UNSIGNED');
		$this -> addColumn('node_id', 'int') -> setIndex('INDEX') -> setAttribute('UNSIGNED');

		$this -> addColumn('category_name', 'string') -> isVirtual();

		$this -> addConstraing('category_id', 'category_id', 'tb_categories', 'category_id', 'CASCADE', 'CASCADE');
		$this -> addConstraing('cat_page_alloc', 'node_id', 'tb_page', 'node_id', 'CASCADE', 'CASCADE');
	}
}

?>