<?php

require_once 'CSingleton.php';

require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelLoginObjects.php';
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelDeniedRemote.php';
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUserAgent.php';
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUsersRegister.php';

class	CSession extends CSingleton
{
	private	array	$m_aSessionData;
	private	int		$m_iTimeout;
	private	bool	$m_bInitialized = false;

	public function
	initialize()
	{
		$this -> m_iTimeout 	= 	60 * 60 * 3;
		$this -> m_aSessionData =	[
										"user_agent"		=>	strip_tags($_SERVER['HTTP_USER_AGENT']),
										"user_ip"			=>	strip_tags($_SERVER['REMOTE_ADDR']),
										"user_id"			=> '',
										"session_id"		=>	'',
										"is_auth"			=>	false,
										"is_auth_objects"	=>	[],
										"login_fail_count"	=>	0,
										"is_remote"			=>	false
									];
		$this -> m_bInitialized	= 	true;
	}

	public function
	updateSession(int $_nodeId, string $_language, CUserRights &$_pUserRights) : bool
	{
		## Check if class got initialized 
		if($this -> m_bInitialized === NULL || $this -> m_bInitialized === false) 
			$this -> initialize();





		## Required data
		$this -> m_aSessionData['session_id'] = $this -> _createSessionID();

		$_timestamp			= 	time();
		$_sessionTimeout 	= 	$_timestamp + $this -> m_iTimeout;
		$_bIsNewSession 	= 	false;

		$spamAccessTimeout	= 	$_timestamp - CFG::GET() -> SESSION -> SPAM_ACCESS_TIMEOUT;
		$spamAccessLimit	= 	CFG::GET() -> SESSION -> SPAM_ACCESS_LIMIT;

		##	Check if session already exists


		$pDatabase = &CDatabase::instance() -> getConnection(CFG::GET() -> MYSQL -> PRIMARY_DATABASE);

		if($pDatabase === null)
			return false;



		##	Denied Remote Access, if enabled but htacces type disabled/not supported

		if(CFG::GET() -> SESSION -> DENIED_ACCESS_ON || !CFG::GET() -> SESSION -> DENIED_ACCESS_HTACCESS)
		{
			$modelDeniedRemote	= new modelDeniedRemote();
			$modelDeniedRemote -> load($pDatabase);
			$deniedList 		= $modelDeniedRemote -> getResult();

			foreach($deniedList as $deniedAddr)
			{
				if(tk::matchRemoteAddr($_SERVER['REMOTE_ADDR'], $deniedAddr -> denied_ip))
				{
					header('HTTP/1.0 403 Forbidden');
					exit;
				}
			}
		}








		$sessionCheckCond		 = new CModelCondition();
		$sessionCheckCond		-> where('session_id', $this -> m_aSessionData['session_id']);

		$dbQuery 	= $pDatabase		-> query(DB_SELECT) 
										-> table('tb_sessions') 
										-> selectColumns(['data_id', 'login_fail_count'])
										-> condition($sessionCheckCond);

		$sessionCheckRes = $dbQuery -> exec();

		if($sessionCheckRes !== false && count($sessionCheckRes) == 0)
		{	##	Create new session

			##	User Agent check

			$modelUserAgent	 =	new modelUserAgent();
			$modelUserAgent	->	load($pDatabase);
			$agentsList	 	 =	&$modelUserAgent -> getResult();

			foreach( $agentsList as $agent)
			{
				if( strpos($this -> m_aSessionData['user_agent'], $agent -> agent_suffix) !== false && $agent -> agent_allowed == 0)
				{	##	Found not allowed agent
					header("HTTP/1.0 403 Forbidden");
					exit;
				}
			}

			##	Spam access check

			if(CFG::GET() -> SESSION -> SPAM_ACCESS_ON)
			{


				$sessionCondition		 = new CModelCondition();
				$sessionCondition		-> where('user_ip', $_SERVER['REMOTE_ADDR'])
										-> whereGreater('time_create', $spamAccessTimeout);


				$dbQuery 	= $pDatabase		-> query(DB_SELECT) 
												-> table('tb_sessions') 
												-> selectColumns(['user_ip'])
												-> condition($sessionCondition);

				$_sqlSpamAccRes = $dbQuery -> exec();

				

				if(count($_sqlSpamAccRes) >= $spamAccessLimit && CFG::GET() -> SESSION -> DENIED_ACCESS_ON)
				{
					$newDeniedAddress['denied_ip'] 		= $_SERVER['REMOTE_ADDR'];
					$newDeniedAddress['denied_desc'] 	= 'Automatic added by mass access protection';
					$newDeniedAddress['create_time'] 	= time();
					$newDeniedAddress['create_by'] 		= '1';

					$insertedIdDeniedRemote = NULL;

					$modelDeniedRemote	 =	new modelDeniedRemote();
					$modelDeniedRemote	->	insert($_db, $newDeniedAddress, $insertedIdDeniedRemote);

					$_pHTAccess  = new CHTAccess();
					$_pHTAccess -> generatePart4DeniedAddress($_db);
					$_pHTAccess -> writeHTAccess($_db);

					CSysMailer::instance() 	-> sendMail(
														CLanguage::instance() -> getString('SYSMAIL_SESSDENIED_SUBJ'), 
														CLanguage::instance() -> getStringExt(
																							'SYSMAIL_SESSDENIED_TEXT',
																							[
																								'[USER_IP]' => $_SERVER['REMOTE_ADDR']
																							]
																							),
														true,
														'mass-access-protection'
														);

					header("HTTP/1.0 403 Forbidden");
					exit;
				}
			}

			##	Write session data

			$dtaObject = new stdClass();
			$dtaObject -> session_id 	= $this -> m_aSessionData['session_id'];
			$dtaObject -> user_agent 	= $this -> m_aSessionData['user_agent'];
			$dtaObject -> user_ip 		= $this -> m_aSessionData['user_ip'];
			$dtaObject -> time_create 	= $_timestamp;
			$dtaObject -> time_update 	= $_timestamp;
			$dtaObject -> time_out 		= $_sessionTimeout;

			$dbQuery = $pDatabase		-> query(DB_INSERT) 
											-> table('tb_sessions') 
											-> dtaObject($dtaObject);

			$dbQuery -> exec();




			$_bIsNewSession 	= 	true;
		}
		elseif($sessionCheckRes !== false)
		{
			##	Update session
		


			$sessionUpdateCond		 = new CModelCondition();
			$sessionUpdateCond		-> where('session_id', $this -> m_aSessionData['session_id']);

			$dtaObject = new stdClass();
			$dtaObject -> time_out 		= $_sessionTimeout;
			$dtaObject -> time_update 	= $_timestamp;

			$dbQuery 	= $pDatabase		-> query(DB_UPDATE) 
											-> table('tb_sessions') 
											-> dtaObject($dtaObject)
											-> condition($sessionUpdateCond);

			$dbQuery -> exec();





			$_sqlSessionData	=	$sessionCheckRes[0];

			$this -> m_aSessionData['login_fail_count']		=	$_sqlSessionData -> login_fail_count;

			##	Check user auth by session cookie

			if(!empty($_COOKIE))
			{
				foreach($_COOKIE as $_cookieKey => $_cookieValue)
				{
					$modelCondition = new CModelCondition();
					$modelCondition -> where('object_id', $_cookieKey);

					// get login objects
					$_pModelLoginObjects	 =	new modelLoginObjects();

					$_pModelLoginObjects	->	load($pDatabase, $modelCondition);	

					$_loginObjects			 = 	&$_pModelLoginObjects -> getResult();


					if($_loginObjects !== NULL && count($_loginObjects) != 0)
					{
						$_loginObject			 =	&$_loginObjects[0];
				
					#	$_loginObject -> object_databases 	= json_decode($_loginObject -> object_databases);
					#	$_loginObject -> object_session_ext = json_decode($_loginObject -> object_session_ext);
					#	$_loginObject -> object_fields 		= json_decode($_loginObject -> object_fields);

					}
								

					if(isset($_loginObject))
					{	##	Login object for this cookies exists

						$userTables			= ['tb_users','tb_users_backend'];
						$userTable			= NULL;
						$userTableResult	= NULL;

						foreach($_loginObject -> object_databases as $_dbName)
						{
							if(		CFG::GET() -> MYSQL -> PRIMARY_DATABASE !== $_dbName 
								&& !CFG::GET() -> USER_SYSTEM -> REMOTE_USER -> ENABLED
							)	continue;


							// find user table
							foreach($_loginObject -> object_fields as $field)
							{
								switch($field -> query_type)
								{
									case 	'compare':	

											if(in_array($field -> table, $userTables, true) && $userTable === NULL)
											{
												$userTable 			= $field -> table;
												break;
											}

											break;
								}
							}

							$_dbLogin 	=	&CDatabase::instance() -> getConnection($_dbName);
							$_cookieID	=	CCookie::instance() -> getCookieID($_cookieKey);




								$condition		 = new CModelCondition();
								$condition		-> whereLike('cookie_id', "%\"". $_cookieKey ."\":{\"id\":\"". $_cookieID ."\"}%")
												-> where('is_locked', '0')
												-> limit(1);

							if(CFG::GET() -> MYSQL -> PRIMARY_DATABASE !== $_dbName)
								$condition -> where('allow_remote', '1');


								$dbQuery 	= $_dbLogin			-> query(DB_SELECT) 
																-> table($userTable) 
																-> selectColumns(['data_id','time_login','cookie_id','user_id','login_name'])
																-> condition($condition);

								$_sqlLoginChkRes = $dbQuery -> exec();















		
							if($_sqlLoginChkRes !== false && count($_sqlLoginChkRes) > 0)
							{
								$_sqlLoginChk = $_sqlLoginChkRes[0];

								if(CCookie::instance() -> isCookieIdinatorValid($_cookieKey, $_sqlLoginChk -> time_login, $this -> m_aSessionData['session_id']))
								{	##	User auth positiv
			
									$this -> m_aSessionData['is_auth']						=	true;
									$this -> m_aSessionData['is_auth_objects'][$_cookieKey]	=	[	##	For additional data access
																								'db'		=>	&$_dbLogin,
																								'table'		=>	$userTable,
																								'data_id'	=>	$_sqlLoginChk -> data_id
																								];			

									## Gathering additional columns
									
									if(!empty($_loginObject -> object_session_ext))
									{
										$gatheredUserId = false;

										$userId = NULL;
										foreach($_loginObject -> object_session_ext as $_extColumn)
										{
											switch($_extColumn -> query_type)
											{
												case 	'compare':	

														$_selectColumns 	= [];

														if(in_array($_extColumn -> table, $userTables, true) && !$gatheredUserId)
														{
															$_selectColumns[] 	= 'user_id';
															$gatheredUserId 	= true;
														}

															$_selectColumns[] = $_extColumn -> name;






								$condition		 = new CModelCondition();
								$condition		-> where('user_id', $_sqlLoginChk -> user_id);



								$dbQuery 	= $_dbLogin			-> query(DB_SELECT) 
																-> table($_extColumn -> table) 
																-> selectColumns($_selectColumns)
																-> condition($condition);

								$_extData = $dbQuery -> exec()[0];









														foreach($_extData as $_datKey => $_dataValue)
														{
															if(!empty($_extData -> user_id))
																$userId = $_extData -> user_id;


															foreach($_loginObject -> object_session_ext as $_extColumn)
															{

																switch($_extColumn -> query_type)
																{
																	case 	'compare':	
																
																			if($_extColumn -> name === $_datKey)
																			{
																				switch($_extColumn -> data_prc)
																				{
																					case 'crypt':	$_dataValue = CRYPT::DECRYPT($_dataValue, $userId, true); break;
																				}
																			}

																			break;
																}

															}

															$this -> m_aSessionData[$_datKey] = $_dataValue;

														}

														break;
											}
										}

										foreach($_loginObject -> object_session_ext as $_extColumn)
										{
											switch($_extColumn -> query_type)
											{
												case 	'assign':	



								$joinCondition		 = new CModelCondition();
								$joinCondition		-> where($_extColumn -> infoTable .".". $_extColumn -> infoAssignCol, $_extColumn -> checkTable .".". $_extColumn -> checkColumn );



		$relationsList[] = new CModelRelations('JOIN', $_extColumn -> infoTable, $joinCondition);


								$condition		 = new CModelCondition();
								$condition		-> where($_extColumn -> checkTable.'.user_id', $userId);


								$dbQuery 	= $_dbLogin			-> query(DB_SELECT) 
																-> table($_extColumn -> checkTable) 
																-> selectColumns([$_extColumn -> infoColumn])
																-> condition($condition)
																-> relations($relationsList);

								$_sqlAssignRes = $dbQuery -> exec();








														if($_sqlAssignRes !== false && count($_sqlAssignRes) == 1)
														{
															$_sqlAssignItm = $_sqlAssignRes[0];

															

															$this -> m_aSessionData[$_extColumn -> infoColumn] = $_sqlAssignItm[$_extColumn -> infoColumn];


																	break;
														}
											}
										}
										}
										

									$userHash = NULL;

									/*
										Temporary Solution for remote users

										if dbname unequal to primary db, this is a remote user
									*/

									if(CFG::GET() -> MYSQL -> PRIMARY_DATABASE !== $_dbName)
									{
										foreach(CFG::GET() -> MYSQL -> DATABASE as $database)
										{
											if($database['name'] !== $_dbName)
												continue;
											$userHash = hash('sha256', $database['name'] . $database['server'] . $_sqlLoginChk['user_id'] . $_sqlLoginChk['login_name']);
										}

										$modelUsersRegister	 	= new modelUsersRegister();

										$registerCondition = new CModelCondition();
										$registerCondition -> where('user_hash', $userHash);

										$modelUsersRegister -> load($pDatabase, $registerCondition);

										if(!empty($modelUsersRegister -> getResult())) {

											$_sqlLoginChk -> user_id = $modelUsersRegister -> getResult()[0] -> user_id;

											$this -> m_aSessionData['user_id'] = $_sqlLoginChk -> user_id;
											$this -> m_aSessionData['is_remote'] = true;
										}
										else
										{
											$_sqlLoginChk -> user_id = 0;
										}
									}

									$_pUserRights -> loadUserRights($pDatabase, $_sqlLoginChk -> user_id);
								}								
							}
						}
					}				
				}
				
				return false;	
			}
		}
		else
		{
			CMessages::add('Database table error: '. $_db -> error, MSG_LOG);
			trigger_error("CSession::updateSession -- There is a session table issue, the query failed",E_USER_ERROR);
		}

		## log access
		
		if(!CMS_BACKEND)
		{
			if($_nodeId === 0)
			{
				$modelCondition = new CModelCondition();
				$modelCondition -> where('page_language', $_language);
				$modelCondition -> where('page_path', '/');	
				
				$modelSitemap = new modelSitemap();
				$modelSitemap -> load($pDatabase, $modelCondition);
				$sitemap = &$modelSitemap -> getResult();

				foreach($sitemap as $sitemapItem)
				{
					if($sitemapItem -> level == 1)
					{
						$_nodeId = $sitemapItem -> node_id;
						break;
					}
				}
			}
	



								$condition		 = new CModelCondition();
								$condition		-> where('session_id', $this -> m_aSessionData['session_id'])
												-> orderBy('time_access', 'DESC')
												-> limit(1);



								$dbQuery 		 = $pDatabase	-> query(DB_SELECT) 
																-> table('tb_sessions_access') 
																-> selectColumns(['data_id', 'node_id'])
																-> condition($condition);

								$accessData 		 = $dbQuery -> exec();



			if(!count($accessData) ||  $accessData[0] -> node_id != $_nodeId)
			{
				if(empty($_SERVER['HTTP_REFERER']))
					$_SERVER['HTTP_REFERER'] = '';
		
				$dtaObject = new stdClass();
				$dtaObject -> session_id 	= $this -> m_aSessionData['session_id'];
				$dtaObject -> node_id 		= $_nodeId;
				$dtaObject -> time_access 	= $_timestamp;
				$dtaObject -> referer 		= substr(trim(strip_tags($_SERVER['HTTP_REFERER'])),0,250);

				$dbQuery = $pDatabase		-> query(DB_INSERT) 
											-> table('tb_sessions_access') 
											-> dtaObject($dtaObject);

				$dbQuery -> exec();
			}
		}

		return $_bIsNewSession;
	}

	public function
	getValue(string $_valueKey)
	{
		return (isset($this -> m_aSessionData[$_valueKey]) ? $this -> m_aSessionData[$_valueKey] : NULL);
	}

	public function
	isAuthed(string $_loginObjectId)
	{
		if(isset($this -> m_aSessionData['is_auth_objects'][$_loginObjectId]))
			return $this -> m_aSessionData['is_auth_objects'][$_loginObjectId]; 
		else 
			return false;
	}

	public function
	setValue(string $_valueKey, $_value, bool $_bUpdateDatabase = false)
	{
		switch($_valueKey)
		{
			case   'login_fail_count':		$this -> m_aSessionData['login_fail_count']		= $_value; if($_bUpdateDatabase) $this -> _updateValue('login_fail_count', $_value); break;
		}
		return $_value;
	}

	private function
	_updateValue(string $_columnName, $_value)
	{
		$pDatabase = &CDatabase::instance() -> getConnection(CFG::GET() -> MYSQL -> PRIMARY_DATABASE);

		$updateCondition		 = new CModelCondition();
		$updateCondition		-> where('session_id', $this -> m_aSessionData['session_id']);

		$dtaObject  = new stdClass();
		$dtaObject -> $_columnName 		= $_value;

		$dbQuery 	= $pDatabase		-> query(DB_UPDATE) 
										-> table('tb_sessions') 
										-> dtaObject($dtaObject)
										-> condition($updateCondition);

		$dbQuery -> exec();
	}

	private function
	_createSessionID()
	{
		return md5($this -> m_aSessionData['user_agent']) . md5($this -> m_aSessionData['user_ip']);
	}
}

?>