<?php

require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelRightGroups.php';	
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUserGroups.php';	
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUsers.php';	
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUsersBackend.php';	

require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUsersRegister.php';	

class	controllerRemoteUsers extends CController
{

	public function
	__construct($_module, &$_object)
	{		
		#$this -> m_pModel	= new modelCategories();
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
		#	case 'create'	: $_logicResults = $this -> logicCreate($_sqlConnection, $_isXHRequest);	break;
		#	case 'delete'	: $_logicResults = $this -> logicDelete($_sqlConnection, $_isXHRequest);	break;	
		}

		if(!$_logicResults)
		{
			##	Default View
			$this -> logicIndex($_sqlConnection, $enableEdit, $enableDelete);	
		}
	}

	private function
	logicIndex(&$_sqlConnection, $_enableEdit = false, $_enableDelete = false)
	{
		$usersList = $this -> _getUsersList();

		$registerCondition	 = new CModelCondition();
		$registerCondition 	-> groupBy('user_id');
		$modelUsersRegister	 = new modelUsersRegister();
		$modelUsersRegister -> load($_sqlConnection, $registerCondition);

		$usergroupCondition	 = new CModelCondition();
		$usergroupCondition	-> groupBy('user_id');
		$modelUserGroups	 = new modelUserGroups();
		$modelUserGroups 	-> addSelectColumns('*', 'COUNT(DISTINCT(group_id)) AS allocation');
		$modelUserGroups	-> load($_sqlConnection, $usergroupCondition);

		##	set view

		$this -> setView(	
						'index',	
						'',
						[
							'usersList' 	=> $usersList,
							'registersList' => $modelUsersRegister -> getDataInstance(),
							'groupsList' 	=> $modelUserGroups -> getDataInstance(),
							'enableEdit'	=> $_enableEdit,
							'enableDelete'	=> $_enableDelete
						]
						);
	}

	private function
	_getUsersList($_userId = '')
	{
		$usersList = [];
		
		foreach(CFG::GET() -> MYSQL -> DATABASE as $database)
		{

			if($database['name'] === CFG::GET() -> MYSQL -> PRIMARY_DATABASE)
				continue;

			$remoteSQL = CSQLConnect::GET() -> getConnection($database['name']);

			##	condition

			$remoteCondition = new CModelCondition();
			$remoteCondition -> where('allow_remote', '1');
			$remoteCondition -> where('is_locked', '0');

			##	get users

			$modelUsers	  = new modelUsers();

			$modelUsers  -> load($remoteSQL, $remoteCondition);	
			$modelUsers  -> getDataInstance();

			foreach($modelUsers -> getDataInstance() as $user)
			{
				$id = hash('sha256', $database['name'] . $database['server'] . $user -> user_id . $user -> login_name);

				if(!empty($_userId) && $_userId !== $id)
					continue;

				$usersList[] = [
								"db_name"	=> $database['name'],
								"db_server"	=> $database['server'],
								"id" 		=> $id,
								"user"		=> $user
							   ];
			}

			## get backend users

			$modelUsersBackend	= new modelUsersBackend();
			$modelUsersBackend  -> load($remoteSQL, $remoteCondition);	
			$modelUsersBackend -> getDataInstance();

			foreach($modelUsersBackend -> getDataInstance() as $user)
			{
				$id = hash('sha256', $database['name'] . $database['server'] . $user -> user_id . $user -> login_name);

				if(!empty($_userId) && $_userId !== $id)
					continue;

				$usersList[] = [
								"db_name"	=> $database['name'],
								"db_server"	=> $database['server'],
								"id" 		=> $id,
								"user"		=> $user
							   ];
			}
		}

		return $usersList;
	}

	private function
	logicCreate(&$_sqlConnection, $_isXHRequest)
	{
	}

	private function
	logicView(&$_sqlConnection, $_isXHRequest = false, $_enableEdit = false, $_enableDelete = false)
	{	
		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "cms-system-id",  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
		$_pURLVariables -> retrieve($_request, true, false); // POST 

		if($_pURLVariables -> getValue("cms-system-id") !== false )
		{	
			$usersList = $this -> _getUsersList($_pURLVariables -> getValue("cms-system-id"));

			if(!empty($usersList))
			{
				$this -> m_modelRightGroups = new modelRightGroups();
				$this -> m_modelRightGroups -> load($_sqlConnection);

				$modelUserGroups	 = new modelUserGroups();

				$modelCondition = new CModelCondition();
				$modelCondition -> where('user_hash', $_pURLVariables -> getValue("cms-system-id"));

				$modelUserGroups -> load($_sqlConnection, $modelCondition);

				##

				$user = current($usersList)['user'];
				
				##	Gathering additional data

				$_crumbName	 = $user -> user_name_first .' '. $user -> user_name_last;

				$this -> setCrumbData('edit', $_crumbName, true);
				$this -> setView(
								'edit',
								'user/'. $_pURLVariables -> getValue("cms-system-id"),								
								[
									'usersList' 	=> $usersList,
									'right_groups' 	=> $this -> m_modelRightGroups -> getDataInstance(),
									'user_groups' 	=> $modelUserGroups -> getDataInstance(),
									'enableEdit'	=> $_enableEdit,
									'enableDelete'	=> $_enableDelete
								]								
								);
				return true;
			}
		}
		
		CMessages::instance() -> addMessage(CLanguage::get() -> string('MOD_BECATEGORIES_ERR_USERID_UK'), MSG_WARNING);
		return false;
	}

	private function
	logicEdit(&$_sqlConnection, $_isXHRequest = false)
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
				case 'edit-user'  :	

										$_pFormVariables =	new CURLVariables();
										$_request		 =	[];
										$_request[] 	 = 	[	"input" => "groups",    	"validate" => "strip_tags|strip_whitespaces|!empty", 	"use_default" => true, "default_value" => [] ]; 		
	
										$_pURLVariables -> retrieve($_request, false, true); // POST 
										$_aFormData		 = $_pURLVariables ->getArray();

										if(!$_bValidationErr)
										{
											$usersList = $this -> _getUsersList($_pURLVariables -> getValue("cms-system-id"));

											if(!empty($usersList))
											{
												$user = current($usersList)['user'];

												$_aFormData['update_by'] 	= CSession::instance() -> getValue('user_id');
												$_aFormData['update_time'] 	= time();

												$modelUsersRegister	 	= new modelUsersRegister();

												$registerCondition = new CModelCondition();
												$registerCondition -> where('user_hash', $_pURLVariables -> getValue("cms-system-id"));

												$modelUsersRegister -> load($_sqlConnection, $registerCondition);

												if(empty($modelUsersRegister -> getDataInstance()))
													$_aFormData['user_id'] 	= $modelUsersRegister -> registerUserId($_sqlConnection, 3, $_pURLVariables -> getValue("cms-system-id"), $user -> user_name_first .' '. $user -> user_name_last);
												else
													$_aFormData['user_id'] 	= $modelUsersRegister -> getDataInstance()[0] -> user_id;

												##	Updating rights table

												$modelCondition = new CModelCondition();
												$modelCondition -> where('user_id', $_aFormData['user_id']);
												$modelCondition -> where('user_hash', $_pURLVariables -> getValue("cms-system-id"));

												$modelUserGroups = new modelUserGroups();
												$modelUserGroups -> delete($_sqlConnection, $modelCondition);

												foreach($_aFormData['groups'] as $_groupID)
												{
													$insertedId = 0;

													$insertData = [
																	'user_id' 	=> $_aFormData['user_id'],
																	'group_id' 	=> $_groupID,
																	'user_hash' 	=> $_pURLVariables -> getValue("cms-system-id"),
																	'update_by' 	=> $_aFormData['update_by'],
																	'update_time' 	=> $_aFormData['update_time']
																	];

													$modelUserGroups -> insert($_sqlConnection,$insertData, $insertedId);
												}

										
												$_bValidationMsg = CLanguage::get() -> string('USER') .' '. CLanguage::get() -> string('WAS_UPDATED');
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
	logicDelete(&$_sqlConnection, $_isXHRequest = false)
	{	
	}
}

?>