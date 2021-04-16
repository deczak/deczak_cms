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

		$logicResults = false;
		switch($_controllerAction)
		{
			case 'view'		: $logicResults = $this -> logicView(	$_pDatabase, $_isXHRequest, $enableEdit, $enableDelete);	break;
			case 'ping'		: $logicResults = $this -> logicPing(	$_pDatabase, $_isXHRequest, $enableEdit, $enableDelete);	break;	
			case 'edit'		: $logicResults = $this -> logicEdit(	$_pDatabase, $_isXHRequest);	break;	
			case 'create'	: $logicResults = $this -> logicCreate( $_pDatabase, $_isXHRequest);	break;
			case 'delete'	: $logicResults = $this -> logicDelete( $_pDatabase, $_isXHRequest);	break;	
		}

		if(!$logicResults)
		{
			##	Default View
			$this -> logicIndex($_pDatabase, $_isXHRequest, $enableEdit, $enableDelete);		
		}
	}

	protected function
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
										$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
										$_bValidationErr = true;
									}											
						
									$data = $this -> m_pModel -> getResult();

									foreach($data as &$item)
									{
										$item -> creaty_by_name = tk::getBackendUserName($_pDatabase, $item -> create_by);
										$item -> update_by_name = tk::getBackendUserName($_pDatabase, $item -> update_by);
									}

									break;
			}

			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $data);	// contains exit call
		}

		##	No XHR request

		$this -> setView(	
						'index',	
						'',
						[
							'enableEdit'	=> $_enableEdit,
							'enableDelete'	=> $_enableDelete
						]
						);
	}

	protected function
	logicCreate(CDatabaseConnection &$_pDatabase, $_isXHRequest)
	{
		if($_isXHRequest !== false)
		{
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

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

			if(empty($_aFormData['user_name_first'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'user_name_first'; 	}
			if(empty($_aFormData['user_name_last'])) { 		$_bValidationErr = true; 	$_bValidationDta[] = 'user_name_last'; 		}
			if(empty($_aFormData['user_mail'])) { 			$_bValidationErr = true; 	$_bValidationDta[] = 'user_mail'; 			}
			if(empty($_aFormData['login_name'])) { 			$_bValidationErr = true; 	$_bValidationDta[] = 'login_name'; 			}
			if(empty($_aFormData['login_pass_a'])) { 		$_bValidationErr = true; 	$_bValidationDta[] = 'login_pass_a'; 		}
			if(empty($_aFormData['login_pass_b'])) { 		$_bValidationErr = true; 	$_bValidationDta[] = 'login_pass_b'; 		}
			if(empty($_aFormData['language'])) { 			$_bValidationErr = true; 	$_bValidationDta[] = 'language'; 			}

			if(!$_bValidationErr)	// Validation OK (by pre check)
			{		
				$_aFormData['is_locked'] 	= '0';
				$_aFormData['login_name'] 	= CRYPT::LOGIN_HASH($_aFormData['login_name']);


				$modelUsersRegister	 	= new modelUsersRegister();
				$_aFormData['user_id'] 	= $modelUsersRegister -> registerUserId($_pDatabase, 1);

			

				/*
				while(true)
				{
					$_aFormData['user_id']  = substr(rand(),0,10);
					if($this -> m_pModel -> isUnique($_pDatabase, ['user_id' => $_aFormData['user_id']]))
						break;
				}
				*/

				// Checking password	

				if(isset($_aFormData['login_pass_a']) && isset($_aFormData['login_pass_b']) && $_aFormData['login_pass_a'] === $_aFormData['login_pass_b'])
				{
					$_aFormData['login_pass'] = $_aFormData['login_pass_a'];
					$_aFormData['login_pass'] = CRYPT::LOGIN_CRYPT($_aFormData['login_pass'], CFG::GET() -> ENCRYPTION -> BASEKEY);
				} 
				elseif(isset($_aFormData['login_pass_a']) && isset($_aFormData['login_pass_b']) && $_aFormData['login_pass_a'] !== $_aFormData['login_pass_b'])
				{
					$_bValidationMsg .= CLanguage::get() -> string('M_BEUSER_MSG_PASSNOTEQUAL');
					$_bValidationErr = true;
				}
			}
			else	// Validation Failed
			{
				$_bValidationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
			}

			if(!$_bValidationErr)	// Validation OK
			{
				$_aFormData['create_by'] 	= CSession::instance() -> getValue('user_id');
				$_aFormData['create_time'] 	= time();


				if($this -> m_pModel -> insert($_pDatabase, $_aFormData))
				{
					$_bValidationMsg = CLanguage::get() -> string('USER WAS_CREATED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
					$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath .'user/'.$_aFormData['user_id'];
				}
				else
				{
					$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');

					$modelUsersRegister -> removeUserId($_pDatabase, $_aFormData['user_id']);
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

	protected function
	logicView(CDatabaseConnection &$_pDatabase, $_isXHRequest = false, $_enableEdit = false, $_enableDelete = false)
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

		CMessages::instance() -> addMessage(CLanguage::get() -> string('MOD_BEUSER_ERR_USERID_UK') , MSG_WARNING);
		return false;
	}

	protected function
	logicEdit(CDatabaseConnection &$_pDatabase, $_isXHRequest = false, bool $_preventXHRRequestResultOnError = false, bool $_forcePreventXHRRequestResult = false)
	{	

		$systemId = $this -> querySystemId();

		if($systemId !== false && $_isXHRequest !== false)
		{	
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			switch($_isXHRequest)
			{
				case 'user-data'  :	// Update user data

									$_pFormVariables =	new CURLVariables();
									$_request		 =	[];
									$_request[] 	 = 	[	"input" => "user_name_first",  	"validate" => "strip_tags|!empty" ]; 	
									$_request[] 	 = 	[	"input" => "user_name_last",   	"validate" => "strip_tags|!empty" ]; 	
									$_request[] 	 = 	[	"input" => "user_mail",    		"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
									$_request[] 	 = 	[	"input" => "language",    		"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
									$_request[] 	 = 	[	"input" => "allow_remote",   	"validate" => "strip_tags|strip_whitespaces|!empty" ]; 
									$_pFormVariables-> retrieve($_request, false, true); // POST 
									$_aFormData		 = $_pFormVariables ->getArray();

									if(empty($_aFormData['user_name_first'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'user_name_first'; 	}
									if(empty($_aFormData['user_name_last'])) { 		$_bValidationErr = true; 	$_bValidationDta[] = 'user_name_last'; 		}
									if(empty($_aFormData['user_mail'])) { 			$_bValidationErr = true; 	$_bValidationDta[] = 'user_mail'; 			}
									if(empty($_aFormData['language'])) { 			$_bValidationErr = true; 	$_bValidationDta[] = 'language'; 			}	

									if(!$_bValidationErr)
									{
										$_aFormData['update_by'] 	= CSession::instance() -> getValue('user_id');
										$_aFormData['update_time'] 	= time();

										$modelCondition = new CModelCondition();
										$modelCondition -> where('user_id', $systemId);

										if($this -> m_pModel -> update($_pDatabase, $_aFormData, $modelCondition))
										{
											$_bValidationMsg = CLanguage::get() -> string('USER WAS_UPDATED');
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

				case 'user-auth'  :	// Update user auth data

									$_pFormVariables	 =	new CURLVariables();
									$_request		 =	[];
									$_request[] 	 = 	[	"input" => "login_name",    	"validate" => "strip_tags|!empty" ]; 	
									$_request[] 	 = 	[	"input" => "login_pass_a",    	"validate" => "strip_tags|!empty" ]; 	
									$_request[] 	 = 	[	"input" => "login_pass_b",    	"validate" => "strip_tags|!empty" ]; 	
									$_pFormVariables -> retrieve($_request, false, true); // POST 
									$_aFormData		 = $_pFormVariables ->getArray();


									if(empty($_aFormData['login_name'])) { 			$_bValidationErr = true; 	$_bValidationDta[] = 'login_name'; 			}
									if(empty($_aFormData['login_pass_a'])) { 		$_bValidationErr = true; 	$_bValidationDta[] = 'login_pass_a'; 		}
									if(empty($_aFormData['login_pass_b'])) { 		$_bValidationErr = true; 	$_bValidationDta[] = 'login_pass_b'; 		}

									if(!$_bValidationErr)	// Validation OK (by pre check)
									{		
										$_aFormData['login_name'] 	= CRYPT::LOGIN_HASH($_aFormData['login_name']);

										$_aFormData['update_by'] 	= CSession::instance() -> getValue('user_id');
										$_aFormData['update_time'] 	= time();



										$uniqueCondition = new CModelCondition();
										$uniqueCondition -> where('login_name', $_aFormData['login_name']);
										$uniqueCondition -> whereNot('user_id', $systemId);


										if(!$this -> m_pModel -> unique($_pDatabase, $uniqueCondition))
										{
											$_bValidationMsg .= CLanguage::get() -> string('M_BEUSER_MSG_USERNAMEEXIST');
											$_bValidationErr = true;
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
											$_bValidationMsg .= CLanguage::get() -> string('M_BEUSER_MSG_PASSNOTEQUAL');
											$_bValidationErr = true;
										}
									}

									if(!$_bValidationErr)
									{
										$_aFormData['update_by'] 	= CSession::instance() -> getValue('user_id');
										$_aFormData['update_time'] 	= time();

										$modelCondition = new CModelCondition();
										$modelCondition -> where('user_id', $systemId);

										if($this -> m_pModel -> update($_pDatabase, $_aFormData, $modelCondition))
										{
											$_bValidationMsg = CLanguage::get() -> string('USER WAS_UPDATED');
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

				case 'user-rights': // Update user rights

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
										$_bValidationMsg = CLanguage::get() -> string('USER WAS_UPDATED');
									}
									else
									{
										$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
									}	

									break;

				default: 			//	Default if XHR Target has not been found
								
									$_bValidationErr =	true;
									$_bValidationMsg =	'Unknown XHR Target';

			}

			if((!$_preventXHRRequestResultOnError && $_bValidationErr) || (!$_forcePreventXHRRequestResult && !$_bValidationErr))
				tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call	
		}

		return false;
	}

	protected function
	logicDelete(CDatabaseConnection &$_pDatabase, $_isXHRequest = false, bool $_preventXHRRequestResultOnError = false, bool $_forcePreventXHRRequestResult = false)
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
					case 'user-delete': // delete user

										$modelCondition = new CModelCondition();
										$modelCondition -> where('user_id', $systemId);

										if($this -> m_pModel -> delete($_pDatabase, $modelCondition))
										{
											$_bValidationMsg = CLanguage::get() -> string('USER WAS_DELETED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
											$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath;

											$modelUsersRegister  = new modelUsersRegister();
											$modelUsersRegister -> removeUserId($_pDatabase, $systemId);

										$modelUserGroups = new modelUserGroups();
										$modelUserGroups -> delete($_pDatabase, $modelCondition);

										}
										else
										{
											$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
										}

										break;
				}

				if((!$_preventXHRRequestResultOnError && $_bValidationErr) || (!$_forcePreventXHRRequestResult && !$_bValidationErr))
					tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
			}		
		
		}

		CMessages::instance() -> addMessage(CLanguage::get() -> string('MOD_BEUSER_ERR_USERID_UK') , MSG_WARNING);
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