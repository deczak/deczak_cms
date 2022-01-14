<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SCHEME.'schemeDeniedRemote.php';	

class 	modelDeniedRemote extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('schemeDeniedRemote', 'deniedRemote');	
	}	
}

/**
 * 	Parent class for the data class with toolkit functions. It get the child instance to access the child properties.

class 	toolkitDeniedRemote
{
	protected	$m_childInstance;

	public function
	__construct($_instance)
	{
		$this -> m_childInstance = $_instance;
	}

}
 */
