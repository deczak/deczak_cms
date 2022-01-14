<?php

class schemeSessionsArchiv extends CScheme
{
	public function
	__construct()
	{
		parent::__construct('tb_sessions_archiv');		
		
		$this -> addColumn('data_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();

		$this -> addColumn('session_id', DB_COLUMN_TYPE_STRING) -> setLength(65);
		$this -> addColumn('user_agent', DB_COLUMN_TYPE_TEXT);
		$this -> addColumn('user_ip', DB_COLUMN_TYPE_STRING) -> setLength(40);
		$this -> addColumn('time_create', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('time_update', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
		$this -> addColumn('time_out', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('login_fail_count', DB_COLUMN_TYPE_INT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
	}
}
