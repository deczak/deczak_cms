<?php

include_once 'schemePage.php';

class schemeBackendPage extends schemePage 
{
	public function
	__construct()
	{
		parent::__construct();
		$this -> setTableName('tb_backend_page');	
		$this -> addColumn('menu_group', DB_COLUMN_TYPE_INT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED)-> setDefault('0');
	}
}
