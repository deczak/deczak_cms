<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelRightGroups.php';	

class	controllerRightGroups extends CController
{
	private		$modelRightGroups;

	public function
	__construct($_module, &$_object)
	{		
		$this -> modelRightGroups	= new modelRightGroups();

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
												$modelCondition -> whereLike('group_name', $itemParts[0]);
											}
											else
											{
												if( $itemParts[0] == 'cms-system-id' )
													$itemParts[0] = 'group_id';
												
												$modelCondition -> where($itemParts[0], $itemParts[1]);
											}
										}										
									}

									if(!$this -> modelRightGroups -> load($_pDatabase, $modelCondition, MODEL_RIGHTGROUPS_NUM_ASSIGNMENTS))
									{
										$validationMsg .= CLanguage::string('ERR_SQL_ERROR');
										$validationErr = true;
									}											
						
									$data = $this -> modelRightGroups -> getResult();

									foreach($data as &$item)
									{
										$item -> creaty_by_name = tk::getBackendUserName($_pDatabase, $item -> create_by);
										$item -> update_by_name = tk::getBackendUserName($_pDatabase, $item -> update_by);
									}

			tk::xhrResult(intval($validationErr), $validationMsg, $data);	// contains exit call
	

		return true;
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
			$_request[] 	 = 	[	"input" => "group_name",  	"validate" => "strip_tags|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "group_rights",   	"validate" => "strip_tags|!empty" ]; 	
			$_pURLVariables -> retrieve($_request, false, true);
			$_aFormData		 = $_pURLVariables ->getArray();

			if(empty($_aFormData['group_name'])) { 	$validationErr = true; 	$responseData[] = 'group_name'; 	}

			if(!$validationErr)	// Validation OK (by pre check)
			{	
			}
			else	// Validation Failed
			{
				$validationMsg .= CLanguage::string('ERR_VALIDATIONFAIL');
			}

			if(!$validationErr)	// Validation OK
			{

				$_aFormData['create_by'] 	= CSession::instance() -> getValue('user_id');
				$_aFormData['create_time'] 	= time();


				if(isset($_aFormData['group_rights'])) $_aFormData['group_rights'] = json_encode($_aFormData['group_rights']);
	
				$groupId = $this -> modelRightGroups -> insert($_pDatabase, $_aFormData);
				
				if($groupId !== false)
				{
					$_pPageRequest 	= CPageRequest::instance();

					$validationMsg = CLanguage::string('MOD_RGROUPS_GROUP_RIGHTS') .' '. CLanguage::string('WAS_CREATED'). ' - '. CLanguage::string('WAIT_FOR_REDIRECT');
					$responseData['redirect'] = CMS_SERVER_URL_BACKEND . $_pPageRequest -> urlPath .'group/'.$groupId;
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
			$modelCondition -> where('group_id', $systemId);

			if($this -> modelRightGroups -> load($_pDatabase, $modelCondition))
			{
				##	Gathering additional data

				$modelCondition = new CModelCondition();
				$modelCondition -> where('group_id', $systemId);


				$_crumbName	 = $this -> modelRightGroups -> getResultItem('group_id',intval($systemId),'group_name');

				$this -> setCrumbData('edit', $_crumbName, true);
				$this -> setView(
								'edit',
								'group/'. $systemId,								
								[
									'rightGroupsList' 	=> $this -> modelRightGroups -> getResult(),
									'enableEdit'	=> $_enableEdit,
									'enableDelete'	=> $_enableDelete
								]								
								);
				return true;
			}
		}

		CMessages::add(CLanguage::string('MOD_RGROUP_ERR_RGROUP_ID_UK') , MSG_WARNING);
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
									$_request[] 	 = 	[	"input" => "group_name",    		"validate" => "strip_tags|!empty" ]; 	
									$_request[] 	 = 	[	"input" => "group_rights",    	"validate" => "strip_tags|!empty" ]; 	
									$_pFormVariables-> retrieve($_request, false, true); // POST 
									$_aFormData		 = $_pFormVariables ->getArray();

									if(empty($_aFormData['group_name'])) { 	$validationErr = true; 	$responseData[] = 'group_name'; 	}

									if(!$validationErr)
									{
										$_aFormData['update_by'] 	= CSession::instance() -> getValue('user_id');
										$_aFormData['update_time'] 	= time();

										$_aFormData['group_id'] 	= $systemId;

										$modelCondition = new CModelCondition();
										$modelCondition -> where('group_id', $systemId);

										if(isset($_aFormData['group_rights'])) $_aFormData['group_rights'] = json_encode($_aFormData['group_rights']);

										if($this -> modelRightGroups -> update($_pDatabase, $_aFormData, $modelCondition))
										{
											$validationMsg = CLanguage::string('MOD_RGROUPS_GROUP_RIGHTS') .' '. CLanguage::string('WAS_UPDATED');
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
			##	XHR Function call

	
				$validationErr =	false;
				$validationMsg =	'';
				$responseData = 	[];



										$modelCondition = new CModelCondition();
										$modelCondition -> where('group_id', $systemId);

										if($this -> modelRightGroups -> delete($_pDatabase, $modelCondition))
										{
											$validationMsg = CLanguage::string('MOD_RGROUPS_GROUP_RIGHTS') .' '. CLanguage::string('WAS_DELETED'). ' - '. CLanguage::string('WAIT_FOR_REDIRECT');
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
