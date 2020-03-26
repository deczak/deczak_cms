<?php

require_once 'CSingleton.php';
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelLoginObjects.php';

class	CLogin extends CSingleton
{
	public	function
	login(&$_sqlConnection, string $_loginObjectName)
	{

		
		$modelCondition = new CModelCondition();
		$modelCondition -> where('object_id', $_loginObjectName);
		
		## get login objects

		$_pModelLoginObjects	 =	new modelLoginObjects();
		$_pModelLoginObjects	->	load($_sqlConnection, $modelCondition);	

		$_loginObjects			 = 	$_pModelLoginObjects -> getDataInstance();

		## check if requested login object exists 

		$_foundObject = false;
		foreach($_loginObjects as $_objectIndex => $_object)
		{
			if($_object -> object_id === $_loginObjectName)
			{
				$_loginObject = &$_loginObjects[$_objectIndex];
				$_foundObject = true;
				break;
			}
		}	

		if(!$_foundObject)
		{
			$this -> setError('ERR_CR_LOGIN_2');
			return false;
		}

		## get Session instance and check login fails count

		$_pSession		 	= CSession::instance();
		$_loginFailCount	= $_pSession -> getValue('login_fail_count');

		if($_loginFailCount > CFG::GET() -> LOGIN -> FAIL_LIMIT)
		{	##	Login limit reached
			$this -> setError('ERR_CR_LOGIN_5');
			return false;
		}

		##

		$_bLoginResult	= false;
		#$_columnsValue	= [];
		$_cryptUsername	= [];
		
		$_loginObject -> object_fields 		= json_decode($_loginObject -> object_fields);
		$_loginObject -> object_databases 	= json_decode($_loginObject -> object_databases);

		## retrieve form data
		$_pLoginVariables	 =	new CURLVariables();

		foreach($_loginObject -> object_fields as $_field)
		{
			switch($_field -> query_type)
			{
				case 'compare': $_aLoginReqStruct[]  = 	[	"input" => $_field -> name, "validate" => "strip_tags|!empty" ]; break;

				case 'assign' : $_aLoginReqStruct[]  = 	[	"input" => $_field -> formValue, "validate" => "strip_tags|!empty" ]; break;
			}
			
		}

		if(!$_pLoginVariables -> retrieve($_aLoginReqStruct, false, true, true))
		{	## one or more fields failed on validation
			return false;
		}

		$_formData		 = $_pLoginVariables ->getArray();
		















		##	processing field value

		$whereColumns 		= []; 
		$iValidationFields 	= 0;
		$iValidationSuccess	= 0;
		$userTables			= ['tb_users','tb_users_backend'];
		$userTable			= NULL;
		$userTableResult	= NULL;

		foreach($_loginObject -> object_fields as $field)
		{


			switch($field -> query_type)
			{
				case 'compare'	:

								if(empty($_formData[ $field -> name ]))
								{
									$this -> setError('ERR_CR_LOGIN_3');
									return $_bLoginResult;
								}

								switch($field -> data_prc)
								{
									case 'plain':	##	No kind of processing
													#$procValue = $_formData[ $field -> name ];						
													break;

									case 'crypt':	##	Encrypt form data for validation
													$_formData[ $field -> name ] = CRYPT::LOGIN_HASH($_formData[ $field -> name ]);						
													break;

									case 'hash':	##	Hash form data for validation
													$_formData[ $field -> name ] = CRYPT::LOGIN_CRYPT($_formData[ $field -> name ], CFG::GET() -> ENCRYPTION -> BASEKEY);	
													break;
								}
								
								break;

			}
		}

		##	auth check


		$whereBuffer = $whereColumns;

		foreach($_loginObject -> object_databases as $_dbName)
		{

			##	check if db is not primary but remote system is disabled
			if(		CFG::GET() -> MYSQL -> PRIMARY_DATABASE !== $_dbName 
				&& !CFG::GET() -> USER_SYSTEM -> REMOTE_USER -> ENABLED
			  )	continue;
	
			$_db 	= CSQLConnect::instance() -> getConnection($_dbName);

			if(CFG::GET() -> MYSQL -> PRIMARY_DATABASE !== $_dbName)
				$whereColumns[] = $_loginObject -> object_table .".allow_remote = '1' ";

			foreach($_loginObject -> object_fields as $field)
			{

				$iValidationFields++;

				switch($field -> query_type)
				{
					case 	'compare':	
				

							$whereColumns = [];
							$whereColumns[] = $field -> name ." = '". $_db -> real_escape_string($_formData[ $field -> name ]) ."'";

							if(isset($field -> is_username) && $field -> is_username === '1') 
								$_cryptUsername[] = $field -> name ." = '". $_formData[ $field -> name ] ."'";	

							$_sqlString			=	"	SELECT		". $field -> table .".is_locked,
																	". $field -> table .".login_count,
																	". $field -> table .".user_id,
																	". $field -> table .".data_id,
																	". $field -> table .".cookie_id
														FROM		". $field -> table ."
														WHERE		". implode(' AND ', $whereColumns) ."
														LIMIT		1
													";

							$_sqlLoginChkRes		=	$_db -> query($_sqlString);	


							if($_sqlLoginChkRes !== false && $_sqlLoginChkRes -> num_rows === 1)
							{	

								if(in_array($field -> table, $userTables, true) && $userTable === NULL)
								{
									$userTable 			= $field -> table;
									$userTableResult 	= $_sqlLoginChkRes -> fetch_assoc();
								}

								$iValidationSuccess++;
							}

							break;
				}
			}
		}

		##	Auth validation for assign check, we need the user_id, this is why we do this in a separated run

		foreach($_loginObject -> object_databases as $_dbName)
		{
			##	check if db is not primary but remote system is disabled
			if(		CFG::GET() -> MYSQL -> PRIMARY_DATABASE !== $_dbName 
				&& !CFG::GET() -> USER_SYSTEM -> REMOTE_USER -> ENABLED
			  )	continue;
	
			$_db 	= CSQLConnect::instance() -> getConnection($_dbName);

			if(CFG::GET() -> MYSQL -> PRIMARY_DATABASE !== $_dbName)
				$whereColumns[] = $_loginObject -> object_table .".allow_remote = '1' ";

			foreach($_loginObject -> object_fields as $field)
			{

				switch($field -> query_type)
				{
					case 	'assign':

							if(!empty($userTableResult['user_id']))
							{
								$sqlString		=	"	SELECT		". $field -> assignTable .".". $field -> assignColumn ."
														FROM		". $field -> assignTable ."
														WHERE		". $field -> assignTable .".". $field -> assignColumn ." = '". $_db -> real_escape_string($_formData[ $field -> formValue ]) ."'
															AND 	". $field -> assignTable .".user_id	= '". $userTableResult['user_id'] ."'
													";

								$_sqlAssignRes	=	$_db -> query($sqlString);	

								if($_sqlAssignRes !== false && $_sqlAssignRes -> num_rows == 1)
								{
									$iValidationSuccess++;
								}
							}

							break;
				}
			}
		}

		if($iValidationFields === $iValidationSuccess)
		{
			switch($userTableResult['is_locked'])
			{
				case '0':	## OK

							$_timeStamp 	=	time();
							$_cookieID		=	CCookie::instance() -> createCookieIdinator( $_loginObjectName , $_timeStamp , $_pSession -> getValue('session_id') );
							$_existsCookies =	json_decode($userTableResult['cookie_id'],true);
							$_existsCookies[$_loginObjectName]['id'] = $_cookieID;
							$_existsCookies	=	json_encode($_existsCookies, JSON_FORCE_OBJECT);

							$_sqlString		=	"	UPDATE		$userTable
													SET			$userTable.login_count	= '". ($userTableResult['login_count'] + 1) ."',
																$userTable.time_login	= '". $_timeStamp ."',
																$userTable.cookie_id	= '". $_db -> real_escape_string($_existsCookies) ."'
													WHERE		$userTable.data_id		= '". $userTableResult['data_id'] ."'
												";

							$_db -> query($_sqlString);	
							$_pSession -> setValue('login_fail_count', 0, true);


							return true;

				case '1':	## Account mail not verified

							$this -> setError('ERR_CR_LOGIN_6');
							return false;

				case '2':	## Account locked (fail login limit reached)

							$this -> setError('ERR_CR_LOGIN_5');
							return false;

				case '3':	## Account locked by administrator

							$this -> setError('ERR_CR_LOGIN_8');
							return false;

				default:	## Unclear account state

							$this -> setError('ERR_CR_LOGIN_7');
							return false;
			}
		}


























		/*

		## data proc for field data
		foreach($_loginObject -> object_fields as $_field)
		{
			if(empty($_formData[ $_field -> name ]))
			{
				$this -> setError('ERR_CR_LOGIN_3');
				return $_bLoginResult;
			}

			// on login field processing, crypt and hash are both one way hash types

			switch($_field -> data_prc)
			{
				case 'plain':	##	No kind of processing
								$_generatedValue = $_formData[ $_field -> name ];						
								break;

				case 'crypt':	##	Encrypt form data for validation
								$_generatedValue = CRYPT::LOGIN_HASH($_formData[ $_field -> name ]);						
								break;

				case 'hash':	##	Hash form data for validation
				default:
								$_generatedValue = CRYPT::LOGIN_CRYPT($_formData[ $_field -> name ], CFG::GET() -> ENCRYPTION -> BASEKEY);					
				
			}

			$_columnsValue[] = $_loginObject -> object_table .'.'. $_field -> name ." = '". $_generatedValue."'";
			if(isset($_field -> is_username) && $_field -> is_username === '1') $_cryptUsername[] = $_loginObject -> object_table .'.'. $_field -> name ." = '". $_generatedValue."'";	
		}		


	
		##	Looping through the databases

		$_loginObject -> object_databases = json_decode($_loginObject -> object_databases);

		foreach($_loginObject -> object_databases as $_dbName)
		{
			if(		CFG::GET() -> MYSQL -> PRIMARY_DATABASE !== $_dbName 
				&& !CFG::GET() -> USER_SYSTEM -> REMOTE_USER -> ENABLED
			  )	continue;


			$_db 	= CSQLConnect::instance() -> getConnection($_dbName);

			if(CFG::GET() -> MYSQL -> PRIMARY_DATABASE !== $_dbName)
				$_columnsValue[] = $_loginObject -> object_table .".allow_remote = '1' ";
				
			$_sqlString			=	"	SELECT		". $_loginObject -> object_table .".is_locked,
													". $_loginObject -> object_table .".login_count,
													". $_loginObject -> object_table .".data_id,
													". $_loginObject -> object_table .".cookie_id
										FROM		". $_loginObject -> object_table ."
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
								$_cookieID		=	CCookie::instance() -> createCookieIdinator( $_loginObjectName , $_timeStamp , $_pSession -> getValue('session_id') );
								$_existsCookies =	json_decode($_sqlLoginChk['cookie_id'],true);
								$_existsCookies[$_loginObjectName]['id'] = $_cookieID;
								$_existsCookies	=	json_encode($_existsCookies, JSON_FORCE_OBJECT);

								$_sqlString		=	"	UPDATE		". $_loginObject -> object_table ."
														SET			". $_loginObject -> object_table .".login_count	= '". ($_sqlLoginChk['login_count'] + 1) ."',
																	". $_loginObject -> object_table .".time_login	= '". $_timeStamp ."',
																	". $_loginObject -> object_table .".cookie_id	= '". $_db -> real_escape_string($_existsCookies) ."'
														WHERE		". $_loginObject -> object_table .".data_id		= '". $_sqlLoginChk['data_id'] ."'
													";

								$_db -> query($_sqlString);	
								$_pSession -> setValue('login_fail_count', 0, true);

								return true;

					case '1':	## Account mail not verified

								$this -> setError('ERR_CR_LOGIN_6');
								return false;

					case '2':	## Account locked (fail login limit reached)

								$this -> setError('ERR_CR_LOGIN_5');
								return false;

					case '3':	## Account locked by administrator

								$this -> setError('ERR_CR_LOGIN_8');
								return false;

					default:	## Unclear account state

								$this -> setError('ERR_CR_LOGIN_7');
								return false;
				}
			}

		}

		*/

































		##	If the login of the user account is valid, the function returns a result before we reach that part of the code.
		##	Because we are here now, that means the login failed

		$_loginFailCount++;

		foreach($_loginObject -> object_databases as $_dbName)
		{
			$_db 	= CSQLConnect::instance() -> getConnection($_dbName);

			if($_loginFailCount > CFG::GET() -> LOGIN -> FAIL_LIMIT)
			{	##	Login limit reached -> lock account, update session for login kill
				
				$_sqlString			=	"	SELECT		$userTable.user_mail,
														$userTable.data_id
											FROM		$userTable
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
						$_sqlString			=	"	UPDATE		$userTable
													SET			$userTable.is_locked	= '2'
													WHERE		$userTable.data_id	= ". $_sqlLoginFail['data_id'] ."
												";

						$_db -> query($_sqlString);	

						CSysMailer::instance() -> sendMail( CLanguage::instance() -> getString('SYSMAIL_ACCLOCKED_SUBJ') , CLanguage::instance() -> getStringExt('SYSMAIL_ACCLOCKED_TEXT', ['[TIMESTAMP]' => date(TIME_FORMAT_SYSMAIL), '[DATABASE]' => $_dbName, '[DATAID]' => $_sqlLoginFail['data_id'], '[REMOTE]' => $_SERVER['REMOTE_ADDR']]) , true , 'loginfail-userid-'. $_sqlLoginFail['data_id'] );
					}
				}

				$_pSession -> setValue('login_fail_count', $_loginFailCount, true);
			}
			else
			{	##	Login limit not reached -> update session
				$_pSession -> setValue('login_fail_count', $_loginFailCount, true);
			}
		}
	}

	public	function
	logout(&$_sqlConnection, string $_loginObjectName)
	{

			$modelCondition = new CModelCondition();
			$modelCondition -> where('object_id', $_loginObjectName);
			
		## get login objects
		$_pModelLoginObjects	 =	new modelLoginObjects();
		$_pModelLoginObjects	->	load($_sqlConnection, $modelCondition);	

		$_loginObjects			 = 	$_pModelLoginObjects -> getDataInstance();

		if(count($_loginObjects) == 0)
		{	##	Login object unknown
			$this -> setError('ERR_CR_LOGIN_2');
			return false;
		}
		
		$_authObject	= CSession::instance() -> isAuthed($_loginObjectName);

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
				unset($_existsCookies[$_loginObjectName]);
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
		CMessages::instance()-> addMessage( CLanguage::get() -> string($_errorCode) , MSG_LOG , '' , true );
	}

}



?>