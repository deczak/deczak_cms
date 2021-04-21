<?php

class shemePageObject extends CSheme
{
	public function
	__construct(bool $_applyConstraint = true)
	{
		parent::__construct('tb_page_object');
		
		$this -> addColumn('object_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();
		$this -> addColumn('node_id', DB_COLUMN_TYPE_INT) -> setIndex('INDEX') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		
		$this -> addColumn('page_version', DB_COLUMN_TYPE_MEDIUMINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('1');
		$this -> addColumn('module_id', DB_COLUMN_TYPE_INT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('content_id', DB_COLUMN_TYPE_STRING) -> setLength(25) -> setDefault('1');
		$this -> addColumn('object_order_by', DB_COLUMN_TYPE_MEDIUMINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);	
			
		$this -> addColumn('update_reason', DB_COLUMN_TYPE_STRING) -> setLength(250) -> setDefault('NULL');
		
		$this -> addColumn('create_time', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('create_by', DB_COLUMN_TYPE_STRING) -> setLength(25);
		$this -> addColumn('update_time', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
		$this -> addColumn('update_by', DB_COLUMN_TYPE_STRING) -> setLength(25) -> setDefault('NULL');

		$this -> addColumn('instance', DB_COLUMN_TYPE_INT) -> setVirtual();
		$this -> addColumn('body', DB_COLUMN_TYPE_STRING) -> setVirtual();
		$this -> addColumn('params'	, 0) -> setVirtual();

		if($_applyConstraint)
			$this -> addConstraint('object_node_id', 'node_id', 'tb_page', 'node_id', 'CASCADE', 'CASCADE');
	}
}
