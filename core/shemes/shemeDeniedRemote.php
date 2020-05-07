<?php

class shemeDeniedRemote extends CSheme
{
	public function
	__construct()
	{
		parent::__construct('tb_denied_remote');	
		
		$this -> addColumn('data_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();
		
		$this -> addColumn('denied_ip', DB_COLUMN_TYPE_STRING) -> setLength(40);
		$this -> addColumn('denied_desc', DB_COLUMN_TYPE_STRING) -> setLength(250);

		$this -> addColumn('create_time', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('create_by', DB_COLUMN_TYPE_STRING) -> setLength(25);
		$this -> addColumn('update_time', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
		$this -> addColumn('update_by', DB_COLUMN_TYPE_STRING) -> setLength(25) -> setDefault('NULL');
		$this -> addColumn('lock_time', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
		$this -> addColumn('lock_by', DB_COLUMN_TYPE_STRING) -> setLength(25) -> setDefault('NULL');
	}
}

?>