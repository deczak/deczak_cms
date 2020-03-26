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

		$_logicResults = false;
		switch($_controllerAction)
		{
			case 'view'		: $_logicResults = $this -> logicView(	$_sqlConnection, $_isXHRequest, $enableEdit, $enableDelete);	break;
			case 'edit'		: $_logicResults = $this -> logicEdit(	$_sqlConnection, $_isXHRequest);	break;	
			case 'create'	: $_logicResults = $this -> logicCreate($_sqlConnection, $_isXHRequest);	break;
			case 'delete'	: $_logicResults = $this -> logicDelete($_sqlConnection, $_isXHRequest);	break;	
		}

		if(!$_logicResults)
		{
			##	Default View
			$this -> logicIndex($_sqlConnection, $enableEdit, $enableDelete);		
		}
	}

	protected function
	logicIndex(&$_sqlConnection, $_enableEdit = false, $_enableDelete = false)
	{
		#$modelCondition = new CModelCondition();
		#$modelCondition -> orderBy('data_id', 'DESC');

		$this -> m_pModel -> load($_sqlConnection);	
		$this -> setView(	
						'index',	
						'',
						[
							'usersList' 		=> $this -> m_pModel -> getDataInstance(),
							'enableEdit'	=> $_enableEdit,
							'enableDelete'	=> $_enableDelete
						]
						);
	}

	protected function
	logicCreate(&$_sqlConnection, $_isXHRequest)
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
				$_aFormData['user_id'] 	= $modelUsersRegister -> registerUserId($_sqlConnection, 1);

			

				/*
				while(true)
				{
					$_aFormData['user_id']  = substr(rand(),0,10);
					if($this -> m_pModel -> isUnique($_sqlConnection, ['user_id' => $_aFormData['user_id']]))
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

				$dataId = 0;

				if($this -> m_pModel -> insert($_sqlConnection, $_aFormData, $dataId))
				{
					$_bValidationMsg = CLanguage::get() -> string('USER WAS_CREATED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
					$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath .'user/'.$_aFormData['user_id'];
				}
				else
				{
					$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');

					$modelUsersRegister -> removeUserId($_sqlConnection, $_aFormData['user_id']);
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
	logicView(&$_sqlConnection, $_isXHRequest = false, $_enableEdit = false, $_enableDelete = false)
	{	
		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "cms-system-id",  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
		$_pURLVariables -> retrieve($_request, true, false); // POST 

		if($_pURLVariables -> getValue("cms-system-id") !== false)
		{	
			$this -> m_modelRightGroups = new modelRightGroups();
			$this -> m_modelRightGroups -> load($_sqlConnection);

			$this -> m_pModelUserGroups	 = new modelUserGroups();

			$modelCondition = new CModelCondition();
			$modelCondition -> where('user_id', $_pURLVariables -> getValue("cms-system-id"));

			$this -> m_pModelUserGroups -> load($_sqlConnection, $modelCondition);

			if($this -> m_pModel -> load($_sqlConnection, $modelCondition))
			{
				##	Gathering additional data

				$_crumbName	 = $this -> m_pModel -> searchValue($_pURLVariables -> getValue("cms-system-id"),'user_id','user_name_first');
				$_crumbName	.= ' '. $this -> m_pModel -> searchValue($_pURLVariables -> getValue("cms-system-id"),'user_id','user_name_last');
				$_crumbName	.= ' ('. $_pURLVariables -> getValue("cms-system-id") .')';

				$this -> setCrumbData('edit', $_crumbName, true);
				$this -> setView(
								'edit',
								'user/'. $_pURLVariables -> getValue("cms-system-id"),								
								[
									'usersList' 	=> $this -> m_pModel -> getDataInstance(),
									'right_groups' 	=> $this -> m_modelRightGroups -> getDataInstance(),
									'user_groups' 	=> $this -> m_pModelUserGroups -> getDataInstance()
								]								
								);
				return true;
			}
		}

		CMessages::instance() -> addMessage(CLanguage::get() -> string('MOD_BEUSER_ERR_USERID_UK') , MSG_WARNING);
		return false;
	}

	protected function
	logicEdit(&$_sqlConnection, $_isXHRequest = false, bool $_preventXHRRequestResultOnError = false, bool $_forcePreventXHRRequestResult = false)
	{	
		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "cms-system-id",  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
		$_pURLVariables -> retrieve($_request, true, false); // POST 

		if($_pURLVariables -> getValue("cms-system-id") !== false && $_isXHRequest !== false)
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
										$modelCondition -> where('user_id', $_pURLVariables -> getValue("cms-system-id"));

										if($this -> m_pModel -> update($_sqlConnection, $_aFormData, $modelCondition))
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


										if(!$this -> m_pModel -> isUnique($_sqlConnection, ['login_name' => $_aFormData['login_name']], ['user_id' => $_pURLVariables -> getValue("cms-system-id")]))
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
										$modelCondition -> where('user_id', $_pURLVariables -> getValue("cms-system-id"));

										if($this -> m_pModel -> update($_sqlConnection, $_aFormData, $modelCondition))
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
									$modelCondition -> where('user_id', $_pURLVariables -> getValue("cms-system-id"));

									$modelUserGroups = new modelUserGroups();
									$modelUserGroups -> delete($_sqlConnection, $modelCondition);

									foreach($_aFormData['groups'] as $_groupID)
									{
										$insertedId = 0;

										$insertData = [
														'user_id' 	=> $_pURLVariables -> getValue('cms-system-id'),
														'group_id' 	=> $_groupID,
														'update_by' 	=> $_aFormData['update_by'],
														'update_time' 	=> $_aFormData['update_time']
													  ];

										$modelUserGroups -> insert($_sqlConnection,$insertData, $insertedId);
									}

									##	Updating locked state

									unset($_aFormData['groups']);

									if($this -> m_pModel -> update($_sqlConnection, $_aFormData, $modelCondition))
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
	logicDelete(&$_sqlConnection, $_isXHRequest = false, bool $_preventXHRRequestResultOnError = false, bool $_forcePreventXHRRequestResult = false)
	{	
		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "cms-system-id",  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
		$_pURLVariables -> retrieve($_request, true, false); // POST 

		if($_pURLVariables -> getValue("cms-system-id") !== false)
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
										$modelCondition -> where('user_id', $_pURLVariables -> getValue("cms-system-id"));

										if($this -> m_pModel -> delete($_sqlConnection, $modelCondition))
										{
											$_bValidationMsg = CLanguage::get() -> string('USER WAS_DELETED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
											$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath;

											$modelUsersRegister  = new modelUsersRegister();
											$modelUsersRegister -> removeUserId($_sqlConnection, $_pURLVariables -> getValue("cms-system-id"));

										$modelUserGroups = new modelUserGroups();
										$modelUserGroups -> delete($_sqlConnection, $modelCondition);

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
	

}

?>