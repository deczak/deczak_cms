<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeUserAgent.php';	

class 	modelUserAgent extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('UserAgent');		
		$this -> m_sheme = new shemeUserAgent();
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
	delete( &$_sqlConnection, CModelCondition $_condition = NULL)
	{
		if($_condition === NULL || !$_condition -> isSet()) return false;
	
		$tableName	=	$this -> m_sheme -> getTableName();
		
		$sqlString	 =	"	DELETE FROM $tableName 
						".	$_condition -> getConditions($_sqlConnection, $_condition);

		if($_sqlConnection -> query($sqlString) !== false) return true;
		return false;
	}
}



/**
 * 	Parent class for the data class with toolkit functions. It get the child instance to access the child properties.

class 	toolkitUserAgent
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