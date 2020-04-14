<?php

require_once 'CSingleton.php';

class	CSQLConnect extends CSingleton
{
	private	$m_aSQLConnections;
	private $m_bFalse;

	public function
	initialize()
	{
		$this -> m_aSQLConnections	= false;
		$this -> m_bFalse	= false;
	}

	/**
	 *	Creates a connection to MySQL Server
	 * 
	 * 	@return boolean		Returns boolean true if it was successful, otherwise false
	 */
	public function
	createConnection()
	{
		foreach(CFG::GET() -> MYSQL -> DATABASE as $_accessData)
		{
			if(		!is_array($_accessData)
				||	!isset($_accessData['server'])
				||	!isset($_accessData['database'])
				||	!isset($_accessData['user'])
				||	!isset($_accessData['password'])
				||	!isset($_accessData['name'])
			  )
			{
			#	CMessages::instance() -> addMessage(CLanguage::instance() -> getStringExt( 'ERR_CR_DBPARAMS' ), MSG_LOG, '', true);				  
				CMessages::instance() -> addMessage('Required parameters for connection establishment not valid', MSG_LOG, '', true);				  
				return false;
			}

			@$this -> m_aSQLConnections[$_accessData['name']] = new mysqli( $_accessData["server"] , $_accessData["user"] , $_accessData["password"] , $_accessData["database"] );

			if( $this -> m_aSQLConnections[$_accessData['name']] -> connect_errno )
			{
			#	CMessages::instance() 	-> addMessage(CLanguage::instance() -> getStringExt( 'ERR_CR_DBFAILED' , ['[DBERROR]' => $this -> m_aSQLConnections[$_accessData['name']] -> connect_error] ), MSG_LOG, '', true);
				CMessages::instance() 	-> addMessage('Database connection error: '. $this -> m_aSQLConnections[$_accessData['name']] -> connect_error, MSG_LOG, '', true);
			#	CSysMailer::instance() 	-> sendMail(CLanguage::instance() -> getString('SYSMAIL_DB_FAILED_SUBJ'), CLanguage::instance() -> getStringExt('SYSMAIL_DB_FAILED_SUBJ',['[TIMESTAMP]' => date(CFG::GET() -> SYSTEM_MAILER -> MAIL_TIME_FORMAT,time())]), true, 'sql-connection');
				CSysMailer::instance() 	-> sendMail('Database connection error', 'Error while attempting to connect to the database on '. date(CFG::GET() -> SYSTEM_MAILER -> MAIL_TIME_FORMAT,time()), true, 'sql-connection');

				$this -> m_aSQLConnections[$_accessData['name']] = false;
				return false;
			}
			
			$this -> m_aSQLConnections[$_accessData['name']] -> query("SET NAMES 'utf8mb4'");
		}

		return true;
	}

	/**
	 *	Returns the connection if exists
	 *	
	 * 	@return object/boolean		Returns the valid connection, otherwise false
	 */
	public function
	&getConnection(string $_connectionName = '')
	{
		if($this -> m_aSQLConnections === NULL) return $this -> m_bFalse;
		if(strlen($_connectionName) === 0)
		{
			$_SQLKeys 		= array_keys($this -> m_aSQLConnections);
			$_SQLConnection = &$this -> m_aSQLConnections[$_SQLKeys[0]];
			if($_SQLConnection === NULL) return $this -> m_bFalse;
			if($_SQLConnection !== false) return $_SQLConnection;	
		}
		else if(isset($this -> m_aSQLConnections[$_connectionName]))
		{
			return $this -> m_aSQLConnections[$_connectionName];
		}
		return $this -> m_bFalse;
	}
}
?>