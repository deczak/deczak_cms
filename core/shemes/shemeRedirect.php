<?php

class shemeRedirect extends CSheme
{
	public function
	__construct()
	{
        parent::__construct('tb_redirect');	
		
		$this -> addColumn('redirect_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();
		$this -> addColumn('node_id', DB_COLUMN_TYPE_INT) -> setIndex('INDEX') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('redirect_target', DB_COLUMN_TYPE_STRING) -> setLength(250);
		$this -> addColumn('redirect_desc', DB_COLUMN_TYPE_STRING) -> setLength(250) -> setDefault('NULL');

		$this -> addColumn('create_time', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('create_by', DB_COLUMN_TYPE_STRING) -> setLength(25);

		$this -> addConstraing('redirect_node_id', 'node_id', 'tb_page', 'node_id', 'CASCADE', 'CASCADE');
	}
}
