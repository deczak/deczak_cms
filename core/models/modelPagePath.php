<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemePagePath.php';	

class 	modelPagePath extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('shemePagePath', 'pagePath');
	}	
}

class 	modelBackendPagePath extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('shemeBackendPagePath', 'backendPagePath');
	}	
}
