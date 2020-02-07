<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeSessionsAccess.php';	

class 	modelSessionsAccess extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('SessionAccess');		
		$this -> m_sheme = new shemeSessionsAccess();
	}	
}



/**
 * 	Parent class for the data class with toolkit functions. It get the child instance to access the child properties.

class 	toolkitSessionsAccess
{
	protected	$m_childInstance;

	public function
	__construct($_instance)
	{
		$this -> m_childInstance = $_instance;
	}

}
 */
?>