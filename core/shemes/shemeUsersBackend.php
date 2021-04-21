<?php

class shemeUsersBackend extends CSheme
{
	public function
	__construct()
	{
		parent::__construct('tb_users_backend');		
		
		$this -> addColumn('data_id'		, DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();

		$this -> addColumn('login_name', DB_COLUMN_TYPE_TEXT);
		$this -> addColumn('login_pass', DB_COLUMN_TYPE_STRING) -> setLength(135);
		$this -> addColumn('login_count', DB_COLUMN_TYPE_INT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
		$this -> addColumn('user_id', DB_COLUMN_TYPE_STRING) -> setKey('UNIQUE') -> setLength(25) -> setSystemId();
		$this -> addColumn('user_name_first', DB_COLUMN_TYPE_TEXT);
		$this -> addColumn('user_name_last', DB_COLUMN_TYPE_TEXT);
		$this -> addColumn('user_mail', DB_COLUMN_TYPE_TEXT);
		$this -> addColumn('time_login', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
		$this -> addColumn('cookie_id', DB_COLUMN_TYPE_TEXT);
		$this -> addColumn('recover_key', DB_COLUMN_TYPE_STRING) -> setLength(65) -> setDefault('NULL');
		$this -> addColumn('recover_timeout', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
		$this -> addColumn('is_locked', DB_COLUMN_TYPE_TINYINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('language', DB_COLUMN_TYPE_STRING) -> setLength(3);
		$this -> addColumn('allow_remote', DB_COLUMN_TYPE_BOOL) -> setDefault(false);

		$this -> addColumnGroup(DB_COLUMN_GROUP_CREATE);
		$this -> addColumnGroup(DB_COLUMN_GROUP_UPDATE);
		$this -> addColumnGroup(DB_COLUMN_GROUP_LOCK);
		
		$this -> addColumn('user_rights'	, DB_COLUMN_TYPE_TEXT) -> setVirtual();
	}
}
