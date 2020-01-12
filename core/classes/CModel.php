<?php

class	CModel
{
	protected	$m_storage;
	protected	$m_additionalProperties;		// deprecated
	protected	$m_tableRelations;				// deprecated

	protected	$m_className;
	protected	$m_sheme;

	public function
	__construct(string $_className = '')
	{
		$this -> m_storage 				= [];

		$this -> m_additionalProperties = [];			// deprecated
		$this -> m_tableRelations		= [];			// deprecated

		$this -> m_className			= $_className;
	}
		
	#protected function ( temporär wegen backend handling)
	public function
	createClass(&$_targetSheme, string $_nameAppendix = '', string $_parentClass = '', array $_additionalProperties = [])
	{
		$_className = __CLASS__.'_data'.(!empty($_nameAppendix) ? '_'.$_nameAppendix : '');

		if(!class_exists($_className))
		{
			$this -> _createClass( $_className , $_targetSheme -> getColumns(), $_parentClass, $_additionalProperties);
		}
		return $_className;
	}

	private function
	_createClass(string $_objectName , array $_columns , string $_extends = '', array $_additionalProperties = [] )
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
						
						##	Adding additional properties 
						foreach($_additionalProperties as $_column)
						{
							$_objectString .= " case '". $_column."': \$this->". $_column ." = \$_initialValue; break;";				
						}

					$_objectString .= " }";

				$_objectString .= " }";

				$_objectString .= " foreach(\$_columnsSheme as \$_column) { ";

					$_objectString .= " \$tmp = \$_column -> name;";
					$_objectString .= " if( !property_exists(\$this, \$tmp ) ) continue; ";
					$_objectString .= " if(\$this -> \$tmp === NULL && isset(\$_column -> defaultValue)) { \$this -> \$tmp = \$_column -> defaultValue; } ";
					$_objectString .= " elseif(\$this -> \$tmp == NULL && \$_column -> isNull === 'NULL') { \$this -> \$tmp = isNull; } ";
				
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
	decryptRawSQLDataset(array &$_sqlDataset, string $_key, array $_columns)
	{		
		foreach($_columns as $_column)
		{
			if(!empty($_sqlDataset[$_column]))	
				$_sqlDataset[$_column] = CRYPT::DECRYPT($_sqlDataset[$_column], $_key, true);
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
	setAdditionalProperties(array $_additionalProperties)
	{
		$this -> m_additionalProperties = array_merge($this -> m_additionalProperties, $_additionalProperties);
	}

	public function
	setReleation($_modelInstance, string $_joinType, array $_joinOn)
	{
		$this -> m_tableRelations[] = 	[
											'shemeInstance'	=> $_modelInstance -> m_sheme,
											'join'			=> $_joinType .' join',
											'on'			=> $_joinOn

										];
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