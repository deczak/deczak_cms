<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SCHEME.'schemePagePath.php';	

class 	modelPagePath extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('schemePagePath', 'pagePath');
	}	
}

class 	modelBackendPagePath extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('schemeBackendPagePath', 'backendPagePath');
	}	
}
