<?php

require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelRightGroups.php';	
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUserGroups.php';	
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUsersRegister.php';	

require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUsers.php';	

class	controllerUsers extends CController
{
	private		$m_modelRightGroups;

	public function
	__construct($_module, &$_object)
	{		
		$this -> m_pModel	= new modelUsers();
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
			case 'view'			  : $logicDone = $this -> logicView($_pDatabase, $enableEdit, $enableDelete); break;
			case 'create'	 	  : $logicDone = $this -> logicCreate($_pDatabase);	break;
			case 'xhr_index' 	  : $logicDone = $this -> logicXHRIndex($_pDatabase, $_xhrInfo); break;
			case 'xhr_create'	  : $logicDone = $this -> logicXHRCreate($_pDatabase); break;
			case 'xhr_delete'	  : $logicDone = $this -> logicXHRDelete($_pDatabase); break;	
			case 'xhr_ping'		  : $logicDone = $this -> logicXHRPing($_pDatabase); break;	
			case 'xhr_edit-rights': $logicDone = $this -> logicXHREditRights($_pDatabase); break;	
			case 'xhr_edit-auth'  : $logicDone = $this -> logicXHREditAuth($_pDatabase); break;	
			case 'xhr_edit-user'  : $logicDone = $this -> logicXHREditUser($_pDatabase); break;			
		}

		if(!$logicDone) // Default
			$logicDone = $this -> logicIndex($_pDatabase, $enableEdit, $enableDelete);	
	
		return $logicDone;
	}

	protected function
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

	protected function
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
					$modelCondition -> whereLike('user_name_first', $itemParts[0]);
					$modelCondition -> whereLike('user_name_last', $itemParts[0]);
				}
				else
				{
					if( $itemParts[0] == 'cms-system-id' )
						$itemParts[0] = 'user_id';
					
					$modelCondition -> where($itemParts[0], $itemParts[1]);
				}
			}										
		}

		if(!$this -> m_pModel -> load($_pDatabase, $modelCondition, MODEL_USERS_STRIP_SENSITIVE_DATA))
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

	protected function
	logicCreate(CDatabaseConnection &$_pDatabase) : bool
	{
		$this -> setCrumbData('create');
		$this -> setView(
						'create',
						'create/'
						);

		return true;
	}

	protected function
	logicXHRCreate(CDatabaseConnection &$_pDatabase) : bool
	{
		$validationErr =	false;
		$validationMsg =	'';
		$responseData = 	[];

		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "user_name_first",  	"validate" => "strip_tags|!empty" ]; 	
		$_request[] 	 = 	[	"input" => "user_name_last",   	"validate" => "strip_tags|!empty" ]; 	
		$_request[] 	 = 	[	"input" => "user_mail",    		"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
		$_request[] 	 = 	[	"input" => "login_name",    	"validate" => "strip_tags|!empty" ]; 	
		$_request[] 	 = 	[	"input" => "login_pass_a",    	"validate" => "strip_tags|!empty" ]; 	
		$_request[] 	 = 	[	"input" => "login_pass_b",    	"validate" => "strip_tags|!empty" ]; 
		$_request[] 	 = 	[	"input" => "language",    		"validate" => "strip_tags|strip_whitespaces|!empty" ]; 			
		$_request[] 	 = 	[	"input" => "allow_remote",   	"validate" => "strip_tags|strip_whitespaces|!empty" ]; 
		$_pURLVariables -> retrieve($_request, false, true); // POST 
		$_aFormData		 = $_pURLVariables ->getArray();

		if(empty($_aFormData['user_name_first'])) { 	$validationErr = true; 	$responseData[] = 'user_name_first'; 	}
		if(empty($_aFormData['user_name_last'])) { 		$validationErr = true; 	$responseData[] = 'user_name_last'; 		}
		if(empty($_aFormData['user_mail'])) { 			$validationErr = true; 	$responseData[] = 'user_mail'; 			}
		if(empty($_aFormData['login_name'])) { 			$validationErr = true; 	$responseData[] = 'login_name'; 			}
		if(empty($_aFormData['login_pass_a'])) { 		$validationErr = true; 	$responseData[] = 'login_pass_a'; 		}
		if(empty($_aFormData['login_pass_b'])) { 		$validationErr = true; 	$responseData[] = 'login_pass_b'; 		}
		if(empty($_aFormData['language'])) { 			$validationErr = true; 	$responseData[] = 'language'; 			}

		if(!$validationErr)	// Validation OK (by pre check)
		{		
			$_aFormData['is_locked'] 	= '0';
			$_aFormData['login_name'] 	= CRYPT::LOGIN_HASH($_aFormData['login_name']);

			$modelUsersRegister	 	= new modelUsersRegister();
			$_aFormData['user_id'] 	= $modelUsersRegister -> registerUserId($_pDatabase, 1);

			// Checking password	

			if(isset($_aFormData['login_pass_a']) && isset($_aFormData['login_pass_b']) && $_aFormData['login_pass_a'] === $_aFormData['login_pass_b'])
			{
				$_aFormData['login_pass'] = $_aFormData['login_pass_a'];
				$_aFormData['login_pass'] = CRYPT::LOGIN_CRYPT($_aFormData['login_pass'], CFG::GET() -> ENCRYPTION -> BASEKEY);
			} 
			elseif(isset($_aFormData['login_pass_a']) && isset($_aFormData['login_pass_b']) && $_aFormData['login_pass_a'] !== $_aFormData['login_pass_b'])
			{
				$validationMsg .= CLanguage::string('M_BEUSER_MSG_PASSNOTEQUAL');
				$validationErr = true;
			}
		}
		else	// Validation Failed
		{
			$validationMsg .= CLanguage::string('ERR_VALIDATIONFAIL');
		}

		if(!$validationErr)	// Validation OK
		{
			$_aFormData['create_by'] 	= CSession::instance() -> getValue('user_id');
			$_aFormData['create_time'] 	= time();


			if($this -> m_pModel -> insert($_pDatabase, $_aFormData))
			{
				$validationMsg = CLanguage::string('USER WAS_CREATED') .' - '. CLanguage::string('WAIT_FOR_REDIRECT');
				$responseData['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath .'user/'.$_aFormData['user_id'];
			}
			else
			{
				$validationMsg .= CLanguage::string('ERR_SQL_ERROR');

				$modelUsersRegister -> removeUserId($_pDatabase, $_aFormData['user_id']);
			}
		}

		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call

		return false;
	}

	protected function
	logicView(CDatabaseConnection &$_pDatabase, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{	

		$systemId = $this -> querySystemId();

		if($systemId !== false)
		{	
			$this -> m_modelRightGroups = new modelRightGroups();
			$this -> m_modelRightGroups -> load($_pDatabase);

			$modelCondition = new CModelCondition();
			$modelCondition -> where('user_id', $systemId);

			if($this -> m_pModel -> load($_pDatabase, $modelCondition, MODEL_USERS_STRIP_SENSITIVE_DATA | MODEL_USERS_APPEND_RIGHTGROUPS))
			{
				##	Gathering additional data

				$_crumbName	 = $this -> m_pModel -> getResultItem('user_id',$systemId,'user_name_first');
				$_crumbName	.= ' '. $this -> m_pModel -> getResultItem('user_id',$systemId,'user_name_last');
				$_crumbName	.= ' ('. $systemId .')';

				$this -> setCrumbData('edit', $_crumbName, true);
				$this -> setView(
								'edit',
								'user/'. $systemId,								
								[
									'usersList' 	=> $this -> m_pModel -> getResult(),
									'right_groups' 	=> $this -> m_modelRightGroups -> getResult(),
									'enableEdit'	=> $_enableEdit,
									'enableDelete'	=> $_enableDelete
								]								
								);
				return true;
			}
		}

		CMessages::add(CLanguage::string('MOD_BEUSER_ERR_USERID_UK') , MSG_WARNING);
		return false;
	}

	protected function
	logicXHREditRights(CDatabaseConnection &$_pDatabase) : bool
	{	

		$systemId = $this -> querySystemId();

			$validationErr =	false;
			$validationMsg =	'';
			$responseData = 	[];



									$_pFormVariables	 =	new CURLVariables();
									$_request		 =	[];
									$_request[] 	 = 	[	"input" => "is_locked",    	"validate" => "strip_tags|strip_whitespaces|!empty", 	"use_default" => true, "default_value" => 1  ]; 	
									$_request[] 	 = 	[	"input" => "groups",    	"validate" => "strip_tags|strip_whitespaces|!empty", 	"use_default" => true, "default_value" => [] ]; 		
									$_pFormVariables -> retrieve($_request, false, true); // POST 
									$_aFormData		 = $_pFormVariables ->getArray();

									$_aFormData['update_by'] 	= CSession::instance() -> getValue('user_id');
									$_aFormData['update_time'] 	= time();

									##	Updating rights table

									$modelCondition = new CModelCondition();
									$modelCondition -> where('user_id', $systemId);

									$modelUserGroups = new modelUserGroups();
									$modelUserGroups -> delete($_pDatabase, $modelCondition);

									foreach($_aFormData['groups'] as $_groupID)
									{
										$insertedId = 0;

										$insertData = [
														'user_id' 	=> $systemId,
														'group_id' 	=> $_groupID,
														'update_by' 	=> $_aFormData['update_by'],
														'update_time' 	=> $_aFormData['update_time']
													  ];

										$modelUserGroups -> insert($_pDatabase,$insertData, $insertedId);
									}

									##	Updating locked state

									unset($_aFormData['groups']);

									if($this -> m_pModel -> update($_pDatabase, $_aFormData, $modelCondition))
									{
										$validationMsg = CLanguage::string('USER WAS_UPDATED');
									}
									else
									{
										$validationMsg .= CLanguage::string('ERR_SQL_ERROR');
									}	


				tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call	


		return false;
	}

	protected function
	logicXHREditAuth(CDatabaseConnection &$_pDatabase) : bool
	{	

		$systemId = $this -> querySystemId();

			$validationErr =	false;
			$validationMsg =	'';
			$responseData = 	[];



									$_pFormVariables	 =	new CURLVariables();
									$_request		 =	[];
									$_request[] 	 = 	[	"input" => "login_name",    	"validate" => "strip_tags|!empty" ]; 	
									$_request[] 	 = 	[	"input" => "login_pass_a",    	"validate" => "strip_tags|!empty" ]; 	
									$_request[] 	 = 	[	"input" => "login_pass_b",    	"validate" => "strip_tags|!empty" ]; 	
									$_pFormVariables -> retrieve($_request, false, true); // POST 
									$_aFormData		 = $_pFormVariables ->getArray();


									if(empty($_aFormData['login_name'])) { 			$validationErr = true; 	$responseData[] = 'login_name'; 			}
									if(empty($_aFormData['login_pass_a'])) { 		$validationErr = true; 	$responseData[] = 'login_pass_a'; 		}
									if(empty($_aFormData['login_pass_b'])) { 		$validationErr = true; 	$responseData[] = 'login_pass_b'; 		}

									if(!$validationErr)	// Validation OK (by pre check)
									{		
										$_aFormData['login_name'] 	= CRYPT::LOGIN_HASH($_aFormData['login_name']);

										$_aFormData['update_by'] 	= CSession::instance() -> getValue('user_id');
										$_aFormData['update_time'] 	= time();



										$uniqueCondition = new CModelCondition();
										$uniqueCondition -> where('login_name', $_aFormData['login_name']);
										$uniqueCondition -> whereNot('user_id', $systemId);


										if(!$this -> m_pModel -> unique($_pDatabase, $uniqueCondition))
										{
											$validationMsg .= CLanguage::string('M_BEUSER_MSG_USERNAMEEXIST');
											$validationErr = true;
										}

										// Checking password	

										if(isset($_aFormData['login_pass_a']) && isset($_aFormData['login_pass_b']) && $_aFormData['login_pass_a'] === $_aFormData['login_pass_b'])
										{
											$_aFormData['login_pass'] = $_aFormData['login_pass_a'];
											$_aFormData['login_pass'] = CRYPT::LOGIN_CRYPT($_aFormData['login_pass'], CFG::GET() -> ENCRYPTION -> BASEKEY);
											unset($_aFormData['login_pass_a']);
											unset($_aFormData['login_pass_b']);
										} 
										elseif(isset($_aFormData['login_pass_a']) && isset($_aFormData['login_pass_b']) && $_aFormData['login_pass_a'] !== $_aFormData['login_pass_b'])
										{
											$validationMsg .= CLanguage::string('M_BEUSER_MSG_PASSNOTEQUAL');
											$validationErr = true;
										}
									}

									if(!$validationErr)
									{
										$_aFormData['update_by'] 	= CSession::instance() -> getValue('user_id');
										$_aFormData['update_time'] 	= time();

										$modelCondition = new CModelCondition();
										$modelCondition -> where('user_id', $systemId);

										if($this -> m_pModel -> update($_pDatabase, $_aFormData, $modelCondition))
										{
											$validationMsg = CLanguage::string('USER WAS_UPDATED');
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


		return false;
	}

	protected function
	logicXHREditUser(CDatabaseConnection &$_pDatabase) : bool
	{	

		$systemId = $this -> querySystemId();

			$validationErr =	false;
			$validationMsg =	'';
			$responseData = 	[];



									$_pFormVariables =	new CURLVariables();
									$_request		 =	[];
									$_request[] 	 = 	[	"input" => "user_name_first",  	"validate" => "strip_tags|!empty" ]; 	
									$_request[] 	 = 	[	"input" => "user_name_last",   	"validate" => "strip_tags|!empty" ]; 	
									$_request[] 	 = 	[	"input" => "user_mail",    		"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
									$_request[] 	 = 	[	"input" => "language",    		"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
									$_request[] 	 = 	[	"input" => "allow_remote",   	"validate" => "strip_tags|strip_whitespaces|!empty" ]; 
									$_pFormVariables-> retrieve($_request, false, true); // POST 
									$_aFormData		 = $_pFormVariables ->getArray();

									if(empty($_aFormData['user_name_first'])) { 	$validationErr = true; 	$responseData[] = 'user_name_first'; 	}
									if(empty($_aFormData['user_name_last'])) { 		$validationErr = true; 	$responseData[] = 'user_name_last'; 		}
									if(empty($_aFormData['user_mail'])) { 			$validationErr = true; 	$responseData[] = 'user_mail'; 			}
									if(empty($_aFormData['language'])) { 			$validationErr = true; 	$responseData[] = 'language'; 			}	

									if(!$validationErr)
									{
										$_aFormData['update_by'] 	= CSession::instance() -> getValue('user_id');
										$_aFormData['update_time'] 	= time();

										$modelCondition = new CModelCondition();
										$modelCondition -> where('user_id', $systemId);

										if($this -> m_pModel -> update($_pDatabase, $_aFormData, $modelCondition))
										{
											$validationMsg = CLanguage::string('USER WAS_UPDATED');
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


		return false;
	}

	protected function
	logicXHRDelete(CDatabaseConnection &$_pDatabase) : bool
	{	

		$systemId = $this -> querySystemId();

		if($systemId !== false)
		{	
	
				$validationErr =	false;
				$validationMsg =	'';
				$responseData = 	[];


										$modelCondition = new CModelCondition();
										$modelCondition -> where('user_id', $systemId);

										if($this -> m_pModel -> delete($_pDatabase, $modelCondition))
										{
											$validationMsg = CLanguage::string('USER WAS_DELETED') .' - '. CLanguage::string('WAIT_FOR_REDIRECT');
											$responseData['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath;

											$modelUsersRegister  = new modelUsersRegister();
											$modelUsersRegister -> removeUserId($_pDatabase, $systemId);

										$modelUserGroups = new modelUserGroups();
										$modelUserGroups -> delete($_pDatabase, $modelCondition);

										}
										else
										{
											$validationMsg .= CLanguage::string('ERR_SQL_ERROR');
										}


			#	if((!$_preventXHRRequestResultOnError && $validationErr) || (!$_forcePreventXHRRequestResult && !$validationErr))
					tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
			}		
	

		CMessages::add(CLanguage::string('MOD_BEUSER_ERR_USERID_UK') , MSG_WARNING);
		return false;
	}
	
	public function
	logicXHRPing(CDatabaseConnection &$_pDatabase)
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
	}
}
