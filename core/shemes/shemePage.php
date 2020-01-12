<?php

class shemePage extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_page');
		
		$this -> addColumn('data_id', 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();

		$this -> addColumn('page_id', 'int') -> setAttribute('UNSIGNED');
		$this -> addColumn('node_id', 'int') -> setAttribute('UNSIGNED');
		$this -> addColumn('page_version', 'mediumint') -> setAttribute('UNSIGNED');

		$this -> addColumn('page_template', 'string') -> setLength(50);
		$this -> addColumn('update_reason', 'string') -> setLength(250);
		$this -> addColumn('hidden_state', 'tinyint') -> setAttribute('UNSIGNED') -> setDefault('0');
		$this -> addColumn('cache_disabled', 'tinyint') -> setAttribute('UNSIGNED') -> setDefault('0');
		$this -> addColumn('crawler_index', 'tinyint') -> setAttribute('UNSIGNED') -> setDefault('1');
		$this -> addColumn('crawler_follow', 'tinyint') -> setAttribute('UNSIGNED') -> setDefault('1');
		$this -> addColumn('menu_follow', 'tinyint') -> setAttribute('UNSIGNED') -> setDefault('1');

		$this -> addColumn('create_time', 'bigint') -> setAttribute('UNSIGNED');
		$this -> addColumn('create_by', 'smallint') -> setAttribute('UNSIGNED');
		$this -> addColumn('update_time', 'bigint') -> setAttribute('UNSIGNED') -> setDefault('0');
		$this -> addColumn('update_by', 'smallint') -> setAttribute('UNSIGNED') -> setDefault('0');

		$this -> addColumn('page_path', 'string') -> isVirtual();
		$this -> addColumn('page_title', 'string') -> isVirtual();
		$this -> addColumn('page_name', 'string') -> isVirtual();
		$this -> addColumn('page_language', 'string') -> isVirtual();
		$this -> addColumn('page_description', 'string') -> isVirtual();
		$this -> addColumn('alternate_path', 'array') -> isVirtual();
		$this -> addColumn('objects', 'array') -> isVirtual();
	}
}

?>