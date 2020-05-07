<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSessions.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSessionsAccess.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUserAgent.php';		

class	controllerSessions extends CController
{
	public function
	__construct($_module, &$_object)
	{		
		$this -> m_pModel	= new modelSessions();
		parent::__construct($_module, $_object);

		CPageRequest::instance() -> subs = $this -> getSubSection();
	}
	
	public function
	logic(CDatabaseConnection &$_pDatabase, array $_rcaTarget, $_isXHRequest)
	{
		##	Set default target if not exists

		$_controllerAction = $this -> getControllerAction($_rcaTarget, 'index');

		##	Check user rights for this target

		if(!$this -> detectRights($_controllerAction))
		{
			if($_isXHRequest !== false)
			{
				$_bValidationErr =	true;
				$_bValidationMsg =	CLanguage::get() -> string('ERR_PERMISSON');
				$_bValidationDta = 	[];

				tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
			}

			CMessages::instance() -> addMessage(CLanguage::get() -> string('ERR_PERMISSON') , MSG_WARNING);
			return;
		}

		##	Call sub-logic function by target, if there results are false, we make a fall back to default view

		$enableEdit 	= $this -> existsUserRight('edit');
		$enableDelete	= $enableEdit;

		$_logicResults = false;
		switch($_controllerAction)
		{
			case 'view'		: $_logicResults = $this -> logicView(	$_pDatabase, $_isXHRequest, $enableEdit, $enableDelete);	break;
			case 'delete'	: $_logicResults = $this -> logicDelete($_pDatabase, $_isXHRequest);	break;	
		}

		if(!$_logicResults)
		{
			##	Default View
			$this -> logicIndex($_pDatabase, $enableEdit, $enableDelete);	
		}
	}

	private function
	logicIndex(CDatabaseConnection &$_pDatabase, $_enableEdit = false, $_enableDelete = false)
	{
		$modelCondition  	 = new CModelCondition();
		$modelCondition		-> orderBy('time_create', 'DESC');

		$this -> m_pModel 	-> load($_pDatabase, $modelCondition, MODEL_SESSIONS_APPEND_ACCESS_DATA);	

		$modelUserAgent	 	 = new modelUserAgent();
		$modelUserAgent		-> load($_pDatabase);

		$this -> setView(	
						'index',	
						'',
						[
							'sessionList' 	=> $this -> m_pModel -> getResult(),
							'agentsList' 	=> $modelUserAgent -> getResult(),
							'enableEdit'	=> $_enableEdit,
							'enableDelete'	=> $_enableDelete
						]
						);
	}

	private function
	logicCreate(CDatabaseConnection &$_pDatabase, $_isXHRequest)
	{
	}

	private function
	logicView(CDatabaseConnection &$_pDatabase, $_isXHRequest = false, $_enableEdit = false, $_enableDelete = false)
	{	

		$systemId = $this -> querySystemId();

		if($systemId !== false)
		{
	
			$modelCondition = new CModelCondition();
			$modelCondition -> where('data_id', $systemId);



			if($this -> m_pModel -> load($_pDatabase, $modelCondition, MODEL_SESSIONS_APPEND_ACCESS_DATA))
			{
				##	Gathering additional data

				$_crumbName	 = $this -> m_pModel -> getResultItem('data_id', intval($systemId),'user_ip');

				$this -> setCrumbData('view', $systemId .' ('. $_crumbName .')', true);
				$this -> setView(
								'view',
								'session/'. $systemId,								
								[
									'sessionList' 	=> $this -> m_pModel -> getResult(),
									'enableEdit'	=> $_enableEdit,
									'enableDelete'	=> $_enableDelete
								]								
								);
				return true;
			}
		}

		CMessages::instance() -> addMessage(CLanguage::get() -> string('SESSION IS_UNKNOWN') , MSG_WARNING);
		return false;
	}

	private function
	logicEdit(CDatabaseConnection &$_pDatabase, $_isXHRequest = false)
	{	
	}

	private function
	logicDelete(CDatabaseConnection &$_pDatabase, $_isXHRequest = false)
	{	
		$systemId = $this -> querySystemId();

		if($systemId !== false)
		{	
			##	XHR Function call

			if($_isXHRequest !== false)
			{
				$_bValidationErr =	false;
				$_bValidationMsg =	'';
				$_bValidationDta = 	[];

				switch($_isXHRequest)
				{
					case 'session-delete':


										$modelCondition = new CModelCondition();
										$modelCondition -> where('data_id', $systemId);


										if($this -> m_pModel -> delete($_pDatabase, $modelCondition))
										{
											$_bValidationMsg = CLanguage::get() -> string('SESSION WAS_DELETED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
											$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath;

											$_pHTAccess  = new CHTAccess();
											$_pHTAccess -> generatePart4DeniedAddress($_pDatabase);
											$_pHTAccess -> writeHTAccess();

										}
										else
										{
											$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
										}

										break;
				}

				tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
			}		
		
		}

		CMessages::instance() -> addMessage(CLanguage::get() -> string('SESSION IS_UNKNOWN') , MSG_WARNING);
		return false;
	}
}

?>