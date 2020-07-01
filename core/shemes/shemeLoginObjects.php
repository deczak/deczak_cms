<?php

class shemeLoginObjects extends CSheme
{
	public function
	__construct()
	{
        parent::__construct('tb_login_objects');
		
		$this -> addColumn('data_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();

		$this -> addColumn('object_id', DB_COLUMN_TYPE_STRING) -> setKey('UNIQUE') -> setLength(25) -> setSystemId();
		$this -> addColumn('object_databases', DB_COLUMN_TYPE_JSON);
		$this -> addColumn('object_fields', DB_COLUMN_TYPE_JSON);
		$this -> addColumn('object_session_ext', DB_COLUMN_TYPE_JSON);
		$this -> addColumn('object_description', DB_COLUMN_TYPE_STRING) -> setLength(200);
		$this -> addColumn('is_disabled', DB_COLUMN_TYPE_TINYINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('is_protected', DB_COLUMN_TYPE_TINYINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);

		$this -> addColumnGroup(DB_COLUMN_GROUP_CREATE);
		$this -> addColumnGroup(DB_COLUMN_GROUP_UPDATE);
		$this -> addColumnGroup(DB_COLUMN_GROUP_LOCK);
	}
}

?>