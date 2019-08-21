<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeRightGroups.php';	

class 	modelRightGroups extends CModel
{
	public	$m_sheme;

	public function
	__construct()
	{		
		parent::__construct();		

		$this -> m_sheme = new shemeRightGroups();
	}	

	public function
	load(&$_sqlConnection, array $_where = [])
	{
		$_className	=	$this -> createClass($this -> m_sheme,'rightGroup');

		$_tableName	=	$this -> m_sheme -> getTableName();

		$_sqlWhere = [];

		foreach($_where as $_whereKey => $_whereColumn)
		{
			$_sqlWhere[] = " `$_whereKey` = '". $_sqlConnection -> real_escape_string($_whereColumn) . "' ";
		}

		$_sqlSelect	=	"	SELECT		$_tableName.group_id,
										$_tableName.group_name,
										$_tableName.group_rights
							FROM		$_tableName
						";

		if(count($_sqlWhere) !== 0)
			$_sqlSelect	.=	" WHERE ". implode(' AND ', $_sqlWhere);

		$_sqlResult	=	 $_sqlConnection -> query($_sqlSelect);

		if($_sqlResult -> num_rows === 0) return false;

		while($_sqlResult !== false && $_sqlRow = $_sqlResult -> fetch_assoc())
		{
			$_instanceKey = count($this -> m_storage);
			$this -> m_storage[] = new $_className($_sqlRow, $this -> m_sheme -> getColumns());
			$this -> m_storage[$_instanceKey] -> group_rights = json_decode($this -> m_storage[$_instanceKey] -> group_rights );
		}	
			
		return true;
	}
	
	
	public function
	insert( &$_sqlConnection, $_dataset, &$_insertID)
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
	update( &$_sqlConnection, $_dataset)
	{


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
		$_sqlString 	 .= " WHERE group_id = '". $_dataset['group_id'] ."'";

		if($_sqlConnection -> query($_sqlString) !== false) return true;
		return false;
	}
	
	public function
	delete( &$_sqlConnection, $_where)
	{
		$_tableRight	=	$this -> m_sheme -> getTableName();
		if(empty($_where)) return false;

		$_sqlString		 =	"DELETE FROM $_tableRight WHERE ";
		$_loopCounter 	 = 0;
		foreach($_where as $_column => $_value)
		{
			$_sqlString .= ($_loopCounter != 0 ? ', ':'');
			$_sqlString .= "`".$_column ."` = '". $_value ."'";
			$_loopCounter++;
		}

		if($_sqlConnection -> query($_sqlString) !== false) return true;
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