<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeSimple.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeBackendSimple.php';	

class 	modelSimple extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('shemeSimple', 'simple');
	}
}

class 	modelBackendSimple extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('shemeBackendSimple', 'backendSimple');
	}
}

?>