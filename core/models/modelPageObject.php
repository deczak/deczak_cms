<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemePageObject.php';	

class 	modelPageObject extends CModel
{


	public function
	__construct()
	{		
		parent::__construct();		

		$this -> m_sheme	= new shemePageObject();
	}	


	public function
	load(&$_sqlConnection, array $_where = [])
	{


		$_className	=	$this -> createClass($this -> m_sheme, 'shemePageObject', '', $this -> m_additionalProperties);

		$_tableName	=	$this -> m_sheme -> getTableName();

		$_sqlWhere = [];

		foreach($_where as $_whereKey => $_whereColumn)
		{
			$_sqlWhere[] = " `$_whereKey` = '". $_sqlConnection -> real_escape_string($_whereColumn) . "' ";
		}


		$_sqlString	=	"	SELECT		*
							FROM		$_tableName								
						";




		if(count($_sqlWhere) !== 0)
			$_sqlString	.=	" WHERE ". implode(' AND ', $_sqlWhere);


$_sqlString	.= " ORDER BY 	$_tableName.object_order_by ";

			$_sqlUserGroupRes	=	 $_sqlConnection -> query($_sqlString);

			while($_sqlUserGroupRes !== false && $_sqlUserGroup = $_sqlUserGroupRes -> fetch_assoc())
			{	
				#$_modelIndex = count($this -> m_storage);
				
				$this -> m_storage[] = new $_className($_sqlUserGroup, $this -> m_sheme -> getColumns());


			}

		
		return true;
	}

	public function
	create(&$_sqlConnection, $_dataset)
	{	
		##	

		$_className			=	$this -> createClass($this -> m_sheme, 'object');
		$this -> m_storage 	= 	new $_className($_dataset, $this -> m_sheme -> getColumns());

		$_insertColumns = 	[];
		foreach($this -> m_sheme -> getColumns() as $_column)
		{
			if($_column -> name === 'object_id') continue;
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
		{
			return false;
		}

		$this -> m_storage -> object_id = $_sqlConnection -> insert_id;
		return true;
	}

	public function
	update(&$_sqlConnection, $_dataset)
	{	

		if(empty($_dataset['object_id'])) return false;

		$_className			=	$this -> createClass($this -> m_sheme, 'pageObject'. $_dataset['object_id'] );
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
	updateOrderBy(&$_sqlConnection, $_dataset)
	{	
		if(empty($_dataset['object_order_by'])) return false;
		if(empty($_dataset['node_id'])) return false;
		if(empty($_dataset['object_id'])) return false;

		$_tableName			=	$this -> m_sheme -> getTableName();

		$_sqlString 	= 	"	UPDATE		$_tableName
								SET 		$_tableName.object_order_by = '". $_sqlConnection -> real_escape_string($_dataset['object_order_by']) ."' 
								WHERE 		$_tableName.object_id = '". $_sqlConnection -> real_escape_string($_dataset['object_id']) ."'
									AND		$_tableName.node_id = '". $_sqlConnection -> real_escape_string($_dataset['node_id']) ."'
							";

		if($_sqlConnection -> query($_sqlString))
			return false;

		return true;
	}	
	
	public function
	insert(&$_sqlConnection, &$_dataset)
	{
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

/**
 * 	Parent class for the data class with toolkit functions. It get the child instance to access the child properties.

class 	toolkitSite
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