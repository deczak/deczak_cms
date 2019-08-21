<?php

require_once 'CSingleton.php';

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
										"session_id"		=>	'',
										"is_auth"			=>	false,
										"is_auth_objects"	=>	[],
										"login_fail_count"	=>	0
									];
		$this -> m_bInitialized	= 	true;
	}

	public function
	updateSession()
	{
		## Check if class got initialized 
		if($this -> m_bInitialized === NULL || $this -> m_bInitialized === false) $this -> initialize();

		## Required data
		$this -> m_aSessionData['session_id'] = $this -> createSessionID();

		$_timestamp			= 	time();
		$_sessionTimeout 	= 	$_timestamp + $this -> m_iTimeout;
		$_bIsNewSession 	= 	false;

		##	Check if session already exists

		$_db = CSQLConnect::instance() -> getConnection(CFG::MYSQL_PRMY);
		
		$_sqlString			=	"	SELECT		tb_sessions.data_id,
												tb_sessions.login_fail_count						
									FROM		tb_sessions
									WHERE		tb_sessions.session_id	= '". $this -> m_aSessionData['session_id'] ."'
								";

		$_sqlSessionRes		=	$_db -> query($_sqlString);	

		if($_sqlSessionRes !== false && $_sqlSessionRes -> num_rows === 0)
		{
			##	Create session
		
			$_sqlString		=	"	INSERT INTO	tb_sessions
												(
													tb_sessions.session_id,
													tb_sessions.user_agent,
													tb_sessions.user_ip,
													tb_sessions.time_create,
													tb_sessions.time_out
												)
												VALUES
												(
													'". $_db -> real_escape_string($this -> m_aSessionData['session_id']) ."',
													'". $_db -> real_escape_string($this -> m_aSessionData['user_agent']) ."',
													'". $_db -> real_escape_string($this -> m_aSessionData['user_ip']) ."',
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
					if(isset(CFG::LOGIN['objects'][$_cookieKey]))
					{	##	Login object for this cookies exists

						foreach(CFG::LOGIN['objects'][$_cookieKey]['db_name'] as $_dbName)
						{
							$_dbLogin 	=	CSQLConnect::instance() -> getConnection($_dbName);
							$_cookieID	=	CCookie::instance() -> getCookieID($_cookieKey);
				
							$_sqlString			=	"	SELECT		". CFG::LOGIN['objects'][$_cookieKey]['table'] .".data_id,
																	". CFG::LOGIN['objects'][$_cookieKey]['table'] .".time_login,
																	". CFG::LOGIN['objects'][$_cookieKey]['table'] .".cookie_id,
																	". CFG::LOGIN['objects'][$_cookieKey]['table'] .".user_id
														FROM		". CFG::LOGIN['objects'][$_cookieKey]['table'] ."
														WHERE 		". CFG::LOGIN['objects'][$_cookieKey]['table'] .".cookie_id LIKE '%\"". $_dbLogin -> real_escape_string($_cookieKey) ."\":{\"id\":\"". $_dbLogin -> real_escape_string($_cookieID) ."\"}%'
															AND		". CFG::LOGIN['objects'][$_cookieKey]['table'] .".is_locked = '0'
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
																								'table'		=>	CFG::LOGIN['objects'][$_cookieKey]['table'],
																								'data_id'	=>	$_sqlLoginChk['data_id']
																								];			

									## Gathering additional columns

									if(!empty(CFG::LOGIN['objects'][$_cookieKey]['extend_session']))
									{
										$_selectColumns = [];
										foreach(CFG::LOGIN['objects'][$_cookieKey]['extend_session'] as $_extColumn)
										{
											$_selectColumns[] = CFG::LOGIN['objects'][$_cookieKey]['table'] .'.'. $_extColumn;
										}

										$_sqlString		=	"	SELECT		". implode(', ',$_selectColumns) ."
																FROM		". CFG::LOGIN['objects'][$_cookieKey]['table'] ."
																WHERE		". CFG::LOGIN['objects'][$_cookieKey]['table'] .".data_id		= '". $_sqlLoginChk['data_id'] ."'
															";

										$_extDataRes 	= 	$_dbLogin -> query($_sqlString);	
										$_extData		= 	$_extDataRes -> fetch_assoc();

										foreach($_extData as $_datKey => $_dataValue)
										{
											$this -> m_aSessionData[$_datKey] = $_dataValue;
										}
									}

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

										foreach($_sqlUserRights['group_rights'] as $_moduleRights  => $_rightsSet)
										{
											$this -> m_aSessionData['user_rights'][$_moduleRights] = $_rightsSet;
										}										
									}
								}
								return false;
							}
						}
					}					
				}
			}
		}
		else
		{
			CMessages::instance() -> addMessage('Database table error: '. $_db -> error, MSG_LOG);
			trigger_error("CSession::updateSession -- There is a session table issue, the query failed",E_USER_ERROR);
		}

		return $_bIsNewSession;
	}

	public function
	getSessionValue(string $_valueName, string $_subValue = '')
	{
		switch($_valueName)
		{
			case 'LOGIN_FAIL_COUNT':	return $this -> m_aSessionData['login_fail_count'];
			case 'SESSION_ID':			return $this -> m_aSessionData['session_id'];
			case 'IS_AUTH':				return $this -> m_aSessionData['is_auth'];
			case 'IS_AUTH_OBJECT':		if(isset($this -> m_aSessionData['is_auth_objects'][$_subValue])) return $this -> m_aSessionData['is_auth_objects'][$_subValue]; else return false;
			default:					return (isset($this -> m_aSessionData[$_valueName]) ? $this -> m_aSessionData[$_valueName] : NULL);
		}
		return NULL;
	}

	public function
	setSessionValue(string $_valueName, $_value, bool $_bUpdateDatabase = false)
	{
		switch($_valueName)
		{
			case   'LOGIN_FAIL_COUNT':		$this -> m_aSessionData['login_fail_count']		= $_value; if($_bUpdateDatabase) $this -> updateDatabaseTable('login_fail_count', $_value); break;
		}
		return $_value;
	}

	private function
	updateDatabaseTable(string $_columnName, $_value)
	{
		$_db = CSQLConnect::instance() -> getConnection(CFG::MYSQL_PRMY);

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