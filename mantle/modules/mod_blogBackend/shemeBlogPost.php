<?php

class shemeBlogPost extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_blog_post');
		
		$this -> addColumn('post_id', 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();
		
		$this -> addColumn('display', 'tinyint')	-> setAttribute('UNSIGNED');
		$this -> addColumn('display_time_from', 'bigint') -> setAttribute('UNSIGNED');
		$this -> addColumn('display_time_until', 'bigint') -> setAttribute('UNSIGNED');
		$this -> addColumn('display_time_end_locked', 'tinyint') -> setAttribute('UNSIGNED');

		$this -> addColumn('create_time', 'bigint') -> setAttribute('UNSIGNED');
		$this -> addColumn('create_by', 'smallint') -> setAttribute('UNSIGNED');
		$this -> addColumn('update_time', 'bigint') -> setAttribute('UNSIGNED') -> setDefault('0');
		$this -> addColumn('update_by', 'smallint') -> setAttribute('UNSIGNED') -> setDefault('0');
	}
}

?>
						
			