<?php

define('MODEL_RIGHTGROUPS_NUM_ASSIGNMENTS',0x101);

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeRightGroups.php';	

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUserGroups.php';	

class 	modelRightGroups extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('shemeRightGroups', 'rightGroup');	
	}	

	public function
	load(CDatabaseConnection &$_pDatabase, CModelCondition $_pCondition = NULL, $_execFlags = NULL)
	{
		$dtaCount = parent::load($_pDatabase, $_pCondition, $_execFlags);

		if($_execFlags & MODEL_RIGHTGROUPS_NUM_ASSIGNMENTS) 
		for($i = 0; $i < $dtaCount; $i++)
		{
			$accessCondition  		 = new CModelCondition();
			$accessCondition		-> where('group_id', $this -> m_resultList[$i] -> group_id);

			$modelUserGroups 		 = new modelUserGroups();
			$modelUserGroups		-> load($_pDatabase, $accessCondition);


			$this -> m_resultList[$i] -> num_assignments = count($modelUserGroups -> getResult());
			
		}

		return $dtaCount;
	}
}
