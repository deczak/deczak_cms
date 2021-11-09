<?php


define('MODEL_SESSIONS_APPEND_ACCESS_DATA',0x101);
define('MODEL_SESSIONS_APPEND_AGENT_NAME',0x101);

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeSessions.php';
include_once 'modelUserAgent.php';

include_once 'modelSessionsAccess.php';	

class 	modelSessions extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('shemeSessions', 'session');
	}	

	public function
	load(CDatabaseConnection &$_pDatabase, CModelCondition $_pCondition = NULL, $_execFlags = NULL)
	{
		$dtaCount = parent::load($_pDatabase, $_pCondition, $_execFlags);

		if($_execFlags & MODEL_SESSIONS_APPEND_AGENT_NAME) 
		{
			$modelUserAgent	 = new modelUserAgent();
			$agentsCount 	= $modelUserAgent -> load($_pDatabase);
			$agentsList 	= $modelUserAgent -> getResult();

			for($i = 0; $i < $dtaCount; $i++)
			{
				$this -> m_resultList[$i] -> agent_name = tk::getValueFromArrayByValueI($agentsList, 'agent_suffix', $this -> m_resultList[$i] -> user_agent, 'agent_name',  CLanguage::string('VISITOR'));
			}
		}

		if($_execFlags & MODEL_SESSIONS_APPEND_ACCESS_DATA) 
		for($i = 0; $i < $dtaCount; $i++)
		{
			$accessCondition  		 = new CModelCondition();
			$accessCondition		-> where('session_id', $this -> m_resultList[$i] -> session_id);

			$modelSessionsAccess 	 = new modelSessionsAccess();
			$modelSessionsAccess	-> load($_pDatabase, $accessCondition);

			$this -> m_resultList[$i] -> pages = $modelSessionsAccess -> getResult();

			$this -> m_resultList[$i] -> num_pages = count($this -> m_resultList[$i] -> pages);

			if($this -> m_resultList[$i] -> num_pages != 0)			
				$this -> m_resultList[$i] -> latest_page = $this -> m_resultList[$i] -> pages[ $this -> m_resultList[$i] -> num_pages - 1] -> page_title;
			else
				$this -> m_resultList[$i] -> latest_page = '';		
		}

		return $dtaCount;
	}
}
