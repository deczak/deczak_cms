<?php

class schemeSimple extends CScheme
{
	public function
	__construct(bool $_applyConstraint = true)
	{
		parent::__construct('tb_page_object_simple');		
		
		$this -> addColumn('data_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();
		$this -> addColumn('object_id', DB_COLUMN_TYPE_INT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('body', DB_COLUMN_TYPE_TEXT);
		$this -> addColumn('params', DB_COLUMN_TYPE_TEXT);

		if($_applyConstraint)
			$this -> addConstraint('simple_object_id', 'object_id', 'tb_page_object', 'object_id', 'CASCADE', 'CASCADE');
	}
}
