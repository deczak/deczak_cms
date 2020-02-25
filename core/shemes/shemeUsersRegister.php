<?php

class shemeUsersRegister extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('tb_users_register');
		
		$this -> addColumn('data_id', 'int') -> setKey('PRIMARY') -> setAttribute('UNSIGNED') -> isAutoIncrement();

		$this -> addColumn('user_id', 'string') -> setLength(25) -> setKey('UNIQUE');
		$this -> addColumn('user_type', 'tinyint') -> setAttribute('UNSIGNED');
		$this -> addColumn('user_hash', 'string') -> setLength(64) -> setDefault('NULL');
		$this -> addColumn('user_name', 'text') -> setDefault('NULL');
	}
}

?>