<?php

class shemeLoginObjects extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_login_objects') ;
		
		$this -> addColumn('data_id', 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();

		$this -> addColumn('object_id', 'string') -> setKey('UNIQUE') -> setLength(25);
		$this -> addColumn('object_databases', 'text');
		$this -> addColumn('object_fields', 'text');
		$this -> addColumn('object_session_ext', 'text');
		$this -> addColumn('object_description', 'string') -> setLength(200);

		$this -> addColumn('is_disabled', 'tinyint') -> setAttribute('UNSIGNED');
		$this -> addColumn('is_protected', 'tinyint') -> setAttribute('UNSIGNED');
		
		$this -> addColumn('create_time', 'bigint') -> setAttribute('UNSIGNED');
		$this -> addColumn('create_by', 'string') -> setLength(25);
		$this -> addColumn('update_time', 'bigint') -> setAttribute('UNSIGNED') -> setDefault('0');
		$this -> addColumn('update_by', 'string') -> setLength(25) -> setDefault('NULL');
	}
}

?>