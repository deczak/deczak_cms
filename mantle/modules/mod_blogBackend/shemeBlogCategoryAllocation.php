<?php

class shemeBlogCategoryAllocation extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_blog_category_allocation');
		
		$this -> addColumn('allocation_id', 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();
		$this -> addColumn('post_id', 'int') -> setIndex('INDEX') -> setAttribute('UNSIGNED');
		$this -> addColumn('category_id', 'int') -> setIndex('INDEX') -> setAttribute('UNSIGNED');
	}
}

?>