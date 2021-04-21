<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeSessionsAccess.php';	

class 	modelSessionsAccess extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('shemeSessionsAccess', 'sessionAccess');		
	}	

	public function
	load(CDatabaseConnection &$_pDatabase, CModelCondition $_pCondition = NULL, $_execFlags = NULL)
	{
		$condition	 = new CModelCondition();
		$condition	-> where('tb_page_header.node_id', 'tb_sessions_access.node_id');

		$this 		-> addRelation('JOIN', 'tb_page_header', $condition);
		$this  		-> addSelectColumns('tb_sessions_access.*', 'tb_page_header.page_title');

		$dtaCount 	 = parent::load($_pDatabase, $_pCondition, $_execFlags);

		return $dtaCount;
	}
}
