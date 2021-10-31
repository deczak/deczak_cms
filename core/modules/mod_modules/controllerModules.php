<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelModules.php';	

class	controllerModules extends CController
{
	#private		$m_modelRightGroups;

	public function
	__construct($_module, &$_object)
	{		
		$this -> m_pModel	= new modelModules();
		parent::__construct($_module, $_object);

		CPageRequest::instance() -> subs = $this -> getSubSection();
	}
	/*
	public function
	logic(CDatabaseConnection &$_pDatabase, array $_rcaTarget, $responseData)
	{
		##	Set default target if not exists

		$_controllerAction = $this -> getControllerAction($_rcaTarget, 'index');

		##	Check user rights for this target

		if(!$this -> detectRights($_controllerAction))
		{
			if($responseData !== false)
			{
				$validationErr =	true;
				$validationMsg =	CLanguage::get() -> string('ERR_PERMISSON');
				$responseData = 	[];

				tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
			}

			CMessages::add(CLanguage::get() -> string('ERR_PERMISSON') , MSG_WARNING);
			return;
		}

		##	Call sub-logic function by target, if there results are false, we make a fall back to default view

		$enableEdit 	= $this -> existsUserRight('edit');
		$enableDelete	= $this -> existsUserRight('delete');

		$logicResults = false;
		switch($_controllerAction)
		{
			case 'view'		: $logicResults = $this -> logicView(	$_pDatabase, $responseData, $enableEdit, $enableDelete);	break;
			case 'ping'		: $logicResults = $this -> logicPing(	$_pDatabase, $responseData, $enableEdit, $enableDelete);	break;
			case 'edit'		: $logicResults = $this -> logicEdit(	$_pDatabase, $responseData);	break;	
			case 'delete'	: $logicResults = $this -> logicDelete(	$_pDatabase, $responseData);	break;	
		}

		if(!$logicResults)
		{
			##	Default View
			$this -> logicIndex($_pDatabase, $responseData, $enableEdit, $enableDelete);	
		}
	}
	*/

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

			CMessages::add(CLanguage::get() -> string('ERR_PERMISSON') , MSG_WARNING);
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

			case 'xhr_index' : $logicDone = $this -> logicXHRIndex($_pDatabase, $_xhrInfo); break;
			case 'xhr_install' : $logicDone = $this -> logicXHRInstall($_pDatabase, $_xhrInfo); break;
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

									
									$systemId = $this -> querySystemId();


									$modelCondition  = 	new CModelCondition();

									if($systemId !== false)
									{	
										$modelCondition -> where('module_id', $systemId);
									}



									$modelCondition -> orderBy('is_frontend');
									$modelCondition -> orderBy('module_name');

									if(!$this -> m_pModel -> load($_pDatabase, $modelCondition))
									{
										$validationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
										$validationErr = true;
									}											
						
										$data['installed'] = $this -> m_pModel -> getResult();
										$data['available'] = CModules::instance() -> getAvailableModules();



		

			tk::xhrResult(intval($validationErr), $validationMsg, $data);	// contains exit call
	
		return false;
		
	}

	private function
	logicXHRInstall(CDatabaseConnection &$_pDatabase) : bool
	{

			$validationErr =	false;
			$validationMsg =	'';
			$responseData = 	[];

	

									$_pURLVariables	 =	new CURLVariables();
									$_request		 =	[];
									$_request[] 	 = 	[	"input" => "module", "validate" => "strip_tags|!empty", "use_default" => true, "default_value" => false ]; 		
									$_request[] 	 = 	[	"input" => "type", "validate" => "strip_tags|!empty", "use_default" => true, "default_value" => false ]; 		
									$_pURLVariables -> 	retrieve($_request, false, true); // POST 

									$errorMsg = '';

									if(!CModules::instance() -> install($_pDatabase, $_pURLVariables -> getValue("module"), $_pURLVariables -> getValue("type"), $errorMsg))
									{
										$validationErr =	true;
										$validationMsg =	$errorMsg;
									}

									$data = [];

		

			tk::xhrResult(intval($validationErr), $validationMsg, $data);	// contains exit call
	
		return false;
		
	}

	private function
	logicView(CDatabaseConnection &$_pDatabase, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{	
		$systemId = $this -> querySystemId();

		if($systemId !== false)
		{	
			$modelCondition = new CModelCondition();
			$modelCondition -> where('module_id', $systemId);

			if($this -> m_pModel -> load($_pDatabase, $modelCondition))
			{
				##	Gathering additional data

				$_crumbName	 = $this -> m_pModel -> getResultItem('module_id',intval($systemId),'module_name');

				$this -> setCrumbData('edit', $_crumbName, true);
				$this -> setView(
								'edit',
								'module/'. $systemId,								
								[
									'modulesList' 	=> $this -> m_pModel -> getResult(),
									'enableEdit'	=> $_enableEdit,
									'enableDelete'	=> $_enableDelete
								]								
								);
				return true;
			}
		}

		CMessages::add(CLanguage::get() -> string('MOD_BEUSER_ERR_USERID_UK') , MSG_WARNING);
		return false;
	}

	private function
	logicXHREdit(CDatabaseConnection &$_pDatabase) : bool
	{	
		

		$systemId = $this -> querySystemId();
		if($systemId !== false)
		{	
			
			$validationErr =	false;
			$validationMsg =	'';
			$responseData = 	[];


											$_pFormVariables =	new CURLVariables();
											$_request		 =	[];
											$_request[] 	 = 	[	"input" => "is_active",  	"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
											$_pFormVariables-> retrieve($_request, false, true); // POST 
											$_aFormData		 = $_pFormVariables ->getArray();

											#if(empty($_aFormData['is_active'])) { 	$validationErr = true; 	$responseData[] = 'is_active'; 	}

											if(!$validationErr)	// Validation OK (by pre check)
											{		
											}
											else	// Validation Failed 
											{
												$validationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
											}

											if(!$validationErr)
											{
												$_aFormData['update_by'] 	= CSession::instance() -> getValue('user_id');
												$_aFormData['update_time'] 	= time();

												$modelCondition = new CModelCondition();
												$modelCondition -> where('module_id', $systemId);
												
												if($this -> m_pModel -> update($_pDatabase, $_aFormData, $modelCondition))
												{
													$validationMsg = CLanguage::get() -> string('M_BEMOULE_MSG_MODULE') .' '. CLanguage::get() -> string('WAS_UPDATED');
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

							
									CModules::instance() -> uninstall($_pDatabase, $systemId, $validationMsg);

									$validationMsg = CLanguage::get() -> string('M_BEMOULE_MSG_REMOVED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
									$responseData['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath;


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
