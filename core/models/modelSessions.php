<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeSessions.php';	

class 	modelSessions extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('Session');		
		$this -> m_sheme = new shemeSessions();
	}	
}

/**
 * 	Parent class for the data class with toolkit functions. It get the child instance to access the child properties.

class 	toolkitSessions
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