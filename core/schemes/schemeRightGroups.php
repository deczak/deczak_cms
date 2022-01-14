<?php

class schemeRightGroups extends CScheme
{
	public function
	__construct()
	{
		parent::__construct('tb_right_groups');		

				
		$this -> addColumn('group_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement() -> setSystemId();

		$this -> addColumn('group_name', DB_COLUMN_TYPE_STRING) -> setLength(50);
		$this -> addColumn('group_rights', DB_COLUMN_TYPE_JSON);

		$this -> addColumnGroup(DB_COLUMN_GROUP_CREATE);
		$this -> addColumnGroup(DB_COLUMN_GROUP_UPDATE);
		$this -> addColumnGroup(DB_COLUMN_GROUP_LOCK);
	}
}
