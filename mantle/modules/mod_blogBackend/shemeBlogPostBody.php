<?php

class shemeBlogPostBody extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_blog_post_body');
		
		$this -> addColumn('post_body_id', 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();
		$this -> addColumn('post_id', 'int') -> setIndex('INDEX') -> setAttribute('UNSIGNED');
		
		$this -> addColumn('post_teaser', 'text');
		$this -> addColumn('post_body', 'text');
		$this -> addColumn('post_headline', 'string') -> setLength('200');
		$this -> addColumn('post_subheadline', 'string') -> setLength('200');
		$this -> addColumn('post_language', 'string') -> setLength('3');
		$this -> addColumn('post_url', 'string') -> setLength('150');

	}
}

?>