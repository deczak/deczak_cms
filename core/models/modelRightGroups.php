<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeRightGroups.php';	

class 	modelRightGroups extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('shemeRightGroups', 'rightGroup');	
	}	

/*
	public function
	load(&$_sqlConnection, CModelCondition $_condition = NULL, CModelComplementary $_complementary = NULL)
	{
		$result = parent::load($_sqlConnection, $_condition);

	//	if($result)
	//	foreach($this -> m_storage as $dataset)
	//		$dataset -> group_rights = json_decode($dataset -> group_rights);

		return $result;
	}
	*/
}

/**
 * 	Parent class for the data class with toolkit functions. It get the child instance to access the child properties.

class 	toolkitRightGroups
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