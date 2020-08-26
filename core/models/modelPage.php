<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemePage.php';

include_once 'modelPagePath.php';		
include_once 'modelPageHeader.php';

include_once 'modelCategories.php';	
include_once 'modelTags.php';	

class 	modelPage extends CModel
{
	private	$m_shemePageHeader;
	private	$m_shemePagePath;
	private	$m_shemePage;

	public function
	__construct()
	{
		parent::__construct('shemePage', 'page');

		$this -> m_shemePageHeader 	= new shemePageHeader();
		$this -> m_shemePagePath 	= new shemePagePath();
		$this -> m_shemePage	 	= new shemePage();
	}	
	
	public function
	load(CDatabaseConnection &$_pDatabase, CModelCondition &$_pCondition = NULL, $_execFlags = NULL)
	{
		if($_pDatabase === null)
			return false;

		$_tablePageHeader		=	$this -> m_shemePageHeader 	-> getTableName();
		$_tablePagePath			=	$this -> m_shemePagePath 	-> getTableName();
		$_tablePage				=	$this -> m_shemePage 		-> getTableName();

		$className 	= $this -> createPrototype();

		$condition	 = new CModelCondition();
		$condition	-> where("$_tablePageHeader.node_id", "$_tablePagePath.node_id");
		$this  		-> addRelation('LEFT JOIN', $_tablePageHeader, $condition);

		$condition	 = new CModelCondition();
		$condition	-> where("$_tablePage.node_id", "$_tablePagePath.node_id");
		$this  		-> addRelation('LEFT JOIN', $_tablePage, $condition);

		$this 		 -> addSelectColumns(	"$_tablePagePath.page_path", 
											"$_tablePagePath.node_id", 
											"$_tablePageHeader.page_title",
											"$_tablePageHeader.page_name",
											"$_tablePageHeader.page_description",
											"$_tablePageHeader.page_language",
											"$_tablePage.page_id",
											"$_tablePage.create_time",
											"$_tablePage.update_time",
											"$_tablePage.publish_from",
											"$_tablePage.publish_until",
											"$_tablePage.publish_expired",
											"$_tablePage.create_by",
											"$_tablePage.update_by",
											"$_tablePage.page_version",
											"$_tablePage.page_template",
											"$_tablePage.hidden_state",
											"$_tablePage.crawler_index",
											"$_tablePage.crawler_follow",
											"$_tablePage.page_auth"
											);

		$dbQuery 	= $_pDatabase		-> query(DB_SELECT) 
										-> table($_tablePagePath) 
										-> selectColumns($this -> m_selectList)
										-> dtaObjectName($className)
										-> condition($_pCondition)
										-> relations($this -> m_relationsList);

		$queryResult = $dbQuery -> exec($_execFlags);

		foreach($queryResult as $page)
		{
			$page -> alternate_path  = $this -> getAlternatePaths($_pDatabase, $page -> page_id);
			$page -> page_categories = $this -> getCategories($_pDatabase, $page -> node_id);
			$page -> page_tags 		 = $this -> getTags($_pDatabase, $page -> node_id);
		
			$this -> m_resultList[] = new $className($page, $this -> m_shemePage -> getColumns());
		}

		if(count($this -> m_resultList) == 0)
			return false;

		return true;
	}
	
	public function
	update(CDatabaseConnection &$_pDatabase, array $_insertData, CModelCondition &$_pCondition, $_execFlags = NULL)
	{	
		$updateResult = true;

		if($this -> m_resultList[0] -> page_path != '/')			
			$_insertData['page_path']		=	$this -> getValidPath($_pDatabase, $_insertData['node_id'], $_insertData['page_name']) .'/';
			#parent node id funktion erstellen

		##	tb_page



		$condition 			 = new CModelCondition();
		$condition 			-> where('node_id', $_insertData['node_id'])
							-> where('page_version', $_insertData['page_version']);

		$updateResult 		 = parent::update($_pDatabase, $_insertData, $condition, $_execFlags);

		##	tb_page_header

		$condition 			 = new CModelCondition();
		$condition 			-> where('node_id', $_insertData['node_id'])
							-> where('page_version', $_insertData['page_version'])
							-> where('page_language', $_insertData['page_language']);	

		$modelPageHeader 	 = new modelPageHeader();
		$modelPageHeader 	-> update($_pDatabase, $_insertData, $condition, $_execFlags);

		##	tb_page_path

		$condition 			 = new CModelCondition();
		$condition 			-> where('node_id', $_insertData['node_id'])
							-> where('page_language', $_insertData['page_language']);	

		$modelPagePath 		 = new modelPagePath();
		$modelPagePath 		-> update($_pDatabase, $_insertData, $condition, $_execFlags);

		##	return

		return $updateResult;
	}	

	/**
	 * 	This function updates target node and all child nodes
	 */
	public function
	updateChilds(CDatabaseConnection &$_pDatabase, $_insertData, CModelCondition $_condition = NULL)
	{
		if($_condition === NULL || !$_condition -> isSet()) return false;

		$childNodesList = [];
		$nodeId 		= $_condition -> getConditionListValue('node_id');
					
		$this -> getNodeTree($_pDatabase, $nodeId, $childNodesList);

		foreach($childNodesList as $node)
		{
			$condition = new CModelCondition();
			$condition -> where('node_id', $node -> node_id );

			parent::update($_pDatabase, $_insertData, $condition);
		}

		return true;
	}

	public function
	insert(CDatabaseConnection &$_pDatabase, array $_insertData, $_execFlags = NULL)
	#insert(CDatabaseConnection &$_pDatabase, &$_dataset, &$_insertID)
	{
		$_bQueryResult = true;

		$_tablePageHeader		=	$this -> m_shemePageHeader 	-> getTableName();
		$_tablePagePath			=	$this -> m_shemePagePath 	-> getTableName();
		$_tablePage				=	$this -> m_shemePage 		-> getTableName();
		
		##	add required data

		$_insertData['page_version'] 	= 	1;
		$_insertData['page_language']	=	$_insertData['cms-edit-page-lang'];

		if(empty($_insertData['page_title']))
			$_insertData['page_title'] = $_insertData['page_name'];

		if(!isset($_insertData['page_id']))
			$_insertData['page_id'] 		= 	$this -> getFreePageID($_pDatabase);

		if($_insertData['page_id'] !== '1')		
			$_insertData['page_path']		=	$this -> getValidPath($_pDatabase, $_insertData['cms-edit-page-node'], $_insertData['page_name']) .'/';
		else
			$_insertData['page_path']		= '/';
			
		##	Table tb_page_path
		

		$_parentNode = [];
		if(!$this -> getNodeData($_pDatabase, $_insertData['cms-edit-page-node'], $_parentNode))
		{
			trigger_error('modelPage::insert() - Node does not exists');
			return false;
		}

		$pagePathCond	 = new CModelCondition();
		$pagePathCond	-> whereGreaterEven('node_rgt', $_parentNode -> node_rgt);
		$dtaObject 		 = new stdClass();
		$dtaObject 		-> node_rgt 		= 'node_rgt+2';
		$dtaObject 		-> prepareMode 		= false;
		$_pDatabase		-> query(DB_UPDATE) -> table($_tablePagePath) -> dtaObject($dtaObject) -> condition($pagePathCond) -> exec();

		$pagePathCond	 = new CModelCondition();
		$pagePathCond	-> whereGreater('node_lft', $_parentNode -> node_rgt);
		$dtaObject 		 = new stdClass();
		$dtaObject 		-> node_lft 		= 'node_lft+2';
		$dtaObject 		-> prepareMode 		= false;
		$_pDatabase		-> query(DB_UPDATE) -> table($_tablePagePath) -> dtaObject($dtaObject) -> condition($pagePathCond) -> exec();

		$_insertData['node_lft'] = $_parentNode -> node_rgt;
		$_insertData['node_rgt'] = $_parentNode -> node_rgt + 1;

		$modelPagePath 	 		= new modelPagePath();
		$_insertData['node_id'] = $modelPagePath -> insert($_pDatabase, $_insertData, $_execFlags);


		##	Get page_auth from parent node and append dataset data

		$condition		 = new CModelCondition();
		$condition		-> where('node_id', $_parentNode -> node_id);

		$dbQuery 	= $_pDatabase		-> query(DB_SELECT) 
										-> table($_tablePage) 
										-> selectColumns(['page_auth'])
										-> condition($condition);

		$queryRes = $dbQuery -> exec();

		$_dataset['page_auth'] = $queryRes[0] -> page_auth;

		##	Table tb_page
		
		parent::insert($_pDatabase, $_insertData, $_execFlags);

		##	Table tb_page_header
		
		$modelPageHeader 	 = new modelPageHeader();
		$modelPageHeader 	-> insert($_pDatabase, $_insertData, $_execFlags);

		return $_bQueryResult;
	}
	
	public function
	delete(CDatabaseConnection &$_pDatabase, CModelCondition &$_pCondition, $_execFlags = NULL)
	{
		if($_pCondition === NULL || !$_pCondition -> isSet()) return false;
		
		$nodeId = $_pCondition -> getConditionListValue('node_id');

		$_nodeData = [];
		if(!$nodeId || !$this -> getNodeData($_pDatabase, $nodeId, $_nodeData))
		{
			trigger_error('modelPage::delete() - Node does not exists');
			return false;
		}

		$_tablePagePath			=	$this -> m_shemePagePath 	-> getTableName();
		$_tablePage				=	$this -> m_shemePage 		-> getTableName();

		$_pDatabase		-> query(DB_DELETE) -> table($_tablePage) -> condition($_pCondition) -> exec();

		$pagePathCond	 = new CModelCondition();
		$pagePathCond	-> where('node_lft', $_nodeData -> node_lft);
		$_pDatabase		-> query(DB_DELETE) -> table($_tablePagePath) -> condition($pagePathCond) -> exec();

		$pagePathCond	 = new CModelCondition();
		$pagePathCond	-> whereBetween('node_lft', $_nodeData -> node_lft, $_nodeData -> node_rgt);
		$dtaObject 		 = new stdClass();
		$dtaObject 		-> node_lft 		= 'node_lft-1';
		$dtaObject 		-> node_rgt 		= 'node_rgt-1';
		$dtaObject 		-> prepareMode 		= false;
		$_pDatabase		-> query(DB_UPDATE) -> table($_tablePagePath) -> dtaObject($dtaObject) -> condition($pagePathCond) -> exec();

		$pagePathCond	 = new CModelCondition();
		$pagePathCond	-> whereGreater('node_lft', $_nodeData -> node_rgt);
		$dtaObject 		 = new stdClass();
		$dtaObject 		-> node_lft 		= 'node_lft-2';
		$dtaObject 		-> prepareMode 		= false;
		$_pDatabase		-> query(DB_UPDATE) -> table($_tablePagePath) -> dtaObject($dtaObject) -> condition($pagePathCond) -> exec();

		$pagePathCond	 = new CModelCondition();
		$pagePathCond	-> whereGreater('node_rgt', $_nodeData -> node_rgt);
		$dtaObject 		 = new stdClass();
		$dtaObject 		-> node_rgt 		= 'node_rgt-2';
		$dtaObject 		-> prepareMode 		= false;
		$_pDatabase		-> query(DB_UPDATE) -> table($_tablePagePath) -> dtaObject($dtaObject) -> condition($pagePathCond) -> exec();

		return true;
	}	

	public function
	deleteTree(CDatabaseConnection &$_pDatabase, CModelCondition $_condition = NULL)
	{
		if($_condition === NULL || !$_condition -> isSet()) return false;

		$nodeId = $_condition -> getConditionListValue('node_id');

		$_nodeData = [];
		if(!$nodeId || !$this -> getNodeData($_pDatabase, $nodeId, $_nodeData))
		{
			trigger_error('modelPage::delete() - Node does not exists');
			return false;
		}

		$_tablePagePath			=	$this -> m_shemePagePath 	-> getTableName();
		$_tablePage				=	$this -> m_shemePage 		-> getTableName();

		$_nodeTree 	= [];
		$this -> getNodeTree($_pDatabase, $nodeId, $_nodeTree);
		foreach($_nodeTree as $_node)
		{
			$condition		 = new CModelCondition();
			$condition		-> where('node_id', $_node -> node_id);

			$_pDatabase		-> query(DB_DELETE) 
							-> table($_tablePage) 
							-> condition($condition)
							-> exec();
		}

		$condition		 = new CModelCondition();
		$condition		-> whereBetween('node_lft', $_nodeData -> node_lft, $_nodeData -> node_rgt);

		$_pDatabase		-> query(DB_DELETE) 
						-> table($_tablePagePath) 
						-> condition($condition)
						-> exec();

		$pagePathCond	 = new CModelCondition();
		$pagePathCond	-> where('node_lft', $_nodeData -> node_rgt);
		$dtaObject 		 = new stdClass();
		$dtaObject 		-> node_lft 		= 'node_lft-ROUND('. ( $_nodeData -> node_rgt - $_nodeData -> node_lft + 1 ) .')';
		$dtaObject 		-> prepareMode 		= false;
		$_pDatabase		-> query(DB_UPDATE) -> table($_tablePagePath) -> dtaObject($dtaObject) -> condition($pagePathCond) -> exec();


		$pagePathCond	 = new CModelCondition();
		$pagePathCond	-> where('node_rgt', $_nodeData -> node_rgt);
		$dtaObject 		 = new stdClass();
		$dtaObject 		-> node_rgt 		= 'node_rgt-ROUND('. ( $_nodeData -> node_rgt - $_nodeData -> node_lft + 1 ) .')';
		$dtaObject 		-> prepareMode 		= false;
		$_pDatabase		-> query(DB_UPDATE) -> table($_tablePagePath) -> dtaObject($dtaObject) -> condition($pagePathCond) -> exec();

		return true;
	}
			
	private function
	getNodeData(CDatabaseConnection &$_pDatabase, int $_nodeID, array &$_nodeData)
	{
		$condition		 = new CModelCondition();
		$condition		-> where('node_id', $_nodeID);

		$dbQuery = $_pDatabase		-> query(DB_SELECT) 
										-> table('tb_page_path') 
										-> condition($condition);

		$queryRes = $dbQuery -> exec();


		if($queryRes === false || !count($queryRes))
			return false;

		$_nodeData		=	$queryRes[0];
		return true;
	}

	private function
	getNodeTree(CDatabaseConnection &$_pDatabase, int $_nodeID, array &$_nodeData)
	{
		$_tablePagePath	=	$this -> m_shemePagePath 	-> getTableName();

		/*
			the directUse paramter is not a good practice

			but this between condition with table names needs to be outside of execute-input-parameters
		*/


		$nodeTreeCond	 = new CModelCondition();
		$nodeTreeCond	-> whereBetween('o.node_lft', 'p.node_lft', 'p.node_rgt', true)
						-> whereBetween('o.node_lft', 'n.node_lft', 'n.node_rgt', true)
						-> where('n.node_id', $_nodeID)
						-> groupBy('o.node_lft')
						-> orderBy('o.node_lft');

		$accessRes 		 = $_pDatabase		-> query(DB_SELECT) 
											-> table($_tablePagePath, 'n') 
											-> table($_tablePagePath, 'p') 
											-> table($_tablePagePath, 'o') 
											-> selectColumns(['o.page_path', 'o.node_id', 'COUNT(p.node_id)-1 AS level'])
											-> condition($nodeTreeCond)
											-> exec();


		/*

		$sqlString 	=	"	SELECT 		o.page_path,
											o.node_id,
											COUNT(p.node_id)-1 AS level
								FROM 		$_tablePagePath AS n,
											$_tablePagePath AS p,
											$_tablePagePath AS o
								WHERE 		o.node_lft BETWEEN p.node_lft AND p.node_rgt
								AND 		o.node_lft BETWEEN n.node_lft AND n.node_rgt
								AND 		n.node_id = '". $_nodeID ."'
								GROUP BY	o.node_lft
								ORDER BY 	o.node_lft
							";

		$accessRes = $_pDatabase -> getConnection() -> query($sqlString, PDO::FETCH_CLASS, 'stdClass');

		*/

		$_nodeData = $accessRes;


	}

	private function
	getFreePageID(CDatabaseConnection &$_pDatabase)
	{
		$tableName		=	$this -> m_shemePage 		-> getTableName();

		$condition		 = new CModelCondition();
		$condition		-> limit(1);
		$condition		-> orderBy('page_id', 'DESC');

		$dbQuery 	= $_pDatabase		-> query(DB_SELECT) 
										-> table($tableName) 
										-> selectColumns(['page_id'])
										-> condition($condition);

		$queryRes = $dbQuery -> exec();

		return (intval($queryRes[0] -> page_id) + 1);
	}

	private function
	getValidPath(CDatabaseConnection &$_pDatabase, int $_nodeID, string $_path, int $_try = 0)
	{
		$_level 	= 0;
		$_path 		= tk::normalizeFilename($_path, true) ;
		$_nodeTree 	= [];
		$_result	= false;
		$_path2Check = $_path;
		if($_try != 0)
			$_path2Check =  $_path .'-'. $_try;
	
		$this -> getNodeTree($_pDatabase, $_nodeID, $_nodeTree);
	
		foreach($_nodeTree as $_node)
		{
			if($_level == 0) $_level = $_node -> level + 1;
			if($_node -> level != $_level) continue;
			if($_node -> page_path === $_path2Check .'/')
			{
				$_result = $this -> getValidPath($_pDatabase, $_nodeID, $_path, ($_try + 1));
				if($_result !== false)
					return $_result;
			}
		}
		return $_path2Check;
	}

	private function
	getAlternatePaths(CDatabaseConnection &$_pDatabase, $_pageID)
	{
		$timestamp 		= 	time();

		$_returnArray	=	[];

		/*
		$sqlString 	=	"	SELECT 		tb_page_path.node_id,
											tb_page_path.page_language,
											tb_page.hidden_state,
											tb_page.page_auth,
											tb_page.publish_from,
											tb_page.publish_until
								FROM 		tb_page_path
								JOIN		tb_page
									ON		tb_page.node_id			= tb_page_path.node_id
								WHERE 		tb_page_path.page_id 	= '". $_pageID ."'
							";
		
		try
		{
			$sqlPagesRes = $_pDatabase -> getConnection() -> query($sqlString, PDO::FETCH_CLASS, 'stdClass') -> fetchAll();
		}
		catch(PDOException $exception)
		{
			CMessages::instance() -> addMessage('modelPage::getAlternatePaths - Query src node failed', MSG_LOG, '', true);				  
			return $_returnArray;
		}
		*/
		
		$nodePageRelCond = new CModelCondition();
		$nodePageRelCond-> where('tb_page.node_id', 'tb_page_path.node_id');
		$nodePathRel	 = new CModelRelations('join', 'tb_page', $nodePageRelCond);

		$nodePageCond	 = new CModelCondition();
		$nodePageCond	-> where('tb_page_path.page_id', $_pageID);

		$sqlPagesRes 	 = $_pDatabase		-> query(DB_SELECT) 
											-> table('tb_page_path') 
											-> selectColumns(['tb_page_path.node_id', 'tb_page_path.page_language', 'tb_page.hidden_state', 'tb_page.page_auth', 'tb_page.publish_from', 'tb_page.publish_until'])
											-> condition($nodePageCond)
											-> relations([$nodePathRel])
											-> exec();

		if($sqlPagesRes === false)
			return $_returnArray;

		foreach($sqlPagesRes as $_sqlPages)
		{
			if(
					($_sqlPages -> hidden_state == 0)
				&&	(empty($_sqlPages -> page_auth) || (!empty($_sqlPages -> page_auth) && CSession::instance() -> isAuthed($_sqlPages -> page_auth) === true))
				||	(	($_sqlPages -> hidden_state == 5 && $_sqlPages -> publish_from  < $timestamp)
					&&	($_sqlPages -> hidden_state == 5 && $_sqlPages -> publish_until > $timestamp && $_sqlPages -> publish_until != 0)
					)
			); else continue;

			$sqlString =	"	SELECT 		p.node_id, 
											p.page_path,
											p.page_id,
											p.page_language
								FROM 		tb_page_path AS n,
											tb_page_path AS p
								WHERE 		n.node_lft
									BETWEEN p.node_lft 
										AND	p.node_rgt 
									AND 	n.node_id = '". $_sqlPages -> node_id ."'
								ORDER BY 	p.node_lft
							";
			try
			{
				$sqlPgHeadRes = $_pDatabase -> getConnection() -> query($sqlString, PDO::FETCH_CLASS, 'stdClass') -> fetchAll();
			}
			catch(PDOException $exception)
			{
				CMessages::instance() -> addMessage('modelPage::getPagePath - Query alternate node failed', MSG_LOG, '', true);				  
				break;
			}


		/*

		$nodeTreeCond	 = new CModelCondition();
		$nodeTreeCond	-> whereBetween('o.node_lft', 'p.node_lft', 'p.node_rgt', true)
						-> whereBetween('o.node_lft', 'n.node_lft', 'n.node_rgt', true)
						-> where('n.node_id', $_nodeID)
						-> groupBy('o.node_lft')
						-> orderBy('o.node_lft');

		$accessRes 		 = $_pDatabase		-> query(DB_SELECT) 
											-> table($_tablePagePath, 'n') 
											-> table($_tablePagePath, 'p') 
											-> table($_tablePagePath, 'o') 
											-> selectColumns(['o.page_path', 'o.node_id', 'COUNT(p.node_id)-1 AS level'])
											-> condition($nodeTreeCond)
											-> exec();

													
		*/






			foreach($sqlPgHeadRes as $_sqlPgHead)
			{
				if($_sqlPgHead -> page_language == '0') continue;

				if(!isset($_returnArray[$_sqlPgHead -> page_language]))
					$_returnArray[$_sqlPgHead -> page_language]['path'] = '';

				$_returnArray[$_sqlPgHead -> page_language]['path'] 	    .= $_sqlPgHead -> page_path;
				$_returnArray[$_sqlPgHead -> page_language]['node_id'] 		 = $_sqlPgHead -> node_id;
				$_returnArray[$_sqlPgHead -> page_language]['page_language'] = $_sqlPgHead -> page_language;
				$_returnArray[$_sqlPgHead -> page_language]['page_id'] 		 = $_sqlPgHead -> page_id;
			}	
		}

		return $_returnArray;
	}

	private function
	getCategories(CDatabaseConnection &$_pDatabase, $_nodeId)
	{
		$catArray	=	[];

		$joinCondition		 = new CModelCondition();
		$joinCondition		-> where("tb_categories_allocation.category_id", "tb_categories.category_id");

		$selectCondition	 = new CModelCondition();
		$selectCondition	-> where("tb_categories_allocation.node_id", $_nodeId);

		$modelCategories	 = new modelCategories();
		$modelCategories  	-> addRelation('JOIN', 'tb_categories_allocation', $joinCondition);
		$modelCategories 	-> load($_pDatabase, $selectCondition);

		foreach($modelCategories -> getResult() as $sqlCatsItm)
		{
			$catArray[] = 	[	
							"id" 	=> 	$sqlCatsItm -> category_id,
							"name" 	=>	$sqlCatsItm -> category_name
							];
		}
	
		return $catArray;
	}

	private function
	getTags(CDatabaseConnection &$_pDatabase, $_nodeId)
	{
		$tagArray	=	[];

		$joinCondition		 = new CModelCondition();
		$joinCondition		-> where("tb_tags_allocation.tag_id", "tb_tags.tag_id");

		$selectCondition	 = new CModelCondition();
		$selectCondition	-> where("tb_tags_allocation.node_id", $_nodeId);

		$modelTags			 = new modelTags();
		$modelTags 		 	-> addRelation('JOIN', 'tb_tags_allocation', $joinCondition);
		$modelTags 			-> load($_pDatabase, $selectCondition);

		foreach($modelTags -> getResult() as $sqlTagItm)
		{
			$catArray[] = 	[	
							"id" 	=> 	$sqlTagItm -> tag_id,
							"name" 	=>	$sqlTagItm -> tag_name
							];
		}

		return $tagArray;
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