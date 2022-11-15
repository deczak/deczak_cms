<?php

class cmsModelConstruct
{
	public static function
	createPrototype(string $modelName, $_flags = NULL)
	{
		$className = $modelName.'Item';
		
		if(!class_exists($className))
		{
			$schemeInstance = new $modelName::$schemeName();
			$columns = $schemeInstance -> getColumns();

			##	prototype 

			$_objectString  = "class $className ". (!empty($modelName) ? "extends $modelName " : "") ." {\r\n\r\n";

				##	Members with columns as name

				foreach($columns as $column)
				{
					if(($_flags & MODEL_PROTOTYPE_EXCEPT_AUTOINCREMENT) && $column -> m_isAutoIncrement)
						continue;

					if(($_flags & MODEL_PROTOTYPE_EXCEPT_VIRTUALCOLUMN) && $column -> m_isVirtual)
						continue;

					if($_flags & MODEL_PROTOTYPE_EXCEPT_ALLCOLUMNS)
						break;



					switch($column -> m_columnType)
					{
						case DB_COLUMN_TYPE_BIGINT    :
						case DB_COLUMN_TYPE_MEDIUMINT :
						case DB_COLUMN_TYPE_TINYINT	  :
						case DB_COLUMN_TYPE_INT	      : $type = ($column -> m_isAutoIncrement ? '?':'').'int'; 		break;
						case DB_COLUMN_TYPE_TEXT      :
						case DB_COLUMN_TYPE_STRING    : $type = 'string'; 	break;
						case DB_COLUMN_TYPE_BOOL      : $type = 'bool'; 	break;				
						case DB_COLUMN_TYPE_DECIMAL   : 				
						case DB_COLUMN_TYPE_FLOAT     : $type = 'double'; 	break;			
						case DB_COLUMN_TYPE_ARRAY     : $type = 'array'; 	break;	
						case DB_COLUMN_TYPE_JSON      : $type = 'object'; 	break;	
						default         			  : $type = null; 	break;
					}




					$_objectString .= "\tpublic ". ($type !== null && $column -> m_isNull ? "?" : "")."$type \$". $column -> m_columnName .";\r\n";
				}
			
				## constructor

				$_objectString .= "\r\n ";
				$_objectString .= "\tpublic function __construct(\$_initialValues = null, \$_columnsScheme = []) {\r\n\r\n";

					$_objectString .= "\t\tif(\$_initialValues !== null) foreach( \$_initialValues as \$_initialKey => \$_initialValue ) {\r\n\r\n ";

						$_objectString .= "\t\t\tswitch(\$_initialKey) {\r\n";

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
									case DB_COLUMN_TYPE_INT	      : $_objectString .= "\t\t\t\tcase '". $column -> m_columnName ."':\r\n\t\t\t\t\t\$this->". $column -> m_columnName ." = intval(\$_initialValue); break;\r\n"; break;
									case DB_COLUMN_TYPE_TEXT      :
									case DB_COLUMN_TYPE_STRING    : $_objectString .= "\t\t\t\tcase '". $column -> m_columnName ."':\r\n\t\t\t\t\t\$this->". $column -> m_columnName ." = strval(\$_initialValue); break;\r\n"; break;
									case DB_COLUMN_TYPE_BOOL      : $_objectString .= "\t\t\t\tcase '". $column -> m_columnName ."':\r\n\t\t\t\t\t\$this->". $column -> m_columnName ." = boolval(\$_initialValue); break;\r\n"; break;				
									case DB_COLUMN_TYPE_DECIMAL   : 				
									case DB_COLUMN_TYPE_FLOAT     : $_objectString .= "\t\t\t\tcase '". $column -> m_columnName ."':\r\n\t\t\t\t\t\$this->". $column -> m_columnName ." = floatval(\$_initialValue); break;\r\n"; break;				
									case DB_COLUMN_TYPE_ARRAY     : $_objectString .= "\t\t\t\tcase '". $column -> m_columnName ."':\r\n\t\t\t\t\t\$this->". $column -> m_columnName ." = (is_string(\$_initialValue) ? json_decode(\$_initialValue, true) : \$_initialValue); break;\r\n"; break;				
									case DB_COLUMN_TYPE_JSON      : $_objectString .= "\t\t\t\tcase '". $column -> m_columnName ."':\r\n\t\t\t\t\t\$this->". $column -> m_columnName ." = (is_string(\$_initialValue) ? (object)json_decode(\$_initialValue) : \$_initialValue); break;\r\n"; break;				
									default         			  : $_objectString .= "\t\t\t\tcase '". $column -> m_columnName ."':\r\n\t\t\t\t\t\$this->". $column -> m_columnName ." = \$_initialValue; break;\r\n"; break;				
								}
							}


						if(!($_flags & MODEL_PROTOTYPE_EXCEPT_UNKNOWNS))
							$_objectString .= "\t\t\t\tdefault:\r\n\t\t\t\t\t\$this-> \$_initialKey = \$_initialValue; break;\r\n"; 

						$_objectString .= "\t\t\t}\r\n";

					$_objectString .= "\t\t}\r\n\r\n";
		
					$_objectString .= "\t\tforeach(\$_columnsScheme as \$_column) {\r\n\r\n";

						$_objectString .= "\t\t\t\$tmp = \$_column -> m_columnName;\r\n";
						$_objectString .= "\t\t\tif(!property_exists(\$this, \$tmp))\r\n\t\t\t\tcontinue;\r\n";

						$_objectString .= "\t\t\t\$reflectProp = new ReflectionProperty(self::class, \$tmp);\r\n";

						$_objectString .= "\t\t\tif(!\$reflectProp->isInitialized(\$this)) {switch(\$_column -> m_columnType){case DB_COLUMN_TYPE_JSON:  \$this -> \$tmp = new stdClass; continue 2;case DB_COLUMN_TYPE_ARRAY:  \$this -> \$tmp = []; continue 2;}}\r\n";
						$_objectString .= "\t\t\tif(!\$reflectProp->isInitialized(\$this) && \$_column -> m_isAutoIncrement) {\r\n\t\t\t\t\$this -> \$tmp = null;\r\n\t\t\t}\r\n";
						$_objectString .= "\t\t\tif(!\$reflectProp->isInitialized(\$this) && \$_column -> m_defaultValue !== null) {\r\n\t\t\t\t\$this -> \$tmp = \$_column -> m_defaultValue;\r\n\t\t\t}\r\n";
						$_objectString .= "\t\t\tif(!\$reflectProp->isInitialized(\$this) && \$_column -> m_isNull) {\r\n\t\t\t\t\$this -> \$tmp = null;\r\n\t\t\t}\r\n";

					
					$_objectString .= "\t\t}\r\n\r\n";

					if(!empty($modelName)) $_objectString .= "\t\tparent::__construct( \$this );\r\n";

				$_objectString .= "\t}\r\n";  // __construct

				##	end	

			$_objectString .= "}";	// class

			eval($_objectString);
		}

		return $className;
	}

}

class cmsModelQuery
{
	public function
	__construct(string $modelName)
	{
		$this->model($modelName);
	}

	public function
	model(string $modelName): cmsModelQuery
	{
		$this->modelName  = $modelName;
		$this->schemeName = $modelName::$schemeName;
		return $this;
	}

	public function
	limit(int $limit, ?int $offset = null): cmsModelQuery
	{
		$this->limit  = $limit;
		if($offset !== null)
			$this->offset = $offset;
		return $this;
	}

	public function
	offset(int $offset): cmsModelQuery
	{
		$this->offset = $offset;
		return $this;
	}

	public function
	where(string $column, string $condition, $value) : cmsModelQuery
	{
		if(!isset($this->where))
			$this->where = [[]];
		$this->where[ count($this->where) - 1 ][] = [
			'column' => $column,
			'condition' => $condition,
			'value' => $value,
		];
		return $this;
	}

	public function
	whereIn(string $column, array $values) : cmsModelQuery
	{
		if(!isset($this->where))
			$this->where = [[]];
		$this->whereIn[ count($this->whereIn) - 1 ][] = [
			'column' => $column,
			'values' => $values,
		];
		return $this;
	}

	public function
	get()
	{
		$pDBInstance  = CDatabase::instance();
		$dbConnection = $pDBInstance -> getConnection(CFG::GET() -> MYSQL -> PRIMARY_DATABASE);

		$schemeInstance = new $this->schemeName;

		$tableName = $schemeInstance -> getTableName();

		$className = cmsModelConstruct::createPrototype($this->modelName);

		$pModelCondition = new CModelCondition;
		$pModelCondition->limit($this->limit ?? 0, $this->offset ?? 0);

		if(isset($this->where))
		{
			foreach($this->where as $conditions)
			foreach($conditions as $condition)
			{
				switch($condition['condition'])
				{
					case '=':

						$pModelCondition->where($condition['column'], $condition['value']);
						break;
				}
			}
		}

		if(isset($this->whereIn))
		{
			foreach($this->whereIn as $conditions)
			foreach($conditions as $condition)
			{
				if(is_array($condition['values']))
					$condition['values'] = implode(',', $condition['values']);

				$pModelCondition->whereIn($condition['column'], $condition['values']);
			}
		}

		$dbQuery = $dbConnection 
			-> query(DB_SELECT) 
			-> table($tableName)
			-> dtaObjectName($className)
			-> condition($pModelCondition);


		return new cmsModelCollection(
			$dbQuery -> exec()
		);
	}

	public function
	delete() : bool
	{
		$pDBInstance  = CDatabase::instance();
		$dbConnection = $pDBInstance -> getConnection(CFG::GET() -> MYSQL -> PRIMARY_DATABASE);

		$schemeInstance = new $this->schemeName;

		$tableName = $schemeInstance -> getTableName();

		$className = cmsModelConstruct::createPrototype($this->modelName);

		$pModelCondition = new CModelCondition;
		$pModelCondition->limit($this->limit ?? 0, $this->offset ?? 0);

		if(isset($this->where))
		{
			foreach($this->where as $conditions)
			foreach($conditions as $condition)
			{
				switch($condition['condition'])
				{
					case '=':

						$pModelCondition->where($condition['column'], $condition['value']);
						break;
				}
			}
		}

		if(isset($this->whereIn))
		{
			foreach($this->whereIn as $conditions)
			foreach($conditions as $condition)
			{
				if(is_array($condition['values']))
					$condition['values'] = implode(',', $condition['values']);

				$pModelCondition->whereIn($condition['column'], $condition['values']);
			}
		}

		$dbQuery = $dbConnection 
			-> query(DB_DELETE) 
			-> table($tableName)
			-> dtaObjectName($className)
			-> condition($pModelCondition);


		return $dbQuery -> exec();
	}
	
	public function
	one()
	{
		$collection = $this->limit(1)->get();
		return count($collection) ? $collection->first() : null;
	}

	public function
	find(int $primaryKeyId) : ?object
	{
		$pDBInstance  = CDatabase::instance();
		$dbConnection = $pDBInstance -> getConnection(CFG::GET() -> MYSQL -> PRIMARY_DATABASE);

		$schemeInstance = new $this->schemeName;

		$tableName = $schemeInstance -> getTableName();

		$className = cmsModelConstruct::createPrototype($this->modelName);

		$primaryKeyColumnName = $schemeInstance -> getPrimaryKeyColumnName();

		if(empty($primaryKeyColumnName))
			return [];

		$pModelCondition = new CModelCondition;
		$pModelCondition->where($primaryKeyColumnName, $primaryKeyId);

		$dbQuery = $dbConnection 
			-> query(DB_SELECT) 
			-> table($tableName)
			-> dtaObjectName($className)
			-> condition($pModelCondition);


		$queryResponse = $dbQuery -> exec();

		return empty($queryResponse) ? null : reset($queryResponse);
	}

	public function 
	save(object &$srcObject) : bool
	{
		if(empty($this->schemeName))
			return false;

		$pDBInstance  = CDatabase::instance();

		$dbConnection = $pDBInstance -> getConnection(CFG::GET() -> MYSQL -> PRIMARY_DATABASE);

		$schemeInstance = new $this->schemeName;

		$tableName = $schemeInstance -> getTableName();

		$primaryKeyColumnName = $schemeInstance -> getPrimaryKeyColumnName();

		if(!property_exists($srcObject, $primaryKeyColumnName) || empty($srcObject->$primaryKeyColumnName))
		{
			$dbQuery = $dbConnection
				-> query(DB_INSERT) 
				-> table($tableName) 
				-> scheme($schemeInstance)
				-> dtaObject($srcObject);

			if(($latestId = $dbQuery -> exec()) === false)
				return false;

			$srcObject->$primaryKeyColumnName = $latestId;
		}
		else
		{
			$modelCondition = new CModelCondition();
			$modelCondition -> where($primaryKeyColumnName, $srcObject->$primaryKeyColumnName);

			$dbQuery = $dbConnection 
				-> query(DB_UPDATE) 
				-> table($tableName) 
				-> scheme($schemeInstance)
				-> dtaObject($srcObject)
				-> condition($modelCondition);

			if($dbQuery -> exec() === false)
				return false;
		}

		return true;
	}

	public function
	deleteItem(object &$srcObject) : bool
	{
		if(empty($this->schemeName))
			return false;

		$pDBInstance  = CDatabase::instance();

		$dbConnection = $pDBInstance -> getConnection(CFG::GET() -> MYSQL -> PRIMARY_DATABASE);

		$schemeInstance = new $this->schemeName;

		$tableName = $schemeInstance -> getTableName();

		$primaryKeyColumnName = $schemeInstance -> getPrimaryKeyColumnName();

		if($primaryKeyColumnName === null || empty($srcObject->$primaryKeyColumnName))
			return false;
	
		$modelCondition = new CModelCondition();
		$modelCondition -> where($primaryKeyColumnName, $srcObject->$primaryKeyColumnName);

		$dbQuery = $dbConnection 
			-> query(DB_DELETE) 
			-> table($tableName) 
			-> condition($modelCondition);			

		if($dbQuery -> exec() === false)
			return false;

		$srcObject->$primaryKeyColumnName = null;
		
		return true;
	}
}

class cmsModelCollection implements IteratorAggregate, Countable, JsonSerializable
{
	protected array $modelItems;

	public function
	__construct(array $itemsList = [])
	{
		$this->modelItems = $itemsList;
	}

	public function
	toJson($options = 0) : string
	{
		return json_encode($this, $options);
	}

	public function
	first()
	{
		return reset($this->modelItems);
	}

	public function
	last() : object
	{
		return end($this->modelItems);
	}

	public function
	pop() : object
	{
		return array_pop($this->modelItems);
	}

	public function
	reverse() : void
	{
		$this->modelItems = array_reverse($this->modelItems);
	}

	public function
	search(string $columnName, $value) : cmsModelCollection
	{
		$searchCollection = new cmsModelCollection;
		foreach($this as $model)
		{
			if($model->$columnName === $value)
				$searchCollection->append($model);
		}
		return $searchCollection;
	}

	public function
	random() : ?object
	{
		if(!empty($this->modelItems))
			return $this->modelItems[array_rand($this->modelItems, 1)];
		return null;
	}

	public function
	column(string $columnName) : array
	{
		$columnList = array_column($this->modelItems, $columnName);
		$columnList = array_unique($columnList, SORT_REGULAR);
		return $columnList;
	}

	public function
	append(object $modelInstance) : void
	{
		$this->modelItems[] = $modelInstance;
	}

	public function
	delete() : ?bool
	{
		$cmsModelQuery = new cmsModelQuery($this::class);
		foreach($this as $item)
			$cmsModelQuery->deleteItem($item);

		return null;
	}

	# Entry cmsModelQuery

	public static function
	where(string $column, string $condition, $value) : cmsModelQuery
	{
		$cmsModelQuery = new cmsModelQuery(static::class);
		return $cmsModelQuery->where($column,$condition, $value);
	}

	public static function
	whereIn(string $column, array $values) : cmsModelQuery
	{
		$cmsModelQuery = new cmsModelQuery(static::class);
		return $cmsModelQuery->whereIn($column, $values);
	}

	public static function
	limit(int $limit, ?int $offset = null) : cmsModelQuery
	{
		$cmsModelQuery = new cmsModelQuery(static::class);
		return $cmsModelQuery->limit($limit, $offset);
	}

	public static function
	get() : cmsModelCollection
	{
		$cmsModelQuery = new cmsModelQuery(static::class);
		return $cmsModelQuery->get();
	}

	public static function
	one() : ?object
	{
		$cmsModelQuery = new cmsModelQuery(static::class);
		return $cmsModelQuery->one();
	}

	public static function
	find(int $primaryKeyId): ?object
	{
		$cmsModelQuery = new cmsModelQuery(static::class);
		return $cmsModelQuery->find($primaryKeyId);
	}

	# Interface IteratorAggregate

    public function
	getIterator() : Traversable
	{
        return new ArrayIterator($this->modelItems);
    }

	# Interface Countable

	public function 
	count()
    {
        return count($this -> modelItems);
    }

	# Interface JsonSerializable

	public function
	jsonSerialize()
	{
        return $this->modelItems;
    }
}

class cmsModel extends cmsModelCollection
{
	public function
	__construct()
	{
        parent::__construct();
		unset($this->modelItems);
	}

	public function
	toJson($options = 0) : string
	{
		return json_encode($this, $options);
	}

	public function
	save() : bool
	{
		$cmsModelQuery = new cmsModelQuery($this::class);
		return $cmsModelQuery->save($this);
	}

	public static function
	new(array|object $presetValues = null) : object
	{
		$modelName = static::class;
		$schemeInstance = new $modelName::$schemeName();
		$className = cmsModelConstruct::createPrototype($modelName);

		return new $className($presetValues,$schemeInstance -> getColumns());
	}
	/**
	 *	Pulls $values into the model but does not check for types, wrong types ends in php error.
	 *	Beware of not to overwrite the primary key.
	 */
	public function
	pull(object|array $values)
	{
		foreach($values as $key =>$value)
		{
			if(property_exists($this, $key))
				$this->$key = $value;
		}
	}

	/**
	 *	Delete the current model in the datebase so far it exists and has a primary key
	 */
	public function
	delete() : ?bool
	{
		$cmsModelQuery = new cmsModelQuery($this::class);
		return $cmsModelQuery->deleteItem($this);
	}

	# Interface IteratorAggregate from cmsModelCollection

    public function
	getIterator() : Traversable
	{
        return new ArrayIterator($this);
    }

	# Interface JsonSerializable from cmsModelCollection

	public function
	jsonSerialize()
	{
		$className = $this::class;
		$schemeInstance = new $className::$schemeName();
		$columnList = $schemeInstance -> getColumns();
		$columnList = array_column((array)$columnList,'m_columnName');
	 	$modelValues = new stdClass; 
		foreach($columnList as $column)
	 		$modelValues -> {$column} =  $this->{$column};
        return $modelValues;
    }
}




/*

	todo

	collection

		remove		removes defined item from collection

	static collection bzw. überlegen wie das gemacht werden kann... eventuell andere bezeichnung da delete ja schon existiert

		delete		deletes items with condition

		update		update items with condition and values 

	query static collection

		select mit spalten angaben		$argList = func_get_args();

		relation

	comments

*/