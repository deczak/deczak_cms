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
	protected 	$m_pScheme;
	protected 	$m_dataObjectName;
	protected 	$m_dataObjectExtends;
	protected	$m_relationsList;
	protected	$m_complementaryList;
	protected	$m_selectList;
	public		$m_resultList;

	public function
	__construct(string $_schemeName, string $_dataObjectName, string $_dataObjectExtends = '')
	{
		$this -> m_pScheme 				= new $_schemeName();
		$this -> m_dataObjectName 		= $_dataObjectName;
		$this -> m_dataObjectExtends	= $_dataObjectExtends;
		$this -> m_relationsList		= [];
		$this -> m_complementaryList 	= [];
		$this -> m_resultList 			= [];
		$this -> m_selectList			= [];
	}

	public function
	setScheme(string $_schemeName)
	{
		$this -> m_pScheme 				= new $_schemeName();
	}
	
	public function
	setObjectName(string $_objectName)
	{
		$this -> m_dataObjectName 		= $_objectName;
	}
	
	protected function
	createPrototype($_flags = NULL, $_classAppendix = '')
	{
		$className = 'dta_'.$this -> m_dataObjectName . $_classAppendix;
		
		if(!class_exists($className))
		{
			$columns = $this -> m_pScheme -> getColumns();

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

				$_objectString .= " public function __construct( \$_initialValues , \$_columnsScheme = [] ) { ";

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
		
					$_objectString .= " foreach(\$_columnsScheme as \$_column) { ";

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
	load(CDatabaseConnection &$_pDatabase, CModelCondition $_pCondition = NULL, $_execFlags = NULL)
	{
		$className 	= $this -> createPrototype();

		$dbQuery 	= $_pDatabase		-> query(DB_SELECT) 
										-> table($this -> m_pScheme -> getTableName()) 
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

		$dtaObject = new $className($_dataset, $this -> m_pScheme -> getColumns());

		$dbQuery 	= $_pDatabase		-> query(DB_INSERT) 
										-> table($this -> m_pScheme -> getTableName()) 
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
										-> table($this -> m_pScheme -> getTableName()) 
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
										-> table($this -> m_pScheme -> getTableName());

		return $dbQuery -> exec($_execFlags);
	}

	public function
	delete(CDatabaseConnection &$_pDatabase, CModelCondition &$_pCondition, $_execFlags = NULL)
	{
		if(!$_pCondition -> isSet())
			return false;

		$dbQuery 	= $_pDatabase		-> query(DB_DELETE) 
										-> table($this -> m_pScheme -> getTableName())
										-> condition($_pCondition);

		return $dbQuery -> exec($_execFlags);		
	}

	public function
	unique(CDatabaseConnection &$_pDatabase, CModelCondition &$_pCondition, $_execFlags = NULL)
	{
		if(!$_pCondition -> isSet())
			return false;

		$dbQuery 	= $_pDatabase		-> query(DB_SELECT) 
										-> table($this -> m_pScheme -> getTableName()) 
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
		$tableName		=	$this -> m_pScheme -> getTableName();
		$systemIdColumn	= 	$this -> m_pScheme -> getSystemIdColumnName();

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
										-> table($this -> m_pScheme -> getTableName()) 
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
													-> table($this -> m_pScheme -> getTableName()) 
													-> dtaObject($dtaObject)
													-> condition($updateCondition)
													-> exec($_execFlags);
				}
			}
			elseif($resultItm -> lock_by == $_userId && $resultItm -> lock_id != $_pingId && $timeOut <= $resultItm -> lock_time)
			{
				## Locked by same user but different pingId

				$username = TK::getBackendUserName($_pDatabase, $resultItm -> lock_by);
				$responseData['lockedByName'] = (!empty($username) ? $username : CLanguage::string('UNKNOWN'));

				$responseData['lockedState']	= 1;	
				$responseData['lockedMessage']	= CLanguage::string('LOCK_IS_MISMATCH_ID');
				$responseData['lockedById'] 	= $_userId;
			}
			elseif($resultItm -> lock_by != $_userId && $timeOut <= $resultItm -> lock_time)
			{
				## Locked by other user

				$username = TK::getBackendUserName($_pDatabase, $resultItm -> lock_by);
				$responseData['lockedByName'] = (!empty($username) ? $username : CLanguage::string('UNKNOWN'));

				$responseData['lockedState']	= 1;	
				$responseData['lockedMessage']	= CLanguage::string('LOCK_IS_LOCKED') .' <b>'. $responseData['lockedByName'] .'</b>';
				$responseData['lockedById'] 	= $resultItm -> lock_by;
			}
			elseif(		$resultItm -> lock_by != $_userId && $timeOut > $resultItm -> lock_time
					|| ($resultItm -> lock_by == $_userId && $resultItm -> lock_id != $_pingId && $timeOut > $resultItm -> lock_time)
				  )
			{
				## Locked by other user but timed out

				$username = TK::getBackendUserName($_pDatabase, $resultItm -> lock_by);
				$responseData['lockedByName'] = (!empty($username) ? $username : CLanguage::string('UNKNOWN'));

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
													-> table($this -> m_pScheme -> getTableName()) 
													-> dtaObject($dtaObject)
													-> condition($updateCondition)
													-> exec($_execFlags);
				}
			}
		}

		return $responseData;
	}	
}
