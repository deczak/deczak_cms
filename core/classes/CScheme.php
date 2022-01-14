<?php

class 	CSchemeConstraints
{
	public $m_name;
	public $m_key;
	public $m_refTable;
	public $m_refColumn;
	public $m_onDelete;
	public $m_onUpdate;

	public function
	__construct($_constraintName, $_keyName, $_refTable, $_refColumn, $_onDelete, $_onUpdate)
	{
		$this -> m_name			= $_constraintName;
		$this -> m_key			= $_keyName;
		$this -> m_refTable		= $_refTable;
		$this -> m_refColumn	= $_refColumn;
		$this -> m_onDelete		= $_onDelete;
		$this -> m_onUpdate		= $_onUpdate;
	}
}

class	CSchemeColumn
{
	public	$m_columnName;
	public	$m_columnType;
	public	$m_length;
	public	$m_attribute;
	public	$m_isVirtual;
	public	$m_isAutoIncrement;
	public	$m_defaultValue;
	public	$m_isNull;
	public	$m_keyType;
	public	$m_indexType;
	public	$m_isSystemId;

	public function
	__construct($_columnName, $_columnType)
	{
		$this -> m_columnName 		= $_columnName;
		$this -> m_columnType 		= $_columnType;
		$this -> m_isVirtual		= false;
		$this -> m_attribute		= false;


		$this -> m_isAutoIncrement 	= false;
		$this -> m_defaultValue 	= NULL;
		$this -> m_isNull		 	= false;
		$this -> m_length			= 0;
		$this -> m_isSystemId		= false;
	}

	public function
	setLength(int $_length)
	{
		$this -> m_length = $_length;
		return $this;
	}

	public function
	setAttribute($_attribute)
	{
		$this -> m_attribute = $_attribute;
		return $this;
	}

	public function
	setVirtual(bool $_isVirtual = true)
	{
		$this -> m_isVirtual = $_isVirtual;
		return $this;
	}

	public function
	setAutoIncrement()
	{
		$this -> m_isAutoIncrement = true;
		return $this;
	}

	public function
	setDefault($_default)
	{
		if($_default === 'NULL')
			$this -> m_isNull 		= true;
		else
			$this -> m_defaultValue = $_default;

		return $this;
	}

	public function
	setIndex(string $_type)
	{
		$this -> m_indexType = $_type;
		return $this;
	}

	public function
	setKey(string $_type)
	{
		$this -> m_keyType = strtoupper($_type);

		return $this;
	}

	public function
	setSystemId()
	{
		$this -> m_isSystemId = true;
	}

}

define('DB_COLUMN_TYPE_STRING',0x1);
define('DB_COLUMN_TYPE_TEXT',0x2);
define('DB_COLUMN_TYPE_INT',0x3);
define('DB_COLUMN_TYPE_FLOAT',0x4);
define('DB_COLUMN_TYPE_DECIMAL',0x5);
define('DB_COLUMN_TYPE_JSON',0x6);
define('DB_COLUMN_TYPE_BOOL',0x7);
define('DB_COLUMN_TYPE_BIGINT',0x8);
define('DB_COLUMN_TYPE_MEDIUMINT',0x9);
define('DB_COLUMN_TYPE_TINYINT',0x10);
define('DB_COLUMN_TYPE_ARRAY',0x11);

define('DB_COLUMN_ATTR_UNSIGNED',0x1);

define('DB_COLUMN_GROUP_LOCK',0x1);
define('DB_COLUMN_GROUP_UPDATE',0x2);
define('DB_COLUMN_GROUP_CREATE',0x3);

class	CScheme
{
	private	$m_tableName;
	private	$m_isVirtual;
	private	$m_collate;
	private	$m_charset;
	private	$m_tableEngine;
	private	$m_columnsList;
	private	$m_constraintsList;
	private	$m_seedList;

	public function
	__construct(string $_tableName, bool $_isVirtual = false)
	{
		$this -> m_tableName 		= $_tableName;
		$this -> m_isVirtual		= $_isVirtual;

		$this -> m_collate 			= "utf8mb4_unicode_ci"; #CFG::GET() -> MYSQL -> TABLE_COLLATE;
		$this -> m_charset 			= "utf8mb4"; #CFG::GET() -> MYSQL -> TABLE_CHARSET;
		$this -> m_tableEngine 		= "innoDB"; #CFG::GET() -> MYSQL -> TABLE_ENGINE;

		$this -> m_columnsList		= [];
		$this -> m_constraintsList	= [];
		$this -> m_seedList			= [];
	}

	public function
	__get($name)
	{
		return $this -> $name; 
	}

	public function
	getTableName() : string
	{
		return $this -> m_tableName;
	}

	public function
	setTableName(string $_tableName)
	{
		$this -> m_tableName = $_tableName;
	}

	public function
	getColumns()
	{
		return $this -> m_columnsList;
	}

	public function
	getSystemIdColumnName()
	{
		foreach($this -> m_columnsList as $column)
		{
			if($column -> m_isSystemId)
				return $column -> m_columnName;
		}
		return NULL;
	}

	public function
	&addColumn($_columnName, $_columnType)
	{
		$columnIndex = count($this -> m_columnsList);
		$this -> m_columnsList[$columnIndex] = new CSchemeColumn($_columnName, $_columnType);
		return $this -> m_columnsList[$columnIndex];
	}

	/**
	 * 	Add pre defined columns
	 */
	public function
	addColumnGroup(int $_columnGroupType)
	{
		switch($_columnGroupType)
		{
			case DB_COLUMN_GROUP_CREATE:

				$this -> addColumn('create_time', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
				$this -> addColumn('create_by', DB_COLUMN_TYPE_STRING) -> setLength(25);
				break;

			case DB_COLUMN_GROUP_UPDATE:

				$this -> addColumn('update_time', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
				$this -> addColumn('update_by', DB_COLUMN_TYPE_STRING) -> setLength(25) -> setDefault('NULL');
				break;


			case DB_COLUMN_GROUP_LOCK:

				$this -> addColumn('lock_time', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
				$this -> addColumn('lock_by', DB_COLUMN_TYPE_STRING) -> setLength(25) -> setDefault('0');
				$this -> addColumn('lock_id', DB_COLUMN_TYPE_STRING) -> setLength(40) -> setDefault('0');
				break;
		}
	}
	
	protected function
	addConstraint($_constraintName, $_keyName, $_refTable, $_refColumn, $_onDelete, $_onUpdate)
	{
		$this -> m_constraintsList[] = new CSchemeConstraints($_constraintName, $_keyName, $_refTable, $_refColumn, $_onDelete, $_onUpdate);
	}

	public function
	existsColumn(string $_columnName, bool $_excludeVirtual = true)
	{
		foreach($this -> m_columnsList as $column)
		{
			if($_excludeVirtual && $column -> m_columnName === $_columnName && $column -> m_isVirtual) return false;
			if($column -> m_columnName === $_columnName) return true;
		}
		return false;
	}

	public function
	existsTable(CDatabaseConnection &$_dbConnection, string $_tablename)
	{
		$dbTableQry = $_dbConnection -> query(DB_DESCRIBE) -> table($_tablename) -> silentException();

		if($dbTableQry -> exec() === false)
			return false;

		return true;
	}

	public function
	createTable(CDatabaseConnection &$_dbConnection)
	{
		if($this -> m_isVirtual === true)
			return true;

		if($this -> existsTable($_dbConnection, $this -> m_tableName))
			return true;

		return $_dbConnection -> query(DB_CREATE) -> scheme($this) -> exec();
	}

	public function
	dropTable(CDatabaseConnection &$_dbConnection)
	{
		if($this -> m_isVirtual === true)
			return true;

		return $_dbConnection -> query(DB_DROP) -> scheme($this) -> exec();
	}

	public function
	createConstraints(CDatabaseConnection &$_dbConnection)
	{
		if($this -> m_isVirtual === true || count($this -> m_constraintsList) == 0)
			return true;

		if(!$this -> existsTable($_dbConnection, $this -> m_tableName))
			return false;
	
		return $_dbConnection -> query(DB_CONSTRAINTS) -> scheme($this) -> exec();
	}

	public function
	seed(array $seedItm)
	{
		$this -> m_seedList[] = $seedItm;
	}

	public function 
	getSeedList() : array
	{
		return $this -> m_seedList;
	}

	/**
	 * 	This function checks the table and his columns, it will update the table to the has-to-be-state
	 * 
	 * 	@param CDatabaseConnection $_dbConnection Database Connection object
	 */
	public function
	updateTable(CDatabaseConnection &$_dbConnection) : bool
	{
		cmsLog::add('CScheme::updateTable -- Call');

		if($this -> m_isVirtual === true)
			return true;

		cmsLog::add('CScheme::updateTable -- Update table '. $this -> m_tableName);

		$tableInfoList 	= $_dbConnection -> query(DB_COLUMNS) 
										 -> table($this -> m_tableName)
										 -> exec();

		if(empty($tableInfoList))
		{
			cmsLog::add('CScheme::updateTable -- Table does not exist, create table '. $this -> m_tableName);
			return $_dbConnection -> query(DB_CREATE) -> scheme($this) -> exec();
		}

		$schemeColumnList = $this -> m_columnsList;

		##	Compare tables

		foreach($schemeColumnList as $columnInfoSOLL_KEY => $columnInfoSOLL)
		{
			foreach($tableInfoList as $columnInfoIST_KEY => $columnInfoIST)
			{
				if($columnInfoSOLL -> m_columnName === $columnInfoIST -> COLUMN_NAME)
				{
					// TODO :: Check column settings, update if required

					/*
					[TABLE_CATALOG] => def
					[TABLE_SCHEMA] => ***** db name
					[TABLE_NAME] => tb_modules
					[COLUMN_NAME] => module_id
					[ORDINAL_POSITION] => 1
					[COLUMN_DEFAULT] => 
					[IS_NULLABLE] => NO
					[DATA_TYPE] => int
					[CHARACTER_MAXIMUM_LENGTH] => 
					[CHARACTER_OCTET_LENGTH] => 
					[NUMERIC_PRECISION] => 10
					[NUMERIC_SCALE] => 0
					[DATETIME_PRECISION] => 
					[CHARACTER_SET_NAME] => 
					[COLLATION_NAME] => 
					[COLUMN_TYPE] => int(10) unsigned
					[COLUMN_KEY] => PRI
					[EXTRA] => auto_increment
					[PRIVILEGES] => select,insert,update,references
					[COLUMN_COMMENT] => 
					*/
					
					unset($schemeColumnList[$columnInfoSOLL_KEY]);
					unset($tableInfoList[$columnInfoIST_KEY]);
				}
			}
		}

		##	Add Columns that not exists

		foreach($schemeColumnList as $columnInfoSOLL)
		{
			if($columnInfoSOLL -> m_isVirtual)
				continue;

			cmsLog::add('CScheme::updateTable -- Add column '. $columnInfoSOLL -> m_columnName);

			$execResult = $_dbConnection -> query(DB_ALTER_TABLE_COLUMN_ADD) -> table($this -> m_tableName) -> schemeColumn($columnInfoSOLL) -> exec();

			if($execResult === false)
			{
				// add column failed
				cmsLog::add('CScheme::updateTable -- Add column '. $columnInfoSOLL -> m_columnName .' ... aborted');
				return false;
			}
			else
			{
				// add successful
				cmsLog::add('CScheme::updateTable -- Add column '. $columnInfoSOLL -> m_columnName .' ... successful');
			}
		}

		##	Drop Columns that still exists

		foreach($tableInfoList as $columnInfoIST)
		{
			cmsLog::add('CScheme::updateTable -- Add column '. $columnInfoIST -> COLUMN_NAME);

			$execResult = $_dbConnection -> query(DB_ALTER_TABLE_COLUMN_DROP) 
										 -> table($this -> m_tableName)
										 -> selectColumns([$columnInfoIST -> COLUMN_NAME]) // Only one column per call
										 -> exec();

			if($execResult === false)
			{
				// drop column failed
				cmsLog::add('CScheme::updateTable -- Drop column '. $columnInfoIST -> COLUMN_NAME .' ... aborted');
				return false;
			}
			else
			{
				// drop successful
				cmsLog::add('CScheme::updateTable -- Drop column '. $columnInfoIST -> COLUMN_NAME .' ... successful');
			}		
		}


		cmsLog::add('CScheme::updateTable -- Update table successful');

		return true;
	}
}
