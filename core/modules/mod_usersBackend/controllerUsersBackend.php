<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelRightGroups.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUserGroups.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUsersBackend.php';	


class	controllerUsersBackend extends CController
{
	private		$m_modelRightGroups;

	public function
	__construct(array $_module, &$_object)
	{		
		$this -> m_pModel	= new modelUsersBackend();
		parent::__construct($_module, $_object);
	}
	
	public function
	logic(&$_sqlConnection, array $_rcaTarget, array $_userRights, $_isXHRequest)
	{
		##	Set default target if not exists

		$_controllerAction = $this -> getControllerAction($_rcaTarget, 'view');

		##	Check user rights for this target

		if(!$this -> hasRights($_userRights, $_controllerAction))
		{ 
			if($_isXHRequest !== false)
			{
				$_bValidationErr =	true;
				$_bValidationMsg =	CLanguage::instance() -> getString('ERR_PERMISSON');
				$_bValidationDta = 	[];

				tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
			}

			CMessages::instance() -> addMessage(CLanguage::instance() -> getString('ERR_PERMISSON') , MSG_WARNING);
			return;
		}

		##	Call sub-logic function by target, if there results are false, we make a fall back to default view

		$_logicResults = false;
		switch($_controllerAction)
		{
			case 'create': 	/* Create new user   */	$_logicResults = $this -> logicCreate($_sqlConnection, $_isXHRequest);			break;
			case 'edit': 	/* Edit user 		 */	$_logicResults = $this -> logicEdit($_sqlConnection, $_isXHRequest);			break;	
			case 'delete': 	/* Delete user 		 */	$_logicResults = $this -> logicDelete($_sqlConnection, $_isXHRequest);			break;	
		}

		if(!$_logicResults)
		{
			##	Default View
			$this -> logicOverview($_sqlConnection);	
		}
	}

	private function
	logicOverview(&$_sqlConnection)
	{
		$this -> m_pModel -> load($_sqlConnection);	
		$this -> setView(	
						'index',	
						'',
						[
							'users' 		=> $this -> m_pModel -> getDataInstance()
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
			$_request[] 	 = 	[	"input" => "user_name_first",  	"validate" => "strip_tags|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "user_name_last",   	"validate" => "strip_tags|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "user_mail",    		"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "login_name",    	"validate" => "strip_tags|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "login_pass_a",    	"validate" => "strip_tags|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "login_pass_b",    	"validate" => "strip_tags|!empty" ]; 	
			$_pURLVariables -> retrieve($_request, false, true); // POST 
			$_aFormData		 = $_pURLVariables ->getArray();

			if(empty($_aFormData['user_name_first'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'user_name_first'; 	}
			if(empty($_aFormData['user_name_last'])) { 		$_bValidationErr = true; 	$_bValidationDta[] = 'user_name_last'; 		}
			if(empty($_aFormData['user_mail'])) { 			$_bValidationErr = true; 	$_bValidationDta[] = 'user_mail'; 			}
			if(empty($_aFormData['login_name'])) { 			$_bValidationErr = true; 	$_bValidationDta[] = 'login_name'; 			}
			if(empty($_aFormData['login_pass_a'])) { 		$_bValidationErr = true; 	$_bValidationDta[] = 'login_pass_a'; 		}
			if(empty($_aFormData['login_pass_b'])) { 		$_bValidationErr = true; 	$_bValidationDta[] = 'login_pass_b'; 		}

			if(!$_bValidationErr)	// Validation OK (by pre check)
			{		
				$_aFormData['user_id'] 		= substr(rand(),0,10);
				$_aFormData['is_locked'] 	= '0';
				$_aFormData['login_name'] 	= CRYPT::LOGIN_HASH($_aFormData['login_name']);
				#$_aFormData['login_name'] 	= CRYPT::ENCRYPT($_aFormData['login_name'], CRYPT::CHECKSUM($_aFormData['login_name']), true);

				// Checking password	

				if(isset($_aFormData['login_pass_a']) && isset($_aFormData['login_pass_b']) && $_aFormData['login_pass_a'] === $_aFormData['login_pass_b'])
				{
					$_aFormData['login_pass'] = $_aFormData['login_pass_a'];
					$_aFormData['login_pass'] = CRYPT::LOGIN_CRYPT($_aFormData['login_pass'], ENCRYPTION_BASEKEY);
					#$_aFormData['login_pass'] = CRYPT::HASH512($_aFormData['login_pass'], CRYPT::CHECKSUM(CRYPT::HASH256($_aFormData['login_pass'])), ENCRYPTION_BASEKEY ) . CRYPT::CHECKSUM(CRYPT::HASH256($_aFormData['login_pass']));
		
				} 
				elseif(isset($_aFormData['login_pass_a']) && isset($_aFormData['login_pass_b']) && $_aFormData['login_pass_a'] !== $_aFormData['login_pass_b'])
				{
					$_bValidationMsg .= '<br>The login password fields are not equal';
					$_bValidationErr = true;
				}
			}
			else	// Validation Failed
			{
				$_bValidationMsg .= 'Data validation failed - user account was not created';
			}

			if(!$_bValidationErr)	// Validation OK
			{
				if($this -> m_pModel -> insert($_sqlConnection, $_aFormData))
				{
					$_bValidationMsg = 'User account was created - please wait for redirect';
					$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . REQUESTED_PAGE_PATH .'user/'.$_aFormData['user_id'];
				}
				else
				{
					$_bValidationMsg .= 'Unknown error on sql query';
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
	logicEdit(&$_sqlConnection, $_isXHRequest = false)
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
					case 'user-data'  :	// Update user data

										$_pFormVariables =	new CURLVariables();
										$_request		 =	[];
										$_request[] 	 = 	[	"input" => "user_name_first",  	"validate" => "strip_tags|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "user_name_last",   	"validate" => "strip_tags|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "user_mail",    		"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
										$_pFormVariables-> retrieve($_request, false, true); // POST 
										$_aFormData		 = $_pFormVariables ->getArray();

										if(empty($_aFormData['user_name_first'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'user_name_first'; 	}
										if(empty($_aFormData['user_name_last'])) { 		$_bValidationErr = true; 	$_bValidationDta[] = 'user_name_last'; 		}
										if(empty($_aFormData['user_mail'])) { 			$_bValidationErr = true; 	$_bValidationDta[] = 'user_mail'; 			}

										if(!$_bValidationErr)
										{
											$_aFormData['user_id'] = $_pURLVariables -> getValue("cms-system-id");

											if($this -> m_pModel -> update($_sqlConnection, $_aFormData))
											{
												$_bValidationMsg = 'User account was updated';
											}
											else
											{
												$_bValidationMsg .= 'Unknown error on sql query';
												$_bValidationErr = true;
											}											
										}
										else	// Validation Failed
										{
											$_bValidationMsg .= 'Data validation failed - user account was not updated';
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

											if($this -> m_pModel -> isDatasetExists($_sqlConnection, '', ['login_name' => $_aFormData['login_name']]))
											{
												$_bValidationMsg .= '<br>Username exists already';
												$_bValidationErr = true;
											}

											// Checking password	

											if(isset($_aFormData['login_pass_a']) && isset($_aFormData['login_pass_b']) && $_aFormData['login_pass_a'] === $_aFormData['login_pass_b'])
											{
												$_aFormData['login_pass'] = $_aFormData['login_pass_a'];
												$_aFormData['login_pass'] = CRYPT::LOGIN_CRYPT($_aFormData['login_pass'], ENCRYPTION_BASEKEY);
												unset($_aFormData['login_pass_a']);
												unset($_aFormData['login_pass_b']);
											} 
											elseif(isset($_aFormData['login_pass_a']) && isset($_aFormData['login_pass_b']) && $_aFormData['login_pass_a'] !== $_aFormData['login_pass_b'])
											{
												$_bValidationMsg .= '<br>The login password fields are not equal';
												$_bValidationErr = true;
											}
										}

										if(!$_bValidationErr)
										{
											$_aFormData['user_id'] = $_pURLVariables -> getValue("cms-system-id");

											if($this -> m_pModel -> update($_sqlConnection, $_aFormData))
											{
												$_bValidationMsg = 'User account was updated';
											}
											else
											{
												$_bValidationMsg .= 'Unknown error on sql query';
												$_bValidationErr = true;
											}											
										}
										else	// Validation Failed
										{
											$_bValidationMsg .= 'Data validation failed - user account was not updated';
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

										##	Updating rights table

										$this -> m_modelRightGroups = new modelRightGroups();
										$this -> m_modelRightGroups -> delete($_sqlConnection, ['user_id' => $_pURLVariables -> getValue("cms-system-id")]);

										$_sqlConnection -> query("DELETE FROM tb_users_groups WHERE tb_users_groups.user_id = '". $_pURLVariables -> getValue("cms-system-id") ."'");

										foreach($_aFormData['groups'] as $_groupID)
										{
											#$this -> xxx -> insert($_sqlConnection, ['group_id' => $_groupID, 'user_id' => $_pURLVariables -> getValue("cms-system-id")]);
											echo "INSERT INTO tb_users_groups SET tb_users_groups.user_id = '". $_sqlConnection -> real_escape_string($_pURLVariables -> getValue('cms-system-id')) ."', tb_users_groups.group_id = '". $_sqlConnection -> real_escape_string($_groupID) ."'";
											$_sqlConnection -> query("INSERT INTO tb_users_groups SET tb_users_groups.user_id = '". $_sqlConnection -> real_escape_string($_pURLVariables -> getValue('cms-system-id')) ."', tb_users_groups.group_id = '". $_sqlConnection -> real_escape_string($_groupID) ."'");

										}

										##	Updating locked state

										unset($_aFormData['groups']);

										$_aFormData['user_id'] = $_pURLVariables -> getValue("cms-system-id");

										if($this -> m_pModel -> update($_sqlConnection, $_aFormData))
										{
											$_bValidationMsg = 'User account was updated';
										}
										else
										{
											$_bValidationMsg .= 'Unknown error on sql query';
										}	


										break;


				}

				tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
			}
		
			##	Non XHR call



				$this -> m_modelRightGroups = new modelRightGroups();
				$this -> m_modelRightGroups -> load($_sqlConnection);

				$this -> m_pModelUserGroups	 = new modelUserGroups();
				$this -> m_pModelUserGroups -> setAdditionalProperties(['group_name', 'group_rights']);
				$this -> m_pModelUserGroups -> setReleation($this -> m_modelRightGroups, '', ['group_id']);
				$this -> m_pModelUserGroups -> load($_sqlConnection, [ 'user_id' => $_pURLVariables -> getValue("cms-system-id") ]	);

	

			if($this -> m_pModel -> load($_sqlConnection, [ 'user_id' => $_pURLVariables -> getValue("cms-system-id") ]))
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
									'users' 		=> $this -> m_pModel -> getDataInstance(),
									'right_groups' 	=> $this -> m_modelRightGroups -> getDataInstance(),
									'user_groups' 	=> $this -> m_pModelUserGroups -> getDataInstance()
								]								
								);
				return true;
			}
		}

		CMessages::instance() -> addMessage(CLanguage::instance() -> getString('MOD_BEUSER_ERR_USERID_UK') , MSG_WARNING);
		return false;
	}

	private function
	logicDelete(&$_sqlConnection, $_isXHRequest = false)
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

										if($this -> m_pModel -> delete($_sqlConnection, ['user_id' => $_pURLVariables -> getValue("cms-system-id")]))
										{
											$_bValidationMsg = 'User account was deleted - please wait for redirect';
											$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . REQUESTED_PAGE_PATH;

											$_sqlConnection -> query("DELETE FROM tb_users_groups WHERE tb_users_groups.user_id = '". $_pURLVariables -> getValue("cms-system-id") ."'");
										}
										else
										{
											$_bValidationMsg .= 'Unknown error on sql query';
										}

										break;
				}

				tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
			}		
		
		}

		CMessages::instance() -> addMessage(CLanguage::instance() -> getString('MOD_BEUSER_ERR_USERID_UK') , MSG_WARNING);
		return false;
	}
	

	private function
	setView(string $_view, string $_moduleTarget,  array $_dataInstances = [])
	{
		$this -> m_pView = new CView( CMS_SERVER_ROOT . DIR_CORE . DIR_MODULES . $this -> m_aModule['module_location'].'/view/'. $_view, $_moduleTarget , $_dataInstances );	
	}

	private function
	setCrumbData(string $_ctrlTarget, string $_customMenuName = '', bool $_noLink = false)
	{
		$_sectionIndex = array_search($_ctrlTarget, array_column($this -> m_aModule['sub'], 'ctl_target'));
		if($_sectionIndex !== false)
		{		
			if(!empty($_customMenuName))
				$this -> m_aCrumb['page_name'] 	= $_customMenuName;
			else
				$this -> m_aCrumb['page_name'] 	= CLanguage::instance() -> getString($this -> m_aModule['sub'][$_sectionIndex]['menu_name']);

			if(!$_noLink)
				$this -> m_aCrumb['page_path'] 	= $this -> m_aModule['sub'][$_sectionIndex]['url_name'] .'/';
			else
				$this -> m_aCrumb['no_link'] 	= true;
		}
	}


}

?>