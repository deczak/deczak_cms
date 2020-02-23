<?php

class shemeLanguages extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_languages') ;
		
		$this -> addColumn('data_id', 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();
		$this -> addColumn('lang_key', 'string') -> setLength('4') -> setKey('UNIQUE');
		$this -> addColumn('lang_name', 'string') -> setLength('25');
		$this -> addColumn('lang_name_native', 'string') -> setLength('25');
		$this -> addColumn('lang_hidden', 'bool');
		$this -> addColumn('lang_locked', 'bool');
		$this -> addColumn('lang_default', 'bool');
		$this -> addColumn('lang_frontend', 'bool') -> setDefault('1');
		$this -> addColumn('lang_backend', 'bool') -> setDefault('0');

		$this -> addColumn('create_time', 'bigint') -> setAttribute('UNSIGNED');
		$this -> addColumn('create_by', 'string') -> setLength(25);
		$this -> addColumn('update_time', 'bigint') -> setAttribute('UNSIGNED') -> setDefault('0');
		$this -> addColumn('update_by', 'string') -> setLength(25) -> setDefault('NULL');
	}
}

?>