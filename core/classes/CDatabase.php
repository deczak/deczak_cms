<?php

require_once 'CSingleton.php';
require_once 'CDatabaseConnection.php';

class	CDatabase extends CSingleton
{
	private array	$m_connectionsList;
	private 		$m_activeConnection;
	private 		$m_activeConnectionName;

	public function
	__construct()
	{
		$this->m_connectionsList 		= [];
		$this->m_activeConnection		= null;
		$this->m_activeConnectionName	= null;
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
				CMessages::add('CDatabase::connect - Required parameters for connection establishment not valid', MSG_LOG, '', true);				  
				return false;
			}

			try
			{
				$connection = 	new PDO(	'mysql:host='. $_accessData['server'] .';dbname='. $_accessData['database'] .';charset=utf8mb4', 
											$_accessData['user'], 
											$_accessData['password'],
											[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
										);

				$this->m_connectionsList[$_accessData['name']] = new CDatabaseConnection($connection, $_accessData['database']);
			}
			catch(PDOException $exception)
			{	  
				CMessages::add('CDatabase::connect - establishing connection failed for '. $_accessData['name'] .'. Exception: '. $exception->getMessage(), MSG_LOG, '', true);
				return false;	
			}
		}
		return true;
	}

	public function
	&getConnection(string $_connectionName) : ?CDatabaseConnection
	{
		if(isset($this->m_connectionsList[$_connectionName]))
		{
			return $this->m_connectionsList[$_connectionName];
		}
		return null;
	}

	public static function
	setActiveConnectionName(string $_connectionName) : bool
	{
		$instance  = static::instance();
		$instance->m_activeConnection = $instance->getConnection($_connectionName);
		if($instance->m_activeConnection === null)
			return false;

		$instance->m_activeConnectionName = $_connectionName;
		return true;
	}

	public static function
	getActiveConnectionName() : ?string
	{
		$instance  = static::instance();
		return $instance->m_activeConnectionName;
	}

	public static function
	query(int $_queryType) : ?CDatabaseQuery
	{
		$instance  = static::instance();
		if($instance->m_activeConnection === null)
			return null;
		return $instance->m_activeConnection->query($_queryType);
	}
}