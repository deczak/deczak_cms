<?php

class shemeCategoriesAllocation extends CSheme
{
	public function
	__construct()
	{
		parent::__construct('tb_categories_allocation');	
		
		$this -> addColumn('allocation_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();
		$this -> addColumn('category_id', DB_COLUMN_TYPE_INT) -> setIndex('INDEX') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('node_id', DB_COLUMN_TYPE_INT) -> setIndex('INDEX') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);

		$this -> addColumn('category_name', DB_COLUMN_TYPE_STRING) -> setVirtual();

		$this -> addConstraing('category_id', 'category_id', 'tb_categories', 'category_id', 'CASCADE', 'CASCADE');
		$this -> addConstraing('cat_page_alloc', 'node_id', 'tb_page', 'node_id', 'CASCADE', 'CASCADE');
	}
}
