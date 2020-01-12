<?php

class shemeBlogTags extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_blog_tags');
		
		$this -> addColumn('tag_body_id', 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();
		$this -> addColumn('tag_id', 'int') -> setIndex('INDEX') -> setAttribute('UNSIGNED');
		
		$this -> addColumn('tag_language', 'string') -> setLength('3');
		$this -> addColumn('tag_name', 'string') -> setLength('150');
		$this -> addColumn('tag_url', 'string') -> setLength('150');
	}
}

?>