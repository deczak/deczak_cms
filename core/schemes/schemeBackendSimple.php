<?php

include_once 'schemeSimple.php';

class schemeBackendSimple extends schemeSimple
{
	public function
	__construct()
	{
		parent::__construct(false);
		$this -> setTableName('tb_backend_page_object_simple');
		$this -> addConstraint('backend_simple_object_id', 'object_id', 'tb_backend_page_object', 'object_id', 'CASCADE', 'CASCADE');
	}
}
