<?php

class shemeUsersRegister extends CSheme
{
	public function
	__construct()
	{
        parent::__construct('tb_users_register');
		
		$this -> addColumn('data_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();

		$this -> addColumn('user_id', DB_COLUMN_TYPE_STRING) -> setLength(25) -> setKey('UNIQUE');
		$this -> addColumn('user_type', DB_COLUMN_TYPE_TINYINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('user_hash', DB_COLUMN_TYPE_STRING) -> setLength(64) -> setDefault('NULL') -> setSystemId();
		$this -> addColumn('user_name', DB_COLUMN_TYPE_TEXT) -> setDefault('NULL');

		$this -> addColumnGroup(DB_COLUMN_GROUP_LOCK);
	}
}

?>