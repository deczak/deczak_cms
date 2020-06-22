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

		#$_pCondition -> where('page_path', $_sqlConnection -> real_escape_string('/'));

		##
/*

		#$_sqlString =	"	SELECT 		tb_page_path.node_id
		$_sqlString =	"	SELECT 		*
							FROM 		tb_page_path
						".	($_pCondition != NULL ? $_pCondition -> getConditions($_sqlConnection, $_pCondition) : '');

		$_sqlNodeRes = $_sqlConnection -> query($_sqlString);
*/





		$dbQuery 	= $_pDatabase		-> query(DB_SELECT) 
										-> table('tb_page_path') 
										-> selectColumns(['*'])
										-> condition($_pCondition);

		$nodeResult = $dbQuery -> exec($_execFlags);



		
			$_sqlNode 			= $nodeResult[0];
			$_mainpageNodeID 	= $_sqlNode -> node_id;
		

		##	Get node and children

		$sqlString 	=	"	SELECT 		o.node_id,
										o.page_id,
										o.page_language,
										COUNT(p.node_id)-1 AS level,
										ROUND ((o.node_rgt - o.node_lft - 1) / 2) AS offspring,
										tb_page_header.page_title,
										tb_page_header.page_name,
										tb_page_header.page_version,
										tb_page.create_time,
										tb_page.update_time,
										tb_page.page_auth,
										tb_page.publish_from,
										tb_page.publish_until,
										tb_page.publish_expired,
										tb_page.hidden_state,
										tb_page.menu_follow,
										o.page_path AS page_path_segment
							FROM 		tb_page_path AS n,
										tb_page_path AS p,
										tb_page_path AS o
							LEFT JOIN	tb_page_header
								ON		tb_page_header.node_id 			= o.node_id
							LEFT JOIN	tb_page
								ON		tb_page.node_id 				= o.node_id
							WHERE 		o.node_lft BETWEEN p.node_lft AND p.node_rgt
							AND 		o.node_lft BETWEEN n.node_lft AND n.node_rgt
							AND 		n.node_id = '$_mainpageNodeID'
							GROUP BY	o.node_lft
							ORDER BY 	o.node_lft
						";

		try
		{
			$sqlNodeRes = $_pDatabase -> getConnection() -> query($sqlString, PDO::FETCH_CLASS, 'stdClass') -> fetchAll();
		}
		catch(PDOException $exception)
		{
			CMessages::instance() -> addMessage('modelSitemap::load - Query node and childrens failed', MSG_LOG, '', true);				  
			return false;
		}

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

		$sqlString 	=	"	SELECT 		tb_page_path.node_id,
										tb_page_path.page_language
							FROM 		tb_page_path
							WHERE 		tb_page_path.page_id 		= '". $_pageID ."'
						";

		try
		{
			$sqlPagesRes = $_pDatabase -> getConnection() -> query($sqlString, PDO::FETCH_CLASS, 'stdClass') -> fetchAll();
		}
		catch(PDOException $exception)
		{
			CMessages::instance() -> addMessage('modelSitemap::getAlternatePaths - Query src node failed', MSG_LOG, '', true);				  
			return $_returnArray;
		}

		foreach($sqlPagesRes as $_sqlPages)
		{
			$sqlString =	"	SELECT 		p.node_id, 
											p.page_path,
											p.page_language
								FROM 		tb_page_path AS n,
											tb_page_path AS p
								WHERE 		n.node_lft
									BETWEEN p.node_lft 
										AND	p.node_rgt 
									AND 	n.node_id = '". $_sqlPages -> node_id ."'
								ORDER BY 	p.node_lft ASC
							";

			try
			{
				$sqlPgHeadRes = $_pDatabase -> getConnection() -> query($sqlString, PDO::FETCH_CLASS, 'stdClass') -> fetchAll();
			}
			catch(PDOException $exception)
			{
				CMessages::instance() -> addMessage('modelSitemap::getPagePath - Query alternate node failed', MSG_LOG, '', true);				  
				break;
			}

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
		$sqlString =	"	SELECT 		p.node_id, 
										p.page_path
							FROM 		tb_page_path n,
										tb_page_path p
							WHERE 		n.node_lft
								BETWEEN p.node_lft 
									AND	p.node_rgt 
								AND 	n.node_id 		= '$_nodeID'
								AND 	p.page_language = '$_language'
							ORDER BY 	p.node_lft ASC
						";
	
		try
		{
			$sqlNodeRes = $_pDatabase -> getConnection() -> query($sqlString, PDO::FETCH_CLASS, 'stdClass') -> fetchAll();
		}
		catch(PDOException $exception)
		{
			CMessages::instance() -> addMessage('modelSitemap::getPagePath - Query node failed', MSG_LOG, '', true);				  
			return '/';
		}

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