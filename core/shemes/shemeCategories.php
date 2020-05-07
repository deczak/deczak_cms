<?php

class shemeCategories extends CSheme
{
	public function
	__construct()
	{
		parent::__construct('tb_categories');		
		
		$this -> addColumn('category_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();

		$this -> addColumn('category_name', DB_COLUMN_TYPE_STRING) -> setLength(50);
		$this -> addColumn('category_url', DB_COLUMN_TYPE_STRING) -> setLength(50);
		$this -> addColumn('category_hidden', DB_COLUMN_TYPE_TINYINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('category_disabled', DB_COLUMN_TYPE_TINYINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
	}
}

?>