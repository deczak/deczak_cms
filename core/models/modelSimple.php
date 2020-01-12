<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeSimple.php';	

class 	modelSimple extends CModel
{

	public function
	__construct()
	{		
		parent::__construct();		

		$this -> m_sheme = new shemeSimple();
	}

	public function
	load(&$_sqlConnection, CModelCondition $_condition = NULL)
	{
		$_className		=	$this -> createClass($this -> m_sheme, 'simple');
		$_tableName		=	$this -> m_sheme -> getTableName();
		
		$_sqlString		=	"	SELECT		*
								FROM 		$_tableName
							".	($_condition != NULL ? $_condition -> getConditions($_sqlConnection, $_condition) : '');

		$_sqlResult		=	 $_sqlConnection -> query($_sqlString);

		if($_sqlResult !== false && $_sqlResult -> num_rows !== 0)
		{
			$_sqlRow = $_sqlResult -> fetch_assoc();
			$this -> m_storage = new $_className($_sqlRow, $this -> m_sheme -> getColumns());
		}
	}

	public function
	update(&$_sqlConnection, $_dataset, CModelCondition $_condition = NULL)
	{
		##	

		$_className			=	$this -> createClass($this -> m_sheme, 'simple');
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

		$_sqlString	.=	$_condition -> getConditions($_sqlConnection, $_condition);

		if($_sqlConnection -> query($_sqlString) === false)
			return false;

		return true;
	}

	public function
	insert(&$_sqlConnection, array $_dataset)
	{	
	}

	public function
	create(&$_sqlConnection, array $_dataset)
	{
		##	

		$_className			=	$this -> createClass($this -> m_sheme, 'simple');
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
	delete(&$_sqlConnection, CModelCondition $_condition = NULL)
	{
		if($_condition === NULL || !$_condition -> isSet()) return false;

		$_tableName		 =	$this -> m_sheme -> getTableName();

		$_sqlString	 	 =	"DELETE FROM $_tableName ";
		$_sqlString		.=	$_condition -> getConditions($_sqlConnection, $_condition);

		if($_sqlConnection -> query($_sqlString) !== false) return true;
		return false;
	}		

}
		

?>