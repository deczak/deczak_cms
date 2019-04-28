<?php

class	CSQLConnect
{
	public	$m_hSQLConnection;

	public function
	__construct()
	{
		$this -> m_hSQLConnection	= false;
	}

	/**
	 *	Creates a connection to MySQL Server
	 * 
	 * 	@return boolean		Returns boolean true if it was successful, otherwise false
	 */
	public function
	createConnection(array $_accessData)
	{
		if(		!isset($_accessData['server'])
			||	!isset($_accessData['database'])
			||	!isset($_accessData['user'])
			||	!isset($_accessData['password'])
		  ) return false;

		@ $this -> m_hSQLConnection = new mysqli( $_accessData["server"] , $_accessData["user"] , $_accessData["password"] , $_accessData["database"] );

		if( $this -> m_hSQLConnection -> connect_errno )
		{
			return false;
		}
		
		$this -> m_hSQLConnection -> query("SET NAMES 'utf8mb4'");
		return true;
	}

	/**
	 *	Returns the connection if exists
	 *	
	 * 	@return object/boolean		Returns the valid connection, otherwise false
	 */
	public function
	&getConnection()
	{
		return $this -> m_hSQLConnection;
	}

	/**
	 *	Returns the error message if exists
	 *	
	 * 	@return string		MySQL error message, otherwise OK
	 */
	public function
	getErrorMsg()
	{
		if( $this -> m_hSQLConnection !== false && $this -> m_hSQLConnection -> connect_errno )
		{
			return $this -> m_hSQLConnection -> connect_error;
		}
		return 'OK';
	}
}
?>