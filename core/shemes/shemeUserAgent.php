<?php

class shemeUserAgent extends CSheme
{
	public function
	__construct()
	{
        parent::__construct('tb_useragents');
		
		$this -> addColumn('data_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();

		$this -> addColumn('agent_name', DB_COLUMN_TYPE_STRING) -> setLength(35);
		$this -> addColumn('agent_suffix', DB_COLUMN_TYPE_STRING) -> setLength(75);
		$this -> addColumn('agent_desc', DB_COLUMN_TYPE_STRING) -> setLength(200);
		$this -> addColumn('agent_allowed', DB_COLUMN_TYPE_TINYINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);

		$this -> addColumn('create_time', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('create_by', DB_COLUMN_TYPE_STRING) -> setLength(25);
		$this -> addColumn('update_time', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
		$this -> addColumn('update_by', DB_COLUMN_TYPE_STRING) -> setLength(25) -> setDefault('NULL');
		$this -> addColumn('lock_time', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
		$this -> addColumn('lock_by', DB_COLUMN_TYPE_STRING) -> setLength(25) -> setDefault('NULL');
	}
}

?>