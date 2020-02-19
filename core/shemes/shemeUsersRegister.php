<?php

class shemeUserRegister extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_users_register');
		
		$this -> addColumn('data_id', 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();

		$this -> addColumn('user_id', 'string') -> setLength(25) -> setKey('UNIQUE');
		$this -> addColumn('user_type', 'tinyint') -> setAttribute('UNSIGNED');
	}
}

?>