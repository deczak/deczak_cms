<?php

define('DB_SELECT',0x1);
define('DB_INSERT',0x2);
define('DB_UPDATE',0x3);
define('DB_DELETE',0x4);
define('DB_DROP',0x5);
define('DB_TRUNCATE',0x6);
define('DB_DESCRIBE',0x7);
define('DB_ALTER_TABLE',0x8);
define('DB_CREATE',0x9);
define('DB_CONSTRAINTS',0x10);

class	CDatabaseQuery
{
	private $m_pDatabase;

	private $m_tableName;
	private $m_tableColumns;
	private	$m_queryType;
	private	$m_queryCondition;
	private	$m_queryRelationsList;
	private	$m_printException;

	private	$m_tableSheme;
	private	$m_dtaObject;
	private	$m_dtaObjectName;

	public	string	$m_queryString;

	public function
	__construct(CDatabaseConnection &$_pDatabase, $_queryType)
	{
		$this -> m_pDatabase 	 		= $_pDatabase;
		$this -> m_queryType			= $_queryType;
		$this -> m_tableColumns			= ['*'];
		$this -> m_printException		= true;
		$this -> m_queryString	= '';
	}

	public function
	table(string $_tableName)
	{
		$this -> m_tableName = $_tableName;
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
	sheme($_sheme)
	{
		$this -> m_tableSheme = $_sheme;
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
					$queryString[]	= '`'. $this -> m_pDatabase -> getDatabaseName() .'`.`'. $this -> m_tableName .'`';
					$queryString[]	= $this -> _getRelations();
					$queryString[]	= $this -> _getConditions();
					$execParameters = $this -> _getConditionsValues();
					break;

			case 	DB_INSERT:

					$queryString[]	= 'INSERT INTO';
					$queryString[]	= '`'. $this -> m_pDatabase -> getDatabaseName() .'`.`'. $this -> m_tableName .'`';
					$queryString[]	= 'SET';
	
					$insertColumns 	= [];
					foreach($this -> m_dtaObject as $columnName => $columnValue)
						$insertColumns[] = "`". $columnName ."` = :". $columnName ."";
					$queryString[] 	= implode(', ', $insertColumns);
					$execParameters = $this -> _getDtaObjectValues();
					break;

			case 	DB_UPDATE:

					$queryString[]	= 'UPDATE';
					$queryString[]	= '`'. $this -> m_pDatabase -> getDatabaseName() .'`.`'. $this -> m_tableName .'`';
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

					$queryString[]	= 'DELETE FROM';
					$queryString[]	= '`'. $this -> m_pDatabase -> getDatabaseName() .'`.`'. $this -> m_tableName .'`';
				
					$queryString[]	= $this -> _getConditions();
					$execParameters = $this -> _getConditionsValues();
					break;

			case 	DB_TRUNCATE:

					$queryString[]	= 'TRUNCATE TABLE';
					$queryString[]	= '`'. $this -> m_pDatabase -> getDatabaseName() .'`.`'. $this -> m_tableName .'`';
					break;


			case 	DB_DROP:

					$queryString[]	= '"DROP TABLE IF EXISTS';
					$queryString[]	= '`'. $this -> m_pDatabase -> getDatabaseName() .'`.`'. $this -> m_tableName .'`';
					break;

			case 	DB_DESCRIBE:

					$queryString[]	= 'DESCRIBE';
					$queryString[]	= '`'. $this -> m_pDatabase -> getDatabaseName() .'`.`'. $this -> m_tableName .'`';
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
			$statement 		-> 	execute($execParameters);	
		}
		catch(PDOException $exception)
		{
			if($this -> m_printException)
			{
				echo '<br><br>'. $exception -> getMessage();
				CMessages::instance() -> addMessage('CDatabaseQuery::exec - prepared execution failed. Exception: '. $exception -> getMessage(), MSG_LOG, '', true);
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
		$sqlString[] 	= "CREATE TABLE `". $this -> m_tableSheme -> m_tableName ."` (";

		$isFirstColumn = true;
		foreach($this -> m_tableSheme -> m_columnsList as $columnName => $columnData)
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

		foreach($this -> m_tableSheme -> m_columnsList as $columnName => $columnData)
		{

			if($columnData -> m_keyType !== NULL)
			{
				if($columnData -> m_keyType === 'PRIMARY' && !$primaryKeySet)
				{
					$primaryKeySet = true;
				}
				elseif($columnData -> m_keyType === 'PRIMARY' && $primaryKeySet)
				{

					$_errorMsg = 'Setting multiple primary keys on table '. $this -> m_tableSheme -> m_columnName .' is not allowed.';
					return false;

				}

				$primaryKey	= [ "type" => $columnData -> m_keyType, "column" => $columnData -> m_columnName];

				$sqlString[] 	= ", ". $columnData -> m_keyType ." KEY (". $columnData -> m_columnName .")";

			}	
		}

		$sqlString[] 	= ") ENGINE=". $this -> m_tableSheme -> m_tableEngine ." DEFAULT CHARSET=". $this -> m_tableSheme -> m_charset ." COLLATE=". $this -> m_tableSheme -> m_collate;

		try
		{
			$dbConnection -> query(implode(' ', $sqlString));
		}
		catch(PDOException $exception)
		{
			if($this -> m_printException)
			{
				CMessages::instance() -> addMessage('CDatabaseQuery::_createTable - query failed on create table. Exception: '. $exception -> getMessage(), MSG_LOG, '', true);
			}

			return false;
		}

		##	Index

		$sqlString 		= [];
		$sqlString[] 	= "ALTER TABLE `". $this -> m_tableSheme -> m_tableName ."`";
		$isFirstColumn 	= true;
		foreach($this -> m_tableSheme -> m_columnsList as $columnName => $columnData)
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
				CMessages::instance() -> addMessage('CDatabaseQuery::_createTable - query failed on alter table. Exception: '. $exception -> getMessage(), MSG_LOG, '', true);
			}

			return false;
		}
		
		return true;
	}

	private function
	_createConstraints()
	{
		$dbConnection = &$this -> m_pDatabase -> getConnection();

		foreach($this -> m_tableSheme -> m_constraintsList as $constraint)
		{	
			$sqlString = 	"	ALTER TABLE 	 `". $this -> m_tableSheme -> m_tableName ."` 
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
					CMessages::instance() -> addMessage('CDatabaseQuery::_createConstraints - query failed on alter table. Exception: '. $exception -> getMessage(), MSG_LOG, '', true);
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

						switch($conditionStage -> type)
						{
							case 'BETWEEN'	: $_sqlString .= " ". $conditionStage -> column ." ". $conditionStage -> type ." :". $this -> _getValidConditionPlaceholder($conditionStage -> column) ."A AND  :". $this -> _getValidConditionPlaceholder($conditionStage -> column) ."B "; break;

							default			: $_sqlString .= " ". $conditionStage -> column ." ". $conditionStage -> type ." :". $this -> _getValidConditionPlaceholder($conditionStage -> column) ." ";
						}

					}
				}
			}
			
			if(count($this -> m_queryCondition -> groupByList) != 0)
			{
				$arrayOrderBy = [];

				foreach($this -> m_queryCondition -> groupByList as $oderBy)
					$arrayOrderBy[] = " `". $oderBy -> column ."` ";

				$_sqlString	.= " GROUP BY ". implode(',', $arrayOrderBy);
			}	

			if(count($this -> m_queryCondition -> oderByList) != 0)
			{
				$arrayOrderBy = [];

				foreach($this -> m_queryCondition -> oderByList as $oderBy)
					$arrayOrderBy[] = " `". $oderBy -> column ."` ". $oderBy -> direction;

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
				foreach($condition as $conditionStage)
				{

					switch($conditionStage -> type)
					{
						case 'BETWEEN'	: $conditionValues[ $this -> _getValidConditionPlaceholder($conditionStage -> column).'A' ] = $conditionStage -> valueA;
										  $conditionValues[ $this -> _getValidConditionPlaceholder($conditionStage -> column).'B' ] = $conditionStage -> valueB;
										  break;
										  
						default			: $conditionValues[ $this -> _getValidConditionPlaceholder($conditionStage -> column) ] = $conditionStage -> valueA;;
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