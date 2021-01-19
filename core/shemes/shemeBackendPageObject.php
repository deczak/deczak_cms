<?php

class shemeBackendPageObject extends shemePageObject 
{
	public function
	__construct()
	{
		parent::__construct();
		$this -> setTableName('tb_backend_page_object');
		$this -> addConstraint('backend_object_node_id', 'node_id', 'tb_backend_page', 'node_id', 'CASCADE', 'CASCADE');
	}
}

?>