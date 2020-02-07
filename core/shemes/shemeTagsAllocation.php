<?php

class shemeTagsAllocation extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_tags_allocation');
		
		$this -> addColumn('allocation_id', 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();
		$this -> addColumn('tag_id', 'int') -> setIndex('INDEX') -> setAttribute('UNSIGNED');
		$this -> addColumn('node_id', 'int') -> setIndex('INDEX') -> setAttribute('UNSIGNED');

		$this -> addColumn('tag_name', 'string') -> isVirtual();

		$this -> addConstraing('tag_id', 'tag_id', 'tb_tags', 'tag_id', 'CASCADE', 'CASCADE');
		$this -> addConstraing('tag_page_alloc', 'node_id', 'tb_page', 'node_id', 'CASCADE', 'CASCADE');
	}
}

?>