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
			case 'edit'		: $logicResults = $this -> logicEdit(	$_pDatabase, $_isXHRequest);	break;	
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

									$_pURLVariables	 =	new CURLVariables();
									$_request		 =	[];
									$_request[] 	 = 	[	"input" => 'q',  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
									$_pURLVariables -> retrieve($_request, false, true);	
					
									$registerCondition	 = new CModelCondition();
									$registerCondition 	-> groupBy('user_id');
									$modelUsersRegister	 = new modelUsersRegister();
									$modelUsersRegister -> load($_pDatabase, $registerCondition);

									$usergroupCondition	 = new CModelCondition();
									$usergroupCondition	-> groupBy('user_id');
									$modelUserGroups	 = new modelUserGroups();
									$modelUserGroups 	-> addSelectColumns('*', 'COUNT(DISTINCT(group_id)) AS allocation');
									$modelUserGroups	-> load($_pDatabase, $usergroupCondition);

									$data		= $this -> _getUsersList();
									
									foreach($data as $dataKey => $dataSet)
									{
										$data[$dataKey]['allocations'] 	= $this ->getAllocations($modelUserGroups -> getResult(), $dataSet['id']);
										$data[$dataKey]['update_time'] 	= $this ->getUpdateTime($modelUserGroups -> getResult(), $dataSet['id']);
										$data[$dataKey]['update_by'] 	= $this ->getUpdateBy($modelUserGroups -> getResult(), $dataSet['id']);
										$data[$dataKey]['update_by'] 	= tk::getBackendUserName($_pDatabase, $data[$dataKey]['update_by']);
										
										$data[$dataKey]['user_name_first'] 	= $dataSet['user'] -> user_name_first;
										$data[$dataKey]['user_name_last'] 	= $dataSet['user'] -> user_name_last;
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

	private function
	_getUsersList($_userId = '')
	{
		$usersList = [];
		
		foreach(CFG::GET() -> MYSQL -> DATABASE as $database)
		{

			if($database['name'] === CFG::GET() -> MYSQL -> PRIMARY_DATABASE)
				continue;

			$remoteDB = CDatabase::instance() -> getConnection($database['name']);

			##	condition

			$remoteCondition = new CModelCondition();
			$remoteCondition -> where('allow_remote', '1');
			$remoteCondition -> where('is_locked', '0');

			##	get users

			$modelUsers	  = new modelUsers();

			$modelUsers  -> load($remoteDB, $remoteCondition);	
			$modelUsers  -> getResult();

			foreach($modelUsers -> getResult() as $user)
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
			$modelUsersBackend  -> load($remoteDB, $remoteCondition);	
			$modelUsersBackend -> getResult();

			foreach($modelUsersBackend -> getResult() as $user)
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
	getAllocations($_groupsList, $_userHash)
	{
		foreach($_groupsList as $group)
		{
			if($group -> user_hash === $_userHash)
				return $group -> allocation;
		}
		
		return 0;
	}

	private function
	getUpdateBy($_groupsList, $_userHash)
	{
		foreach($_groupsList as $group)
		{
			if($group -> user_hash === $_userHash)
				return $group -> update_by;
		}
		
		return 0;
	}

	private function
	getUpdateTime($_groupsList, $_userHash)
	{
		foreach($_groupsList as $group)
		{
			if($group -> user_hash === $_userHash)
				return $group -> update_time;
		}
		
		return 0;
	}


	private function
	logicView(CDatabaseConnection &$_pDatabase, $_isXHRequest = false, $_enableEdit = false, $_enableDelete = false)
	{
		$systemId = $this -> querySystemId();

		if($systemId !== false )
		{	
			$usersList = $this -> _getUsersList($systemId);

			if(!empty($usersList))
			{
				$this -> m_modelRightGroups = new modelRightGroups();
				$this -> m_modelRightGroups -> load($_pDatabase);

				$modelUserGroups	 = new modelUserGroups();

				$modelCondition = new CModelCondition();
				$modelCondition -> where('user_hash', $systemId);

				$modelUserGroups -> load($_pDatabase, $modelCondition);

				##

				$user = current($usersList)['user'];
				
				##	Gathering additional data

				$_crumbName	 = $user -> user_name_first .' '. $user -> user_name_last;

				$this -> setCrumbData('edit', $_crumbName, true);
				$this -> setView(
								'edit',
								'user/'. $systemId,								
								[
									'usersList' 	=> $usersList,
									'right_groups' 	=> $this -> m_modelRightGroups -> getResult(),
									'user_groups' 	=> $modelUserGroups -> getResult(),
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
				case 'edit-user'  :	

										$_pFormVariables =	new CURLVariables();
										$_request		 =	[];
										$_request[] 	 = 	[	"input" => "groups",    	"validate" => "strip_tags|strip_whitespaces|!empty", 	"use_default" => true, "default_value" => [] ]; 		
	
										$_pFormVariables -> retrieve($_request, false, true); // POST 
										$_aFormData		 = $_pFormVariables ->getArray();

										if(!$_bValidationErr)
										{
											$usersList = $this -> _getUsersList($systemId);

											if(!empty($usersList))
											{
												$user = current($usersList)['user'];

												$_aFormData['update_by'] 	= CSession::instance() -> getValue('user_id');
												$_aFormData['update_time'] 	= time();

												$modelUsersRegister	 	= new modelUsersRegister();

												$registerCondition = new CModelCondition();
												$registerCondition -> where('user_hash', $systemId);

												$modelUsersRegister -> load($_pDatabase, $registerCondition);

												if(empty($modelUsersRegister -> getResult()))
													$_aFormData['user_id'] 	= $modelUsersRegister -> registerUserId($_pDatabase, 3, $systemId, $user -> user_name_first .' '. $user -> user_name_last);
												else
													$_aFormData['user_id'] 	= $modelUsersRegister -> getResult()[0] -> user_id;

												##	Updating rights table

												$modelCondition = new CModelCondition();
												$modelCondition -> where('user_id', $_aFormData['user_id']);
												$modelCondition -> where('user_hash', $systemId);

												$modelUserGroups = new modelUserGroups();
												$modelUserGroups -> delete($_pDatabase, $modelCondition);

												foreach($_aFormData['groups'] as $_groupID)
												{
													$insertedId = 0;

													$insertData = [
																	'user_id' 	=> $_aFormData['user_id'],
																	'group_id' 	=> $_groupID,
																	'user_hash' 	=> $systemId,
																	'update_by' 	=> $_aFormData['update_by'],
																	'update_time' 	=> $_aFormData['update_time']
																	];

													$modelUserGroups -> insert($_pDatabase,$insertData, $insertedId);
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

}

?>