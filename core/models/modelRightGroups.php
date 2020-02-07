<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeRightGroups.php';	

class 	modelRightGroups extends CModel
{
	public function
	__construct()
	{		
		parent::__construct();		

		$this -> m_sheme = new shemeRightGroups();
	}	

	public function
	load(&$_sqlConnection, CModelCondition $_condition = NULL)
	{
		$className	=	$this -> createClass($this -> m_sheme,'rightGroup');
		$tableName	=	$this -> m_sheme -> getTableName();

		$sqlSelect	=	"	SELECT		$tableName.*
							FROM		$tableName
						".	($_condition != NULL ? $_condition -> getConditions($_sqlConnection, $_condition) : '');

		$_sqlResult	=	 $_sqlConnection -> query($sqlSelect);

		if($_sqlResult -> num_rows === 0) return false;

		while($_sqlResult !== false && $_sqlRow = $_sqlResult -> fetch_assoc())
		{
			$_instanceKey = count($this -> m_storage);

			$this -> m_storage[] = new $className($_sqlRow, $this -> m_sheme -> getColumns());
			$this -> m_storage[$_instanceKey] -> group_rights = json_decode($this -> m_storage[$_instanceKey] -> group_rights );
		}	
			
		return true;
	}
	
		public function
	insert( &$_sqlConnection, &$_dataset, &$_insertID)
	{
		$_tableRight	=	$this -> m_sheme -> getTableName();


		if(isset($_dataset['group_rights'])) $_dataset['group_rights'] = json_encode($_dataset['group_rights']);
	

		$_className		=	$this -> createClass($this -> m_sheme,'rightGroup');
		$_model 		= 	new $_className($_dataset, $this -> m_sheme -> getColumns());

		$_sqlString		=	"INSERT INTO $_tableRight	SET ";

		$_loopCounter = 0;
		foreach($this -> m_sheme -> getColumns() as $_column)
		{
			if($_column -> name === 'group_id') continue;
			$_tmp		 = $_column -> name;
			$_sqlString .= ($_loopCounter != 0 ? ', ':'');
			$_sqlString .= "`".$_column -> name ."` = '". $_model -> $_tmp ."'";
			$_loopCounter++;
		}

		if($_sqlConnection -> query($_sqlString) !== false) 
		{
			$_insertID = $_sqlConnection -> insert_id;
			return true;
		}
		return false;
	}
	
	public function
	update( &$_sqlConnection, &$_dataset, CModelCondition $_condition = NULL)
	{
		if($_condition === NULL || !$_condition -> isSet()) return false;

		$_tableRight	=	$this -> m_sheme -> getTableName();
		
		if(isset($_dataset['group_rights'])) $_dataset['group_rights'] = json_encode($_dataset['group_rights']);
	
		$_sqlString		 =	"UPDATE $_tableRight SET ";
		$_loopCounter 	= 0;
		foreach($_dataset as $_column => $_value)
		{	
			if($_column === 'group_id') continue;
			if(!$this -> m_sheme -> columnExists(true, $_column)) continue;
			$_sqlString  .= ($_loopCounter != 0 ? ', ':'');
			$_sqlString  .= "`". $_sqlConnection -> real_escape_string($_column) ."` = '". $_sqlConnection -> real_escape_string($_value) ."'";
			$_loopCounter++;
		}

		$_sqlString	.=	$_condition -> getConditions($_sqlConnection, $_condition);

		if($_sqlConnection -> query($_sqlString) !== false) return true;
		return false;
	}
}


/**
 * 	Parent class for the data class with toolkit functions. It get the child instance to access the child properties.

class 	toolkitRightGroups
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