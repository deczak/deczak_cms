<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemePageHeader.php';
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemePagePath.php';
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemePage.php';

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeBackendPageHeader.php';
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeBackendPagePath.php';
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeBackendPage.php';

include_once 'modelPagePath.php';		
include_once 'modelPageHeader.php';

include_once 'modelCategories.php';	
include_once 'modelTags.php';	
include_once 'modelSimple.php';

class 	modelPage extends CModel
{
	protected	$m_shemePageHeader;
	protected	$m_shemePagePath;
	protected	$m_shemePage;

	public function
	__construct(string $_shemeName = 'shemePage', string $_dataObjectName = 'page')
	{
		parent::__construct($_shemeName, $_dataObjectName);

		$this -> m_shemePageHeader 	= new shemePageHeader();
		$this -> m_shemePagePath 	= new shemePagePath();
		$this -> m_shemePage	 	= new shemePage();

		$this -> m_modelPageHeader	= 'modelPageHeader';
		$this -> m_modelPagePath	= 'modelPagePath';
	}	
	
	public function
	load(CDatabaseConnection &$_pDatabase, CModelCondition $_pCondition = NULL, $_execFlags = NULL)
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
	loadByNodeSearch(CDatabaseConnection &$_pDatabase, CNodesSearch $_nodesSearch, int $_rootNodeId = 0)
	{
		$nodeIdList = [];

		switch($_nodesSearch -> getType())
		{
			case 'tag':

					$tagAllocCondition  = new CModelCondition();
					$tagAllocCondition -> where('tag_url', urldecode($_nodesSearch -> getValue()));		

					$conditionPages  = new CModelCondition();
					$conditionPages -> where('tb_tags_allocation.tag_id', 'tb_tags.tag_id');	

					$modelTagsAllocation  = new modelTagsAllocation();
					$modelTagsAllocation -> addSelectColumns('tb_tags.*', 'tb_tags_allocation.*');
					$modelTagsAllocation -> addRelation('join', 'tb_tags', $conditionPages);
					$modelTagsAllocation -> load($_pDatabase, $tagAllocCondition);

					foreach($modelTagsAllocation -> getResult() as $node)
						$nodeIdList[] = $node -> node_id;

					break;

			case 'category':
			
					$categorieAllocCondition  = new CModelCondition();
					$categorieAllocCondition -> where('category_url', urldecode($_nodesSearch -> getValue()));		

					$conditionPages  = new CModelCondition();
					$conditionPages -> where('tb_categories_allocation.category_id', 'tb_categories.category_id');	

					$modelCategoriesAllocation  = new modelCategoriesAllocation();
					$modelCategoriesAllocation -> addSelectColumns('tb_categories.*', 'tb_categories_allocation.*');
					$modelCategoriesAllocation -> addRelation('join', 'tb_categories', $conditionPages);
					$modelCategoriesAllocation -> load($_pDatabase, $categorieAllocCondition);

					foreach($modelCategoriesAllocation -> getResult() as $node)
						$nodeIdList[] = $node -> node_id;

					break;

			case 'search':

					if($_rootNodeId == 0)
						return false;

					$whereInList = [];

					$sitemapCondition = new CModelCondition();
					$sitemapCondition -> where('node_id', $_rootNodeId);

					$modelSitemap = new modelSitemap();
					$modelSitemap -> load($_pDatabase, $sitemapCondition);

					foreach($modelSitemap -> getResult() as $node)
						$whereInList[] = $node -> node_id;

					$searchValues = urldecode($_nodesSearch -> getValue());
					$searchValues = explode(' ', $searchValues);
					$searchValues = array_filter($searchValues, 'strlen');

					$condSimple  = new CModelCondition();

					foreach($searchValues as $value)
					{
						if($value[0] === '-')
						{
							$condSimple -> whereNotLike('tb_page_object_simple.body', '%'. substr($value, 1) .'%');
						}
						else
						{
							$condSimple -> whereLike('tb_page_object_simple.body', '%'. $value .'%');
						}
					}

					$condSimple -> whereIn('tb_page_object.node_id', implode(',', $whereInList));

					$condObject  = new CModelCondition();
					$condObject -> where('tb_page_object.object_id', 'tb_page_object_simple.object_id');	

					$modelSimple  = new modelSimple();
					$modelSimple -> addSelectColumns('tb_page_object_simple.*', 'tb_page_object.*');
					$modelSimple -> addRelation('join', 'tb_page_object', $condObject);
					$modelSimple -> load($_pDatabase, $condSimple);

					foreach($modelSimple -> getResult() as $node)
						$nodeIdList[] = $node -> node_id;

					$nodeIdList = array_unique($nodeIdList);

					break;
		}

		$_tablePage				=	$this -> m_shemePage 		-> getTableName();

		$condition	 = new CModelCondition();
		$condition	-> whereIn("$_tablePage.node_id", implode(',', $nodeIdList));
		$condition  -> orderBy('create_time', 'DESC'); 

		$this -> load($_pDatabase, $condition);

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
	{
		$_tablePageHeader		=	$this -> m_shemePageHeader 	-> getTableName();
		$_tablePagePath			=	$this -> m_shemePagePath 	-> getTableName();
		$_tablePage				=	$this -> m_shemePage 		-> getTableName();

		$m_modelPageHeader		=	$this -> m_modelPageHeader;
		$m_modelPagePath		=	$this -> m_modelPagePath;
		
		##	add required data

		$_insertData['page_version'] 	= 	1;
		$_insertData['page_language']	=	$_insertData['cms-edit-page-lang'];

		if(empty($_insertData['page_title']))
			$_insertData['page_title'] = $_insertData['page_name'];

		if(!isset($_insertData['page_id']))
			$_insertData['page_id'] 		= 	$this -> getFreePageID($_pDatabase);


		if(empty($_insertData['page_path']))
			$_insertData['page_path'] = $_insertData['page_name'];

		if($_insertData['page_id'] !== '1')		
			$_insertData['page_path']		=	$this -> getValidPath($_pDatabase, $_insertData['cms-edit-page-node'], $_insertData['page_path']) .'/';
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
		$_insertData['node_level'] = $_parentNode -> node_level + 1;

		$modelPagePath 	 		= new $m_modelPagePath();
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
		
		$modelPageHeader 	 = new $m_modelPageHeader();
		$modelPageHeader 	-> insert($_pDatabase, $_insertData, $_execFlags);

		return $_insertData['node_id'];
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
		$dtaObject 		-> node_level 		= 'node_level-1';

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
			trigger_error('modelPage::deleteTree() - Node does not exists');
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
		$pagePathCond	-> whereGreater('node_lft', $_nodeData -> node_rgt);
		$dtaObject 		 = new stdClass();
		$dtaObject 		-> node_lft 		= 'node_lft-ROUND('. ( $_nodeData -> node_rgt - $_nodeData -> node_lft + 1 ) .')';
		$dtaObject 		-> prepareMode 		= false;
		$_pDatabase		-> query(DB_UPDATE) -> table($_tablePagePath) -> dtaObject($dtaObject) -> condition($pagePathCond) -> exec();


		$pagePathCond	 = new CModelCondition();
		$pagePathCond	-> whereGreater('node_rgt', $_nodeData -> node_rgt);
		$dtaObject 		 = new stdClass();
		$dtaObject 		-> node_rgt 		= 'node_rgt-ROUND('. ( $_nodeData -> node_rgt - $_nodeData -> node_lft + 1 ) .')';
		$dtaObject 		-> prepareMode 		= false;
		$_pDatabase		-> query(DB_UPDATE) -> table($_tablePagePath) -> dtaObject($dtaObject) -> condition($pagePathCond) -> exec();

		return true;
	}
			
	private function
	getNodeData(CDatabaseConnection &$_pDatabase, int $_nodeID, array &$_nodeData)
	{
		$tablePagePath	 =	$this -> m_shemePagePath 	-> getTableName();
		$condition		 = new CModelCondition();
		$condition		-> where('node_id', $_nodeID);

		$dbQuery = $_pDatabase		-> query(DB_SELECT) 
									-> table($tablePagePath) 
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
		$tablePagePath			=	$this -> m_shemePagePath 	-> getTableName();
		$tablePage				=	$this -> m_shemePage 		-> getTableName();

		$timestamp 		= 	time();
		$_returnArray	=	[];
		
		$nodePageRelCond = new CModelCondition();
		$nodePageRelCond-> where($tablePage.'.node_id', $tablePagePath.'.node_id');
		$nodePathRel	 = new CModelRelations('join', $tablePage, $nodePageRelCond);

		$nodePageCond	 = new CModelCondition();
		$nodePageCond	-> where($tablePagePath.'.page_id', $_pageID);

		$sqlPagesRes 	 = $_pDatabase		-> query(DB_SELECT) 
											-> table($tablePagePath) 
											-> selectColumns([$tablePagePath.'.node_id', $tablePagePath.'.page_language', $tablePage.'.hidden_state', $tablePage.'.page_auth', $tablePage.'.publish_from', $tablePage.'.publish_until'])
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

			$nodePathCond	 = new CModelCondition();
			$nodePathCond	-> whereBetween('n.node_lft', 'p.node_lft', 'p.node_rgt', true)
							-> where('n.node_id', $_sqlPages -> node_id)
							-> orderBy('p.node_lft');

			$sqlPgHeadRes	 = $_pDatabase	-> query(DB_SELECT) 
											-> table($tablePagePath, 'n') 
											-> table($tablePagePath, 'p') 
											-> selectColumns(['p.node_id', 'p.page_path', 'p.page_id', 'p.page_language'])
											-> condition($nodePathCond)
											-> exec();

			if($sqlPgHeadRes === false)
				break;

			foreach($sqlPgHeadRes as $_sqlPgHead)
			{
				if($_sqlPgHead -> page_language == '0')
					continue;

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
			$tagArray[] = 	[	
							"id" 	=> 	$sqlTagItm -> tag_id,
							"name" 	=>	$sqlTagItm -> tag_name
							];
		}

		return $tagArray;
	}
}

class 	modelBackendPage extends modelPage
{
	public function
	__construct()
	{
		parent::__construct('shemeBackendPage');
	
		$this -> m_shemePageHeader 	= new shemeBackendPageHeader();
		$this -> m_shemePagePath 	= new shemeBackendPagePath();
		$this -> m_shemePage	 	= new shemeBackendPage();


		$this -> m_modelPageHeader	= 'modelBackendPageHeader';
		$this -> m_modelPagePath	= 'modelBackendPagePath';
	}
}

?>