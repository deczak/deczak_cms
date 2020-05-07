<?php

class shemeModules extends CSheme
{
	public function
	__construct()
	{
        parent::__construct('tb_modules');
		
		$this -> addColumn('module_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();

		$this -> addColumn('module_location', DB_COLUMN_TYPE_STRING) -> setLength(50);
		$this -> addColumn('module_controller', DB_COLUMN_TYPE_STRING) -> setLength(50);
		$this -> addColumn('module_type', DB_COLUMN_TYPE_STRING) -> setLength(10);
		$this -> addColumn('module_group', DB_COLUMN_TYPE_STRING) -> setLength(25);
		$this -> addColumn('module_icon', DB_COLUMN_TYPE_STRING) -> setLength(10);
		$this -> addColumn('module_name', DB_COLUMN_TYPE_STRING) -> setLength(35);
		$this -> addColumn('module_desc', DB_COLUMN_TYPE_STRING) -> setLength(200);
		$this -> addColumn('module_extends', DB_COLUMN_TYPE_STRING) -> setLength(50) -> setDefault('NULL');
		$this -> addColumn('module_extends_by', DB_COLUMN_TYPE_STRING) -> setLength(50) -> setDefault('NULL');

		$this -> addColumn('is_frontend', DB_COLUMN_TYPE_TINYINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('is_active', DB_COLUMN_TYPE_TINYINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);

		$this -> addColumn('create_time', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('create_by', DB_COLUMN_TYPE_STRING) -> setLength(25);
		$this -> addColumn('update_time', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
		$this -> addColumn('update_by', DB_COLUMN_TYPE_STRING) -> setLength(25) -> setDefault('NULL');
	}
}

?>