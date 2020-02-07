<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeTagsAllocation.php';	

class 	modelTagsAllocation extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('TagsAllocation');		
		$this -> m_sheme = new shemeTagsAllocation();
	}	
}



/**
 * 	Parent class for the data class with toolkit functions. It get the child instance to access the child properties.

class 	toolkitTagsAllocation
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