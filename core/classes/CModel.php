<?php

class 	CModelConditionStage
{
	public	$type;
	public	$column;
	public	$valueA;
	public	$valueB;
	public	$directUse;

	public function
	__construct($_type, $_column, $_valueA = NULL, $_valueB = NULL, bool $_directUse = false)
	{
		$this -> type 		= $_type;
		$this -> column 	= $_column;
		$this -> valueA 	= $_valueA;
		$this -> valueB 	= $_valueB;
		$this -> directUse 	= $_directUse;
	}
}

class 	CModelConditionOrder
{
	public	$column;
	public	$direction;

	public function
	__construct($_column, $_direction)
	{
		$this -> column 	= $_column;
		$this -> direction	= $_direction;
	}
}

class 	CModelConditionGroup
{
	public	$column;

	public function
	__construct($_column)
	{
		$this -> column 	= $_column;
	}
}

class 	CModelCondition
{
	public $conditionList;
	public $conditionLevel;
	public $oderByList;
	public $groupByList;
	public $limit;
	public $offset;

	public function
	__construct()
	{
		$this -> conditionList 		= [];
		$this -> conditionLevel 	= 0;
		$this -> groupByList 		= [];
		$this -> oderByList 		= [];

		$this -> limit				= 0;
		$this -> offset				= 0;
	}

	public function
	where(string $_columnName, string $_columnValue)
	{
		$this -> conditionList[$this -> conditionLevel][] = new CModelConditionStage('=', $_columnName, $_columnValue);
		return $this;
	}


	public function
	whereGreater(string $_columnName, string $_columnValue)
	{
		$this -> conditionList[$this -> conditionLevel][] = new CModelConditionStage('>', $_columnName, $_columnValue);
		return $this;
	}

	public function
	whereGreaterEven(string $_columnName, string $_columnValue)
	{
		$this -> conditionList[$this -> conditionLevel][] = new CModelConditionStage('>=', $_columnName, $_columnValue);
		return $this;
	}

	public function
	whereSmaller(string $_columnName, string $_columnValue)
	{
		$this -> conditionList[$this -> conditionLevel][] = new CModelConditionStage('<', $_columnName, $_columnValue);
		return $this;
	}

	public function
	whereIn(string $_columnName, string $_columnValue)
	{
		$this -> conditionList[$this -> conditionLevel][] = new CModelConditionStage('IN', $_columnName, $_columnValue);
		return $this;
	}

	public function
	whereSmallerEven(string $_columnName, string $_columnValue)
	{
		$this -> conditionList[$this -> conditionLevel][] = new CModelConditionStage('<=', $_columnName, $_columnValue);
		return $this;
	}

	public function
	whereLike(string $_columnName, string $_columnValue)
	{
		$this -> conditionList[$this -> conditionLevel][] = new CModelConditionStage('LIKE', $_columnName, $_columnValue);
		return $this;
	}

	public function
	whereNotLike(string $_columnName, string $_columnValue)
	{
		$this -> conditionList[$this -> conditionLevel][] = new CModelConditionStage('NOT LIKE', $_columnName, $_columnValue);
		return $this;
	}

	public function
	whereNot(string $_columnName, string $_columnValue)
	{
		$this -> conditionList[$this -> conditionLevel][] = new CModelConditionStage('!=', $_columnName, $_columnValue);
		return $this;
	}

	public function
	whereBetween(string $_columnName, string $_columnValueA, string $_columnValueB, bool $_directUse = false)
	{
		$this -> conditionList[$this -> conditionLevel][] = new CModelConditionStage('BETWEEN', $_columnName, $_columnValueA, $_columnValueB, $_directUse);
		return $this;
	}

	public function
	or()
	{
		$this -> conditionLevel++;
		$this -> conditionList[] = [];
		return $this;
	}

	public function
	whereNull(string $_columnName)
	{

		$this -> conditionList[$this -> conditionLevel][] = new CModelConditionStage('IS NULL', $_columnName);
		return $this;		
	}

	public function
	whereNotNull(string $_columnName)
	{
	
		$this -> conditionList[$this -> conditionLevel][] = new CModelConditionStage('IS NOT NULL', $_columnName);
		return $this;	
	}

	public function
	orderBy(string $_columnName, string $_direction = 'ASC')
	{
		$this -> oderByList[] = new CModelConditionOrder($_columnName, $_direction);
		return $this;
	}

	public function
	groupBy(string $_columnName)
	{
		$this -> groupByList[] = new CModelConditionGroup($_columnName);
		return $this;
	}

	public function
	limit(int $limit, int $offset = 0)
	{
		$this -> limit				= $limit;
		$this -> offset				= $offset;
	}

	public function
	isSet()
	{
		if(count($this -> conditionList[0]) === 0)
			return false;
		return true;
	}

	public function
	getConditionListValue($_columnName)
	{
		foreach($this -> conditionList as $conditionLevel)
		{
			foreach($conditionLevel as $conditionItem)
			{
				if($conditionItem -> column === $_columnName)
				{
					return $conditionItem -> valueA; 
				}
			}
		}

		return false;
	}
}

class	CModelRelations
{
	public	$joinType;
	public	$tableName;
	public	$condition;

	public function
	__construct(string $_joinType, string $_tableName, CModelCondition $_condition)
	{
		$this -> joinType 				= $_joinType;
		$this -> tableName 				= $_tableName;
		$this -> condition 				= $_condition;
	}
}

class CModelComplementary
{
	public	$m_propertyName;
	public	$m_compPropertySrcModel;
	public	$m_compPropertyDstModel;
	public	$m_modelName;

	public function
	__construct(string $_propertyName, string $_compPropertySrcModel, string $_compPropertyDstModel, string $_modelName)
	{
		$this -> m_propertyName 		= $_propertyName;
		$this -> m_compPropertySrcModel	= $_compPropertySrcModel;
		$this -> m_compPropertyDstModel	= $_compPropertyDstModel;
		$this -> m_modelName			= $_modelName;
	}
}

define('MODEL_PROTOTYPE_EXCEPT_AUTOINCREMENT',0x1);
define('MODEL_PROTOTYPE_EXCEPT_VIRTUALCOLUMN',0x2);
define('MODEL_PROTOTYPE_EXCEPT_ALLCOLUMNS',0x3);
define('MODEL_PROTOTYPE_EXCEPT_UNKNOWNS',0x4);

define('MODEL_RESULT_RESET',0x31);
define('MODEL_RESULT_APPEND_DTAOBJECT',0x32);
define('MODEL_LOCK_UPDATE',0x51);

class	CModel
{
	protected 	$m_pSheme;
	protected 	$m_dataObjectName;
	protected 	$m_dataObjectExtends;
	protected	$m_relationsList;
	protected	$m_complementaryList;
	protected	$m_selectList;
	public		$m_resultList;

	public function
	__construct(string $_shemeName, string $_dataObjectName, string $_dataObjectExtends = '')
	{
		$this -> m_pSheme 				= new $_shemeName();
		$this -> m_dataObjectName 		= $_dataObjectName;
		$this -> m_dataObjectExtends	= $_dataObjectExtends;
		$this -> m_relationsList		= [];
		$this -> m_complementaryList 	= [];
		$this -> m_resultList 			= [];
		$this -> m_selectList			= [];
	}
	
	protected function
	createPrototype($_flags = NULL, $_classAppendix = '')
	{
		$className = 'dta_'.$this -> m_dataObjectName . $_classAppendix;
		
		if(!class_exists($className))
		{
			$columns = $this -> m_pSheme -> getColumns();

			##	prototype 

			$_objectString  = "class $className ". (!empty($this -> m_dataObjectExtends) ? "extends $this -> m_dataObjectExtends " : "") ." {";

				##	Members with columns as name

				foreach($columns as $column)
				{
					if(($_flags & MODEL_PROTOTYPE_EXCEPT_AUTOINCREMENT) && $column -> m_isAutoIncrement)
						continue;

					if(($_flags & MODEL_PROTOTYPE_EXCEPT_VIRTUALCOLUMN) && $column -> m_isVirtual)
						continue;

					if($_flags & MODEL_PROTOTYPE_EXCEPT_ALLCOLUMNS)
						break;

					$_objectString .= " public \$". $column -> m_columnName .";";
				}
			
				## constructor

				$_objectString .= " public function __construct( \$_initialValues , \$_columnsSheme = [] ) { ";

					$_objectString .= " foreach( \$_initialValues as \$_initialKey => \$_initialValue ) { ";

						$_objectString .= " switch(\$_initialKey) {";

							##	Werte der Spalten in die Member Variablen eintragen, Typen Konvertierung gemäß Vorgabe
							foreach($columns as $column)
							{
								if(($_flags & MODEL_PROTOTYPE_EXCEPT_VIRTUALCOLUMN) && $column -> m_isVirtual)
									continue;

								if(($_flags & MODEL_PROTOTYPE_EXCEPT_AUTOINCREMENT) && $column -> m_isAutoIncrement)
									continue;

								switch($column -> m_columnType)
								{
									case DB_COLUMN_TYPE_BIGINT    :
									case DB_COLUMN_TYPE_MEDIUMINT :
									case DB_COLUMN_TYPE_TINYINT	  :
									case DB_COLUMN_TYPE_INT	      : $_objectString .= " case '". $column -> m_columnName ."': \$this->". $column -> m_columnName ." = intval(\$_initialValue); break;"; break;
									case DB_COLUMN_TYPE_TEXT      :
									case DB_COLUMN_TYPE_STRING    : $_objectString .= " case '". $column -> m_columnName ."': \$this->". $column -> m_columnName ." = strval(\$_initialValue); break;"; break;
									case DB_COLUMN_TYPE_BOOL      : $_objectString .= " case '". $column -> m_columnName ."': \$this->". $column -> m_columnName ." = boolval(\$_initialValue); break;"; break;				
									case DB_COLUMN_TYPE_DECIMAL   : 				
									case DB_COLUMN_TYPE_FLOAT     : $_objectString .= " case '". $column -> m_columnName ."': \$this->". $column -> m_columnName ." = floatval(\$_initialValue); break;"; break;				
									case DB_COLUMN_TYPE_ARRAY     : $_objectString .= " case '". $column -> m_columnName ."': \$this->". $column -> m_columnName ." = (is_string(\$_initialValue) ? json_decode(\$_initialValue, true) : \$_initialValue); break;"; break;				
									case DB_COLUMN_TYPE_JSON      : $_objectString .= " case '". $column -> m_columnName ."': \$this->". $column -> m_columnName ." = (is_string(\$_initialValue) ? json_decode(\$_initialValue) : \$_initialValue); break;"; break;				
									default         			  : $_objectString .= " case '". $column -> m_columnName ."': \$this->". $column -> m_columnName ." = \$_initialValue; break;"; break;				
								}
							}


						if(!($_flags & MODEL_PROTOTYPE_EXCEPT_UNKNOWNS))
							$_objectString .= " default: \$this-> \$_initialKey = \$_initialValue; break;"; 

						$_objectString .= " }";

					$_objectString .= " }";
		
					$_objectString .= " foreach(\$_columnsSheme as \$_column) { ";

						$_objectString .= " \$tmp = \$_column -> m_columnName;";
						$_objectString .= " if( !property_exists(\$this, \$tmp ) ) continue; ";
						$_objectString .= " if(\$this -> \$tmp === NULL && isset(\$_column -> m_defaultValue)) { \$this -> \$tmp = \$_column -> m_defaultValue; } ";
					
					$_objectString .= " }";

					if(!empty($this -> m_dataObjectExtends)) $_objectString .= "parent::__construct( \$this );";

				$_objectString .= " }";  // __construct

				##	end	

			$_objectString .= "}";	// class

			eval($_objectString);
		}

		return $className;
	}

	public function
	&getResult()
	{
		return $this -> m_resultList;
	}

	public function
	addRelation(string $_joinType, string $_tableName, CModelCondition $_condition)
	{
		$this -> m_relationsList[] = new CModelRelations($_joinType, $_tableName, $_condition);
	}

	public function
	addComplemantary($_destPropertyName, $_compPropertySrcModel, $_compPropertyDstModel, string $_modelName)
	{
		$this -> m_complementaryList[] 		 = new CModelComplementary($_destPropertyName, $_compPropertySrcModel, $_compPropertyDstModel, $_modelName);
		return $this;
	}

	public function
	addSelectColumns()
	{
		$argList = func_get_args();
		$this -> m_selectList = array_merge($this -> m_selectList, $argList);
	}

	public function
	load(CDatabaseConnection &$_pDatabase, CModelCondition &$_pCondition = NULL, $_execFlags = NULL)
	{
		$className 	= $this -> createPrototype();

		$dbQuery 	= $_pDatabase		-> query(DB_SELECT) 
										-> table($this -> m_pSheme -> getTableName()) 
										-> selectColumns($this -> m_selectList)
										-> dtaObjectName($className)
										-> condition($_pCondition)
										-> relations($this -> m_relationsList);

		$this -> m_resultList = $dbQuery -> exec($_execFlags);

		if($this -> m_resultList === false)
			return 0;

		$dtaCount = count($this -> m_resultList);

		if($this -> m_complementaryList !== NULL)
		{
			for($i = 0; $i < $dtaCount; $i++)
			{
				foreach($this -> m_complementaryList as $complementary)
				{
					$propertyName 			= $complementary -> m_propertyName;
					$compPropertySrcModel 	= $complementary -> m_compPropertySrcModel;
					$compPropertyDstModel 	= $complementary -> m_compPropertyDstModel;
					$compModelName 			= $complementary -> m_modelName;

					$condition		 = new CModelCondition();
					$condition		-> where($compPropertyDstModel, $this -> m_resultList[$i] -> $compPropertySrcModel);

					$modelInstance = new $compModelName();
					$modelInstance -> load($_pDatabase, $condition);

					$this -> m_resultList[$i] -> $propertyName = $modelInstance -> m_resultList;
				}
			}
		}

		return $dtaCount;
	}

	public function
	insert(CDatabaseConnection &$_pDatabase, array $_dataset, $_execFlags = NULL)
	{
		$className = $this -> createPrototype(MODEL_PROTOTYPE_EXCEPT_AUTOINCREMENT | MODEL_PROTOTYPE_EXCEPT_VIRTUALCOLUMN | MODEL_PROTOTYPE_EXCEPT_UNKNOWNS, 'Insert');

		$dtaObject = new $className($_dataset, $this -> m_pSheme -> getColumns());

		$dbQuery 	= $_pDatabase		-> query(DB_INSERT) 
										-> table($this -> m_pSheme -> getTableName()) 
										-> dtaObject($dtaObject);

		if($_execFlags & MODEL_RESULT_RESET)	
			$this -> m_resultList = [];

		if($_execFlags & MODEL_RESULT_APPEND_DTAOBJECT)	
			$this -> m_resultList[] = $dtaObject;

		return $dbQuery -> exec($_execFlags);
	}

	public function
	update(CDatabaseConnection &$_pDatabase, array $_insertData, CModelCondition &$_pCondition, $_execFlags = NULL)
	{
		if(!$_pCondition -> isSet())
			return false;

		$className = $this -> createPrototype(MODEL_PROTOTYPE_EXCEPT_ALLCOLUMNS | MODEL_PROTOTYPE_EXCEPT_VIRTUALCOLUMN | MODEL_PROTOTYPE_EXCEPT_UNKNOWNS, 'Update');


		$dtaObject = new $className($_insertData);

		$dbQuery 	= $_pDatabase		-> query(DB_UPDATE) 
										-> table($this -> m_pSheme -> getTableName()) 
										-> dtaObject($dtaObject)
										-> condition($_pCondition);

		if($_execFlags & MODEL_RESULT_RESET)	
			$m_resultList = [];

		if($_execFlags & MODEL_RESULT_APPEND_DTAOBJECT)	
			$m_resultList[] = $dtaObject;

		return $dbQuery -> exec($_execFlags);
	}

	public function
	truncate(CDatabaseConnection &$_pDatabase, $_execFlags = NULL)
	{
		$dbQuery 	= $_pDatabase		-> query(DB_TRUNCATE) 
										-> table($this -> m_pSheme -> getTableName());

		return $dbQuery -> exec($_execFlags);
	}

	public function
	delete(CDatabaseConnection &$_pDatabase, CModelCondition &$_pCondition, $_execFlags = NULL)
	{
		if(!$_pCondition -> isSet())
			return false;

		$dbQuery 	= $_pDatabase		-> query(DB_DELETE) 
										-> table($this -> m_pSheme -> getTableName())
										-> condition($_pCondition);

		return $dbQuery -> exec($_execFlags);		
	}

	public function
	unique(CDatabaseConnection &$_pDatabase, CModelCondition &$_pCondition, $_execFlags = NULL)
	{
		if(!$_pCondition -> isSet())
			return false;

		$dbQuery 	= $_pDatabase		-> query(DB_SELECT) 
										-> table($this -> m_pSheme -> getTableName()) 
										-> selectColumns($this -> m_selectList)
										-> condition($_pCondition);

		return !boolval(count($dbQuery -> exec($_execFlags)));	
	}

	protected function
	decryptRawSQLDataset(object &$_sqlDataset, string $_key, array $_columns)
	{		
		foreach($_columns as $_column)
		{
			if(!empty($_sqlDataset -> $_column))	
				$_sqlDataset -> $_column = CRYPT::DECRYPT($_sqlDataset -> $_column, $_key, true);
		}	
	}

	protected function
	encryptRawSQLDataset(array &$_sqlDataset, string $_key, array $_columns)
	{		
		foreach($_columns as $_column)
		{
			if(!empty($_sqlDataset[$_column]))	
				$_sqlDataset[$_column] = CRYPT::ENCRYPT($_sqlDataset[$_column], $_key, true);
		}
	}

	public function
	getResultItem(string $_srcKey, $_srcValue, ?string $_dstValue = NULL)
	{
		foreach($this -> m_resultList as $resultItm)
		{
			if(property_exists($resultItm, $_srcKey) && $resultItm -> $_srcKey === $_srcValue)
			{
				if($_dstValue !== NULL && property_exists($resultItm, $_dstValue))
					return $resultItm -> $_dstValue;
				else
					return $resultItm;
			}
		}

		return NULL;
	}

	public function
	ping(CDatabaseConnection &$_pDatabase, string $_userId, string $_systemId, string $_pingId, $_execFlags = NULL)
	{
		$tableName		=	$this -> m_pSheme -> getTableName();
		$systemIdColumn	= 	$this -> m_pSheme -> getSystemIdColumnName();

		$timeStamp 		   = time();
		$timeOut		   = $timeStamp - CFG::GET() -> USER_SYSTEM -> MODULE_LOCKING -> LOCK_TIMEOUT;

		$responseData	   = [
								'lockedById' 	=> 0,
								'lockedState' 	=> 0
							];

		if($systemIdColumn === NULL)
			return $responseData;

		$selectCondition = new CModelCondition();
		$selectCondition -> where($systemIdColumn, $_systemId);

		$dbQuery 	= $_pDatabase		-> query(DB_SELECT) 
										-> table($this -> m_pSheme -> getTableName()) 
										-> selectColumns(['lock_time', 'lock_by', 'lock_id'])
										-> condition($selectCondition);

		$this -> m_resultList = $dbQuery -> exec($_execFlags);

		if(count($this -> m_resultList) == 1)
		{
			$resultItm = $this -> m_resultList[0];

			if(		$resultItm -> lock_by == 0 
				|| ($resultItm -> lock_by == $_userId && $resultItm -> lock_id == $_pingId)
			  )
			{
				## Lock dataset

				$responseData['lockedState']	= 0;	
				$responseData['lockedMessage']	= '';
				$responseData['lockedById'] 	= $_userId;

				if($_execFlags & MODEL_LOCK_UPDATE)
				{
					$dtaObject  = new stdClass;
					$dtaObject -> lock_by 	= $_userId;
					$dtaObject -> lock_id 	= $_pingId;
					$dtaObject -> lock_time = $timeStamp;

					$updateCondition = new CModelCondition();
					$updateCondition -> where($systemIdColumn, $_systemId);

					$dbQuery 	= $_pDatabase		-> query(DB_UPDATE) 
													-> table($this -> m_pSheme -> getTableName()) 
													-> dtaObject($dtaObject)
													-> condition($updateCondition)
													-> exec($_execFlags);
				}
			}
			elseif($resultItm -> lock_by == $_userId && $resultItm -> lock_id != $_pingId && $timeOut <= $resultItm -> lock_time)
			{
				## Locked by same user but different pingId

				$username = TK::getBackendUserName($_pDatabase, $resultItm -> lock_by);
				$responseData['lockedByName'] = (!empty($username) ? $username : CLanguage::get() -> string('UNKNOWN'));

				$responseData['lockedState']	= 1;	
				$responseData['lockedMessage']	= CLanguage::get() -> string('LOCK_IS_MISMATCH_ID');
				$responseData['lockedById'] 	= $_userId;
			}
			elseif($resultItm -> lock_by != $_userId && $timeOut <= $resultItm -> lock_time)
			{
				## Locked by other user

				$username = TK::getBackendUserName($_pDatabase, $resultItm -> lock_by);
				$responseData['lockedByName'] = (!empty($username) ? $username : CLanguage::get() -> string('UNKNOWN'));

				$responseData['lockedState']	= 1;	
				$responseData['lockedMessage']	= CLanguage::get() -> string('LOCK_IS_LOCKED') .' <b>'. $responseData['lockedByName'] .'</b>';
				$responseData['lockedById'] 	= $resultItm -> lock_by;
			}
			elseif(		$resultItm -> lock_by != $_userId && $timeOut > $resultItm -> lock_time
					|| ($resultItm -> lock_by == $_userId && $resultItm -> lock_id != $_pingId && $timeOut > $resultItm -> lock_time)
				  )
			{
				## Locked by other user but timed out

				$username = TK::getBackendUserName($_pDatabase, $resultItm -> lock_by);
				$responseData['lockedByName'] = (!empty($username) ? $username : CLanguage::get() -> string('UNKNOWN'));

				$responseData['lockedState']	= 2;	
				$responseData['lockedMessage']	= '';
				$responseData['lockedById'] 	= $_userId;

				if($_execFlags & MODEL_LOCK_UPDATE)
				{
					$dtaObject  = new stdClass;
					$dtaObject -> lock_by 	= $_userId;
					$dtaObject -> lock_id 	= $_pingId;
					$dtaObject -> lock_time = $timeStamp;

					$updateCondition = new CModelCondition();
					$updateCondition -> where($systemIdColumn, $_systemId);

					$dbQuery 	= $_pDatabase		-> query(DB_UPDATE) 
													-> table($this -> m_pSheme -> getTableName()) 
													-> dtaObject($dtaObject)
													-> condition($updateCondition)
													-> exec($_execFlags);
				}
			}
		}

		return $responseData;
	}	
}



/*
define('LOCK_UPDATE',0x1);

class	CModel
{
	protected	$m_storage;

	protected	$m_relationsList;
	protected	$m_selectList;

	protected	$m_className;
	protected	$m_sheme;

	public function
	__construct(string $_className = '')
	{
		$this -> m_storage 				= [];

		$this -> m_relationsList		= [];
		$this -> m_selectList			= [];
		$this -> m_className			= $_className;
	}

	public function
	addRelation(string $_joinType, string $_tableName, CModelCondition $_condition)
	{
		$this -> m_relationsList[] = new CModelRelations($_joinType, $_tableName, $_condition);
	}

	public function
	addSelectColumns()
	{
		$argList = func_get_args();
		$this -> m_selectList = array_merge($this -> m_selectList, $argList);
	}

	private function
	getSelectColumns()
	{
		if(count($this -> m_selectList) == 0)
			return '*';

		return implode(',', $this -> m_selectList);
	}

	/**
	 * 	Function to load data from the database
	 * @param object $_sqlConnection valid sql connection
	 * @param object $_condition CModelCondition instance (optional)
	 * @return bool	Returns true if succeeded, otherwise false
	 *
	public function
	load(&$_sqlConnection, CModelCondition $_condition = NULL, CModelComplementary $_complementary = NULL)
	{
		// Adding select columns to Sheme instance

		foreach($this -> m_selectList as $selectItem)
		{
			if($selectItem === '*')
				continue;

			if(strpos($selectItem, '.*') !== false)
				continue;

			$selectItem = strtolower($selectItem);
			$selectItem = explode(' as ', $selectItem);
			$selectItem = (count($selectItem) == 1 ? $selectItem[0] : $selectItem[1]);
			$selectItem = explode('.', $selectItem);
			$selectItem = (count($selectItem) == 1 ? $selectItem[0] : $selectItem[1]);

			$this -> m_sheme -> addColumn($selectItem, 'string');
		}

		// Create class prototpe

		$className	=	$this -> createClass($this -> m_sheme, $this -> m_className, '');

		// Generate sql string

		$sqlString   =  "";
		$sqlString	.=	"	SELECT		". $this -> getSelectColumns();
		$sqlString	.=	"	FROM		". $this -> m_sheme -> getTableName();

		foreach($this -> m_relationsList as $relation)
		{
			$sqlString	.=	"	". $relation -> joinType ." ". $relation -> tableName;
			$sqlString	.=	"		ON ". $relation -> condition -> getRelationConditions($_sqlConnection, $relation -> condition);
		}

		$sqlString	.=	"	". ($_condition != NULL ? $_condition -> getConditions($_sqlConnection, $_condition) : '');

		// Query and process data

		$sqlResult	 =	$_sqlConnection -> query($sqlString);

		while($sqlResult !== false && $sqlRow = $sqlResult -> fetch_assoc())
		{	
			$this -> m_storage[] = new $className($sqlRow, $this -> m_sheme -> getColumns());

			//	Add complementary data from another model result into array if property compares

			if($_complementary !== NULL)
			{
				$index = count($this -> m_storage) - 1;

				foreach($_complementary -> complementaryList as $complementarySet)
				{
					$propertyName 		= $complementarySet -> propertyName;
					$propertyCompare 	= $complementarySet -> propertyCompare;

					$this -> m_storage[$index] -> $propertyName = [];

					foreach($complementarySet -> storageInstance as $subDataset)
					{
						if($this -> m_storage[$index] -> $propertyCompare === $subDataset -> $propertyCompare)
						{
							$this -> m_storage[$index] -> $propertyName[] = $subDataset; 
						}
					}
				}
			}

		}
		
		return true;
	}

	/**
	 * 	Function to load data from the database
	 * @param object $_sqlConnection valid sql connection
	 * @param array $_dataset Reference to Array with the values to be inserted
	 * @param int $_condition Reference to variable that get the inserted id
	 * @return bool	Returns true if succeeded, otherwise false
	 *
	public function
	insert(&$_sqlConnection, &$_dataset, &$_insertedId)
	{
		$className		=	$this -> createClass($this -> m_sheme, $this -> m_className);
		$tableName		=	$this -> m_sheme -> getTableName();

		$model			= 	new $className($_dataset, $this -> m_sheme -> getColumns());

		$sqlString		=	"INSERT INTO $tableName	SET ";

		$loopCounter 	= 0;
		foreach($this -> m_sheme -> getColumns() as $column)
		{
			if($column -> isVirtual) continue;
			if(!property_exists($model, $column -> name)) continue;
			$tmp		 = $column -> name;
			$sqlString 	.= ($loopCounter != 0 ? ', ':'');
			$sqlString 	.= "`".$column -> name ."` = '". $model -> $tmp ."'";
			$loopCounter++;
		}
		
		if($_sqlConnection -> query($sqlString) !== false) 
		{
			$_insertedId = $_sqlConnection -> insert_id;
			$this -> m_storage[] = $model;
			return true;
		}
		return false;
	}

	public function
	update(&$_sqlConnection, &$_dataset, CModelCondition $_condition = NULL)
	{
		if($_condition === NULL || !$_condition -> isSet()) return false;

		$tableName	=	$this -> m_sheme -> getTableName();

		$sqlString		 =	"UPDATE $tableName SET ";
		$loopCounter 	= 0;
		foreach($_dataset as $column => $_value)
		{	
			if(!$this -> m_sheme -> columnExists(true, $column)) continue;
			$sqlString  .= ($loopCounter != 0 ? ', ':'');
			$sqlString  .= "`". $_sqlConnection -> real_escape_string($column) ."` = '". $_sqlConnection -> real_escape_string($_value) ."'";
			$loopCounter++;
		}

		$sqlString	.=	$_condition -> getConditions($_sqlConnection, $_condition);

		if($_sqlConnection -> query($sqlString) !== false) return true;
		return false;
	}
	
	public function
	delete(&$_sqlConnection, CModelCondition $_condition = NULL)
	{
		if($_condition === NULL || !$_condition -> isSet()) return false;
	
		$tableName	=	$this -> m_sheme -> getTableName();
		
		$sqlString	 =	"	DELETE FROM $tableName 
						".	$_condition -> getConditions($_sqlConnection, $_condition);

		if($_sqlConnection -> query($sqlString) !== false) return true;
		return false;
	}

	#protected function ( temporär wegen backend handling)
	public function
	createClass(&$_targetSheme, string $_nameAppendix = '', string $_parentClass = '')
	{
		$_className = __CLASS__.'_data'.(!empty($_nameAppendix) ? '_'.$_nameAppendix : '');

		if(!class_exists($_className))
		{
			$this -> _createClass( $_className , $_targetSheme -> getColumns(), $_parentClass);
		}
		return $_className;
	}

	private function
	_createClass(string $_objectName , array $_columns , string $_extends = '')
	{
		$_objectString  = "class $_objectName ". (!empty($_extends) ? "extends $_extends " : "") ." {";

			##	Members with columns as name

			foreach($_columns as $_column)
				$_objectString .= " public \$". $_column -> name .";";
		
			## constructor

			$_objectString .= " public function __construct( \$_initialValues , \$_columnsSheme ) { ";

				$_objectString .= " foreach( \$_initialValues as \$_initialKey => \$_initialValue ) { ";

					$_objectString .= " switch(\$_initialKey) {";

						##	Werte der Spalten in die Member Variablen einträgen, Typen Konvertierung gemäß Vorgabe
						foreach($_columns as $_column)
						{
							switch($_column -> type)
							{
								case 'bigint'   :
								case 'mediumint':
								case 'tinyint':
								case 'int'	    : $_objectString .= " case '". $_column -> name ."': \$this->". $_column -> name ." = intval(\$_initialValue); break;"; break;
								case 'text'     :
								case 'string'   : $_objectString .= " case '". $_column -> name ."': \$this->". $_column -> name ." = strval(\$_initialValue); break;"; break;
								case 'bool'     : $_objectString .= " case '". $_column -> name ."': \$this->". $_column -> name ." = boolval(\$_initialValue); break;"; break;				
								default         : $_objectString .= " case '". $_column -> name ."': \$this->". $_column -> name ." = \$_initialValue; break;"; break;				
							}
						}

					$_objectString .= " }";

				$_objectString .= " }";

				$_objectString .= " foreach(\$_columnsSheme as \$_column) { ";

					$_objectString .= " \$tmp = \$_column -> name;";
					$_objectString .= " if( !property_exists(\$this, \$tmp ) ) continue; ";
					$_objectString .= " if(\$this -> \$tmp === NULL && isset(\$_column -> defaultValue)) { \$this -> \$tmp = \$_column -> defaultValue; } ";
				
				$_objectString .= " }";

				if(!empty($_extends)) $_objectString .= "parent::__construct( \$this );";

			$_objectString .= " }";  // __construct

			##	end	

		$_objectString .= "}";	// class

		eval($_objectString);
	}	

	public function
	&getDataInstance()
	{
		return $this -> m_storage;
	}

	protected function
	isDatasetExists(&$_sqlConnection, string $_tableName, array $_where)
	{
		if(count($_where) === 0)
		{
			trigger_error(__CLASS__.'::'.__FUNCTION__ .'() -- Call of function without valid $_where parameter', E_USER_NOTICE);
			return false;
		}

		if(empty($_tableName))
		{
			trigger_error(__CLASS__.'::'.__FUNCTION__ .'() -- Call of function without valid $_tableName', E_USER_NOTICE);
			return false;
		}

		$_sqlWhere = [];

		foreach($_where as $_whereKey => $_whereColumn)
		{
			$_sqlWhere[] = " `$_whereKey` = '". $_sqlConnection -> real_escape_string($_whereColumn) . "' ";
		}

		$_sqlSelect	=	"	SELECT		`". key($_where) ."`
							FROM		$_tableName	
							WHERE ". implode(' AND ', $_sqlWhere) ."
						";

		$_sqlResult	=	 $_sqlConnection -> query($_sqlSelect);

		if($_sqlResult -> num_rows > 0) return true;
		return false;
	}

	public function
	searchValue($_needle, string $_searchColumn, string $_returnColumn)
	{
		foreach($this -> m_storage as $_model)
		{
			if($_model -> $_searchColumn === $_needle) return $_model -> $_returnColumn;
		}
		return NULL;
	}	

	public function
	isUnique(&$_sqlConnection, array $_where, array $_whereNot = [])
	{
		$tableName	=	$this -> m_sheme -> getTableName();

		if(count($_where) === 0)
			return false;

		$_sqlWhere = [];
		foreach($_where as $_whereKey => $_whereColumn)
			$_sqlWhere[] = " `$_whereKey` = '". $_sqlConnection -> real_escape_string($_whereColumn) . "' ";
		
		$_sqlWhereNot = [];
		foreach($_whereNot as $_whereKey => $_whereColumn)
			$_sqlWhereNot[] = " `$_whereKey` != '". $_sqlConnection -> real_escape_string($_whereColumn) . "' ";
		
		$sqlString	=	"	SELECT		`data_id` 
							FROM		`$tableName`
						";

		if(count($_sqlWhere) !== 0)
			$sqlString	.=	" WHERE ". implode(' AND ', $_sqlWhere);

		if(count($_sqlWhereNot) !== 0)
			$sqlString	.=	" AND ". implode(' AND ', $_sqlWhereNot);

		$sqlResult	=	$_sqlConnection -> query($sqlString);

		if($sqlResult -> num_rows === 0)
			return true;
		
		return false;
	}	

	public function
	lock(&$_sqlConnection, string $_userId, $_systemId, $_flags = NULL)
	{
		$tableName		=	$this -> m_sheme -> getTableName();
		$systemIdColumn	= 	$this -> m_sheme -> getSystemIdColumnName();

		$timeStamp 		   = time();
		$timeOut		   = $timeStamp - CFG::GET() -> USER_SYSTEM -> MODULE_LOCKING -> LOCK_TIMEOUT;

		$responseData	   = [
								'lockedById' 	=> 0,
								'lockedState' 	=> 0
							];

		if($systemIdColumn === NULL)
			return $responseData;
		
		$sqlString	= 	"	SELECT 		$tableName.lock_by,
										$tableName.lock_time
							FROM		$tableName
							WHERE		$tableName.$systemIdColumn = '". $_sqlConnection -> real_escape_string($_systemId) ."'
						";

		$sqlLockRes =	$_sqlConnection -> query($sqlString);

		if($sqlLockRes !== false && $sqlLockRes -> num_rows == 1)
		{
			$sqlLockItm = $sqlLockRes -> fetch_assoc();

			if	(
						$sqlLockItm['lock_by'] !== $_userId
					&&	$timeOut <= $sqlLockItm['lock_time']
				)	
			{
				##	dataset locked by other user

				$responseData['lockedById'] 	= $sqlLockItm['lock_by'];
				$responseData['lockedState']	= 1;

				$username = TK::getBackendUserName($_sqlConnection, $sqlLockItm['lock_by']);
				$responseData['lockedByName'] = (!empty($username) ? $username : CLanguage::get() -> string('UNKNOWN'));

				$responseData['lockedMessage']	= CLanguage::get() -> string('LOCK_IS_LOCKED') .' <b>'. $responseData['lockedByName'] .'</b>';
			}
			else
			{
				$lockedTime = date('Y-m-d', $sqlLockItm['lock_time']);
				$lockedTime = strtotime($lockedTime .' 01:00:00');

				$currentTime = date('Y-m-d');
				$currentTime = strtotime($currentTime .' 01:00:00');

				$responseData['lockedById'] 	= $_userId;

				if($lockedTime < $currentTime || $sqlLockItm['lock_by'] == '0')
				{
					##	lock dataset 

					$responseData['lockedState']	= 0;	
					$responseData['lockedMessage']	= '';

					$sqlString	= 	"	UPDATE 		$tableName
										SET			$tableName.lock_time 		= '". $timeStamp ."',
													$tableName.lock_by			= '". $_userId ."'
										WHERE		$tableName.$systemIdColumn	= '". $_sqlConnection -> real_escape_string($_systemId) ."'
									";


 					if($_flags & LOCK_UPDATE)
						$_sqlConnection -> query($sqlString);
				}
				elseif($sqlLockItm['lock_by'] != '0' && $sqlLockItm['lock_by'] !== $_userId)
				{
					##	not locked

					##	this state gets removed later, we need this to force the user to reload the current page

					$responseData['lockedState']	= 2;	
					$responseData['lockedMessage']	= CLanguage::get() -> string('LOCK_IS_NOT_LOCKED') .'
					
														<a class="" href=""><div class="submit-container button-only refresh-button"><button type="button" class="ui button icon labeled" style="width:100%;"><span><i class="fas fa-sync-alt" data-icon="fa-sync-alt"></i></span> '. CLanguage::get() -> string('BUTTON_REFRESH') .'</button></div></a>
														';

					$sqlString	= 	"	UPDATE 		$tableName
										SET			$tableName.lock_time 		= '0',
													$tableName.lock_by			= '0'
										WHERE		$tableName.$systemIdColumn	= '". $_sqlConnection -> real_escape_string($_systemId) ."'
									";
				
 					if($_flags & LOCK_UPDATE)
						$_sqlConnection -> query($sqlString);
				}
			}
		}

		return $responseData;
	}

}
*/

?>