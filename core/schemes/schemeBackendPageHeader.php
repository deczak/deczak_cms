<?php

include_once 'schemePageHeader.php';

class schemeBackendPageHeader extends schemePageHeader 
{
	public function
	__construct()
	{
		parent::__construct(false);
		$this -> setTableName('tb_backend_page_header');
		$this -> addConstraint('backend_header_node_id', 'node_id', 'tb_backend_page', 'node_id', 'CASCADE', 'CASCADE');
	}
}
