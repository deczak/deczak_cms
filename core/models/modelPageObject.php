<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SCHEME.'schemePageObject.php';	

class 	modelPageObject extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('schemePageObject', 'pageObject');	
	}	

	public function
	createClass()
	{
		return $this -> createPrototype();
	}
}

class 	modelBackendPageObject extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('schemeBackendPageObject', 'backendPageObject');	
	}	

	public function
	createClass()
	{
		return $this -> createPrototype();
	}
}
