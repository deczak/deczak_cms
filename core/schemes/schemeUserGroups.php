<?php

class schemeUserGroups extends CScheme
{
	public function
	__construct()
	{
		parent::__construct('tb_users_groups');		
		
		$this -> addColumn('data_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();

		$this -> addColumn('user_id', DB_COLUMN_TYPE_STRING) -> setLength(25);
		$this -> addColumn('group_id', DB_COLUMN_TYPE_INT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('user_hash', DB_COLUMN_TYPE_STRING) -> setLength(64) -> setDefault('NULL');
		
		$this -> addColumn('update_time', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
		$this -> addColumn('update_by', DB_COLUMN_TYPE_STRING) -> setLength(25) -> setDefault('NULL');
	}
}
