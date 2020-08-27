<?php

define('SITEMAP_OWN_CHILDS_ONLY',0x1);

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeSitemap.php';	

class 	modelSitemap extends CModel
{
	public function
	__construct()
	{		
        parent::__construct('shemeSitemap', 'sitemap');
	}	
				
	public function
	load(CDatabaseConnection &$_pDatabase, CModelCondition &$_pCondition = NULL, $_execFlags = NULL) : bool
	{
		$_mainpageNodeID = 1;

		$dbQuery 	= $_pDatabase		-> query(DB_SELECT) 
										-> table('tb_page_path') 
										-> selectColumns(['*'])
										-> condition($_pCondition);

		$nodeResult = $dbQuery -> exec($_execFlags);
		
		$_sqlNode 			= $nodeResult[0];
		$_mainpageNodeID 	= $_sqlNode -> node_id;

		##	Get node and children
					
		$relCondPgHead	 = new CModelCondition();
		$relCondPgHead	-> where('tb_page_header.node_id', 'o.node_id');
		$relPgHead		 = new CModelRelations('join', 'tb_page_header', $relCondPgHead);
					
		$relCondPg		 = new CModelCondition();
		$relCondPg		-> where('tb_page.node_id', 'o.node_id');
		$relPg			 = new CModelRelations('join', 'tb_page', $relCondPg);

		$condPgPath	 	 = new CModelCondition();
		$condPgPath		-> whereBetween('o.node_lft', 'p.node_lft', 'p.node_rgt', true)
						-> whereBetween('o.node_lft', 'n.node_lft', 'n.node_rgt', true)
						-> where('n.node_id', $_mainpageNodeID)
						-> groupBy('o.node_lft')
						-> orderBy('o.node_lft');

		$sqlNodeRes 	 = $_pDatabase		-> query(DB_SELECT) 
											-> table('tb_page_path', 'n') 
											-> table('tb_page_path', 'p') 
											-> table('tb_page_path', 'o') 
											-> selectColumns([	'o.node_id',
																'o.page_id',
																'o.page_language',
																'COUNT(p.node_id)-1 AS level',
																'ROUND ((o.node_rgt - o.node_lft - 1) / 2) AS offspring',
																'tb_page_header.page_title',
																'tb_page_header.page_name',
																'tb_page_header.page_version',
																'tb_page.create_time',
																'tb_page.update_time',
																'tb_page.page_auth',
																'tb_page.publish_from',
																'tb_page.publish_until',
																'tb_page.publish_expired',
																'tb_page.hidden_state',
																'tb_page.menu_follow',
																'o.page_path AS page_path_segment'])
											-> condition($condPgPath)
											-> relations([$relPgHead, $relPg])
											-> exec();
		
		if($sqlNodeRes === false || !count($sqlNodeRes))
		{
			trigger_error('modelSitemap::load() - Node does not exists');
			return false;
		}

		##	Loop node result and add page path by parents
		$childsLevel = NULL;
		$_pages = [];
		foreach($sqlNodeRes as $_sqlNode)
		{
			if($_execFlags & SITEMAP_OWN_CHILDS_ONLY)
			{
				if($childsLevel === NULL)
				{
					$childsLevel = intval($_sqlNode -> level);
					$childsLevel++;
					continue;
				}

				if($childsLevel !== intval($_sqlNode -> level))
				{
					continue;
				}
			}

			$_sqlNode -> page_path = $this -> getPagePath($_pDatabase, $_sqlNode -> node_id, $_sqlNode -> page_language);
			$_sqlNode -> page_path_segment = trim($_sqlNode -> page_path_segment, '/');
	
			$_pages[]  = $_sqlNode;
		}

		##	Create data objects and get alternate pages

		$_className		=	$this -> createPrototype();

		foreach($_pages as $_pageIndex => $_page)
		{
			$_page -> alternate_path = $this -> getAlternatePaths($_pDatabase, $_page -> page_id);

			$this -> m_resultList[] = new $_className($_page, $this -> m_pSheme -> getColumns());
		}	

		return true;
	}

	private function
	getAlternatePaths(CDatabaseConnection &$_pDatabase, $_pageID) : array
	{
		$_returnArray	=	[];
					
		$condPage = new CModelCondition();
		$condPage-> where('page_id', $_pageID);

		$sqlPagesRes 	 = $_pDatabase		-> query(DB_SELECT) 
											-> table('tb_page_path') 
											-> selectColumns(['node_id', 'page_language'])
											-> condition($condPage)
											-> exec();

		if($sqlPagesRes === false)
			return $_returnArray;

		foreach($sqlPagesRes as $_sqlPages)
		{
			$condPgHead		 = new CModelCondition();
			$condPgHead		-> whereBetween('n.node_lft', 'p.node_lft', 'p.node_rgt', true)
							-> where('n.node_id', $_sqlPages -> node_id)
							-> orderBy('p.node_lft');

			$sqlPgHeadRes	 = $_pDatabase		-> query(DB_SELECT) 
												-> table('tb_page_path', 'n') 
												-> table('tb_page_path', 'p') 
												-> selectColumns(['p.node_id', 'p.page_path', 'p.page_language'])
												-> condition($condPgHead)
												-> exec();
		
			if($sqlPgHeadRes === false)
				break;

			foreach($sqlPgHeadRes as $_sqlPgHead)
			{
				if($_sqlPgHead -> page_language == '0') continue;

				if(!isset($_returnArray[$_sqlPgHead -> page_language]))
					$_returnArray[$_sqlPgHead -> page_language]['path'] = '';

				$_returnArray[$_sqlPgHead -> page_language]['path'] 	.= $_sqlPgHead -> page_path;
				$_returnArray[$_sqlPgHead -> page_language]['node_id']   = $_sqlPgHead -> node_id;
			}	
		}

		return $_returnArray;
	}
	
	private function
	getPagePath(CDatabaseConnection &$_pDatabase, int $_nodeID, string $_language) : string
	{
		$condNode		 = new CModelCondition();
		$condNode		-> whereBetween('n.node_lft', 'p.node_lft', 'p.node_rgt', true)
						-> where('n.node_id', $_nodeID)
						-> where('p.page_language', $_language)
						-> orderBy('p.node_lft');

		$sqlNodeRes 	 = $_pDatabase		-> query(DB_SELECT) 
											-> table('tb_page_path', 'n') 
											-> table('tb_page_path', 'p') 
											-> selectColumns(['p.node_id', 'p.page_path'])
											-> condition($condNode)
											-> exec();

		if($sqlNodeRes === false || !count($sqlNodeRes))
		{
			return '/';
		}

		$_pagePath = '';
		
		foreach($sqlNodeRes as $_sqlNode)
		{
			$_pagePath .= $_sqlNode -> page_path;
		}
		return $_pagePath;
	}
}


/**
 * 	Parent class for the data class with toolkit functions. It get the child instance to access the child properties.

class 	toolkitSitemap
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