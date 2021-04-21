<?php

class shemeTagsAllocation extends CSheme
{
	public function
	__construct()
	{
		parent::__construct('tb_tags_allocation');		
		
		$this -> addColumn('allocation_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();
		$this -> addColumn('tag_id', DB_COLUMN_TYPE_INT) -> setIndex('INDEX') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('node_id', DB_COLUMN_TYPE_INT) -> setIndex('INDEX') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);

		$this -> addColumn('tag_name', DB_COLUMN_TYPE_STRING) -> setVirtual();

		$this -> addConstraing('tag_id', 'tag_id', 'tb_tags', 'tag_id', 'CASCADE', 'CASCADE');
		$this -> addConstraing('tag_page_alloc', 'node_id', 'tb_page', 'node_id', 'CASCADE', 'CASCADE');
	}
}
