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
	logic(CDatabaseConnection &$_pDatabase, array $_rcaTarget, ?object $_xhrInfo) : bool
	{
		##	Set default target if not exists

		$controllerAction = $this -> getControllerAction($_rcaTarget, 'index');
		
		##	Check user rights for this target
		
		if(!$this -> detectRights($controllerAction))
		{
			if($_xhrInfo !== null)
			{
				$validationErr =	true;
				$validationMsg =	CLanguage::get() -> string('ERR_PERMISSON');
				$responseData  = 	[];


				tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
			}

			CMessages::instance() -> addMessage(CLanguage::get() -> string('ERR_PERMISSON') , MSG_WARNING);
			return false;
		}

		$controllerAction = $this -> getControllerAction_v2($_rcaTarget, $_xhrInfo, 'index');

			
		if($_xhrInfo !== null && $_xhrInfo -> isXHR && $_xhrInfo -> objectId === $this -> objectInfo -> object_id)
			$controllerAction = 'xhr_'. $_xhrInfo -> action;

		##	Call sub-logic function by target, if there results are false, we make a fall back to default view

		$enableEdit 	= $this -> existsUserRight('edit');
		$enableDelete	= $enableEdit;
	

		$logicDone = false;
		switch($controllerAction)
		{
			case 'view'		: $logicDone = $this -> logicView(	$_pDatabase, $enableEdit, $enableDelete);	break;
			case 'create'	: $logicDone = $this -> logicCreate($_pDatabase);	break;

			case 'xhr_index' : $logicDone = $this -> logicXHRIndex($_pDatabase, $_xhrInfo); break;	
			case 'xhr_create'	: $logicDone = $this -> logicXHRCreate($_pDatabase);	break;
			case 'xhr_delete'	: $logicDone = $this -> logicXHRDelete($_pDatabase);	break;	
			case 'xhr_ping'		: $logicDone = $this -> logicXHRPing($_pDatabase);	break;	
			case 'xhr_edit'		: $logicDone = $this -> logicXHREdit($_pDatabase);	break;	
		
		
		}

		if(!$logicDone) // Default
			$logicDone = $this -> logicIndex($_pDatabase, $enableEdit, $enableDelete);	
	
		return $logicDone;
	}

	private function
	logicIndex(CDatabaseConnection &$_pDatabase, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{
		$this -> setView(	
						'index',	
						'',
						[
							'enableEdit'	=> $_enableEdit,
							'enableDelete'	=> $_enableDelete
						]
						);

		return true;
	}

	private function
	logicXHRIndex(CDatabaseConnection &$_pDatabase) : bool
	{

	
			$validationErr =	false;
			$validationMsg =	'';
			$responseData = 	[];


									$_pURLVariables	 =	new CURLVariables();
									$_request		 =	[];
									$_request[] 	 = 	[	"input" => 'q',  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
									$_pURLVariables -> retrieve($_request, false, true);	
					
									$modelCondition  = 	new CModelCondition();

									if($_pURLVariables -> getValue("q") !== false)
									{	
										$conditionSource = 	explode(' ', $_pURLVariables -> getValue("q"));
										foreach($conditionSource as $conditionItem)
										{
											$itemParts = explode(':', $conditionItem);

											if(count($itemParts) == 1)
											{
												$modelCondition -> whereLike('agent_name', $itemParts[0]);
												$modelCondition -> whereLike('agent_suffix', $itemParts[0]);
											}
											else
											{
												if( $itemParts[0] == 'cms-system-id' )
													$itemParts[0] = 'data_id';
												
												$modelCondition -> where($itemParts[0], $itemParts[1]);
											}
										}										
									}

									if(!$this -> m_pModel -> load($_pDatabase, $modelCondition))
									{
										$validationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
										$validationErr = true;
									}											
						
									$responseData = $this -> m_pModel -> getResult();

									foreach($responseData as &$item)
									{
										$item -> creaty_by_name = tk::getBackendUserName($_pDatabase, $item -> create_by);
										$item -> update_by_name = tk::getBackendUserName($_pDatabase, $item -> update_by);
									}

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
	

		return false;
	}

	private function
	logicCreate(CDatabaseConnection &$_pDatabase) : bool
	{
		$this -> setCrumbData('create');
		$this -> setView(
						'create',
						'create/'
						);

		return true;
	}

	private function
	logicXHRCreate(CDatabaseConnection &$_pDatabase) : bool
	{
		
			$validationErr =	false;
			$validationMsg =	'';
			$responseData = 	[];

			$_pURLVariables	 =	new CURLVariables();
			$_request		 =	[];
			$_request[] 	 = 	[	"input" => "agent_name",  		"validate" => "strip_tags|trim|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "agent_suffix",  	"validate" => "strip_tags|trim|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "agent_desc",  		"validate" => "strip_tags|trim|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "agent_allowed",  	"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
			$_pURLVariables -> retrieve($_request, false, true); // POST 
			$_aFormData		 = $_pURLVariables ->getArray();

			if(empty($_aFormData['agent_name'])) 	{ 	$validationErr = true; 	$responseData[] = 'agent_name'; 	}
			if(empty($_aFormData['agent_suffix'])) 	{ 	$validationErr = true; 	$responseData[] = 'agent_suffix'; 	}

			if(!$validationErr)	// Validation OK (by pre check)
			{		
			}
			else	// Validation Failed 
			{
				$validationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
			}

			if(!$validationErr)	// Validation OK
			{

				$_aFormData['create_by'] 	= CSession::instance() -> getValue('user_id');
				$_aFormData['create_time'] 	= time();

				

				if($dataId = $this -> m_pModel -> insert($_pDatabase, $_aFormData))
				{
					$validationMsg = CLanguage::get() -> string('M_BEUSERAG_USERAGENT') .' '. CLanguage::get() -> string('WAS_CREATED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
					$responseData['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath .'agent/'.$dataId;
				}
				else
				{
					$validationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
				}
			}


			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		


		return false;
	}

	private function
	logicView(CDatabaseConnection &$_pDatabase, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{
		$systemId = $this -> querySystemId();

		if($systemId !== false )
		{	
			$modelCondition = new CModelCondition();
			$modelCondition -> where('data_id', $systemId);

			if($this -> m_pModel -> load($_pDatabase, $modelCondition))
			{
				##	Gathering additional data

				$_crumbName	 = $this -> m_pModel -> getResultItem('data_id', intval($systemId), 'agent_name');

				$this -> setCrumbData('edit', $_crumbName, true);
				$this -> setView(
								'edit',
								'agent/'. $systemId,								
								[
									'agentsList' 	=> $this -> m_pModel -> getResult(),
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
	logicXHREdit(CDatabaseConnection &$_pDatabase) : bool
	{
		$systemId 	= $this -> querySystemId();

		if($systemId !== false)
		{	
			$validationErr =	false;
			$validationMsg =	'';
			$responseData = 	[];

			$pingId 	= $this -> querySystemId('cms-ping-id', true);

			## check if dataset is locked, call his own xhrResult() 
			$this -> detectLock($_pDatabase, $systemId, $pingId);


										$_pFormVariables =	new CURLVariables();
										$_request		 =	[];
										$_request[] 	 = 	[	"input" => "agent_name",  		"validate" => "strip_tags|trim|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "agent_suffix",  	"validate" => "strip_tags|trim|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "agent_desc",  		"validate" => "strip_tags|trim|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "agent_allowed",  	"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
										$_pFormVariables-> retrieve($_request, false, true); // POST 
										$_aFormData		 = $_pFormVariables ->getArray();

										if(empty($_aFormData['agent_name'])) 	{ 	$validationErr = true; 	$responseData[] = 'agent_name'; 	}
										if(empty($_aFormData['agent_suffix'])) 	{ 	$validationErr = true; 	$responseData[] = 'agent_suffix'; 	}


										if(!$validationErr)
										{
											$_aFormData['update_by'] 	= CSession::instance() -> getValue('user_id');
											$_aFormData['update_time'] 	= time();

											$modelCondition = new CModelCondition();
											$modelCondition -> where('data_id', $systemId);

											if($this -> m_pModel -> update($_pDatabase, $_aFormData, $modelCondition))
											{
												$validationMsg = CLanguage::get() -> string('M_BEUSERAG_USERAGENT') .' '. CLanguage::get() -> string('WAS_UPDATED');
											}
											else
											{
												$validationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
												$validationErr = true;
											}											
										}
										else	// Validation Failed
										{
											$validationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
											$validationErr = true;
										}

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call		
		}

		return false;
	}

	private function
	logicXHRDelete(CDatabaseConnection &$_pDatabase) : bool
	{	
		$systemId = $this -> querySystemId();

		if($systemId !== false)
		{	
			$validationErr =	false;
			$validationMsg =	'';
			$responseData = 	[];

			$pingId 	= $this -> querySystemId('cms-ping-id', true);

			## check if dataset is locked, call his own xhrResult() 
			$this -> detectLock($_pDatabase, $systemId, $pingId);


									$modelCondition = new CModelCondition();
									$modelCondition -> where('data_id', $systemId);

									if($this -> m_pModel -> delete($_pDatabase, $modelCondition))
									{
										$validationMsg = CLanguage::get() -> string('M_BEUSERAG_USERAGENT') .' '. CLanguage::get() -> string('WAS_DELETED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
										$responseData['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath;
									}
									else
									{
										$validationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
									}

			

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		}

		return false;
	}

	public function
	logicXHRPing(CDatabaseConnection &$_pDatabase) : bool
	{
		$systemId 	= $this -> querySystemId();
		$pingId 	= $this -> querySystemId('cms-ping-id', true);

		if($systemId !== false)
		{	
			$validationErr =	false;
			$validationMsg =	'';
		
			$locked	= $this -> m_pModel -> ping($_pDatabase, CSession::instance() -> getValue('user_id'), $systemId, $pingId, MODEL_LOCK_UPDATE);

			tk::xhrResult(intval($validationErr), $validationMsg, $locked);
		}

		return false;
	}
}
