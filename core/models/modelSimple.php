<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeSimple.php';	

class 	modelSimple extends CModel
{
	private		$m_sheme;

	public function
	__construct()
	{		
		parent::__construct();		

		$this -> m_sheme = new shemeSimple();
	}

	public function
	load(&$_sqlConnection, array $_dataset)	// dataset must contains object_id
	{
		$_className		=	$this -> createClass($this -> m_sheme, 'simple'. $_dataset['object_id'] );

		$_tableName		=	$this -> m_sheme -> getTableName();
		
		$_sqlString		=	"	SELECT		*
								FROM 		$_tableName
								WHERE
							";	

		$_loopCounter = 0;
		foreach($_dataset as $_colKey => $_colValue)
		{
			$_sqlString		.=	($_loopCounter !== 0 ? ' AND ' : '') ." $_colKey = '$_colValue'";
			$_loopCounter++;
		}	

		$_sqlResult		=	 $_sqlConnection -> query($_sqlString) or die($_sqlConnection -> error);

		if($_sqlResult !== false && $_sqlResult -> num_rows !== 0)
		{
			$_sqlRow = $_sqlResult -> fetch_assoc();
			$this -> m_storage = new $_className($_sqlRow, $this -> m_sheme -> getColumns());
		}
	}

	public function
	update(&$_sqlConnection, array $_dataset) // dataset must contains object_id
	{
		##	

		$_className			=	$this -> createClass($this -> m_sheme, 'simple'. $_dataset['object_id'] );
		$this -> m_storage 	= 	new $_className($_dataset, $this -> m_sheme -> getColumns());

		$_tableName			=	$this -> m_sheme -> getTableName();
		
		##

		$_sqlString		 =	"UPDATE $_tableName SET ";
		$_loopCounter 	= 0;
		foreach($_dataset as $_column => $_value)
		{	
			if($_column === 'data_id') continue;
			if($_column === 'object_id') continue;
			if(!$this -> m_sheme -> columnExists(true, $_column)) continue;
			$_sqlString  .= ($_loopCounter != 0 ? ', ':'') . "`". $_sqlConnection -> real_escape_string($_column) ."` = '". $_sqlConnection -> real_escape_string($_value) ."'";
			$_loopCounter++;
		}
		$_sqlString 	 .= 	"	WHERE 		object_id 		= '". $_sqlConnection -> real_escape_string($_dataset['object_id']) ."'	
								";

		if($_sqlConnection -> query($_sqlString) === false)
			return false;

		return true;
	}

	public function
	insert(&$_sqlConnection, array $_dataset)
	{	
	}

	public function
	create(&$_sqlConnection, array $_dataset) // dataset must contains object_id
	{
		##	

		$_className			=	$this -> createClass($this -> m_sheme, 'simple'. $_dataset['object_id'] );
		$this -> m_storage 	= 	new $_className($_dataset, $this -> m_sheme -> getColumns());

		$_insertColumns = 	[];
		foreach($this -> m_sheme -> getColumns() as $_column)
		{
			if($_column -> isVirtual) continue;
			if(!property_exists($this -> m_storage, $_column -> name)) continue;
			$tmp = $_column -> name;
			$_insertColumns[] = "`".$_column -> name ."` = '". $this -> m_storage -> $tmp ."'";					
		}

		##

		$_tableName		=	$this -> m_sheme -> getTableName();
		
		$_sqlString		=	"	INSERT INTO	$_tableName
								SET			". implode(',', $_insertColumns) ."
							";	

		if($_sqlConnection -> query($_sqlString) === false)
			return false;
		
		return true;
	}

	public function
	delete(&$_sqlConnection, array $_deleteWhere)	
	{
		$_tableName			=	$this -> m_sheme -> getTableName();
		
		if($_sqlConnection -> query("DELETE FROM $_tableName WHERE object_id = '". $_sqlConnection -> real_escape_string($_deleteWhere['object_id']) ."'") === false)
			return false;

		return true;
	}		

}
		

?>