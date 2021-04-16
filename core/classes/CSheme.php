<?php

class 	CShemeConstraints
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

class	CShemeColumn
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

class	CSheme
{
	private	$m_tableName;
	private	$m_isVirtual;
	private	$m_collate;
	private	$m_charset;
	private	$m_tableEngine;
	private	$m_columnsList;
	private	$m_constraintsList;

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
		$this -> m_columnsList[$columnIndex] = new CShemeColumn($_columnName, $_columnType);
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
		$this -> m_constraintsList[] = new CShemeConstraints($_constraintName, $_keyName, $_refTable, $_refColumn, $_onDelete, $_onUpdate);
	}

	protected function
	addConstraing($_constraintName, $_keyName, $_refTable, $_refColumn, $_onDelete, $_onUpdate)
	{
		// function name typo  .. remove later
		$this -> addConstraint($_constraintName, $_keyName, $_refTable, $_refColumn, $_onDelete, $_onUpdate);
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

		return $_dbConnection -> query(DB_CREATE) -> sheme($this) -> exec();
	}

	public function
	dropTable(CDatabaseConnection &$_dbConnection)
	{
		if($this -> m_isVirtual === true)
			return true;

		return $_dbConnection -> query(DB_DROP) -> sheme($this) -> exec();
	}

	public function
	createConstraints(CDatabaseConnection &$_dbConnection)
	{
		if($this -> m_isVirtual === true || count($this -> m_constraintsList) == 0)
			return true;

		if(!$this -> existsTable($_dbConnection, $this -> m_tableName))
			return false;
	
		return $_dbConnection -> query(DB_CONSTRAINTS) -> sheme($this) -> exec();
	}
}


/*

class CShemeColumn
{
	public	$name;
	public	$type;
	public	$isVirtual;
	public	$isSystemId;
	public	$isAutoIncrement;
	public	$attribute;
	public	$defaultValue;
	public	$isNull;
	public	$length;
	public	$keyType;
	public	$indexType;

	public function
	__construct(string $name, string $type)
	{
		$this -> name 		= $name;
		$this -> type 		= $type;
		$this -> isVirtual 	= false;

		$this -> isAutoIncrement = false;
		$this -> attribute 		 = false;
		$this -> defaultValue 	 = NULL;
		$this -> isNull		 	 = false;
		$this -> length			 = 0;
	}

	public function
	isVirtual(bool $isVirtual = true)
	{
		$this -> isVirtual = $isVirtual;
		return $this;
	}

	public function
	isSystemId(bool $isSystemId = true)
	{
		$this -> isSystemId = $isSystemId;
		return $this;
	}

	public function
	isAutoIncrement()
	{
		$this -> isAutoIncrement = true;
		return $this;
	}

	public function
	setIndex($_type)
	{
		$this -> indexType = $_type;
		return $this;
	}

	public function
	setDefault($_default)
	{
		if($_default === 'NULL')
		{
			$this -> isNull = true;
			return $this;
		}

		$this -> defaultValue = $_default;
		return $this;
	}

	public function
	setLength($_length)
	{
		$this -> length = $_length;
		return $this;
	}

	public function
	setKey($_type)
	{
		$this -> keyType = strtoupper($_type);

		return $this;
	}

	public function
	setAttribute(string $attribute)
	{
		if(empty($attribute))
			return $this;

		$this -> attribute = $attribute;
		return $this;
	}
}

class sqlConstraints
{
	public $name;
	public $key;
	public $refTable;
	public $refColumn;
	public $onDelete;
	public $onUpdate;

	public function
	__construct($_constraintName, $_keyName, $_refTable, $_refColumn, $_onDelete, $_onUpdate)
	{
		$this -> name		= $_constraintName;
		$this -> key		= $_keyName;
		$this -> refTable	= $_refTable;
		$this -> refColumn	= $_refColumn;
		$this -> onDelete	= $_onDelete;
		$this -> onUpdate	= $_onUpdate;
	}
}

class CSheme
{
	protected	$m_sheme;

	public function
	__construct()
	{
		$this -> m_tableName		= '';
		$this -> m_collate 			= '';
		$this -> m_charset 			= '';
		$this -> m_sheme['columns'] = [];
		$this -> m_tableEngine 		= '';

		$this -> m_duplications		= [];
		$this -> constraintsList	= [];
	}

	protected function
	setTable(string $_tableName, bool $_isVirtual = false)
	{
		$this -> m_tableName 		= $_tableName;
		$this -> m_collate 			= CFG::GET() -> MYSQL -> TABLE_COLLATE;
		$this -> m_charset 			= CFG::GET() -> MYSQL -> TABLE_CHARSET;
		$this -> m_tableEngine 		= CFG::GET() -> MYSQL -> TABLE_ENGINE;
		$this -> m_sheme['virtual']	= $_isVirtual;
		return $this;
	}

	protected function
	setEngine(string $_tableEngine)
	{
		$this -> m_tableName 		= $_tableEngine;
	}

	
	protected function
	addConstraing($_constraintName, $_keyName, $_refTable, $_refColumn, $_onDelete, $_onUpdate)
	{
		$this -> constraintsList[] = new sqlConstraints($_constraintName, $_keyName, $_refTable, $_refColumn, $_onDelete, $_onUpdate);
	}
	
	public function
	&addColumn(string $_columnName, string $_dataType)
	{
		$this -> m_sheme['columns'][$_columnName] = new CShemeColumn($_columnName, $_dataType);
		return $this -> m_sheme['columns'][$_columnName];
	}

	public function
	&getColumns()
	{
		return $this -> m_sheme['columns'];
	}

	public function
	getTableName()
	{
		return $this -> m_tableName;
	}

	public function
	getSystemIdColumnName()
	{
		foreach($this -> m_sheme['columns'] as $column)
		{
			if($column -> isSystemId === true)
				return $column -> name;
		}
		return NULL;
	}

	public function
	columnExists(bool $_excludeVirtual, string $_columnName)
	{
		foreach($this -> m_sheme['columns'] as $_column)
		{
			if($_excludeVirtual && $_column -> name === $_columnName && $_column -> isVirtual) return false;
			if($_column -> name === $_columnName) return true;
		}
		return false;
	}

	public function
	dropTable(&$_sqlConnection)
	{
		if(empty($_sqlConnection) || !property_exists($_sqlConnection, 'errno') || $_sqlConnection -> errno != 0)
			return false;

		$_sqlConnection -> query("DROP TABLE IF EXISTS `". $this -> m_tableName ."`");	
	}

	public function
	createTable(&$_sqlConnection, &$_errorMsg)
	{
		if($this -> m_sheme['virtual'] === true)
			return true;

		if(empty($_sqlConnection) || !property_exists($_sqlConnection, 'errno') || $_sqlConnection -> errno != 0)
			trigger_error("CSheme::createTable -- Invalid SQL connection", E_USER_ERROR);

		if($_sqlConnection -> query("DESCRIBE `". $this -> m_tableName ."`"))
		{
			return false;
		}

		$primaryKey		= '';

		$sqlString 		= [];
		$sqlString[] 	= "CREATE TABLE `". $this -> m_tableName ."` (";

		$isFirstColumn = true;
		foreach($this -> m_sheme['columns'] as $columnName => $columnData)
		{
			if($columnData -> isVirtual)
				continue;

			$columnData -> type = ($columnData -> type === 'string' ? 'VARCHAR' : $columnData -> type);
			$columnData -> type = ($columnData -> type === 'bool' ? 'tinyint' : $columnData -> type);

			if($isFirstColumn)
				$isFirstColumn = false;
			else
				$sqlString[] 	= ",";

			$sqlString[] 	= "`". $columnData -> name ."`";
			$sqlString[] 	= $columnData -> type . ($columnData -> length !== 0 ? "(". $columnData -> length .")" : "");

			if($columnData -> attribute !== false)
				$sqlString[] 	= $columnData -> attribute;

			if(!$columnData -> isNull && $columnData -> defaultValue === NULL)

				$sqlString[] 	= "NOT NULL";

			elseif($columnData -> isNull && $columnData -> defaultValue === NULL)
				$sqlString[] 	= "DEFAULT NULL";

			elseif(!$columnData -> isNull && $columnData -> defaultValue === false)

				$sqlString[] 	= "DEFAULT '0'";

			elseif(!$columnData -> isNull && $columnData -> defaultValue === true)

				$sqlString[] 	= "DEFAULT '1'";

			else
				$sqlString[] 	= "DEFAULT '". $columnData -> defaultValue ."'";

			if($columnData -> isAutoIncrement)
				$sqlString[] 	= "AUTO_INCREMENT";
	
		

		}

		$primaryKeySet = false;

		foreach($this -> m_sheme['columns'] as $columnName => $columnData)
		{

			if($columnData -> keyType !== NULL)
			{
				if($columnData -> keyType === 'PRIMARY' && !$primaryKeySet)
				{
					$primaryKeySet = true;
				}
				elseif($columnData -> keyType === 'PRIMARY' && $primaryKeySet)
				{

					$_errorMsg = 'Setting multiple primary keys on table '. $this -> m_tableName .' is not allowed.';
					return false;

				}

				$primaryKey	= [ "type" => $columnData -> keyType, "column" => $columnData -> name];

				$sqlString[] 	= ", ". $columnData -> keyType ." KEY (". $columnData -> name .")";

			}	
		}


		$sqlString[] 	= ") ENGINE=". $this -> m_tableEngine ." DEFAULT CHARSET=". $this -> m_charset ." COLLATE=". $this -> m_collate;

		if($_sqlConnection -> query(implode(' ', $sqlString)) === false)
		{

			$_errorMsg = 'Query return error: '. $_sqlConnection -> error .' | '. print_r(implode(' ', $sqlString),true);
			return false;			
		}

		##	Index

		$sqlString 		= [];
		$sqlString[] 	= "ALTER TABLE `". $this -> m_tableName ."`";
		$isFirstColumn 	= true;
		foreach($this -> m_sheme['columns'] as $columnName => $columnData)
		{		
			if($columnData -> indexType === NULL)
				continue;

			if($isFirstColumn)
				$isFirstColumn = false;
			else
				$sqlString[] 	= ",";

			$sqlString[] 	= "ADD ". $columnData -> indexType." `". $columnData -> name ."` (`". $columnData -> name ."`)";
		}

		$_sqlConnection -> query(implode(' ', $sqlString));

		return true;
	}

	public function
	createTableConstraints(&$_sqlConnection)
	{
		if($this -> m_sheme['virtual'] === true || count($this -> constraintsList) == 0)
			return true;

		if(empty($_sqlConnection) || !property_exists($_sqlConnection, 'errno') || $_sqlConnection -> errno != 0)
			return true;

		##	Adding constraints if exists

		foreach($this -> constraintsList as $constraint)
		{	
			$sqlString = 	"	ALTER TABLE 	 `". $this -> m_tableName ."` 
								ADD	CONSTRAINT 	 `". $constraint -> name ."` 
									FOREIGN KEY (`". $constraint -> key ."`) 
									REFERENCES   `". $constraint -> refTable ."`(`". $constraint -> refColumn ."`) 
									ON DELETE 	  ". $constraint -> onDelete ." 
									ON UPDATE 	  ". $constraint -> onUpdate ."";

			$_sqlConnection -> query($sqlString);

			
		}

		return true;
	}

	public function
	truncateTable(&$_sqlConnection)
	{		
		if(empty($_sqlConnection) || !property_exists($_sqlConnection, 'errno') || $_sqlConnection -> errno != 0)
			trigger_error("CSheme::truncateTable -- Invalid SQL connection", E_USER_ERROR);

		$_sqlConnection -> query("TRUNCATE TABLE `". $this -> m_tableName ."`");	
	}

	public function
	duplicateTable(string $_tableName)
	{
		$this -> m_duplications[]	= $_tableName;
	}

}
*/

?>