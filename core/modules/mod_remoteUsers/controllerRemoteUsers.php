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
	
	/*
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
				$validationErr =	true;
				$validationMsg =	CLanguage::string('ERR_PERMISSON');
				$responseData = 	[];

				tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
			}

			CMessages::add(CLanguage::string('ERR_PERMISSON') , MSG_WARNING);
			return;
		}

		##	Call sub-logic function by target, if there results are false, we make a fall back to default view

		$enableEdit 	= $this -> existsUserRight('edit');
		$enableDelete	= $enableEdit;

		$logicResults = false;
		switch($_controllerAction)
		{
			case 'view'		: $logicResults = $this -> logicView($_pDatabase, $_isXHRequest, $enableEdit, $enableDelete);	break;
			case 'ping'		: $logicResults = $this -> logicPing($_pDatabase, $_isXHRequest, $enableEdit, $enableDelete);	break;	
			case 'edit'		: $logicResults = $this -> logicEdit($_pDatabase, $_isXHRequest);	break;		
		}

		if(!$logicResults)
		{
			##	Default View
			$this -> logicIndex($_pDatabase, $_isXHRequest, $enableEdit, $enableDelete);	
		}
	}
	*/

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
			case 'xhr_index' 	  : $logicDone = $this -> logicXHRIndex($_pDatabase, $_xhrInfo); break;
			
			case 'xhr_ping'		  : $logicDone = $this -> logicXHRPing($_pDatabase); break;	
			
			case 'xhr_edit-user'  : $logicDone = $this -> logicXHREditUser($_pDatabase); break;			
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

										$data[$dataKey]['userGroups'] 	=  [];

										foreach($modelUserGroups -> getResult() as $userGroup)
										{
											if($dataSet['id'] == $userGroup -> user_hash)
												$data[$dataKey]['userGroups'][]	=  $userGroup;
										}
									}


			tk::xhrResult(intval($validationErr), $validationMsg, $data);	// contains exit call

		return false;
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
	logicView(CDatabaseConnection &$_pDatabase, bool $_enableEdit = false, bool $_enableDelete = false)
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
		
		CMessages::add(CLanguage::string('MOD_BECATEGORIES_ERR_USERID_UK'), MSG_WARNING);
		return false;
	}

	private function
	logicXHREditUser(CDatabaseConnection &$_pDatabase)
	{
		$systemId = $this -> querySystemId();

		if($systemId !== false)
		{	
			$validationErr =	false;
			$validationMsg =	'';
			$responseData = 	[];


										$_pFormVariables =	new CURLVariables();
										$_request		 =	[];
										$_request[] 	 = 	[	"input" => "groups",    	"validate" => "strip_tags|strip_whitespaces|!empty", 	"use_default" => true, "default_value" => [] ]; 		
	
										$_pFormVariables -> retrieve($_request, false, true); // POST 
										$_aFormData		 = $_pFormVariables ->getArray();

										if(!$validationErr)
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

										
												$validationMsg = CLanguage::string('USER') .' '. CLanguage::string('WAS_UPDATED');
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
