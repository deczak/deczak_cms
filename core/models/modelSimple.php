<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SCHEME.'schemeSimple.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SCHEME.'schemeBackendSimple.php';	

class 	modelSimple extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('schemeSimple', 'simple');
	}
}

class 	modelBackendSimple extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('schemeBackendSimple', 'backendSimple');
	}
}
