<?php

require_once 'CBasic.php';

class	CLogin extends CBasic
{
	private	$m_bInitialized;
	private	$m_iFailLimit;
	private $m_iFailTime;

	public 	function
	__construct(array $_parameters)
	{
		$this -> m_bInitialized = false;
		$this -> m_aStorage		= [];

		if(empty($_parameters['fail_limit']))
		{
			$this -> setError('ERR_9');
			return;
		}

		$this -> m_iFailLimit		= $_parameters['fail_limit'];

		foreach($_parameters['objects'] as $_loginKey => $_loginObject)
		{
			if(		!is_array($_loginObject)
				||	empty($_loginObject['ob_name'])
				||	empty($_loginObject['table'])
				||	!is_array($_loginObject['db_name'])
				||	count($_loginObject['db_name']) == 0
				||	!is_array($_loginObject['columns'])
				||	count($_loginObject['columns']) == 0
				||	!is_array($_loginObject['columns'])
				||	count($_loginObject['columns']) == 0
			  )
			{
			}
			$this -> m_aStorage[ $_loginObject['ob_name'] ] = $_loginObject;
		}

		if(count($this -> m_aStorage) != 0)
		{
			$this -> m_bInitialized = true;
			$this -> setError('ERR_0');
		}
		else
		{
			$this -> setError('ERR_1');
		}
	}

	public	function
	login(string $_loginObjectName, array $_formData)
	{
	
		if(!$this -> m_bInitialized)
		{	##	Class not initialized
			$this -> setError('ERR_4');
			return false;
		}


		if(!isset($this -> m_aStorage[$_loginObjectName]))
		{	##	Login object unknown
			$this -> setError('ERR_2');
			return false;
		}


		$_pSession		 = 	CSession::instance();
		$_loginFailCount	= $_pSession -> getSessionValue('LOGIN_FAIL_COUNT');


		if($_loginFailCount > $this -> m_iFailLimit)
		{	##	Login limit reached
			$this -> setError('ERR_5');
			return false;
		}

		$_loginObject 	= &$this -> m_aStorage[$_loginObjectName];
		$_bLoginResult	= false;
		$_columnsValue	= [];
		$_cryptUsername	= [];


		foreach($_loginObject['columns'] as $_column)
		{

			if(empty($_formData[ $_column['field'] ]))
			{
				$this -> setError('ERR_3');
				return $_bLoginResult;
			}

			switch($_column['data_prc'])
			{
				case 'plain':	##	No kind of processing
								$_generatedValue = $_formData[ $_column['field'] ];						
								break;

				case 'crypt':	##	Encrypt form data for validation
								$_generatedValue = CRYPT::LOGIN_HASH($_formData[ $_column['field'] ]);						
								break;

				case 'hash':	##	Hash form data for validation
				default:
								$_generatedValue = CRYPT::LOGIN_CRYPT($_formData[ $_column['field'] ], ENCRYPTION_BASEKEY);					
				
			}

			$_columnsValue[] = $_loginObject['table'] .'.'. $_column['name'] ." = '". $_generatedValue."'";
			if(isset($_column['is_username']) && $_column['is_username'] === true) $_cryptUsername[] = $_loginObject['table'] .'.'. $_column['name'] ." = '". $_generatedValue."'";	
		}		
			
		##	Looping through the databases

		foreach($_loginObject['db_name'] as $_dbName)
		{
			$_db 	= CSQLConnect::instance() -> getConnection($_dbName);

			$_sqlString			=	"	SELECT		". $_loginObject['table'] .".is_locked,
													". $_loginObject['table'] .".login_count,
													". $_loginObject['table'] .".data_id,
													". $_loginObject['table'] .".cookie_id
										FROM		". $_loginObject['table'] ."
										WHERE		". implode(' AND ',$_columnsValue) ."
										LIMIT		1
									";

			$_sqlLoginChkRes		=	$_db -> query($_sqlString);	

			if($_sqlLoginChkRes !== false && $_sqlLoginChkRes -> num_rows === 1)
			{	##	User account found

				$_sqlLoginChk = $_sqlLoginChkRes -> fetch_assoc();

				switch($_sqlLoginChk['is_locked'])
				{
					case '0':	## OK

								$_timeStamp 	=	time();
								$_cookieID		=	CCookie::instance() -> createCookieIdinator( $_loginObjectName , $_timeStamp , $_pSession -> getSessionValue('SESSION_ID') );
								$_existsCookies =	json_decode($_sqlLoginChk['cookie_id'],true);
								$_existsCookies[$_loginObjectName]['id'] = $_cookieID;
								$_existsCookies	=	json_encode($_existsCookies, JSON_FORCE_OBJECT);

								$_sqlString		=	"	UPDATE		". $_loginObject['table'] ."
														SET			". $_loginObject['table'] .".login_count	= '". ($_sqlLoginChk['login_count'] + 1) ."',
																	". $_loginObject['table'] .".time_login		= '". $_timeStamp ."',
																	". $_loginObject['table'] .".cookie_id		= '". $_db -> real_escape_string($_existsCookies) ."'
														WHERE		". $_loginObject['table'] .".data_id		= '". $_sqlLoginChk['data_id'] ."'
													";

								$_db -> query($_sqlString);	
								$_pSession -> setSessionValue('LOGIN_FAIL_COUNT', 0, true);

								return true;

					case '1':	## Account mail not verified

								$this -> setError('ERR_6');
								return false;

					case '2':	## Account locked (fail login limit reached)

								$this -> setError('ERR_5');
								return false;

					case '3':	## Account locked by administrator

								$this -> setError('ERR_8');
								return false;

					default:	## Unclear account state

								$this -> setError('ERR_7');
								return false;
				}
			}

		}

		##	If the login of the user account is valid, the function returns a result before we reach that part of the code
		##	Because we are here now, that means the login failed

		$_loginFailCount++;

		foreach($_loginObject['db_name'] as $_dbName)
		{
			$_db 	= CSQLConnect::instance() -> getConnection($_dbName);

			if($_loginFailCount > $this -> m_iFailLimit)
			{	##	Login limit reached -> lock account, update session for login kill
				
				$_sqlString			=	"	SELECT		". $_loginObject['table'] .".user_mail,
														". $_loginObject['table'] .".data_id
											FROM		". $_loginObject['table'] ."
											WHERE		". implode(' AND ',$_cryptUsername) ."
										";

				$_sqlLoginFailRes	=	$_db -> query($_sqlString);	

				if($_sqlLoginFailRes === false || $_sqlLoginFailRes -> num_rows === 0)
				{	##	Found not users with this username
					##	We do nothing at the point because the user could be in the next database
				}
				else
				{	##	Found users with this username, locking all of them

					while($_sqlLoginFail = $_sqlLoginFailRes -> fetch_assoc())
					{
						$_sqlString			=	"	UPDATE		". $_loginObject['table'] ."
													SET			". $_loginObject['table'] .".is_locked	= '2'
													WHERE		". $_loginObject['table'] .".data_id	= ". $_sqlLoginFail['data_id'] ."
												";

						$_db -> query($_sqlString);	

						CSysMailer::instance() -> sendMail( CLanguage::instance() -> getString('SYSMAIL_ACCLOCKED_SUBJ') , CLanguage::instance() -> getStringExt('SYSMAIL_ACCLOCKED_TEXT', ['[TIMESTAMP]' => date(TIME_FORMAT_SYSMAIL), '[DATABASE]' => $_dbName, '[DATAID]' => $_sqlLoginFail['data_id'], '[REMOTE]' => $_SERVER['REMOTE_ADDR']]) , true , 'loginfail-userid-'. $_sqlLoginFail['data_id'] );
					}
				}

				$_pSession -> setSessionValue('LOGIN_FAIL_COUNT', $_loginFailCount, true);
			}
			else
			{	##	Login limit not reached -> update session
				$_pSession -> setSessionValue('LOGIN_FAIL_COUNT', $_loginFailCount, true);
			}
		}
	}

	public	function
	logout(string $_loginObjectName)
	{
		if(!$this -> m_bInitialized)
		{	##	Class not initialized
			$this -> setError('ERR_4');
			return false;
		}

		if(!isset($this -> m_aStorage[$_loginObjectName]))
		{	##	Login object unknown
			$this -> setError('ERR_2');
			return false;
		}

		$_loginObject 	= &$this -> m_aStorage[$_loginObjectName]['ob_name'];
		$_authObject	= CSession::instance() -> getSessionValue('IS_AUTH_OBJECT', $_loginObject);

		if($_authObject !== false)
		{	
			$_db 			= 	&$_authObject['db'];			
			$_sqlString		=	"	SELECT		". $_authObject['table'] .".cookie_id
									FROM		". $_authObject['table'] ."
									WHERE		". $_authObject['table'] .".data_id		= '". $_authObject['data_id'] ."'
									LIMIT		1
								";
			$_sqlLogoutRes	=	$_db -> query($_sqlString) ;	

			if($_sqlLogoutRes !== false && $_sqlLogoutRes -> num_rows > 0)		
			{
				$_sqlLogout 	=	$_sqlLogoutRes -> fetch_assoc();

				$_existsCookies =	json_decode($_sqlLogout['cookie_id'],true);
				unset($_existsCookies[$_loginObject]);
				$_existsCookies	=	json_encode($_existsCookies, JSON_FORCE_OBJECT);

				$_sqlString		=	"	UPDATE		". $_authObject['table'] ."
										SET			". $_authObject['table'] .".cookie_id	= '". $_db -> real_escape_string($_existsCookies) ."'
										WHERE		". $_authObject['table'] .".data_id		= '". $_authObject['data_id'] ."'
									";

				$_db -> query($_sqlString);	
			}	

			CCookie::instance() -> deleteCookie( $_loginObjectName );
		}
	}

	public	function
	setError($_errorCode)
	{	
		$_pMessages = CMessages::instance();
		$_pLanguage	= CLanguage::instance();

		switch($_errorCode)
		{
			case 'ERR_1':	##	No valid login objects
							$_pMessages -> addMessage( $_pLanguage -> getString('ERR_CR_LOGIN_1') , MSG_LOG , '' , true );
							return;

			case 'ERR_2':	##	Login object does not exists
							$_pMessages -> addMessage( $_pLanguage -> getString('ERR_CR_LOGIN_2') , MSG_LOG , '' , true );
							return;

			case 'ERR_3':	##	Requested field name does not exists on transmitted form data
							$_pMessages -> addMessage( $_pLanguage -> getString('ERR_CR_LOGIN_3') , MSG_LOG , '' , true );
							return;

			case 'ERR_4':	##	Class not initialized
							$_pMessages -> addMessage( $_pLanguage -> getString('ERR_CR_LOGIN_4') , MSG_LOG , '' , true );
							return;

			case 'ERR_5':	##	Login failed on locked account due to login fail limit reached
							$_pMessages -> addMessage( $_pLanguage -> getString('ERR_CR_LOGIN_5') , MSG_LOG , '' , true );
							return;

			case 'ERR_6':	##	Login failed on not verified mail address
							$_pMessages -> addMessage( $_pLanguage -> getString('ERR_CR_LOGIN_6') , MSG_LOG , '' , true );
							return;

			case 'ERR_7':	##	Login failed on unclear account state
							$_pMessages -> addMessage( $_pLanguage -> getString('ERR_CR_LOGIN_7') , MSG_LOG , '' , true );
							return;

			case 'ERR_8':	##	Login failed on locked account by adminitrator
							$_pMessages -> addMessage( $_pLanguage -> getString('ERR_CR_LOGIN_8') , MSG_LOG , '' , true );
							return;

			case 'ERR_9':	##	Missing parameters on initializing
							$_pMessages -> addMessage( $_pLanguage -> getString('ERR_CR_LOGIN_9') , MSG_LOG , '' , true );
							return;
		}
	}

}



?>