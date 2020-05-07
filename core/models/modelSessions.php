<?php


define('MODEL_SESSIONS_APPEND_ACCESS_DATA',0x101);

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeSessions.php';

include_once 'modelSessionsAccess.php';	

class 	modelSessions extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('shemeSessions', 'session');
	}	

	public function
	load(CDatabaseConnection &$_pDatabase, CModelCondition &$_pCondition = NULL, $_execFlags = NULL)
	{
		$dtaCount = parent::load($_pDatabase, $_pCondition, $_execFlags);

		if($_execFlags & MODEL_SESSIONS_APPEND_ACCESS_DATA) 
		for($i = 0; $i < $dtaCount; $i++)
		{
			$accessCondition  		 = new CModelCondition();
			$accessCondition		-> where('session_id', $this -> m_resultList[$i] -> session_id);

			$modelSessionsAccess 	 = new modelSessionsAccess();
			$modelSessionsAccess	-> load($_pDatabase, $accessCondition);

			$this -> m_resultList[$i] -> pages = $modelSessionsAccess -> getResult();
		}

		return $dtaCount;
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