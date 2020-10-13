<?php

class shemePagePath extends CSheme
{
	public function
	__construct()
	{
		parent::__construct('tb_page_path');		
		
		$this -> addColumn('node_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();
		$this -> addColumn('page_id', DB_COLUMN_TYPE_INT) -> setIndex('INDEX') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		
		$this -> addColumn('page_language', DB_COLUMN_TYPE_STRING) -> setLength(5);
		$this -> addColumn('page_path', DB_COLUMN_TYPE_STRING) -> setLength(250);
		$this -> addColumn('node_rgt', DB_COLUMN_TYPE_INT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('node_lft', DB_COLUMN_TYPE_INT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
	}
}

class shemeBackendPagePath extends shemePagePath 
{
	public function
	__construct()
	{
		parent::__construct();
		$this -> setTableName('tb_backend_page_path');
	}
}

?>