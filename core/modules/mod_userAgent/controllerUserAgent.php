<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUserAgent.php';	

class	controllerUserAgent extends CController
{
	private		$m_modelRightGroups;

	public function
	__construct($_module, &$_object)
	{		
		$this -> m_pModel	= new modelUserAgent();
		parent::__construct($_module, $_object);

		CPageRequest::instance() -> subs = $this -> getSubSection();
	}
	
	public function
	logic(&$_sqlConnection, array $_rcaTarget, $_isXHRequest)
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

		$logicResults = false;
		switch($_controllerAction)
		{
			case 'view'		: $logicResults = $this -> logicView(	$_sqlConnection, $_isXHRequest, $enableEdit, $enableDelete);	break;
			case 'edit'		: $logicResults = $this -> logicEdit(	$_sqlConnection, $_isXHRequest);	break;	
			case 'create'	: $logicResults = $this -> logicCreate($_sqlConnection, $_isXHRequest);	break;
			case 'delete'	: $_loglogicResultsicResults = $this -> logicDelete($_sqlConnection, $_isXHRequest);	break;	
			case 'ping'		: $logicResults = $this -> logicPing($_sqlConnection, $_isXHRequest, $enableEdit, $enableDelete);	break;	
		}

		if(!$logicResults)
		{
			##	Default View
			$this -> logicIndex($_sqlConnection, $enableEdit, $enableDelete);	
		}
	}

	private function
	logicIndex(&$_sqlConnection, $_enableEdit = false, $_enableDelete = false)
	{
		$this -> m_pModel -> load($_sqlConnection);	
		$this -> setView(	
						'index',	
						'',
						[
							'agentsList' 		=> $this -> m_pModel -> getDataInstance(),
							'enableEdit'	=> $_enableEdit,
							'enableDelete'	=> $_enableDelete
						]
						);
	}

	private function
	logicCreate(&$_sqlConnection, $_isXHRequest)
	{
		if($_isXHRequest !== false)
		{
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			$_pURLVariables	 =	new CURLVariables();
			$_request		 =	[];
			$_request[] 	 = 	[	"input" => "agent_name",  		"validate" => "strip_tags|trim|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "agent_suffix",  	"validate" => "strip_tags|trim|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "agent_desc",  		"validate" => "strip_tags|trim|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "agent_allowed",  	"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
			$_pURLVariables -> retrieve($_request, false, true); // POST 
			$_aFormData		 = $_pURLVariables ->getArray();

			if(empty($_aFormData['agent_name'])) 	{ 	$_bValidationErr = true; 	$_bValidationDta[] = 'agent_name'; 	}
			if(empty($_aFormData['agent_suffix'])) 	{ 	$_bValidationErr = true; 	$_bValidationDta[] = 'agent_suffix'; 	}

			if(!$_bValidationErr)	// Validation OK (by pre check)
			{		
			}
			else	// Validation Failed 
			{
				$_bValidationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
			}

			if(!$_bValidationErr)	// Validation OK
			{

				$_aFormData['create_by'] 	= CSession::instance() -> getValue('user_id');
				$_aFormData['create_time'] 	= time();

				$dataId = 0;

				if($this -> m_pModel -> insert($_sqlConnection, $_aFormData, $dataId))
				{
					$_bValidationMsg = CLanguage::get() -> string('M_BEUSERAG_USERAGENT') .' '. CLanguage::get() -> string('WAS_CREATED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
					$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath .'agent/'.$dataId;
				}
				else
				{
					$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
				}
			}


			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
		}

		$this -> setCrumbData('create');
		$this -> setView(
						'create',
						'create/'
						);

		return true;
	}

	private function
	logicView(&$_sqlConnection, $_isXHRequest = false, $_enableEdit = false, $_enableDelete = false)
	{
		$systemId = $this -> querySystemId();

		if($systemId !== false )
		{	
			$modelCondition = new CModelCondition();
			$modelCondition -> where('data_id', $systemId);

			if($this -> m_pModel -> load($_sqlConnection, $modelCondition))
			{
				##	Gathering additional data

				$_crumbName	 = $this -> m_pModel -> searchValue(intval($systemId),'data_id','agent_name');

				$this -> setCrumbData('edit', $_crumbName, true);
				$this -> setView(
								'edit',
								'agent/'. $systemId,								
								[
									'agentsList' 	=> $this -> m_pModel -> getDataInstance(),
									'enableEdit'	=> $_enableEdit,
									'enableDelete'	=> $_enableDelete
								]								
								);
				return true;
			}
		}
		
		return false;
	}

	private function
	logicEdit(&$_sqlConnection, $_isXHRequest = false)
	{	
		$systemId = $this -> querySystemId();

		if($systemId !== false && $_isXHRequest !== false)
		{	
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			// check if dataset is locked, call his own xhrResult() 
			$this -> detectLock($_sqlConnection, $systemId);

			switch($_isXHRequest)
			{
				case 'user-agent'  :	

										$_pFormVariables =	new CURLVariables();
										$_request		 =	[];
										$_request[] 	 = 	[	"input" => "agent_name",  		"validate" => "strip_tags|trim|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "agent_suffix",  	"validate" => "strip_tags|trim|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "agent_desc",  		"validate" => "strip_tags|trim|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "agent_allowed",  	"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
										$_pFormVariables-> retrieve($_request, false, true); // POST 
										$_aFormData		 = $_pFormVariables ->getArray();

										if(empty($_aFormData['agent_name'])) 	{ 	$_bValidationErr = true; 	$_bValidationDta[] = 'agent_name'; 	}
										if(empty($_aFormData['agent_suffix'])) 	{ 	$_bValidationErr = true; 	$_bValidationDta[] = 'agent_suffix'; 	}

										// On edit, we ignore the unique check for the ip 

										if(!$_bValidationErr)
										{
											$_aFormData['update_by'] 	= CSession::instance() -> getValue('user_id');
											$_aFormData['update_time'] 	= time();

											$modelCondition = new CModelCondition();
											$modelCondition -> where('data_id', $systemId);

											if($this -> m_pModel -> update($_sqlConnection, $_aFormData, $modelCondition))
											{
												$_bValidationMsg = CLanguage::get() -> string('M_BEUSERAG_USERAGENT') .' '. CLanguage::get() -> string('WAS_UPDATED');
											}
											else
											{
												$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
												$_bValidationErr = true;
											}											
										}
										else	// Validation Failed
										{
											$_bValidationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
											$_bValidationErr = true;
										}

										break;
			}

			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call		
		}

		return false;
	}

	private function
	logicDelete(&$_sqlConnection, $_isXHRequest = false)
	{	
		$systemId = $this -> querySystemId();

		if($systemId !== false && $_isXHRequest !== false)
		{	
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			// check if dataset is locked, call his own xhrResult() 
			$this -> detectLock($_sqlConnection, $systemId);

			switch($_isXHRequest)
			{
				case 'agent-delete':

									$modelCondition = new CModelCondition();
									$modelCondition -> where('data_id', $systemId);

									if($this -> m_pModel -> delete($_sqlConnection, $modelCondition))
									{
										$_bValidationMsg = CLanguage::get() -> string('M_BEUSERAG_USERAGENT') .' '. CLanguage::get() -> string('WAS_DELETED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
										$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath;
									}
									else
									{
										$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
									}

									break;
			}

			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
		}

		return false;
	}

	public function
	logicPing(&$_sqlConnection, $_isXHRequest = false, $_enableEdit = false, $_enableDelete = false)
	{
		$systemId = $this -> querySystemId();

		if($systemId !== false && $_isXHRequest !== false)
		{	
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			switch($_isXHRequest)
			{
				case 'lockState':	
				
					$locked	= $this -> m_pModel -> lock($_sqlConnection, CSession::instance() -> getValue('user_id'), $systemId, LOCK_UPDATE);
					tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $locked);
					break;
			}

			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);
		}
	}


}

?>