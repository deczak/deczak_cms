<?php

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
	 */
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
	 */
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
	decryptRawSQLDataset(&$_sqlDataset, string $_key, array $_columns)
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


}

?>