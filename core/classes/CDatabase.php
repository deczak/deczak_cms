<?php

require_once 'CSingleton.php';
require_once 'CDatabaseQuery.php';

class CDatabaseConnection
{
	public PDO 		$m_connection;
	public string	$m_databaseName;

	public function
	__construct(PDO $_pdoConnection, string $_databaseName)
	{
		$this -> m_connection 	= $_pdoConnection;
		$this -> m_databaseName	= $_databaseName;
	}

	public function
	getDatabaseName() : string
	{
		return $this -> m_databaseName;
	}

	public function
	query(int $_queryType) : ?CDatabaseQuery
	{
		if($this -> m_connection === NULL)
			return null;
		return new CDatabaseQuery($this, $_queryType);
	}

	public function
	&getConnection() : ?PDO
	{
		return $this -> m_connection;
	}

	public function
	beginTransaction() : ?bool
	{
		if($this -> m_connection === NULL)
			return null;
		return $this -> m_connection -> beginTransaction();
	}

	public function
	rollBack() : ?bool
	{
		if($this -> m_connection === NULL)
			return null;
		return $this -> m_connection -> rollBack();
	}

	public function
	commit() : ?bool
	{
		if($this -> m_connection === NULL)
			return null;
		return $this -> m_connection -> commit();
	}
}

class	CDatabase extends CSingleton
{
	private array	$m_connectionsList;
	private 		$m_null;

	public function
	__construct()
	{
		$this -> m_connectionsList 	= [];
		$this -> m_null 			= null;
	}

	public function
	connect(array $_databases) : bool
	{
		foreach($_databases as $_accessData)
		{
			if(		!is_array($_accessData)
				||	!isset($_accessData['server'])
				||	!isset($_accessData['database'])
				||	!isset($_accessData['user'])
				||	!isset($_accessData['password'])
				||	!isset($_accessData['name'])
			  )
			{			  
				CMessages::instance() -> addMessage('CDatabase::connect - Required parameters for connection establishment not valid', MSG_LOG, '', true);				  
				return false;
			}

			try
			{
				$connection = 	new PDO(	'mysql:host='. $_accessData['server'] .';dbname='. $_accessData['database'] .';charset=utf8mb4', 
											$_accessData['user'], 
											$_accessData['password'],
											[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
										);

				$this -> m_connectionsList[$_accessData['name']] = new CDatabaseConnection($connection, $_accessData['database']);
			}
			catch(PDOException $exception)
			{	  
				CMessages::instance() -> addMessage('CDatabase::connect - establishing connection failed for '. $_accessData['name'] .'. Exception: '. $exception -> getMessage(), MSG_LOG, '', true);
				return false;	
			}
		}
		return true;
	}

	public function
	&getConnection($_connectionName) : ?CDatabaseConnection
	{
		if(isset($this -> m_connectionsList[$_connectionName]))
		{
			return $this -> m_connectionsList[$_connectionName];
		}
		return $this -> m_null;
	}
}