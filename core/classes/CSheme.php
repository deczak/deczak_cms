<?php


class CShemeColumn
{
	public	$name;
	public	$type;
	public	$isVirtual;
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
	setDefault(string $_default)
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

?>