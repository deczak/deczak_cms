<?php

class schemeModules extends CScheme
{
	public function
	__construct()
	{
        parent::__construct('tb_modules');
		
		$this -> addColumn('module_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement() -> setSystemId();

		$this -> addColumn('module_location', DB_COLUMN_TYPE_STRING) -> setLength(50);
		$this -> addColumn('module_controller', DB_COLUMN_TYPE_STRING) -> setLength(50);
		$this -> addColumn('module_type', DB_COLUMN_TYPE_STRING) -> setLength(10);
		$this -> addColumn('module_group', DB_COLUMN_TYPE_STRING) -> setLength(25);
		$this -> addColumn('module_icon', DB_COLUMN_TYPE_STRING) -> setLength(10);
		$this -> addColumn('module_name', DB_COLUMN_TYPE_STRING) -> setLength(35);
		$this -> addColumn('module_desc', DB_COLUMN_TYPE_STRING) -> setLength(200);
		$this -> addColumn('module_extends', DB_COLUMN_TYPE_STRING) -> setLength(50) -> setDefault('NULL');
		$this -> addColumn('module_extends_by', DB_COLUMN_TYPE_STRING) -> setLength(50) -> setDefault('NULL');

		$this -> addColumn('is_frontend', DB_COLUMN_TYPE_BOOL) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('is_active', DB_COLUMN_TYPE_BOOL) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('is_systemFunction', DB_COLUMN_TYPE_BOOL) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');

		$this -> addColumnGroup(DB_COLUMN_GROUP_CREATE);
		$this -> addColumnGroup(DB_COLUMN_GROUP_UPDATE);
		$this -> addColumnGroup(DB_COLUMN_GROUP_LOCK);
	}
}
