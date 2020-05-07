<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelRightGroups.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUserGroups.php';	

class	controllerRightGroups extends CController
{
	private		$modelRightGroups;
	private		$modelUserGroups;

	public function
	__construct($_module, &$_object)
	{		
		$this -> modelRightGroups	= new modelRightGroups();
		$this -> modelUserGroups	= new modelUserGroups();

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

		$_logicResults = false;
		switch($_controllerAction)
		{
			case 'view'		: $_logicResults = $this -> logicView(	$_pDatabase, $_isXHRequest, $enableEdit, $enableDelete);	break;
			case 'edit'		: $_logicResults = $this -> logicEdit(	$_pDatabase, $_isXHRequest, $enableEdit, $enableDelete);	break;	
			case 'create'	: $_logicResults = $this -> logicCreate($_pDatabase, $_isXHRequest);	break;
			case 'delete'	: $_logicResults = $this -> logicDelete($_pDatabase, $_isXHRequest);	break;	
		}

		if(!$_logicResults)
		{
			##	Default View
			$this -> logicIndex($_pDatabase, $enableEdit, $enableDelete);		
		}
	}

	private function
	logicIndex(CDatabaseConnection &$_pDatabase, $_enableEdit = false, $_enableDelete = false)
	{
		#$modelCondition = new CModelCondition();
		#$modelCondition -> orderBy('data_id', 'DESC');

		$this -> modelRightGroups -> load($_pDatabase);	
		$this -> modelUserGroups -> load($_pDatabase);	

		$this -> setView(	
						'index',	
						'',
						[
							'right_groups' 		=> $this -> modelRightGroups -> getResult(),
							'user_groups' 		=> $this -> modelUserGroups -> getResult(),
							'enableEdit'	=> $_enableEdit,
							'enableDelete'	=> $_enableDelete
						]
						);
	}

	private function
	logicCreate(CDatabaseConnection &$_pDatabase, $_isXHRequest)
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
				$_bValidationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
			}

			if(!$_bValidationErr)	// Validation OK
			{

				$_aFormData['create_by'] 	= CSession::instance() -> getValue('user_id');
				$_aFormData['create_time'] 	= time();


				if(isset($_aFormData['group_rights'])) $_aFormData['group_rights'] = json_encode($_aFormData['group_rights']);
	

				$groupId = '0';
				if($this -> modelRightGroups -> insert($_pDatabase, $_aFormData, $groupId))
				{


					$_pPageRequest 	= CPageRequest::instance();


					$_bValidationMsg = CLanguage::get() -> string('MOD_RGROUPS_GROUP_RIGHTS') .' '. CLanguage::get() -> string('WAS_CREATED'). ' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
					$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . $_pPageRequest -> urlPath .'group/'.$groupId;
				}
				else
				{
					$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
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
	logicView(CDatabaseConnection &$_pDatabase, $_isXHRequest = false, $_enableEdit = false, $_enableDelete = false)
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

				$this -> modelUserGroups -> load($_pDatabase, $modelCondition);

				$_crumbName	 = $this -> modelRightGroups -> getResultItem('group_id',intval($systemId),'group_name');

				$this -> setCrumbData('edit', $_crumbName, true);
				$this -> setView(
								'edit',
								'group/'. $systemId,								
								[
									'right_groups' 	=> $this -> modelRightGroups -> getResult(),
									'user_groups' 	=> $this -> modelUserGroups -> getResult(),
									'enableEdit'	=> $_enableEdit,
									'enableDelete'	=> $_enableDelete
								]								
								);
				return true;
			}
		}

		CMessages::instance() -> addMessage(CLanguage::get() -> string('MOD_RGROUP_ERR_RGROUP_ID_UK') , MSG_WARNING);
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
				case 'group-data'  :	// Update user data

									$_pFormVariables =	new CURLVariables();
									$_request		 =	[];
									$_request[] 	 = 	[	"input" => "group_name",    		"validate" => "strip_tags|!empty" ]; 	
									$_pFormVariables-> retrieve($_request, false, true); // POST 
									$_aFormData		 = $_pFormVariables ->getArray();

									if(empty($_aFormData['group_name'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'group_name'; 	}

									if(!$_bValidationErr)
									{
										$_aFormData['update_by'] 	= CSession::instance() -> getValue('user_id');
										$_aFormData['update_time'] 	= time();

										$_aFormData['group_id'] 	= $systemId;

										$modelCondition = new CModelCondition();
										$modelCondition -> where('group_id', $systemId);

										if($this -> modelRightGroups -> update($_pDatabase, $_aFormData, $modelCondition))
										{
											$_bValidationMsg = CLanguage::get() -> string('MOD_RGROUPS_GROUP_RIGHTS') .' '. CLanguage::get() -> string('WAS_UPDATED');
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

										$modelCondition = new CModelCondition();
										$modelCondition -> where('group_id', $systemId);


										if(isset($_aFormData['group_rights'])) $_aFormData['group_rights'] = json_encode($_aFormData['group_rights']);

										if($this -> modelRightGroups -> update($_pDatabase, $_aFormData, $modelCondition))
										{
											$_bValidationMsg = CLanguage::get() -> string('MOD_RGROUPS_GROUP_RIGHTS') .' '. CLanguage::get() -> string('WAS_UPDATED');
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
					case 'group-delete': // delete user


										$modelCondition = new CModelCondition();
										$modelCondition -> where('group_id', $systemId);

										if($this -> modelRightGroups -> delete($_pDatabase, $modelCondition))
										{
											$_bValidationMsg = CLanguage::get() -> string('MOD_RGROUPS_GROUP_RIGHTS') .' '. CLanguage::get() -> string('WAS_DELETED'). ' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
											$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath;
										}
										else
										{
											$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
										}

										break;
				}

				tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
			}		
		
		}

		return false;
	}
}

?>