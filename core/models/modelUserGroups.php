<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SCHEME.'schemeUserGroups.php';	

class 	modelUserGroups extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('schemeUserGroups', 'userGroup');	
	}	
}
