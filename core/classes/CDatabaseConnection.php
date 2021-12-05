<?php

require_once 'CDatabaseQuery.php';

class CDatabaseConnection
{
	public PDO 		$m_connection;
	public string	$m_databaseName;

	public function
	__construct(PDO $_pdoConnection, string $_databaseName)
	{
		$this->m_connection 	= $_pdoConnection;
		$this->m_databaseName	= $_databaseName;
	}

	public function
	getDatabaseName() : string
	{
		return $this->m_databaseName;
	}

	public function
	query(int $_queryType) : ?CDatabaseQuery
	{
		if($this->m_connection === NULL)
			return null;
		return new CDatabaseQuery($this, $_queryType);
	}

	public function
	&getConnection() : ?PDO
	{
		return $this->m_connection;
	}

	public function
	beginTransaction() : ?bool
	{
		if($this->m_connection === NULL)
			return null;
		return $this->m_connection->beginTransaction();
	}

	public function
	rollBack() : ?bool
	{
		if($this->m_connection === NULL)
			return null;
		return $this->m_connection->rollBack();
	}

	public function
	commit() : ?bool
	{
		if($this->m_connection === NULL)
			return null;
		return $this->m_connection->commit();
	}
}