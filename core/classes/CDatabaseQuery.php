<?php

define('DB_SELECT',0x1);
define('DB_INSERT',0x2);
define('DB_UPDATE',0x3);
define('DB_DELETE',0x4);
define('DB_DROP',0x5);
define('DB_TRUNCATE',0x6);
define('DB_DESCRIBE',0x7);
define('DB_ALTER_TABLE_COLUMN_DROP',0x8);
define('DB_CREATE',0x9);
define('DB_CONSTRAINTS',0x10);
define('DB_COLUMNS',0x11);
define('DB_ALTER_TABLE_COLUMN_ADD',0x12);

class	CDatabaseQuery
{
	private $m_pDatabase;

	private $m_tableName;
	private $m_tableColumns;
	private	$m_queryType;
	private	$m_queryCondition;
	private	$m_queryRelationsList;
	private	$m_printException;

	private	$m_tableScheme;
	private	$m_tableSchemeColumn;
	private	$m_dtaObject;
	private	$m_dtaObjectName;

	public	string	$m_queryString;

	public function
	__construct(CDatabaseConnection &$_pDatabase, $_queryType)
	{
		$this -> m_pDatabase 	 		= $_pDatabase;
		$this -> m_queryType			= $_queryType;
		$this -> m_tableColumns			= ['*'];
		$this -> m_printException		= PHP_ERROR_DISPLAY;
		$this -> m_queryString			= '';
		$this -> m_tableName			= [];
	}

	public function
	table(string $_tableName, string $_asName = '')
	{
		$table  = new stdClass;
		$table -> name	= $_tableName;
		$table -> as	= $_asName;
		$this -> m_tableName[] 	= $table;
		return $this;
	}

	public function
	type($_queryType)
	{
		$this -> m_queryType = $_queryType;
		return $this;
	}

	public function
	condition($_queryCondition)
	{
		$this -> m_queryCondition = $_queryCondition;
		return $this;
	}
	public function
	relations($_queryRelation)
	{
		$this -> m_queryRelationsList = $_queryRelation;
		return $this;
	}

	public function
	dtaObject($_dtaObject)
	{
		$this -> m_dtaObject = $_dtaObject;
		return $this;
	}

	public function
	dtaObjectName($_dtaObjectName)
	{
		$this -> m_dtaObjectName = $_dtaObjectName;
		return $this;
	}

	public function
	selectColumns(array $_columns)
	{
		$this -> m_tableColumns	= $_columns;
		foreach($this -> m_tableColumns as &$column)
			$column = trim($column);

		$this -> m_tableColumns = array_filter($this -> m_tableColumns, 'strlen');
		return $this;
	}

	public function
	silentException()
	{
		$this -> m_printException = false;
		return $this;
	}

	public function
	scheme($_scheme)
	{
		$this -> m_tableScheme = $_scheme;
		return $this;
	}

	public function
	schemeColumn($_schemeColumn)
	{
		$this -> m_tableSchemeColumn = $_schemeColumn;
		return $this;
	}

	public function
	exec($_flags = NULL)
	{
		$statement 		= NULL;
		$queryString	= [];
		$execParameters = [];

		switch($this -> m_queryType)
		{
			case 	DB_SELECT:

					if(empty($this -> m_tableColumns))
						$this -> m_tableColumns = ['*'];

					$queryString[]	= 'SELECT';
					$queryString[]	= implode(', ', $this -> m_tableColumns);
					$queryString[]	= 'FROM';
					foreach($this -> m_tableName as $tableIndex => $table)
						$queryString[]	= ($tableIndex != 0 ? ', ' : '') .'`'. $this -> m_pDatabase -> getDatabaseName() .'`.`'. $table -> name .'`'. (!empty($table -> as) ? ' AS '. $table -> as : '');
					$queryString[]	= $this -> _getRelations();
					$queryString[]	= $this -> _getConditions();
					$execParameters = $this -> _getConditionsValues();
					break;

			case 	DB_INSERT:

					$tableName = ($this -> m_tableScheme != null && !empty($this -> m_tableScheme -> getTableName()) ? $this -> m_tableScheme -> getTableName() : current($this -> m_tableName) -> name);

					$queryString[]	= 'INSERT INTO';
					$queryString[]	= '`'. $this -> m_pDatabase -> getDatabaseName() .'`.`'. $tableName .'`';
					$queryString[]	= 'SET';
	
					$insertColumns 	= [];
					foreach($this -> m_dtaObject as $columnName => $columnValue)
						$insertColumns[] = "`". $columnName ."` = :". $columnName ."";
					$queryString[] 	= implode(', ', $insertColumns);
					$execParameters = $this -> _getDtaObjectValues();
					break;

			case 	DB_UPDATE:

					$tableName = ($this -> m_tableScheme != null && !empty($this -> m_tableScheme -> getTableName()) ? $this -> m_tableScheme -> getTableName() : current($this -> m_tableName) -> name);

					$queryString[]	= 'UPDATE';
					foreach($this -> m_tableName as $tableIndex => $table)
					$queryString[]	= '`'. $this -> m_pDatabase -> getDatabaseName() .'`.`'. $tableName .'`';
					$queryString[]	= 'SET';
	
					$insertColumns 	= [];
					if(property_exists($this -> m_dtaObject, 'prepareMode') && $this -> m_dtaObject -> prepareMode === false)
						foreach($this -> m_dtaObject as $columnName => $columnValue)
						{
							if($columnName === 'prepareMode')
								continue;
							$insertColumns[] = "`". $columnName ."` = ". $columnValue ."";
						}
					else
						foreach($this -> m_dtaObject as $columnName => $columnValue)
							$insertColumns[] = "`". $columnName ."` = :". $columnName ."";
					$queryString[] 	= implode(', ', $insertColumns);
					$queryString[]	= $this -> _getConditions();
					$execParameters = array_merge($this -> _getConditionsValues(), $this -> _getDtaObjectValues());
					break;

			case 	DB_DELETE:

					$tableName = ($this -> m_tableScheme != null && !empty($this -> m_tableScheme -> getTableName()) ? $this -> m_tableScheme -> getTableName() : current($this -> m_tableName) -> name);

					$queryString[]	= 'DELETE FROM';
					$queryString[]	= '`'. $this -> m_pDatabase -> getDatabaseName() .'`.`'. $tableName .'`';
					$queryString[]	= $this -> _getConditions();
					$execParameters = $this -> _getConditionsValues();
					break;

			case 	DB_TRUNCATE:

					$tableName = ($this -> m_tableScheme != null && !empty($this -> m_tableScheme -> getTableName()) ? $this -> m_tableScheme -> getTableName() : current($this -> m_tableName) -> name);

					$queryString[]	= 'TRUNCATE TABLE';
					$queryString[]	= '`'. $this -> m_pDatabase -> getDatabaseName() .'`.`'. $tableName .'`';
					break;


			case 	DB_DROP:

					$tableName = ($this -> m_tableScheme != null && !empty($this -> m_tableScheme -> getTableName()) ? $this -> m_tableScheme -> getTableName() : current($this -> m_tableName) -> name);

					$queryString[]	= 'DROP TABLE IF EXISTS';
					$queryString[]	= '`'. $this -> m_pDatabase -> getDatabaseName() .'`.`'. $tableName .'`';

					break;


			case 	DB_ALTER_TABLE_COLUMN_DROP:

					if(empty($this -> m_tableColumns))
						return false;

					$tableName = ($this -> m_tableScheme != null && !empty($this -> m_tableScheme -> getTableName()) ? $this -> m_tableScheme -> getTableName() : current($this -> m_tableName) -> name);

					$queryString[]	= 'ALTER TABLE ';
					$queryString[]	= '`'. $this -> m_pDatabase -> getDatabaseName() .'`.`'. $tableName .'`';
					$queryString[]	= 'DROP COLUMN';
					$queryString[]	= reset($this -> m_tableColumns);

					break;

			case 	DB_ALTER_TABLE_COLUMN_ADD:

					return $this -> _createColumn();
					break;

			case 	DB_DESCRIBE:

					$tableName = ($this -> m_tableScheme != null && !empty($this -> m_tableScheme -> getTableName()) ? $this -> m_tableScheme -> getTableName() : current($this -> m_tableName) -> name);

					$queryString[]	= 'DESCRIBE';
					$queryString[]	= '`'. $this -> m_pDatabase -> getDatabaseName() .'`.`'. $tableName .'`';
					break;

			case 	DB_COLUMNS:

					$tableName = ($this -> m_tableScheme != null && !empty($this -> m_tableScheme -> getTableName()) ? $this -> m_tableScheme -> getTableName() : current($this -> m_tableName) -> name);

					$queryString[]	= 'SELECT DISTINCT * FROM';
					$queryString[]	= 'INFORMATION_SCHEMA.COLUMNS';
					$queryString[]	= 'WHERE';
					$queryString[]	= 'TABLE_NAME = \''. $tableName .'\'';
					$queryString[]	= 'AND TABLE_SCHEMA = \''. $this -> m_pDatabase -> getDatabaseName() .'\'';
					break;

			case 	DB_CREATE:

					return $this -> _createTable();
					break;

			case 	DB_CONSTRAINTS:

					return $this -> _createConstraints();
					break;
		}

		$queryString	=	implode(' ', $queryString);

		try
		{
			$this -> m_queryString = $queryString;
			
			$statement  	 = 	$this -> m_pDatabase -> getConnection() -> prepare($queryString);
			$statement 		-> 	execute((!empty($execParameters) ? $execParameters : null));	
		}
		catch(PDOException $exception)
		{
			if($this -> m_printException)
			{
				echo '<br><br>'. $exception -> getMessage();
				CMessages::add('CDatabaseQuery::exec - prepared execution failed. Exception: '. $exception -> getMessage(), MSG_LOG, '', true);
				echo '<pre>';
				var_dump($execParameters);
				echo '</pre>';
				echo $this -> m_queryString;
			}

			return false;
		}


		##	return values

		switch($this -> m_queryType)
		{
			case 	DB_SELECT:

					if($this -> m_dtaObjectName === NULL)
						return $statement -> fetchAll(PDO::FETCH_CLASS, "stdClass");

					$fetchedData = [];
					while($statementItm = $statement -> fetch(PDO::FETCH_ASSOC))
					{
						$fetchedData[] = new $this -> m_dtaObjectName($statementItm); 
					}
					return $fetchedData;
					
			case 	DB_DESCRIBE:
			case 	DB_COLUMNS:

					return $statement -> fetchAll(PDO::FETCH_CLASS, "stdClass");

			case 	DB_INSERT:

					return $this -> m_pDatabase -> getConnection() -> lastInsertId();
		}

		return true;
	}

	private function
	_createTable()
	{
		$dbConnection = &$this -> m_pDatabase -> getConnection();

		$primaryKey		= '';

		$sqlString 		= [];
		$sqlString[] 	= "CREATE TABLE `". $this -> m_tableScheme -> m_tableName ."` (";

		$isFirstColumn = true;
		foreach($this -> m_tableScheme -> m_columnsList as $columnName => $columnData)
		{
			if($columnData -> m_isVirtual)
				continue;

			if($isFirstColumn)
				$isFirstColumn = false;
			else
				$sqlString[] 	= ",";

			$sqlString[] 	= "`". $columnData -> m_columnName ."`";

			switch($columnData -> m_columnType)
			{
				case DB_COLUMN_TYPE_BIGINT    :	$sqlString[] 	= 'BIGINT' . ($columnData -> m_length !== 0 ? "(". $columnData -> m_length .")" : ""); break;
				case DB_COLUMN_TYPE_MEDIUMINT : $sqlString[] 	= 'MEDIUMINT' . ($columnData -> m_length !== 0 ? "(". $columnData -> m_length .")" : ""); break;
				case DB_COLUMN_TYPE_BOOL      : 
				case DB_COLUMN_TYPE_TINYINT	  : $sqlString[] 	= 'TINYINT' . ($columnData -> m_length !== 0 ? "(". $columnData -> m_length .")" : ""); break;
				case DB_COLUMN_TYPE_INT	      : $sqlString[] 	= 'INT' . ($columnData -> m_length !== 0 ? "(". $columnData -> m_length .")" : ""); break;			
				case DB_COLUMN_TYPE_ARRAY     : 
				case DB_COLUMN_TYPE_JSON      : 
				case DB_COLUMN_TYPE_TEXT      : $sqlString[] 	= 'TEXT' . ($columnData -> m_length !== 0 ? "(". $columnData -> m_length .")" : ""); break;
				case DB_COLUMN_TYPE_STRING    : $sqlString[] 	= 'VARCHAR' . ($columnData -> m_length !== 0 ? "(". $columnData -> m_length .")" : ""); break;			
				case DB_COLUMN_TYPE_DECIMAL   : $sqlString[] 	= 'DECIMAL' . ($columnData -> m_length !== 0 ? "(". $columnData -> m_length .")" : ""); break;				
				case DB_COLUMN_TYPE_FLOAT     :	$sqlString[] 	= 'FLOAT' . ($columnData -> m_length !== 0 ? "(". $columnData -> m_length .")" : ""); break;				
			}

			switch($columnData -> m_attribute)
			{
				case DB_COLUMN_ATTR_UNSIGNED : $sqlString[] 	= 'UNSIGNED'; break;
			}
				
			if(!$columnData -> m_isNull && $columnData -> m_defaultValue === NULL)

				$sqlString[] 	= "NOT NULL";

			elseif($columnData -> m_isNull && $columnData -> m_defaultValue === NULL)
				$sqlString[] 	= "DEFAULT NULL";

			elseif(!$columnData -> m_isNull && $columnData -> m_defaultValue === false)

				$sqlString[] 	= "DEFAULT '0'";

			elseif(!$columnData -> m_isNull && $columnData -> m_defaultValue === true)

				$sqlString[] 	= "DEFAULT '1'";

			else
				$sqlString[] 	= "DEFAULT '". $columnData -> m_defaultValue ."'";

			if($columnData -> m_isAutoIncrement)
				$sqlString[] 	= "AUTO_INCREMENT";
		}

		$primaryKeySet = false;

		foreach($this -> m_tableScheme -> m_columnsList as $columnName => $columnData)
		{

			if($columnData -> m_keyType !== NULL)
			{
				if($columnData -> m_keyType === 'PRIMARY' && !$primaryKeySet)
				{
					$primaryKeySet = true;
				}
				elseif($columnData -> m_keyType === 'PRIMARY' && $primaryKeySet)
				{

					$_errorMsg = 'Setting multiple primary keys on table '. $this -> m_tableScheme -> m_columnName .' is not allowed.';
					return false;

				}

				$primaryKey	= [ "type" => $columnData -> m_keyType, "column" => $columnData -> m_columnName];

				$sqlString[] 	= ", ". $columnData -> m_keyType ." KEY (". $columnData -> m_columnName .")";

			}	
		}

		$sqlString[] 	= ") ENGINE=". $this -> m_tableScheme -> m_tableEngine ." DEFAULT CHARSET=". $this -> m_tableScheme -> m_charset ." COLLATE=". $this -> m_tableScheme -> m_collate;

		try
		{
			$dbConnection -> query(implode(' ', $sqlString));
		}
		catch(PDOException $exception)
		{
			if($this -> m_printException)
			{
				CMessages::add('CDatabaseQuery::_createTable - query failed on create table. Exception: '. $exception -> getMessage(), MSG_LOG, '', true);
			}

			return false;
		}

		##	Index

		$sqlString 		= [];
		$sqlString[] 	= "ALTER TABLE `". $this -> m_tableScheme -> m_tableName ."`";
		$isFirstColumn 	= true;
		foreach($this -> m_tableScheme -> m_columnsList as $columnName => $columnData)
		{		
			if($columnData -> m_indexType === NULL)
				continue;

			if($isFirstColumn)
				$isFirstColumn = false;
			else
				$sqlString[] 	= ",";

			$sqlString[] 	= "ADD ". $columnData -> m_indexType." `". $columnData -> m_columnName ."` (`". $columnData -> m_columnName ."`)";
		}

		try
		{
			$dbConnection -> query(implode(' ', $sqlString));
		}
		catch(PDOException $exception)
		{
			if($this -> m_printException)
			{
				CMessages::add('CDatabaseQuery::_createTable - query failed on alter table. Exception: '. $exception -> getMessage(), MSG_LOG, '', true);
			}

			return false;
		}
		
		return true;
	}



	private function
	_createColumn()
	{
		cmsLog::add('CDatabaseQuery::_createColumn -- Call');

		$dbConnection = &$this -> m_pDatabase -> getConnection();

		if(empty($this -> m_tableName))
		{
			cmsLog::add('CDatabaseQuery::_createColumn -- Table name not set, abort call', true);
			return false;
		}

		$sqlString 		= [];
		$sqlString[] 	= "ALTER TABLE `". reset($this -> m_tableName) -> name ."`";

		$columnData = $this -> m_tableSchemeColumn;
		$columnName = $columnData -> m_columnName;

		if($columnData -> m_isVirtual)
		{
			cmsLog::add('CDatabaseQuery::_createColumn -- Table is virtual, abort call');
			return true;
		}
			
		$sqlString[] 	= "ADD";
		$sqlString[] 	= "`". $columnData -> m_columnName ."`";

		switch($columnData -> m_columnType)
		{
			case DB_COLUMN_TYPE_BIGINT    :	$sqlString[] 	= 'BIGINT' . ($columnData -> m_length !== 0 ? "(". $columnData -> m_length .")" : ""); break;
			case DB_COLUMN_TYPE_MEDIUMINT : $sqlString[] 	= 'MEDIUMINT' . ($columnData -> m_length !== 0 ? "(". $columnData -> m_length .")" : ""); break;
			case DB_COLUMN_TYPE_BOOL      : 
			case DB_COLUMN_TYPE_TINYINT	  : $sqlString[] 	= 'TINYINT' . ($columnData -> m_length !== 0 ? "(". $columnData -> m_length .")" : ""); break;
			case DB_COLUMN_TYPE_INT	      : $sqlString[] 	= 'INT' . ($columnData -> m_length !== 0 ? "(". $columnData -> m_length .")" : ""); break;			
			case DB_COLUMN_TYPE_ARRAY     : 
			case DB_COLUMN_TYPE_JSON      : 
			case DB_COLUMN_TYPE_TEXT      : $sqlString[] 	= 'TEXT' . ($columnData -> m_length !== 0 ? "(". $columnData -> m_length .")" : ""); break;
			case DB_COLUMN_TYPE_STRING    : $sqlString[] 	= 'VARCHAR' . ($columnData -> m_length !== 0 ? "(". $columnData -> m_length .")" : ""); break;			
			case DB_COLUMN_TYPE_DECIMAL   : $sqlString[] 	= 'DECIMAL' . ($columnData -> m_length !== 0 ? "(". $columnData -> m_length .")" : ""); break;				
			case DB_COLUMN_TYPE_FLOAT     :	$sqlString[] 	= 'FLOAT' . ($columnData -> m_length !== 0 ? "(". $columnData -> m_length .")" : ""); break;				
		}

		switch($columnData -> m_attribute)
		{
			case DB_COLUMN_ATTR_UNSIGNED : $sqlString[] 	= 'UNSIGNED'; break;
		}
			
		if(!$columnData -> m_isNull && $columnData -> m_defaultValue === NULL)

			$sqlString[] 	= "NOT NULL";

		elseif($columnData -> m_isNull && $columnData -> m_defaultValue === NULL)

			$sqlString[] 	= "DEFAULT NULL";

		elseif(!$columnData -> m_isNull && $columnData -> m_defaultValue === true)

			$sqlString[] 	= "DEFAULT '1'";

		else

			$sqlString[] 	= "DEFAULT '". $columnData -> m_defaultValue ."'";

		if($columnData -> m_isAutoIncrement)
			$sqlString[] 	= "AUTO_INCREMENT";
	
		try
		{
			$dbConnection -> query(implode(' ', $sqlString));
		}
		catch(PDOException $exception)
		{
			cmsLog::add('CDatabaseQuery::_createColumn -- query failed. Exception: '. $exception -> getMessage(), true);
			return false;
		}

		cmsLog::add('CDatabaseQuery::_createColumn -- Call successful');
		return true;
	}

	private function
	_createConstraints()
	{
		$dbConnection = &$this -> m_pDatabase -> getConnection();

		foreach($this -> m_tableScheme -> m_constraintsList as $constraint)
		{	
			$sqlString = 	"	ALTER TABLE 	 `". $this -> m_tableScheme -> m_tableName ."` 
								ADD	CONSTRAINT 	 `". $constraint -> m_name ."` 
									FOREIGN KEY (`". $constraint -> m_key ."`) 
									REFERENCES   `". $constraint -> m_refTable ."`(`". $constraint -> m_refColumn ."`) 
									ON DELETE 	  ". $constraint -> m_onDelete ." 
									ON UPDATE 	  ". $constraint -> m_onUpdate ."";


			$sqlString = preg_replace('!\s+!', ' ', $sqlString);
			
			try
			{
				$dbConnection -> query($sqlString);
			}
			catch(PDOException $exception)
			{
				if($this -> m_printException)
				{
					CMessages::add('CDatabaseQuery::_createConstraints - query failed on alter table. Exception: '. $exception -> getMessage(), MSG_LOG, '', true);
				}	
				
				return false;	
			}						
		}

		return true;
	}

	private function
	_getConditions()
	{
		if($this -> m_queryCondition !== NULL)
		{
			$_sqlString = '';
			if(count($this -> m_queryCondition -> conditionList) != 0)
			{

				$_sqlString .= " WHERE ";

				$firstCondition = true;

				foreach($this -> m_queryCondition -> conditionList as $condition)
				{
					if(!$firstCondition)
					{
						$_sqlString .= " OR ";
						$firstCondition = true;
					}

					foreach($condition as $conditionIndex => $conditionStage)
					{
						if($firstCondition)
						{
							$firstCondition = false;
						}
						else
						{
							$_sqlString .= " AND ";
						}

						/*
							IN needs multiple placeholders for the values

							change CModelCondition::whereIn() second parameter to array and create function that returns values
						*/
						
						if(!$conditionStage -> directUse)
						switch($conditionStage -> type)
						{
							case 'BETWEEN'		: $_sqlString .= " ". $conditionStage -> column ." ". $conditionStage -> type ." :". $this -> _getValidConditionPlaceholder($conditionStage -> column) ."A AND  :". $this -> _getValidConditionPlaceholder($conditionStage -> column) ."B "; break;
							case 'IS NULL'  	: 
							case 'IS NOT NULL'  : $_sqlString .= " ". $conditionStage -> column ." ". $conditionStage -> type ." "; break;
							case 'IN'  			: $_sqlString .= " ". $conditionStage -> column ." ". $conditionStage -> type ." ({$conditionStage -> valueA}) "; break;
							default				: $_sqlString .= " ". $conditionStage -> column ." ". $conditionStage -> type ." :". $this -> _getValidConditionPlaceholder($conditionStage -> column . $conditionIndex) ." ";
						}

						if($conditionStage -> directUse)
						switch($conditionStage -> type)
						{
							case 'BETWEEN'		: $_sqlString .= " ". $conditionStage -> column ." ". $conditionStage -> type ." ". $conditionStage -> valueA ." AND  ". $conditionStage -> valueB ." "; break;
							case 'IN'  			: $_sqlString .= " ". $conditionStage -> column ." ". $conditionStage -> type ." ({$conditionStage -> valueA}) "; break;
							default				: $_sqlString .= " ". $conditionStage -> column ." ". $conditionStage -> type ." ". ($conditionStage -> valueA) ." ";
						}

					}
				}
			}
			
			if(count($this -> m_queryCondition -> groupByList) != 0)
			{
				$arrayOrderBy = [];

				foreach($this -> m_queryCondition -> groupByList as $oderBy)
					$arrayOrderBy[] = " ". $oderBy -> column ." ";

				$_sqlString	.= " GROUP BY ". implode(',', $arrayOrderBy);
			}	

			if(count($this -> m_queryCondition -> oderByList) != 0)
			{
				$arrayOrderBy = [];

				foreach($this -> m_queryCondition -> oderByList as $oderBy)
					$arrayOrderBy[] = " ". $oderBy -> column ." ". $oderBy -> direction;

				$_sqlString	.= " ORDER BY ". implode(',', $arrayOrderBy);
			}	

			if($this -> m_queryCondition -> limit !== 0)
			{
				if($this -> m_queryCondition -> offset !== 0)				
					$_sqlString	.= " LIMIT ". $this -> m_queryCondition -> offset .", ". $this -> m_queryCondition -> limit ." " ;				
				else	
					$_sqlString	.= " LIMIT ". $this -> m_queryCondition -> limit ." " ;
			}
			return $_sqlString;
		}	
		return '';	
	}

	private function
	_getRelations()
	{
		if($this -> m_queryRelationsList !== NULL)
		{
			$sqlString = '';

			foreach($this -> m_queryRelationsList  as $relation)
			{
				$sqlString	.=	"	". $relation -> joinType ." ". $relation -> tableName;
				$sqlString	.=	"		ON ". $this -> _getRelationConditions($relation -> condition);
			}

			return $sqlString;

		}	
		return '';	
	}

	private function
	_getConditionsValues()
	{
		$conditionValues = [];
		if($this -> m_queryCondition !== NULL)
		{
			foreach($this -> m_queryCondition -> conditionList as $condition)
			{
				foreach($condition as $conditionIndex =>  $conditionStage)
				{

					if(!$conditionStage -> directUse)
					switch($conditionStage -> type)
					{
						case 'IS NULL'  	: 
						case 'IS NOT NULL'  : 	
						case 'IN'  : 	
												break;
												
						case 'BETWEEN'		: 	$conditionValues[ $this -> _getValidConditionPlaceholder($conditionStage -> column).'A' ] = $conditionStage -> valueA;
												$conditionValues[ $this -> _getValidConditionPlaceholder($conditionStage -> column).'B' ] = $conditionStage -> valueB;
												break;
										  
						default				: 	$conditionValues[ $this -> _getValidConditionPlaceholder($conditionStage -> column . $conditionIndex) ] = $conditionStage -> valueA;
					}


					
				}
			}
		}
		return $conditionValues;
	}

	private function
	_getDtaObjectValues()
	{
		$return = [];
		if($this -> m_dtaObject === NULL)
			return $return;
		
		if(property_exists($this -> m_dtaObject, 'prepareMode') && $this -> m_dtaObject -> prepareMode === false)
			return $return;

		foreach($this -> m_dtaObject as $propName => $propValue)
		{
			if(is_object($propValue) || is_array($propValue))
			{
				$return[$propName] = json_encode($propValue, JSON_UNESCAPED_UNICODE);
			}
			elseif(is_bool($propValue))
			{
				$return[$propName] = intval($propValue);
			}
			else
			{
				$return[$propName] = $propValue;
			}
		}
		return $return;
	}
	
	private function
	_getRelationConditions($_queryCondition)
	{
		if($_queryCondition !== NULL)
		{
			$_sqlString = '';
			if(count($_queryCondition -> conditionList) != 0)
			{
				$firstCondition = true;

				foreach($_queryCondition -> conditionList as $condition)
				{
					if(!$firstCondition)
					{
						$_sqlString .= " OR ";
						$firstCondition = true;
					}

					foreach($condition as $conditionStage)
					{
						if($firstCondition)
						{
							$firstCondition = false;
						}
						else
						{
							$_sqlString .= " AND ";
						}

						$_sqlString .= " ". $conditionStage -> column ." ". $conditionStage -> type ." ". $conditionStage -> valueA . " ";
					}
				}
			}
			
			return $_sqlString;
		}	
		return '';	
	}

	private function
	_getValidConditionPlaceholder(string $_placeholder)
	{
		$_placeholder = explode('.', $_placeholder);
		return $_placeholder[count($_placeholder) - 1];
	}

}

?>