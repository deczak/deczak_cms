<?php

include_once 'schemePagePath.php';

class schemeBackendPagePath extends schemePagePath 
{
	public function
	__construct()
	{
		parent::__construct();
		$this -> setTableName('tb_backend_page_path');
	}
}
