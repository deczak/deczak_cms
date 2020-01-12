<?php

class shemeBlogCategory extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_blog_category');
		
		$this -> addColumn('category_body_id', 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();
		$this -> addColumn('category_id', 'int') -> setIndex('INDEX') -> setAttribute('UNSIGNED');
		
		$this -> addColumn('category_language', 'string') -> setLength('3');
		$this -> addColumn('category_name', 'string') -> setLength('150');
		$this -> addColumn('category_url', 'string') -> setLength('150');
	}
}

?>