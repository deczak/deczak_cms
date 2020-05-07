<?php

class shemeSimple extends CSheme
{
	public function
	__construct()
	{
		parent::__construct('tb_page_object_simple');		
		
		$this -> addColumn('data_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();
		$this -> addColumn('object_id', DB_COLUMN_TYPE_INT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('body', DB_COLUMN_TYPE_TEXT);
		$this -> addColumn('params', DB_COLUMN_TYPE_TEXT);

		$this -> addConstraing('simple_object_id', 'object_id', 'tb_page_object', 'object_id', 'CASCADE', 'CASCADE');
	}
}

?>