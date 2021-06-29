<?php

class shemePageHeader extends CSheme
{
	public function
	__construct(bool $_applyConstraint = true)
	{
		parent::__construct('tb_page_header');	
		
		$this -> addColumn('data_id'		, DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();
		$this -> addColumn('node_id'		, DB_COLUMN_TYPE_INT) -> setIndex('INDEX') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('page_id'		, DB_COLUMN_TYPE_INT) -> setIndex('INDEX') ->  setAttribute(DB_COLUMN_ATTR_UNSIGNED);

		$this -> addColumn('page_language'	, DB_COLUMN_TYPE_STRING) -> setLength(5);
		$this -> addColumn('page_title'		, DB_COLUMN_TYPE_STRING) -> setLength(100);
		$this -> addColumn('page_name'		, DB_COLUMN_TYPE_STRING) -> setLength(100);
		$this -> addColumn('crumb_name'		, DB_COLUMN_TYPE_STRING) -> setLength(100);
		$this -> addColumn('page_description', DB_COLUMN_TYPE_STRING) -> setLength(160);
		$this -> addColumn('page_version'	, DB_COLUMN_TYPE_MEDIUMINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);

		if($_applyConstraint)
			$this -> addConstraint('header_node_id', 'node_id', 'tb_page', 'node_id', 'CASCADE', 'CASCADE');
	}
}

