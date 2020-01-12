<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeSessionsAccess.php';	

class 	modelSessionsAccess extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('SessionAccess');		
		$this -> m_sheme = new shemeSessionsAccess();


	}	

	public function
	load(&$_sqlConnection, CModelCondition $_condition = NULL, CModelComplementary $_complementary = NULL)
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

			if($_complementary !== NULL)
			{
				$index = count($this -> m_storage) - 1;

				foreach($_complementary -> complementaryList as $complementarySet)
				{
					$propertyName = $complementarySet -> propertyName;
					$propertyCompare = $complementarySet -> propertyCompare;

				#	switch($complementarySet -> type)
				#	{
				#		case 'array' : $this -> m_storage[$index] -> $propertyName 	= []; break;
				#		case 'string': $this -> m_storage[$index] -> $propertyName 	= ''; break;
				#	}

					$this -> m_storage[$index] -> $propertyName = [];

					foreach($complementarySet -> storageInstance as $subDataset)
					{
						if($this -> m_storage[$index] -> $propertyCompare === $subDataset -> $propertyCompare)
						{
							switch($complementarySet -> type)
							{
								case 'array' : $this -> m_storage[$index] -> $propertyName[] 	= $subDataset; break;
								case 'string': $this -> m_storage[$index] -> $propertyName 		= $subDataset -> $propertyName; break;
							}
						}
					}
				}
			}
		}
		
		return true;
	}

	public function
	insert(&$_sqlConnection, $_dataset, &$_insertID)
	{
		$className		 =	$this -> createClass($this -> m_sheme, $this -> m_className);
		$tableName		 =	$this -> m_sheme -> getTableName();

		$model 			 = 	new $className($_dataset, $this -> m_sheme -> getColumns());

		$sqlString		 =	"INSERT INTO $tableName	SET ";

		$loopCounter 	 = 0;
		foreach($this -> m_sheme -> getColumns() as $column)
		{
			$tmp		 = $column -> name;
			$sqlString 	.= ($loopCounter != 0 ? ', ':'');
			$sqlString 	.= "`".$column -> name ."` = '". $model -> $tmp ."'";
			$loopCounter++;
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

class 	toolkitSessionsAccess
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