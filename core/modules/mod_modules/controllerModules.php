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
		$enableDelete	= $this -> existsUserRight('delete');

		$logicResults = false;
		switch($_controllerAction)
		{
			case 'view'		: $logicResults = $this -> logicView(	$_pDatabase, $_isXHRequest, $enableEdit, $enableDelete);	break;
			case 'ping'		: $logicResults = $this -> logicPing(	$_pDatabase, $_isXHRequest, $enableEdit, $enableDelete);	break;
			case 'edit'		: $logicResults = $this -> logicEdit(	$_pDatabase, $_isXHRequest);	break;	
			case 'delete'	: $logicResults = $this -> logicDelete(	$_pDatabase, $_isXHRequest);	break;	
		}

		if(!$logicResults)
		{
			##	Default View
			$this -> logicIndex($_pDatabase, $_isXHRequest, $enableEdit, $enableDelete);	
		}
	}

	private function
	logicIndex(CDatabaseConnection &$_pDatabase, $_isXHRequest, $_enableEdit = false, $_enableDelete = false)
	{
		##	XHR request

		if($_isXHRequest !== false)
		{	
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			switch($_isXHRequest)
			{
				case 'raw-data'  :	// Request raw data



									
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
										$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
										$_bValidationErr = true;
									}											
						
										$data['installed'] = $this -> m_pModel -> getResult();
										$data['available'] = CModules::instance() -> getAvailableModules();



						/*

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
												$modelCondition -> whereLike('module_name', $itemParts[0]);
												$modelCondition -> whereLike('module_desc', $itemParts[0]);
											}
											else
											{
												if( $itemParts[0] == 'cms-system-id' )
													$itemParts[0] = 'module_id';
												
												$modelCondition -> where($itemParts[0], $itemParts[1]);
											}
										}										
									}

									if(!$this -> m_pModel -> load($_pDatabase, $modelCondition))
									{
										$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
										$_bValidationErr = true;
									}											
						
									$data = $this -> m_pModel -> getResult();

									foreach($data as &$item)
									{
										$item -> creaty_by_name = tk::getBackendUserName($_pDatabase, $item -> create_by);
										$item -> update_by_name = tk::getBackendUserName($_pDatabase, $item -> update_by);
									}
									*/

									break;

				/*
				case 'raw-data'  :	// Request raw data

									
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
										$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
										$_bValidationErr = true;
									}											
						
										$data['installed'] = $this -> m_pModel -> getResult();
										$data['available'] = CModules::instance() -> getAvailableModules();


									break;
									*/

				case 'install'  :	// Install





									$_pURLVariables	 =	new CURLVariables();
									$_request		 =	[];
									$_request[] 	 = 	[	"input" => "module", "validate" => "strip_tags|!empty", "use_default" => true, "default_value" => false ]; 		
									$_request[] 	 = 	[	"input" => "type", "validate" => "strip_tags|!empty", "use_default" => true, "default_value" => false ]; 		
									$_pURLVariables -> 	retrieve($_request, false, true); // POST 

									$errorMsg = '';

									if(!CModules::instance() -> install($_pDatabase, $_pURLVariables -> getValue("module"), $_pURLVariables -> getValue("type"), $errorMsg))
									{
										$_bValidationErr =	true;
										$_bValidationMsg =	$errorMsg;
									}

									$data = [];

									break;
			}

			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $data);	// contains exit call
		}

		##	Non XHR request
		
		$this -> setView(	
						'index',	
						'',
						[
							'enableEdit'	=> $_enableEdit,
							'enableDelete'	=> $_enableDelete
						]
						);
		
	}

	private function
	logicView(CDatabaseConnection &$_pDatabase, $_isXHRequest = false, $_enableEdit = false, $_enableDelete = false)
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

		CMessages::instance() -> addMessage(CLanguage::get() -> string('MOD_BEUSER_ERR_USERID_UK') , MSG_WARNING);
		return false;
	}

	private function
	logicEdit(CDatabaseConnection &$_pDatabase, $_isXHRequest = false)
	{	
		

		$systemId = $this -> querySystemId();
		if($systemId !== false && $_isXHRequest !== false)
		{	
			
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			switch($_isXHRequest)
			{
				case 'module-data'  :		// Update user data

											$_pFormVariables =	new CURLVariables();
											$_request		 =	[];
											$_request[] 	 = 	[	"input" => "is_active",  	"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
											$_pFormVariables-> retrieve($_request, false, true); // POST 
											$_aFormData		 = $_pFormVariables ->getArray();

											#if(empty($_aFormData['is_active'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'is_active'; 	}

											if(!$_bValidationErr)	// Validation OK (by pre check)
											{		
											}
											else	// Validation Failed 
											{
												$_bValidationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
											}

											if(!$_bValidationErr)
											{
												$_aFormData['update_by'] 	= CSession::instance() -> getValue('user_id');
												$_aFormData['update_time'] 	= time();

												$modelCondition = new CModelCondition();
												$modelCondition -> where('module_id', $systemId);
												
												if($this -> m_pModel -> update($_pDatabase, $_aFormData, $modelCondition))
												{
													$_bValidationMsg = CLanguage::get() -> string('M_BEMOULE_MSG_MODULE') .' '. CLanguage::get() -> string('WAS_UPDATED');
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
	logicDelete(CDatabaseConnection &$_pDatabase, $_isXHRequest = false)
	{	

		$systemId = $this -> querySystemId();

		if($systemId !== false && $_isXHRequest !== false)
		{	
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			switch($_isXHRequest)
			{
				case 'uninstall':	//	Uninstall
							
									CModules::instance() -> uninstall($_pDatabase, $systemId, $_bValidationMsg);

									$_bValidationMsg = CLanguage::get() -> string('M_BEMOULE_MSG_REMOVED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
									$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath;
														
									break;
			}

			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
		}			

		return false;
	}

	public function
	logicPing(CDatabaseConnection &$_pDatabase, $_isXHRequest = false, $_enableEdit = false, $_enableDelete = false)
	{
		$systemId 	= $this -> querySystemId();
		$pingId 	= $this -> querySystemId('cms-ping-id', true);

		if($systemId !== false && $_isXHRequest !== false)
		{	
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			switch($_isXHRequest)
			{
				case 'lockState':	
				
					$locked	= $this -> m_pModel -> ping($_pDatabase, CSession::instance() -> getValue('user_id'), $systemId, $pingId, MODEL_LOCK_UPDATE);
					tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $locked);
					break;
			}

			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);
		}
	}

}

?>