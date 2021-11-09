<?php

require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelLoginObjects.php';

class	controllerLoginObjects extends CController
{
	#private		$m_pModel;

	public function
	__construct($_module, &$_object)
	{		
		$this -> m_pModel	= new modelLoginObjects();

		parent::__construct($_module, $_object);

		CPageRequest::instance() -> subs = $this -> getSubSection();

		$this -> tablesList['users']		= [];
		$this -> tablesList['users'][] 		= 'tb_users_backend';
		$this -> tablesList['users'][] 		= 'tb_users';

		$this -> tablesList['assignment'] 	= [];
		$this -> tablesList['various'] 		= [];
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
				$validationMsg =	CLanguage::string('ERR_PERMISSON');
				$responseData  = 	[];


				tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
			}

			CMessages::add(CLanguage::string('ERR_PERMISSON') , MSG_WARNING);
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
		$this -> m_pModel -> load($_pDatabase);	
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
												$modelCondition -> whereLike('object_id', $itemParts[0]);
												$modelCondition -> whereLike('object_description', $itemParts[0]);
											}
											else
											{
												if( $itemParts[0] == 'cms-system-id' )
													$itemParts[0] = 'object_id';
												
												$modelCondition -> where($itemParts[0], $itemParts[1]);
											}
										}										
									}

									if(!$this -> m_pModel -> load($_pDatabase, $modelCondition))
									{
										$validationMsg .= CLanguage::string('ERR_SQL_ERROR');
										$validationErr = true;
									}											
						
									$data = $this -> m_pModel -> getResult();

									foreach($data as &$item)
									{
										$item -> creaty_by_name = tk::getBackendUserName($_pDatabase, $item -> create_by);
										$item -> update_by_name = tk::getBackendUserName($_pDatabase, $item -> update_by);
									}


			tk::xhrResult(intval($validationErr), $validationMsg, $data);	// contains exit call
	

		return false;
	}

	private function
	logicCreate(CDatabaseConnection &$_pDatabase) : bool
	{
		$this -> setCrumbData('create');
		$this -> setView(
						'create',
						'create/',
						[
							'tablesList'	=>	$this -> tablesList
						]
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
			$_request[] 	 = 	[	"input" => "object_id",  				"validate" => "strip_tags|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "object_description",	   	"validate" => "strip_tags|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "is_disabled",   			"validate" => "strip_tags|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "object_databases",  	 	"validate" => "strip_tags|!empty" ]; 		
			$_request[] 	 = 	[	"input" => "object_fields",   			"validate" => "strip_tags|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "object_session_ext",   		"validate" => "strip_tags|!empty",	 "use_default" => true, "default_value" => '[]'  ]; 	
			$_request[] 	 = 	[	"input" => "object_field_is_username",  "validate" => "strip_tags|!empty" ]; 	
			$_pURLVariables -> retrieve($_request, false, true);
			$_aFormData		 = $_pURLVariables ->getArray();

			if(empty($_aFormData['object_id'])) { 			$validationErr = true; 	$responseData[] = 'object_id'; 	}
			if(empty($_aFormData['object_databases'])) { 	$validationErr = true; 	$responseData[] = 'object_databases'; 	}
			if(empty($_aFormData['object_fields'])) { 		$validationErr = true; 	$responseData[] = 'object_fields'; 	}

			if(!$validationErr)	// Validation OK (by pre check)
			{		


				$modelCondition = new CModelCondition();
				$modelCondition -> where('object_id', $_aFormData['object_id']);

				if(!$this -> m_pModel -> unique($_pDatabase, $modelCondition))
				{
					$validationMsg .= CLanguage::string('M_BERMADDR_MSG_OBJEXIST');
					$validationErr = true;
				}
			}
			else	// Validation Failed
			{
				$validationMsg .= CLanguage::string('ERR_VALIDATIONFAIL');
			}

			if(!$validationErr)	// Validation OK
			{

										

				foreach($_aFormData['object_fields'] as $key => $fields)
				{
					if($key == intval($_aFormData['object_field_is_username'])) 
						$_aFormData['object_fields'][ $key ]['is_username'] = '1';
					else
						$_aFormData['object_fields'][ $key ]['is_username'] = '0';
				}

				// Re-Index Array for Javascript
				$tempArray = $_aFormData['object_fields'];
				$_aFormData['object_fields'] = [];
				foreach($tempArray as $key => $fields)
					$_aFormData['object_fields'][] = $fields;

				$_aFormData['object_databases'] 	= json_encode($_aFormData['object_databases']);
				$_aFormData['object_fields'] 	= json_encode($_aFormData['object_fields']);
				$_aFormData['object_session_ext'] 	= json_encode($_aFormData['object_session_ext']);

				$_aFormData['create_by'] 	= CSession::instance() -> getValue('user_id');
				$_aFormData['create_time'] 	= time();

				$insertedId = '0';
				if($this -> m_pModel -> insert($_pDatabase, $_aFormData, $insertedId))
				{


					$_pPageRequest 	= CPageRequest::instance();


					$validationMsg = CLanguage::string('MOD_LOGINO_OBJECT WAS_CREATED'). ' - '. CLanguage::string('WAIT_FOR_REDIRECT');
					$responseData['redirect'] = CMS_SERVER_URL_BACKEND . $_pPageRequest -> urlPath .'object/'. $_pURLVariables -> getValue("object_id");
				}
				else
				{
					$validationMsg .= CLanguage::string('ERR_SQL_ERROR');
				}
			}


			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call

		return false;
	}

	private function
	logicView(CDatabaseConnection &$_pDatabase, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{	


		$systemId = $this -> querySystemId();

		if($systemId !== false)
		{	

			$modelCondition = new CModelCondition();
			$modelCondition -> where('object_id', $systemId);

			if($this -> m_pModel -> load($_pDatabase, $modelCondition))
			{
				##	Gathering additional data

				

				$this -> setCrumbData('edit', $systemId, true);
				$this -> setView(
								'edit',
								'object/'. $systemId,								
								[
									'loginObjectsList'	=> $this -> m_pModel -> getResult(),
									'tablesList'	=>	$this -> tablesList,
									'enableEdit'	=> $_enableEdit,
									'enableDelete'	=> $_enableDelete
								]								
								);
				return true;
			}
		}

		CMessages::add(CLanguage::string('MOD_LOGINO_ERR_OBJECT_ID_UK') , MSG_WARNING);
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


										$_pFormVariables	 =	new CURLVariables();
										$_request		 =	[];
										$_request[] 	 = 	[	"input" => "object_description",	   	"validate" => "strip_tags|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "is_disabled",   			"validate" => "strip_tags|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "object_databases",  	 	"validate" => "strip_tags|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "object_table",   			"validate" => "strip_tags|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "object_fields",   			"validate" => "strip_tags|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "object_session_ext",   		"validate" => "strip_tags|!empty" ]; 	
										#$_request[] 	 = 	[	"input" => "object_auth_assign",   		"validate" => "strip_tags|!empty" ]; 	
										#$_request[] 	 = 	[	"input" => "object_session_assign",   		"validate" => "strip_tags|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "object_field_is_username",  "validate" => "strip_tags|!empty" ]; 	
										$_pFormVariables -> retrieve($_request, false, true);
										$_aFormData		 = $_pFormVariables ->getArray();

										if(empty($_aFormData['object_databases'])) { 	$validationErr = true; 	$responseData[] = 'object_databases'; 	}
									#	if(empty($_aFormData['object_fields'])) { 	$validationErr = true; 	$responseData[] = 'object_fields'; 	}


										if(!$validationErr)	// Validation OK (by pre check)
										{	
										}
										else	// Validation Failed
										{
											$validationMsg .= CLanguage::string('ERR_VALIDATIONFAIL');
										}

										if(!$validationErr)	// Validation OK
										{
											if(!empty($_aFormData['object_fields']))
											{
												foreach($_aFormData['object_fields'] as $key => $fields)
												{
													if($key == intval($_aFormData['object_field_is_username'])) 
														$_aFormData['object_fields'][ $key ]['is_username'] = '1';
													else
														$_aFormData['object_fields'][ $key ]['is_username'] = '0';
												}

												// Re-Index Array for Javascript
												$tempArray = $_aFormData['object_fields'];
												$_aFormData['object_fields'] = [];
												foreach($tempArray as $key => $fields)
													$_aFormData['object_fields'][] = $fields;

												$_aFormData['object_fields'] 		= json_encode($_aFormData['object_fields']);
											}

											$_aFormData['object_databases'] 	= json_encode($_aFormData['object_databases']);

											if(!empty($_aFormData['object_session_ext']))
												$_aFormData['object_session_ext'] 	= json_encode($_aFormData['object_session_ext']);

									#		if(!empty($_aFormData['object_auth_assign']))
									#			$_aFormData['object_auth_assign'] 	= json_encode($_aFormData['object_auth_assign']);

									#		if(!empty($_aFormData['object_session_assign']))
									#			$_aFormData['object_session_assign'] = json_encode($_aFormData['object_session_assign']);

											$_aFormData['update_by'] 	= CSession::instance() -> getValue('user_id');
											$_aFormData['update_time'] 	= time();

											$modelCondition = new CModelCondition();
											$modelCondition -> where('object_id', $systemId);	



											if($this -> m_pModel -> update($_pDatabase, $_aFormData, $modelCondition))
											{
												$validationMsg = CLanguage::string('MOD_LOGINO_OBJECT WAS_UPDATED');
											}
											else
											{
												$validationMsg .= CLanguage::string('ERR_SQL_ERROR');
												$validationErr = true;
											}											
										}
										else	// Validation Failed
										{
											$validationMsg .= CLanguage::string('ERR_VALIDATIONFAIL');
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


										$modelCondition = new CModelCondition();
										$modelCondition -> where('object_id', $systemId);

										if($this -> m_pModel -> delete($_pDatabase, $modelCondition))
										{
											$validationMsg = CLanguage::string('MOD_LOGINO_OBJECT WAS_DELETED'). ' - '. CLanguage::string('WAIT_FOR_REDIRECT');
											$responseData['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath;
										}
										else
										{
											$validationMsg .= CLanguage::string('ERR_SQL_ERROR');
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
