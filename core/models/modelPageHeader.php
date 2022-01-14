<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SCHEME.'schemePageHeader.php';	

class 	modelPageHeader extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('schemePageHeader', 'pageHeader');
	}	
}

class 	modelBackendPageHeader extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('schemeBackendPageHeader', 'backendPageHeader');
	}	
}
