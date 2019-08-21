<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemePageObject.php';	

class 	modelPageObject extends CModel
{

	private	$m_shemePageObject;

	public function
	__construct()
	{		
		parent::__construct();		

		$this -> m_shemePageObject	= new shemePageObject();
	}	
	
	public function
	load(&$_sqlConnection, string $_nodeID)
	{
		/*
		$_tablePageHeader		=	$this -> m_shemePageHeader 	-> getTableName();
		$_tablePagePath			=	$this -> m_shemePagePath 	-> getTableName();
		$_tablePage				=	$this -> m_shemePage 		-> getTableName();
		$_tablePageObjects		=	$this -> m_shemePageObjects	-> getTableName();
		
		$_className		=	$this -> createClass($this -> m_shemePage,'page');

		$_sqlString		=	"	SELECT		$_tablePagePath.page_path,
											$_tablePagePath.node_id,
											$_tablePageHeader.page_title,
											$_tablePageHeader.page_name,
											$_tablePageHeader.page_description,
											$_tablePageHeader.page_language,
											$_tablePage.page_id,
											$_tablePage.time_create,
											$_tablePage.time_update,
											$_tablePage.create_by,
											$_tablePage.update_by,
											$_tablePage.page_version,
											$_tablePage.page_template
								FROM 		$_tablePagePath
								LEFT JOIN	$_tablePageHeader
									ON		$_tablePageHeader.node_id 	= $_tablePagePath.node_id
								LEFT JOIN	$_tablePage
									ON		$_tablePage.node_id 		= $_tablePagePath.node_id
								WHERE		$_tablePagePath.node_id 	= '". $_sqlConnection -> real_escape_string($_nodeID) ."'
								ORDER BY	$_tablePage.page_version DESC
								LIMIT		1
							";	

		$_sqlPageRes		=	 $_sqlConnection -> query($_sqlString) or die($_sqlConnection -> error);

		if($_sqlPageRes !== false && $_sqlPageRes -> num_rows !== 0)
		{
			$_sqlPage = $_sqlPageRes -> fetch_assoc();

			##	Gathering info about page path data

			$_sqlString		=	"	SELECT		$_tablePagePath.page_id,
												$_tablePagePath.page_path,											
												$_tablePagePath.page_language											
									FROM		$_tablePagePath
									WHERE		$_tablePagePath.page_id			= '". $_sqlPage['page_id'] ."'
									ORDER BY 	
									CASE
										WHEN 	$_tablePagePath.page_language	= '". $_sqlPage['page_language']  ."' THEN 1 ELSE 2
									END
								";

			$_sqlPgHeadRes	=	 $_sqlConnection -> query($_sqlString);
			while($_sqlPgHeadRes !== false && $_sqlPgHead = $_sqlPgHeadRes -> fetch_assoc())
			{
				$_sqlPage['alternate_path'][$_sqlPgHead['page_language']] = $_sqlPgHead['page_path'];
			}
			
			$this -> m_storage = new $_className($_sqlPage, $this -> m_shemePage -> getColumns());

			##	Gathering info about objects

			$_className		=	$this -> createClass($this -> m_shemePageObjects,'object');

			$_sqlString		=	"	SELECT		$_tablePageObjects.module_id,
												$_tablePageObjects.object_id
									FROM		$_tablePageObjects
									WHERE		$_tablePageObjects.node_id			= '". $_nodeID ."'
										AND		$_tablePageObjects.page_version		= '". $this -> m_storage -> page_version ."'
									ORDER BY 	$_tablePageObjects.object_order_by	
								";

			$_sqlPgOjbRes	=	 $_sqlConnection -> query($_sqlString);
			while($_sqlPgOjbRes !== false && $_sqlPgOjb = $_sqlPgOjbRes -> fetch_assoc())
			{
				$this -> m_storage -> objects[] = new $_className($_sqlPgOjb, $this -> m_shemePageObjects -> getColumns());
			}

			return true;
		}
		
		return false;
		*/
	}

	public function
	create(&$_sqlConnection, $_dataset)
	{	
		##	

		$_className			=	$this -> createClass($this -> m_shemePageObject, 'object');
		$this -> m_storage 	= 	new $_className($_dataset, $this -> m_shemePageObject -> getColumns());

		$_insertColumns = 	[];
		foreach($this -> m_shemePageObject -> getColumns() as $_column)
		{
			if($_column -> name === 'object_id') continue;
			if($_column -> isVirtual) continue;
			if(!property_exists($this -> m_storage, $_column -> name)) continue;
			$tmp = $_column -> name;
			$_insertColumns[] = "`".$_column -> name ."` = '". $this -> m_storage -> $tmp ."'";					
		}

		##

		$_tableName		=	$this -> m_shemePageObject -> getTableName();
		
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

		$_className			=	$this -> createClass($this -> m_shemePageObject, 'pageObject'. $_dataset['object_id'] );
		$this -> m_storage 	= 	new $_className($_dataset, $this -> m_shemePageObject -> getColumns());

		$_tableName			=	$this -> m_shemePageObject -> getTableName();

		##

		$_sqlString		 =	"UPDATE $_tableName SET ";
		$_loopCounter 	= 0;
		foreach($_dataset as $_column => $_value)
		{	
			if($_column === 'data_id') continue;
			if($_column === 'object_id') continue;
			if(!$this -> m_shemePageObject -> columnExists(true, $_column)) continue;
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

		$_tableName			=	$this -> m_shemePageObject -> getTableName();

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
		$_tableName			=	$this -> m_shemePageObject -> getTableName();
		
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