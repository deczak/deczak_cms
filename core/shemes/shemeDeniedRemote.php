<?php

class shemeDeniedRemote extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_denied_remote');
		
		$this -> addColumn('data_id', 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();
		
		$this -> addColumn('denied_ip', 'string') -> setLength(40);
		$this -> addColumn('denied_desc', 'string') -> setLength(250);

		$this -> addColumn('create_time', 'bigint') -> setAttribute('UNSIGNED');
		$this -> addColumn('create_by', 'smallint') -> setAttribute('UNSIGNED');
		$this -> addColumn('update_time', 'bigint') -> setAttribute('UNSIGNED') -> setDefault('0');
		$this -> addColumn('update_by', 'smallint') -> setAttribute('UNSIGNED') -> setDefault('0');
	}
}

?>