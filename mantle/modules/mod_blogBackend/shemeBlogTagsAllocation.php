<?php

class shemeBlogTagsAllocation extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_blog_tags_allocation');
		
		$this -> addColumn('allocation_id', 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();
		$this -> addColumn('post_id', 'int') -> setIndex('INDEX') -> setAttribute('UNSIGNED');
		$this -> addColumn('tag_id', 'int') -> setIndex('INDEX') -> setAttribute('UNSIGNED');
	}
}

?>