<?php

class shemeRightGroups extends CSheme
{
	public function
	__construct()
	{
		parent::__construct('tb_right_groups');		

				
		$this -> addColumn('group_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();

		$this -> addColumn('group_name', DB_COLUMN_TYPE_STRING) -> setLength(50);
		$this -> addColumn('group_rights', DB_COLUMN_TYPE_JSON);

		$this -> addColumn('create_time', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('create_by', DB_COLUMN_TYPE_STRING) -> setLength(25);
		$this -> addColumn('update_time', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
		$this -> addColumn('update_by', DB_COLUMN_TYPE_STRING) -> setLength(25) -> setDefault('NULL');
	}
}

?>