<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeModules.php';	

class 	modelModules extends CModel
{

	public function
	__construct()
	{		
		parent::__construct('modules');		

		$this -> m_sheme = new shemeModules();
	}	

	public function
	load(&$_sqlConnection, CModelCondition $_condition = NULL)
	{
		$className	=	$this -> createClass($this -> m_sheme, $this -> m_className, '', $this -> m_additionalProperties);
		$tableName	=	$this -> m_sheme -> getTableName();

		$sqlString	=	"	SELECT		*
							FROM		$tableName
						".	($_condition != NULL ? $_condition -> getConditions($_sqlConnection, $_condition) : '');

		$sqlResult =	$_sqlConnection -> query($sqlString);

		while($sqlResult !== false && $sqlRow = $sqlResult -> fetch_assoc())
		{	
			$this -> m_storage[] = new $className($sqlRow, $this -> m_sheme -> getColumns());
		}
		
		return true;
	}

	public function
	insert(&$_sqlConnection, $_dataset, &$_insertID)
	{
		if($_sqlConnection === false)
			return false;

		$tableName		=	$this -> m_sheme -> getTableName();

		$_className		=	$this -> createClass($this -> m_sheme,'Modules');
		$_model 		= 	new $_className($_dataset, $this -> m_sheme -> getColumns());

		$sqlString		=	"INSERT INTO $tableName	SET ";

		$_loopCounter = 0;
		foreach($this -> m_sheme -> getColumns() as $_column)
		{
			$_tmp		 = $_column -> name;
			$sqlString .= ($_loopCounter != 0 ? ', ':'');
			$sqlString .= "`".$_column -> name ."` = '". $_model -> $_tmp ."'";
			$_loopCounter++;
		}

		if($_sqlConnection -> query($sqlString) !== false) 
		{
			$_insertID = $_sqlConnection -> insert_id;
			return true;
		}
		return false;
	}

	public function
	update(&$_sqlConnection, $_dataset, CModelCondition $_condition = NULL)
	{
		if($_condition === NULL || !$_condition -> isSet()) return false;
		
		$tableName	=	$this -> m_sheme -> getTableName();

		$sqlString		 =	"UPDATE $tableName SET ";
		$_loopCounter 	= 0;
		foreach($_dataset as $_column => $_value)
		{	
			if(!$this -> m_sheme -> columnExists(true, $_column)) continue;
			$sqlString  .= ($_loopCounter != 0 ? ', ':'');
			$sqlString  .= "`". $_sqlConnection -> real_escape_string($_column) ."` = '". $_sqlConnection -> real_escape_string($_value) ."'";
			$_loopCounter++;
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

		$sqlString	=	"	DELETE FROM $tableName  
						".	$_condition -> getConditions($_sqlConnection, $_condition);

		if($_sqlConnection -> query($sqlString) !== false) return true;
		return false;
	}

/*



	isUniqueValue(&$_sqlConnection, string $tableName, string $_columnName, string $_value)

	public function
	includeDataByComparsion($_includeDataInstance = NULL, string $_includeOn = '', array $_includeValues = [])
	{
		foreach($this -> m_storage as &$_dataInstance)
			$this -> _includeDataByComparsion($_dataInstance, $_includeDataInstance, $_includeOn, $_includeValues);
	}
*/

}



/**
 * 	Parent class for the data class with toolkit functions. It get the child instance to access the child properties.

class 	toolkitUsersBackend
{
	protected	$m_childInstance;

	public function
	__construct($_instance)
	{
		$this -> m_childInstance = $_instance;
	}

}
 */
?>