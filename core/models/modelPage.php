<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemePageHeader.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemePagePath.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemePage.php';	

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
	}	
	
	public function
	load(&$_sqlConnection, CModelCondition $_condition = NULL)
	{
		if($_sqlConnection === false)
			return false;

		$_tablePageHeader		=	$this -> m_shemePageHeader 	-> getTableName();
		$_tablePagePath			=	$this -> m_shemePagePath 	-> getTableName();
		$_tablePage				=	$this -> m_shemePage 		-> getTableName();
		
		$_className		=	$this -> createClass($this -> m_shemePage, 'page');

		$_sqlString		=	"	SELECT		$_tablePagePath.page_path,
											$_tablePagePath.node_id,
											$_tablePageHeader.page_title,
											$_tablePageHeader.page_name,
											$_tablePageHeader.page_description,
											$_tablePageHeader.page_language,
											$_tablePage.page_id,
											$_tablePage.create_time,
											$_tablePage.update_time,
											$_tablePage.publish_from,
											$_tablePage.publish_until,
											$_tablePage.publish_expired,
											$_tablePage.create_by,
											$_tablePage.update_by,
											$_tablePage.page_version,
											$_tablePage.page_template,
											$_tablePage.hidden_state,
											$_tablePage.crawler_index,
											$_tablePage.crawler_follow
								FROM 		$_tablePagePath
								LEFT JOIN	$_tablePageHeader
									ON		$_tablePageHeader.node_id 	= $_tablePagePath.node_id
								LEFT JOIN	$_tablePage
									ON		$_tablePage.node_id 		= $_tablePagePath.node_id
							".	($_condition != NULL ? $_condition -> getConditions($_sqlConnection, $_condition) : '');

					

		$_sqlPageRes		=	 $_sqlConnection -> query($_sqlString) or die($_sqlConnection -> error);

		while($_sqlPageRes !== false && $_sqlPage = $_sqlPageRes -> fetch_assoc())
		{
			$_sqlPage['alternate_path'] = $this -> getAlternatePaths($_sqlConnection, $_sqlPage['page_id']);
		
			$this -> m_storage[] = new $_className($_sqlPage, $this -> m_shemePage -> getColumns());
		}

		if($_sqlPageRes -> num_rows !== 0)
			return true;
		
		return false;
	}
	
	public function
	update(&$_sqlConnection, $_dataset)
	{	

		$_tablePageHeader		=	$this -> m_shemePageHeader 	-> getTableName();
		$_tablePagePath			=	$this -> m_shemePagePath 	-> getTableName();
		$_tablePage				=	$this -> m_shemePage 		-> getTableName();
		
		$_bQueryResult = true;

		if($this -> m_storage[0] -> page_path != '/')			
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
	insert(&$_sqlConnection, &$_dataset, &$_insertID)
	{
		$_bQueryResult = true;

		$_tablePageHeader		=	$this -> m_shemePageHeader 	-> getTableName();
		$_tablePagePath			=	$this -> m_shemePagePath 	-> getTableName();
		$_tablePage				=	$this -> m_shemePage 		-> getTableName();
		
		##	add required data

		$_dataset['page_version'] 	= 	1;
		$_dataset['page_language']	=	$_dataset['cms-edit-page-lang'];

		if(empty($_dataset['page_title']))
			$_dataset['page_title'] = $_dataset['page_name'];

		if(!isset($_dataset['page_id']))
			$_dataset['page_id'] 		= 	$this -> getFreePageID($_sqlConnection);

		if($_dataset['page_id'] !== '1')		
			$_dataset['page_path']		=	$this -> getValidPath($_sqlConnection, $_dataset['cms-edit-page-node'], $_dataset['page_name']) .'/';
		else
			$_dataset['page_path']		= '/';
			
		##	Table tb_page_path
	
		$_parentNode = [];
		if(!$this -> getNodeData($_sqlConnection, $_dataset['cms-edit-page-node'], $_parentNode))
		{
			trigger_error('modelPage::delete() - Node does not exists');
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
		$_insertID			 = $_dataset['node_id'];

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
	delete( &$_sqlConnection, CModelCondition $_condition = NULL)
	{
		if($_condition === NULL || !$_condition -> isSet()) return false;
		
		$nodeId = $_condition -> getConditionListValue('node_id');

		$_nodeData = [];
		if(!$nodeId || !$this -> getNodeData($_sqlConnection, $nodeId, $_nodeData))
		{
			trigger_error('modelPage::delete() - Node does not exists');
			return false;
		}

#		$_tablePageHeader		=	$this -> m_shemePageHeader 	-> getTableName();
		$_tablePagePath			=	$this -> m_shemePagePath 	-> getTableName();
		$_tablePage				=	$this -> m_shemePage 		-> getTableName();

		$_sqlConnection -> query("DELETE FROM $_tablePage 		". $_condition -> getConditions($_sqlConnection, $_condition));
#		$_sqlConnection -> query("DELETE FROM $_tablePageHeader ". $_condition -> getConditions($_sqlConnection, $_condition));

		$_sqlConnection -> query("DELETE FROM $_tablePagePath WHERE node_lft = ". $_nodeData['node_lft']);
		$_sqlConnection -> query("UPDATE 	  $_tablePagePath SET node_lft=node_lft-1, node_rgt=node_rgt-1 WHERE node_lft BETWEEN ". $_nodeData['node_lft'] ." AND ". $_nodeData['node_rgt']);
		$_sqlConnection -> query("UPDATE 	  $_tablePagePath SET node_lft=node_lft-2 WHERE node_lft > ". $_nodeData['node_rgt']);
		$_sqlConnection -> query("UPDATE 	  $_tablePagePath SET node_rgt=node_rgt-2 WHERE node_rgt > ". $_nodeData['node_rgt']);

		return true;
	}	

	public function
	deleteTree(&$_sqlConnection, CModelCondition $_condition = NULL)
	{
		if($_condition === NULL || !$_condition -> isSet()) return false;

		$nodeId = $_condition -> getConditionListValue('node_id');

		$_nodeData = [];
		if(!$nodeId || !$this -> getNodeData($_sqlConnection, $nodeId, $_nodeData))
		{
			trigger_error('modelPage::delete() - Node does not exists');
			return false;
		}

	#	$_tablePageHeader		=	$this -> m_shemePageHeader 	-> getTableName();
		$_tablePagePath			=	$this -> m_shemePagePath 	-> getTableName();
		$_tablePage				=	$this -> m_shemePage 		-> getTableName();

		$_nodeTree 	= [];
		$this -> getNodeTree($_sqlConnection, $nodeId, $_nodeTree);
		foreach($_nodeTree as $_node)
		{
			$_sqlConnection -> query("DELETE FROM $_tablePage 		WHERE node_id = '". $_node['node_id'] ."'");
	#		$_sqlConnection -> query("DELETE FROM $_tablePageHeader WHERE node_id = '". $_node['node_id'] ."'");
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

		$timestamp 		= 	time();

		$_returnArray	=	[];

		$_sqlString 	=	"	SELECT 		tb_page_path.node_id,
											tb_page_path.page_language,
											tb_page.hidden_state,
											tb_page.publish_from,
											tb_page.publish_until
								FROM 		tb_page_path
								JOIN		tb_page
									ON		tb_page.node_id			= tb_page_path.node_id
								WHERE 		tb_page_path.page_id 	= '". $_pageID ."'
							";

		$_sqlPagesRes 	= $_sqlConnection -> query($_sqlString);

		while($_sqlPagesRes !== false && $_sqlPages = $_sqlPagesRes -> fetch_assoc())
		{

			if(
					($_sqlPages['hidden_state'] == 0)
				||	(	($_sqlPages['hidden_state'] == 5 && $_sqlPages['publish_from']  < $timestamp)
					&&	($_sqlPages['hidden_state'] == 5 && $_sqlPages['publish_until'] > $timestamp && $_sqlPages['publish_until'] != 0)
					)
			); else continue;



			$_sqlString =	"	SELECT 		p.node_id, 
											p.page_path,
											p.page_id,
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

				$_returnArray[$_sqlPgHead['page_language']]['path'] 	    .= $_sqlPgHead['page_path'];
				$_returnArray[$_sqlPgHead['page_language']]['node_id'] 		 = $_sqlPgHead['node_id'];
				$_returnArray[$_sqlPgHead['page_language']]['page_language'] = $_sqlPgHead['page_language'];
				$_returnArray[$_sqlPgHead['page_language']]['page_id'] 		 = $_sqlPgHead['page_id'];
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