<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemePageHeader.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemePagePath.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemePage.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemePageObject.php';	

class 	modelPage extends CModel
{

	private	$m_shemePageHeader;
	private	$m_shemePagePath;
	private	$m_shemePage;

	public function
	__construct()
	{		
		parent::__construct();		

		$this -> m_shemePageHeader 	= new shemePageHeader();
		$this -> m_shemePagePath 	= new shemePagePath();
		$this -> m_shemePage	 	= new shemePage();
		$this -> m_shemePageObjects	= new shemePageObject();
	}	
	
	public function
	load(&$_sqlConnection, string $_nodeID)
	{
		$_tablePageHeader		=	$this -> m_shemePageHeader 	-> getTableName();
		$_tablePagePath			=	$this -> m_shemePagePath 	-> getTableName();
		$_tablePage				=	$this -> m_shemePage 		-> getTableName();
		$_tablePageObjects		=	$this -> m_shemePageObjects	-> getTableName();
		
		$_className		=	$this -> createClass($this -> m_shemePage, 'page');

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
				$_sqlPage['alternate_path'] = $this -> getAlternatePaths($_sqlConnection, $_sqlPage['page_id']);
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
	}
	
	public function
	update(&$_sqlConnection, $_dataset)
	{	

		$_tablePageHeader		=	$this -> m_shemePageHeader 	-> getTableName();
		$_tablePagePath			=	$this -> m_shemePagePath 	-> getTableName();
		$_tablePage				=	$this -> m_shemePage 		-> getTableName();
		
		$_bQueryResult = true;
## update by user id
		$_dataset['time_update']	= time();
		$_dataset['update_by']		= '0';
		$_dataset['update_reason']	= '';

		if($this -> m_storage -> page_path != '/')			
			$_dataset['page_path']		=	$this -> getValidPath($_sqlConnection, $_dataset['node_id'], $_dataset['page_name']) .'/';
#parent node id funktion erstellen


		##	tb_page

		$_sqlString		 =	"UPDATE $_tablePage SET ";
		$_loopCounter 	= 0;
		foreach($_dataset as $_column => $_value)
		{	
			if(!$this -> m_shemePage -> columnExists(true, $_column)) continue;
			$_sqlString  .= ($_loopCounter != 0 ? ', ':'') . "`". $_sqlConnection -> real_escape_string($_column) ."` = '". $_sqlConnection -> real_escape_string($_value) ."'";
			$_loopCounter++;
		}
		$_sqlString 	 .= 	"	WHERE 		node_id 		= '". $_sqlConnection -> real_escape_string($_dataset['node_id']) ."'
										AND		page_version 	= '". $_sqlConnection -> real_escape_string($_dataset['page_version']) ."'		
								";

		if($_bQueryResult && $_sqlConnection -> query($_sqlString) === false) $_bQueryResult = false;

		##	tb_page_header

		$_sqlString		 =	"UPDATE $_tablePageHeader SET ";
		$_loopCounter 	= 0;
		foreach($_dataset as $_column => $_value)
		{	
			if(!$this -> m_shemePageHeader -> columnExists(true, $_column)) continue;
			$_sqlString  .= ($_loopCounter != 0 ? ', ':'') . "`". $_sqlConnection -> real_escape_string($_column) ."` = '". $_sqlConnection -> real_escape_string($_value) ."'";
			$_loopCounter++;
		}
		$_sqlString 	 .= 	"	WHERE 		node_id 		= '". $_sqlConnection -> real_escape_string($_dataset['node_id']) ."'
										AND		page_version 	= '". $_sqlConnection -> real_escape_string($_dataset['page_version']) ."'
										AND		page_language 	= '". $_sqlConnection -> real_escape_string($_dataset['page_language']) ."'	
								";

		if($_bQueryResult && $_sqlConnection -> query($_sqlString) === false) $_bQueryResult = false;

		##	tb_page_path

		$_sqlString		 =	"UPDATE $_tablePagePath SET ";
		$_loopCounter 	= 0;
		foreach($_dataset as $_column => $_value)
		{	
			if(!$this -> m_shemePagePath -> columnExists(true, $_column)) continue;
			$_sqlString  .= ($_loopCounter != 0 ? ', ':'') . "`". $_sqlConnection -> real_escape_string($_column) ."` = '". $_sqlConnection -> real_escape_string($_value) ."'";
			$_loopCounter++;
		}
		$_sqlString 	 .= 	"	WHERE 		node_id 		= '". $_sqlConnection -> real_escape_string($_dataset['node_id']) ."'
										AND		page_language 	= '". $_sqlConnection -> real_escape_string($_dataset['page_language']) ."'	
								";

		if($_bQueryResult && $_sqlConnection -> query($_sqlString) === false) $_bQueryResult = false;

		##	return

		return $_bQueryResult;
	}	
	
	public function
	insert(&$_sqlConnection, &$_dataset)
	{
		$_bQueryResult = true;
		##	Username userid nachtragen
		##CSession::instance() -> getSessionValue(string $_valueName, string $_subValue = '')

		$_tablePageHeader		=	$this -> m_shemePageHeader 	-> getTableName();
		$_tablePagePath			=	$this -> m_shemePagePath 	-> getTableName();
		$_tablePage				=	$this -> m_shemePage 		-> getTableName();
		
		##	add required data

		$_dataset['page_version'] 	= 	1;
		$_dataset['page_language']	=	$_dataset['cms-edit-page-lang'];
		$_dataset['time_create']	=	time();
		$_dataset['create_by']		=	'0';
		$_dataset['page_path']		=	$this -> getValidPath($_sqlConnection, $_dataset['cms-edit-page-node'], $_dataset['page_name']) .'/';
		
		$_dataset['page_id'] 		= 	$this -> getFreePageID($_sqlConnection);

		##	Table tb_page_path
	
		$_parentNode = [];
		if(!$this -> getNodeData($_sqlConnection, $_dataset['cms-edit-page-node'], $_parentNode))
		{
			trigger_error('modelSite::delete() - Node does not exists');
			return false;
		}

		$_sqlConnection -> query("UPDATE $_tablePagePath SET node_rgt=node_rgt+2 WHERE node_rgt >= ". $_parentNode['node_rgt']);
		$_sqlConnection -> query("UPDATE $_tablePagePath SET node_lft=node_lft+2 WHERE node_lft >  ". $_parentNode['node_rgt']);

		$_dataset['node_lft'] = $_parentNode['node_rgt'];
		$_dataset['node_rgt'] = $_parentNode['node_rgt'] + 1;

		$_className		=	$this -> createClass($this -> m_shemePagePath, 'page_path');
		$_model 		= 	new $_className($_dataset, $this -> m_shemePagePath -> getColumns());
		$_sqlString		=	"INSERT INTO $_tablePagePath SET ";

		$_loopCounter = 0;
		foreach($this -> m_shemePagePath -> getColumns() as $_column)
		{
			if($_column -> name === 'data_id') continue;
			if($_column -> isVirtual) continue;
			if(!property_exists($_model, $_column -> name)) continue;

			$_tmp		 = $_column -> name;
			$_sqlString .= ($_loopCounter != 0 ? ', ':'');
			$_sqlString .= "`".$_column -> name ."` = '". $_model -> $_tmp ."'";
			$_loopCounter++;
		}
		
		if($_bQueryResult && $_sqlConnection -> query($_sqlString) === false) $_bQueryResult = false;

		$_dataset['node_id'] = $_sqlConnection -> insert_id;

		##	Table tb_page

		$_className		=	$this -> createClass($this -> m_shemePage, 'page');
		$_model 		= 	new $_className($_dataset, $this -> m_shemePage -> getColumns());

		$_sqlString		=	"INSERT INTO $_tablePage SET ";

		$_loopCounter = 0;
		foreach($this -> m_shemePage -> getColumns() as $_column)
		{
			if($_column -> name === 'data_id') continue;
			if($_column -> isVirtual) continue;
			if(!property_exists($_model, $_column -> name)) continue;

			$_tmp		 = $_column -> name;
			$_sqlString .= ($_loopCounter != 0 ? ', ':'');
			$_sqlString .= "`".$_column -> name ."` = '". $_model -> $_tmp ."'";
			$_loopCounter++;
		}

		if($_bQueryResult && $_sqlConnection -> query($_sqlString) === false) $_bQueryResult = false;

		##	Table tb_page_header

		$_className		=	$this -> createClass($this -> m_shemePageHeader,'page_header');
		$_model 		= 	new $_className($_dataset, $this -> m_shemePageHeader -> getColumns());

		$_sqlString		=	"INSERT INTO $_tablePageHeader SET ";

		$_loopCounter = 0;
		foreach($this -> m_shemePageHeader -> getColumns() as $_column)
		{
			if($_column -> name === 'data_id') continue;
			if($_column -> isVirtual) continue;
			if(!property_exists($_model, $_column -> name)) continue;

			$_tmp		 = $_column -> name;
			$_sqlString .= ($_loopCounter != 0 ? ', ':'');
			$_sqlString .= "`".$_column -> name ."` = '". $_model -> $_tmp ."'";
			$_loopCounter++;
		}

		if($_bQueryResult && $_sqlConnection -> query($_sqlString) === false) $_bQueryResult = false;

		return $_bQueryResult;
	}
	
	public function
	delete( &$_sqlConnection, $_where)
	{
		$_nodeData = [];
		if(!$this -> getNodeData($_sqlConnection, $_where['cms-edit-page-node'], $_nodeData))
		{
			trigger_error('modelSite::delete() - Node does not exists');
			return false;
		}

		$_tablePageHeader		=	$this -> m_shemePageHeader 	-> getTableName();
		$_tablePagePath			=	$this -> m_shemePagePath 	-> getTableName();
		$_tablePage				=	$this -> m_shemePage 		-> getTableName();

		$_sqlConnection -> query("DELETE FROM $_tablePage 		WHERE node_id = '". $_sqlConnection -> real_escape_string($_where['cms-edit-page-node']) ."'");
		$_sqlConnection -> query("DELETE FROM $_tablePageHeader WHERE node_id = '". $_sqlConnection -> real_escape_string($_where['cms-edit-page-node']) ."'");

		$_sqlConnection -> query("DELETE FROM $_tablePagePath WHERE node_lft = ". $_nodeData['node_lft']);
		$_sqlConnection -> query("UPDATE 	  $_tablePagePath SET node_lft=node_lft-1, node_rgt=node_rgt-1 WHERE node_lft BETWEEN ". $_nodeData['node_lft'] ." AND ". $_nodeData['node_rgt']);
		$_sqlConnection -> query("UPDATE 	  $_tablePagePath SET node_lft=node_lft-2 WHERE node_lft > ". $_nodeData['node_rgt']);
		$_sqlConnection -> query("UPDATE 	  $_tablePagePath SET node_rgt=node_rgt-2 WHERE node_rgt > ". $_nodeData['node_rgt']);

		return true;
	}	

	public function
	deleteTree(&$_sqlConnection, $_where)
	{
		$_nodeData = [];
		if(!$this -> getNodeData($_sqlConnection, $_where['cms-edit-page-node'], $_nodeData))
		{
			trigger_error('modelSite::delete() - Node does not exists');
			return false;
		}

		$_tablePageHeader		=	$this -> m_shemePageHeader 	-> getTableName();
		$_tablePagePath			=	$this -> m_shemePagePath 	-> getTableName();
		$_tablePage				=	$this -> m_shemePage 		-> getTableName();

		$_nodeTree 	= [];
		$this -> getNodeTree($_sqlConnection, $_where['cms-edit-page-node'], $_nodeTree);
		foreach($_nodeTree as $_node)
		{
			$_sqlConnection -> query("DELETE FROM $_tablePage 		WHERE node_id = '". $_node['node_id'] ."'");
			$_sqlConnection -> query("DELETE FROM $_tablePageHeader WHERE node_id = '". $_node['node_id'] ."'");
		}

		$_sqlConnection -> query("DELETE FROM $_tablePagePath WHERE node_lft BETWEEN ". $_nodeData['node_lft'] ." AND ". $_nodeData['node_rgt']);
		$_sqlConnection -> query("UPDATE 	  $_tablePagePath SET node_lft=node_lft-ROUND(". ( $_nodeData['node_rgt'] - $_nodeData['node_lft'] + 1 ). ") WHERE node_lft > ". $_nodeData['node_rgt']);
		$_sqlConnection -> query("UPDATE 	  $_tablePagePath SET node_rgt=node_rgt-ROUND(". ( $_nodeData['node_rgt'] - $_nodeData['node_lft'] + 1 ) .") WHERE node_rgt > ". $_nodeData['node_rgt']);

		return true;
	}
			
	private function
	getNodeData(&$_sqlConnection, int $_nodeID, array &$_nodeData)
	{
		$_sqlParentNodeRes	= 	$_sqlConnection -> query("SELECT * FROM tb_page_path WHERE node_id = '". $_sqlConnection -> real_escape_string($_nodeID) ."'");

		if($_sqlParentNodeRes === false || !$_sqlParentNodeRes -> num_rows)
			return false;

		$_nodeData		=	$_sqlParentNodeRes -> fetch_assoc();
		return true;
	}

	private function
	getNodeTree(&$_sqlConnection, int $_nodeID, array &$_nodeData)
	{
		$_tablePagePath	=	$this -> m_shemePagePath 	-> getTableName();

		$_sqlString 	=	"	SELECT 		o.page_path,
											o.node_id,
											COUNT(p.node_id)-1 AS level
								FROM 		$_tablePagePath AS n,
											$_tablePagePath AS p,
											$_tablePagePath AS o
								WHERE 		o.node_lft BETWEEN p.node_lft AND p.node_rgt
								AND 		o.node_lft BETWEEN n.node_lft AND n.node_rgt
								AND 		n.node_id = '". $_sqlConnection -> real_escape_string($_nodeID) ."'
								GROUP BY	o.node_lft
								ORDER BY 	o.node_lft
							";

		$_sqlNodeRes = $_sqlConnection -> query($_sqlString);

		if($_sqlNodeRes === false || !$_sqlNodeRes -> num_rows)
		{
			trigger_error('CNestedSets::getNodeChilds() - Node does not exists');
			return;
		}

		$_nodeData 	    = [];
		while($_sqlNode = $_sqlNodeRes -> fetch_assoc())
			$_nodeData[]  = $_sqlNode;		
	}

	private function
	getFreePageID(&$_sqlConnection)
	{
		$_tablePage		=	$this -> m_shemePage 		-> getTableName();

		$_sqlString		=	"	SELECT		$_tablePage.page_id
								FROM 		$_tablePage
								ORDER BY 	$_tablePage.page_id DESC
								LIMIT		1
							";

		$_sqlLatestRes	=	$_sqlConnection -> query($_sqlString);
		$_sqlLatest		=	$_sqlLatestRes -> fetch_assoc();

		return (intval($_sqlLatest['page_id']) + 1);
	}

	private function
	getValidPath(&$_sqlConnection, int $_nodeID, string $_path, int $_try = 0)
	{
		$_level 	= 0;
		$_path 		= tk::normalizeFilename($_path, true) ;
		$_nodeTree 	= [];
		$_result	= false;
		$_path2Check = $_path;
		if($_try != 0)
			$_path2Check =  $_path .'-'. $_try;
	
		$this -> getNodeTree($_sqlConnection, $_nodeID, $_nodeTree);
	
		foreach($_nodeTree as $_node)
		{
			if($_level == 0) $_level = $_node['level'] + 1;
			if($_node['level'] != $_level) continue;
			if($_node['page_path'] === $_path2Check .'/')
			{
				$_result = $this -> getValidPath($_sqlConnection, $_nodeID, $_path, ($_try + 1));
				if($_result !== false)
					return $_result;
			}
		}
		return $_path2Check;
	}

	private function
	getAlternatePaths(&$_sqlConnection, $_pageID)
	{
		$_returnArray	=	[];

		$_sqlString 	=	"	SELECT 		tb_page_path.node_id,
											tb_page_path.page_language
								FROM 		tb_page_path
								WHERE 		tb_page_path.page_id 		= '". $_pageID ."'
							";

		$_sqlPagesRes 	= $_sqlConnection -> query($_sqlString);

		while($_sqlPagesRes !== false && $_sqlPages = $_sqlPagesRes -> fetch_assoc())
		{
			$_sqlString =	"	SELECT 		p.node_id, 
											p.page_path,
											p.page_language
								FROM 		tb_page_path AS n,
											tb_page_path AS p
								WHERE 		n.node_lft
									BETWEEN p.node_lft 
										AND	p.node_rgt 
									AND 	n.node_id = '". $_sqlConnection -> real_escape_string($_sqlPages['node_id']) ."'
								ORDER BY 	n.node_lft
							";

			$_sqlPgHeadRes	=	 $_sqlConnection -> query($_sqlString);

			while($_sqlPgHeadRes !== false && $_sqlPgHead = $_sqlPgHeadRes -> fetch_assoc())
			{
				if($_sqlPgHead['page_language'] == '0') continue;

				if(!isset($_returnArray[$_sqlPgHead['page_language']]))
					$_returnArray[$_sqlPgHead['page_language']]['path'] = '';

				$_returnArray[$_sqlPgHead['page_language']]['path'] 	.= $_sqlPgHead['page_path'];
				$_returnArray[$_sqlPgHead['page_language']]['node_id']   = $_sqlPgHead['node_id'];
			}	
		}

		return $_returnArray;
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