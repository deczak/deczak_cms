<?php

class shemeBackendMenu extends CSheme
{
	public function
	__construct()
	{
		parent::__construct('tb_backend_menu');	
		
		$this -> addColumn('id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();
		$this -> addColumn('menu_group', DB_COLUMN_TYPE_INT) -> setIndex('INDEX') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('menu_name', DB_COLUMN_TYPE_STRING) -> setLength(45);
		$this -> addColumn('menu_icon', DB_COLUMN_TYPE_STRING) -> setLength(10) -> setDefault('');
		$this -> addColumn('menu_order_by', DB_COLUMN_TYPE_INT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
	}
}

?>
