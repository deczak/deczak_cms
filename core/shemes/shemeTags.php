<?php

class shemeTags extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_tags');
		
		$this -> addColumn('tag_id', 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();

		$this -> addColumn('tag_name', 'string') -> setLength(50);
		$this -> addColumn('tag_url', 'string') -> setLength(50);
		$this -> addColumn('tag_hidden', 'tinyint') -> setAttribute('UNSIGNED');
		$this -> addColumn('tag_disabled', 'tinyint') -> setAttribute('UNSIGNED');
	}
}

?>