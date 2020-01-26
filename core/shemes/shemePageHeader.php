<?php

class shemePageHeader extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_page_header');
		
		$this -> addColumn('data_id'		, 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();
		$this -> addColumn('node_id'		, 'int') -> setIndex('INDEX') -> setAttribute('UNSIGNED');
		$this -> addColumn('page_id'		, 'int') -> setIndex('INDEX') ->  setAttribute('UNSIGNED');

		$this -> addColumn('page_language'	, 'string') -> setLength(5);
		$this -> addColumn('page_title'		, 'string') -> setLength(100);
		$this -> addColumn('page_name'		, 'string') -> setLength(100);
		$this -> addColumn('page_description', 'string') -> setLength(160);
		$this -> addColumn('page_version'	, 'mediumint') -> setAttribute('UNSIGNED');

		$this -> addConstraing('header_node_id', 'node_id', 'tb_page', 'node_id', 'CASCADE', 'CASCADE');
	}
}

?>