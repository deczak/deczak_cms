<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemePageObject.php';	

class 	modelPageObject extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('shemePageObject', 'pageObject');	
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
		parent::__construct('shemeBackendPageObject', 'backendPageObject');	
	}	

	public function
	createClass()
	{
		return $this -> createPrototype();
	}
}

?>