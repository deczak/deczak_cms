<?php

class shemeLoginObjects extends CSheme
{
	public function
	__construct()
	{
        parent::__construct('tb_login_objects');
		
		$this -> addColumn('data_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();

		$this -> addColumn('object_id', DB_COLUMN_TYPE_STRING) -> setKey('UNIQUE') -> setLength(25);
		$this -> addColumn('object_databases', DB_COLUMN_TYPE_TEXT);
		$this -> addColumn('object_fields', DB_COLUMN_TYPE_TEXT);
		$this -> addColumn('object_session_ext', DB_COLUMN_TYPE_TEXT);
		$this -> addColumn('object_description', DB_COLUMN_TYPE_STRING) -> setLength(200);
		$this -> addColumn('is_disabled', DB_COLUMN_TYPE_TINYINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('is_protected', DB_COLUMN_TYPE_TINYINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		
		$this -> addColumn('create_time', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('create_by', DB_COLUMN_TYPE_STRING) -> setLength(25);
		$this -> addColumn('update_time', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
		$this -> addColumn('update_by', DB_COLUMN_TYPE_STRING) -> setLength(25) -> setDefault('NULL');
	}
}

?>