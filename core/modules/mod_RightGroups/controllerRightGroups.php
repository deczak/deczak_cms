<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelRightGroups.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUserGroups.php';	


class	controllerRightGroups extends CController
{
	private		$m_modelRightGroups;
	private		$m_modelUserGroups;

	public function
	__construct(array $_module, &$_object)
	{		
		$this -> m_pModelRGroups	= new modelRightGroups();
		$this -> m_pModelUGroups	= new modelUserGroups();

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
			$this -> logicView($_sqlConnection);	
		}
	}

	private function
	logicView(&$_sqlConnection)
	{
		$this -> m_pModelRGroups -> load($_sqlConnection);	
		$this -> m_pModelUGroups -> load($_sqlConnection);	

		$this -> setView(	
						'index',	
						'',
						[
							'right_groups' 		=> $this -> m_pModelRGroups -> getDataInstance(),
							'user_groups' 		=> $this -> m_pModelUGroups -> getDataInstance()
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
			$_request[] 	 = 	[	"input" => "group_name",  	"validate" => "strip_tags|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "group_rights",   	"validate" => "strip_tags|!empty" ]; 	
			$_pURLVariables -> retrieve($_request, false, true);
			$_aFormData		 = $_pURLVariables ->getArray();

			if(empty($_aFormData['group_name'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'group_name'; 	}

			if(!$_bValidationErr)	// Validation OK (by pre check)
			{	
			}
			else	// Validation Failed
			{
				$_bValidationMsg .= CLanguage::instance() -> getString('ERR_VALIDATIONFAIL') .' - '. CLanguage::instance() -> getString('MOD_RGROUP_ERR_NOTCREATED');
			}

			if(!$_bValidationErr)	// Validation OK
			{
				$groupId = '0';
				if($this -> m_pModelRGroups -> insert($_sqlConnection, $_aFormData, $groupId))
				{



					$_bValidationMsg = CLanguage::instance() -> getString('MOD_RGROUP_OK_CREATED'). ' - '. CLanguage::instance() -> getString('WAIT_FOR_REDIRECT');
					$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . REQUESTED_PAGE_PATH .'group/'.$groupId;
				}
				else
				{
					$_bValidationMsg .= CLanguage::instance() -> getString('ERR_SQL_ERROR');
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
		$_pURLVariables -> retrieve($_request, true, false); 

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
					case 'group-data'  :	// Update user data

										$_pFormVariables =	new CURLVariables();
										$_request		 =	[];
										$_request[] 	 = 	[	"input" => "group_name",    		"validate" => "strip_tags|!empty" ]; 	
										$_pFormVariables-> retrieve($_request, false, true); // POST 
										$_aFormData		 = $_pFormVariables ->getArray();

										if(empty($_aFormData['group_name'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'group_name'; 	}

										if(!$_bValidationErr)
										{
											$_aFormData['group_id'] = $_pURLVariables -> getValue("cms-system-id");

											if($this -> m_pModelRGroups -> update($_sqlConnection, $_aFormData))
											{
												$_bValidationMsg = CLanguage::instance() -> getString('MOD_RGROUP_OK_UPDATED');
											}
											else
											{
												$_bValidationMsg .= CLanguage::instance() -> getString('ERR_SQL_ERROR');
												$_bValidationErr = true;
											}											
										}
										else	// Validation Failed
										{
											$_bValidationMsg .= CLanguage::instance() -> getString('ERR_VALIDATIONFAIL') .' - '. CLanguage::instance() -> getString('MOD_RGROUP_ERR_NOTUPDATED');
											$_bValidationErr = true;
										}

										break;

					case 'group-rights'  :	// Update user auth data

										$_pFormVariables	 =	new CURLVariables();
										$_request		 =	[];
										$_request[] 	 = 	[	"input" => "group_rights",    	"validate" => "strip_tags|!empty" ]; 	
										$_pFormVariables -> retrieve($_request, false, true); // POST 
										$_aFormData		 = $_pFormVariables ->getArray();


									#	if(empty($_aFormData['group_rights'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'group_rights'; 			}

										if(!$_bValidationErr)	// Validation OK (by pre check)
										{		

										}

										if(!$_bValidationErr)
										{
											$_aFormData['group_id'] = $_pURLVariables -> getValue("cms-system-id");

											if($this -> m_pModelRGroups -> update($_sqlConnection, $_aFormData))
											{
												$_bValidationMsg = CLanguage::instance() -> getString('MOD_RGROUP_OK_UPDATED');
											}
											else
											{
												$_bValidationMsg .= CLanguage::instance() -> getString('ERR_SQL_ERROR');
												$_bValidationErr = true;
											}											
										}
										else	// Validation Failed
										{
											$_bValidationMsg .= CLanguage::instance() -> getString('ERR_VALIDATIONFAIL') .' - '. CLanguage::instance() -> getString('MOD_RGROUP_ERR_NOTUPDATED');
											$_bValidationErr = true;
										}

										break;

				}

				tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
			}
		
			##	Non XHR call



			if($this -> m_pModelRGroups -> load($_sqlConnection, [ 'group_id' => $_pURLVariables -> getValue("cms-system-id") ]))
			{
				##	Gathering additional data


			$this -> m_pModelUGroups -> load($_sqlConnection, [ 'group_id' => $_pURLVariables -> getValue("cms-system-id") ]);

				$_crumbName	 = $this -> m_pModelRGroups -> searchValue(intval($_pURLVariables -> getValue("cms-system-id")),'group_id','group_name');

				$this -> setCrumbData('edit', $_crumbName, true);
				$this -> setView(
								'edit',
								'group/'. $_pURLVariables -> getValue("cms-system-id"),								
								[
									'right_groups' 	=> $this -> m_pModelRGroups -> getDataInstance(),
									'user_groups' 	=> $this -> m_pModelUGroups -> getDataInstance()
								]								
								);
				return true;
			}
		}

		CMessages::instance() -> addMessage(CLanguage::instance() -> getString('MOD_RGROUP_ERR_RGROUP_ID_UK') , MSG_WARNING);
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
					case 'group-delete': // delete user

										if($this -> m_pModelRGroups -> delete($_sqlConnection, ['group_id' => $_pURLVariables -> getValue("cms-system-id")]))
										{
											$_bValidationMsg = CLanguage::instance() -> getString('MOD_RGROUP_OK_DELETED'). ' - '. CLanguage::instance() -> getString('WAIT_FOR_REDIRECT');
											$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . REQUESTED_PAGE_PATH;

										}
										else
										{
											$_bValidationMsg .= CLanguage::instance() -> getString('ERR_SQL_ERROR');
										}

										break;
				}

				tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
			}		
		
		}

		CMessages::instance() -> addMessage(CLanguage::instance() -> getString('MOD_RGROUP_ERR_RGROUP_ID_UK') , MSG_WARNING);
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