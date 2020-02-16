<?php

require_once 'CSingleton.php';

require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelLoginObjects.php';
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelDeniedRemote.php';
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUserAgent.php';

class	CSession extends CSingleton
{
	private		$m_aSessionData;
	private		$m_iTimeout;
	private		$m_bInitialized;

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
										"login_fail_count"	=>	0
									];
		$this -> m_bInitialized	= 	true;
	}

	public function
	updateSession(int $_nodeId, string $_language, CUserRights &$_pUserRights)
	{
		## Check if class got initialized 
		if($this -> m_bInitialized === NULL || $this -> m_bInitialized === false) $this -> initialize();

		## Required data
		$this -> m_aSessionData['session_id'] = $this -> createSessionID();

		$_timestamp			= 	time();
		$_sessionTimeout 	= 	$_timestamp + $this -> m_iTimeout;
		$_bIsNewSession 	= 	false;

		$spamAccessTimeout	= 	$_timestamp - CFG::GET() -> SESSION -> SPAM_ACCESS_TIMEOUT;
		$spamAccessLimit	= 	CFG::GET() -> SESSION -> SPAM_ACCESS_LIMIT;

		##	Check if session already exists

		$_db = CSQLConnect::instance() -> getConnection(CFG::GET() -> MYSQL -> PRIMARY_DATABASE);

		if($_db === false)
			return false;

		$_sqlString			=	"	SELECT		tb_sessions.data_id,
												tb_sessions.login_fail_count						
									FROM		tb_sessions
									WHERE		tb_sessions.session_id	= '". $this -> m_aSessionData['session_id'] ."'
								";

		$_sqlSessionRes		=	$_db -> query($_sqlString);	

		if($_sqlSessionRes !== false && $_sqlSessionRes -> num_rows === 0)
		{	##	Create new session

			##	User Agent check

			$modelUserAgent	 =	new modelUserAgent();
			$modelUserAgent	->	load($_db);
			$agentsList	 	 =	$modelUserAgent -> getDataInstance();

			foreach( $agentsList as $agent)
			{
				if( strpos($this -> m_aSessionData['user_agent'], $agent -> agent_suffix) !== false && $agent -> agent_allowed == 0)
				{	##	Found not allowed agent
					header("HTTP/1.0 403 Forbidden");
					exit;
				}
			}

			##	Spam access check
		
			$_sqlString			=	"	SELECT		tb_sessions.user_ip						
										FROM		tb_sessions
										WHERE		tb_sessions.user_ip		= '". $_SERVER['REMOTE_ADDR'] ."'
											AND		tb_sessions.time_create > $spamAccessTimeout
									";

			$_sqlSpamAccRes		=	$_db -> query($_sqlString);				

			if($_sqlSpamAccRes -> num_rows >= $spamAccessLimit)
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
				$_pHTAccess -> writeHTAccess();

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

			##	Write session data
		
			$_sqlString		=	"	INSERT INTO	tb_sessions
												(
													tb_sessions.session_id,
													tb_sessions.user_agent,
													tb_sessions.user_ip,
													tb_sessions.time_create,
													tb_sessions.time_update,
													tb_sessions.time_out
												)
												VALUES
												(
													'". $_db -> real_escape_string($this -> m_aSessionData['session_id']) ."',
													'". $_db -> real_escape_string($this -> m_aSessionData['user_agent']) ."',
													'". $_db -> real_escape_string($this -> m_aSessionData['user_ip']) ."',
													'". $_db -> real_escape_string($_timestamp) ."',
													'". $_db -> real_escape_string($_timestamp) ."',
													'". $_db -> real_escape_string($_sessionTimeout) ."'
												)
								";

			$_db -> query($_sqlString);	

			$_bIsNewSession 	= 	true;
		}
		else if($_sqlSessionRes !== false)
		{
			##	Update session
		
			$_sqlString			=	"	UPDATE		tb_sessions
										SET			tb_sessions.time_out 		= '". $_db -> real_escape_string($_sessionTimeout) ."'	,
													tb_sessions.time_update 	= '". $_db -> real_escape_string($_timestamp) ."'
										WHERE		tb_sessions.session_id		= '". $_db -> real_escape_string($this -> m_aSessionData['session_id']) ."'
									";

			$_db -> query($_sqlString);	

			$_sqlSessionData	=	$_sqlSessionRes -> fetch_assoc();

			$this -> m_aSessionData['login_fail_count']		=	$_sqlSessionData['login_fail_count'];

			##	Check user auth by session cookie

			if(!empty($_COOKIE))
			{




				foreach($_COOKIE as $_cookieKey => $_cookieValue)
				{



			$modelCondition = new CModelCondition();
			$modelCondition -> where('object_id', $_cookieKey);

				// get login objects
				$_pModelLoginObjects	 =	new modelLoginObjects();

					$_pModelLoginObjects	->	load($_db, $modelCondition);	

					$_loginObjects			 = 	$_pModelLoginObjects -> getDataInstance();


					if($_loginObjects !== NULL && count($_loginObjects) != 0)
					{
						$_loginObject			 =	&$_loginObjects[0];
				


					$_loginObject -> object_databases = json_decode($_loginObject -> object_databases);
					$_loginObject -> object_session_ext = json_decode($_loginObject -> object_session_ext);
					}
					
			

					if(isset($_loginObject))
					{	##	Login object for this cookies exists

						foreach($_loginObject -> object_databases as $_dbName)
						{
							$_dbLogin 	=	CSQLConnect::instance() -> getConnection($_dbName);
							$_cookieID	=	CCookie::instance() -> getCookieID($_cookieKey);
				
							$_sqlString			=	"	SELECT		". $_loginObject -> object_table .".data_id,
																	". $_loginObject -> object_table .".time_login,
																	". $_loginObject -> object_table .".cookie_id,
																	". $_loginObject -> object_table .".user_id
														FROM		". $_loginObject -> object_table ."
														WHERE 		". $_loginObject -> object_table .".cookie_id LIKE '%\"". $_dbLogin -> real_escape_string($_cookieKey) ."\":{\"id\":\"". $_dbLogin -> real_escape_string($_cookieID) ."\"}%'
															AND		". $_loginObject -> object_table .".is_locked = '0'
														LIMIT		1
													";

							$_sqlLoginChkRes		=	$_dbLogin -> query($_sqlString);	

		



							if($_sqlLoginChkRes !== false && $_sqlLoginChkRes -> num_rows > 0)
							{

								$_sqlLoginChk = $_sqlLoginChkRes -> fetch_assoc();


								if(CCookie::instance() -> isCookieIdinatorValid($_cookieKey, $_sqlLoginChk['time_login'], $this -> m_aSessionData['session_id']))
								{	##	User auth positiv



			
									$this -> m_aSessionData['is_auth']						=	true;
									$this -> m_aSessionData['is_auth_objects'][$_cookieKey]	=	[	##	For additional data access
																								'db'		=>	&$_dbLogin,
																								'table'		=>	$_loginObject -> object_table,
																								'data_id'	=>	$_sqlLoginChk['data_id']
																								];			
																								
																							
									## Gathering additional columns
									
									if(!empty($_loginObject -> object_session_ext))
									{
										$_selectColumns 	= [];
										$_selectColumns[] 	= $_loginObject -> object_table .'.user_id';

										foreach($_loginObject -> object_session_ext as $_extColumn)
										{
											$_selectColumns[] = $_loginObject -> object_table .'.'. $_extColumn -> name;
										}

										$_sqlString		=	"	SELECT		". implode(', ',$_selectColumns) ."
																FROM		". $_loginObject -> object_table ."
																WHERE		". $_loginObject -> object_table .".data_id		= '". $_sqlLoginChk['data_id'] ."'
															";

										$_extDataRes 	= 	$_dbLogin -> query($_sqlString);	
										$_extData		= 	$_extDataRes -> fetch_assoc();

										foreach($_extData as $_datKey => $_dataValue)
										{
											##
											##	looping for data_proc if the field crypted

											foreach($_loginObject -> object_session_ext as $_extColumn)
											{
												if($_extColumn -> name === $_datKey)
												{
													switch($_extColumn -> data_prc)
													{
														case 'crypt':	$_dataValue = CRYPT::DECRYPT($_dataValue, $_extData['user_id'], true); break;
													}
												}
											}

											$this -> m_aSessionData[$_datKey] = $_dataValue;
										}
									}
// HIER CUSERRIGHTS
// $_pUserRights

$_pUserRights -> loadUserRights($_db, $_sqlLoginChk['user_id']);


									##	Gathering user group rights

									$_sqlString			=	"	SELECT		tb_users_groups.*,
																			tb_right_groups.*
																FROM		tb_users_groups
																LEFT JOIN	tb_right_groups ON tb_right_groups.group_id = tb_users_groups.group_id
																WHERE		tb_users_groups.user_id	= '". $_sqlLoginChk['user_id'] ."'
															";

									$_sqlUserRightsRes	= 	$_db -> query($_sqlString);		

									while($_sqlUserRightsRes !== false && $_sqlUserRights = $_sqlUserRightsRes -> fetch_assoc())
									{
						
										$_sqlUserRights['group_rights'] = json_decode($_sqlUserRights['group_rights'], true);
										if(!empty($_sqlUserRights['group_rights']))
										foreach($_sqlUserRights['group_rights'] as $_moduleRights  => $_rightsSet)
										{
											$this -> m_aSessionData['user_rights'][$_moduleRights] = $_rightsSet;
										}										
									}
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
			CMessages::instance() -> addMessage('Database table error: '. $_db -> error, MSG_LOG);
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
				$modelSitemap -> load($_db, $modelCondition);
				$sitemap = &$modelSitemap -> getDataInstance();

				foreach($sitemap as $sitemapItem)
				{
					if($sitemapItem -> level == 1)
					{
						$_nodeId = $sitemapItem -> node_id;
						break;
					}
				}
			}

			$_sqlString			=	"	SELECT		tb_sessions_access.data_id,
													tb_sessions_access.node_id
										FROM		tb_sessions_access
										WHERE 		tb_sessions_access.session_id = '". $this -> m_aSessionData['session_id'] ."'
										ORDER BY	tb_sessions_access.time_access DESC
										LIMIT		1
									";


			$accessRes 	= $_db -> query($_sqlString);	
			$accessData	=	$accessRes -> fetch_array();

			if($accessData['node_id'] != $_nodeId)
			{
				if(empty($_SERVER['HTTP_REFERER']))
					$_SERVER['HTTP_REFERER'] = '';

				$_sqlString		=	"	INSERT INTO	tb_sessions_access
													(
														tb_sessions_access.session_id,
														tb_sessions_access.node_id,
														tb_sessions_access.time_access,
														tb_sessions_access.referer
													)
													VALUES
													(
														'". $_db -> real_escape_string($this -> m_aSessionData['session_id']) ."',
														'". $_db -> real_escape_string($_nodeId) ."',
														'". $_db -> real_escape_string($_timestamp) ."',
														'". $_db -> real_escape_string(substr(trim(strip_tags($_SERVER['HTTP_REFERER'])),0,250)) ."'
													)
									";

					$_db -> query($_sqlString);	
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

	/*
	public function
	setSessionValue(string $_valueName, $_value, bool $_bUpdateDatabase = false)
	{
		switch($_valueName)
		{
			case   'LOGIN_FAIL_COUNT':		$this -> m_aSessionData['login_fail_count']		= $_value; if($_bUpdateDatabase) $this -> updateDatabaseTable('login_fail_count', $_value); break;
		}
		return $_value;
	}
	*/

	public function
	setValue(string $_valueKey, $_value, bool $_bUpdateDatabase = false)
	{
		switch($_valueKey)
		{
			case   'login_fail_count':		$this -> m_aSessionData['login_fail_count']		= $_value; if($_bUpdateDatabase) $this -> updateDatabaseTable('login_fail_count', $_value); break;
		}
		return $_value;
	}

	private function
	updateDatabaseTable(string $_columnName, $_value)
	{
		$_db = CSQLConnect::instance() -> getConnection(CFG::GET() -> MYSQL -> PRIMARY_DATABASE);

		$_sqlString			=	"	UPDATE		tb_sessions
									SET			tb_sessions.". $_columnName ." = '". $_db -> real_escape_string($_value) ."'
									WHERE		tb_sessions.session_id		   = '". $_db -> real_escape_string($this -> m_aSessionData['session_id']) ."'
								";

		$_db -> query($_sqlString);	
	}

	private function
	createSessionID()
	{
		return md5($this -> m_aSessionData['user_agent']) . md5($this -> m_aSessionData['user_ip']);
	}

	public function
	getUserRights(string $_moduleID)
	{
		if(isset($this -> m_aSessionData['user_rights'][$_moduleID])) return $this -> m_aSessionData['user_rights'][$_moduleID];
		return [];
	}
}

?>