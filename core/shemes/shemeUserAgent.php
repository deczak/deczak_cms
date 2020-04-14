<?php

class shemeUserAgent extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_useragents');
		
		$this -> addColumn('data_id', 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement() -> isSystemId();

		$this -> addColumn('agent_name', 'string') -> setLength(35);
		$this -> addColumn('agent_suffix', 'string') -> setLength(75);
		$this -> addColumn('agent_desc', 'string') -> setLength(200);

		$this -> addColumn('agent_allowed', 'tinyint') -> setAttribute('UNSIGNED');

		$this -> addColumn('create_time', 'bigint') -> setAttribute('UNSIGNED');
		$this -> addColumn('create_by', 'string') -> setLength(25);
		$this -> addColumn('update_time', 'bigint') -> setAttribute('UNSIGNED') -> setDefault('0');
		$this -> addColumn('update_by', 'string') -> setLength(25) -> setDefault('NULL');
		$this -> addColumn('lock_time', 'bigint') -> setAttribute('UNSIGNED') -> setDefault('0');
		$this -> addColumn('lock_by', 'string') -> setLength(25) -> setDefault('NULL');
	}
}

?>