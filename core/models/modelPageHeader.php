<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemePageHeader.php';	

class 	modelPageHeader extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('shemePageHeader', 'pageHeader');
	}	
}

class 	modelBackendPageHeader extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('shemeBackendPageHeader', 'backendPageHeader');
	}	
}
