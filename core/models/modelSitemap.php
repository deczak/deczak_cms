<?php

define('SITEMAP_OWN_CHILDS_ONLY',0x1);

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeSitemap.php';	

class 	modelSitemap extends CModel
{
	private	$m_shemeSitemap;

	public function
	__construct()
	{		
		parent::__construct();		

		$this -> m_shemeSitemap = new shemeSitemap();
	}	
				
	public function
	load(&$_sqlConnection, CModelCondition $_condition = NULL, CModelComplementary $_complementary = NULL, $_flags = NULL)
	{
		$_mainpageNodeID = 1;

		#$_condition -> where('page_path', $_sqlConnection -> real_escape_string('/'));

		##

#		$_sqlString =	"	SELECT 		tb_page_path.node_id
		$_sqlString =	"	SELECT 		*
							FROM 		tb_page_path
						".	($_condition != NULL ? $_condition -> getConditions($_sqlConnection, $_condition) : '');

		$_sqlNodeRes = $_sqlConnection -> query($_sqlString);

		if($_sqlNodeRes !== false && $_sqlNodeRes -> num_rows == 1)
		{
			$_sqlNode 			= $_sqlNodeRes -> fetch_assoc();
			$_mainpageNodeID 	= $_sqlNode['node_id'];
		}

		##	Get node and children

		$_sqlString =	"	SELECT 		o.node_id,
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
										tb_page.menu_follow
							FROM 		tb_page_path AS n,
										tb_page_path AS p,
										tb_page_path AS o
							LEFT JOIN	tb_page_header
								ON		tb_page_header.node_id 			= o.node_id
							LEFT JOIN	tb_page
								ON		tb_page.node_id 				= o.node_id
							WHERE 		o.node_lft BETWEEN p.node_lft AND p.node_rgt
							AND 		o.node_lft BETWEEN n.node_lft AND n.node_rgt
							AND 		n.node_id = '". $_sqlConnection -> real_escape_string($_mainpageNodeID) ."'
							GROUP BY	o.node_lft
							ORDER BY 	o.node_lft
						";
			
		$_sqlNodeRes = $_sqlConnection -> query($_sqlString) or die($_sqlConnection -> error);

		if($_sqlNodeRes === false || !$_sqlNodeRes -> num_rows)
		{
			trigger_error('modelSitemap::load() - Node does not exists');
			return false;
		}

		##	Loop node result and add page path by parents
		$childsLevel = NULL;
		$_pages = [];
		while($_sqlNode = $_sqlNodeRes -> fetch_assoc())
		{
			if($_flags & SITEMAP_OWN_CHILDS_ONLY)
			{
				if($childsLevel === NULL)
				{
					$childsLevel = intval($_sqlNode['level']);
					$childsLevel++;
					continue;
				}

				if($childsLevel !== intval($_sqlNode['level']))
				{
					continue;
				}
			}

			$_sqlNode['page_path'] = $this -> getPagePath($_sqlConnection, $_sqlNode['node_id'], $_sqlNode['page_language']);
			$_pages[]  = $_sqlNode;
		}

		##	Create data objects and get alternate pages

		$_className		=	$this -> createClass($this -> m_shemeSitemap);

		foreach($_pages as $_pageIndex => $_page)
		{
			$_page['alternate_path'] = $this -> getAlternatePaths($_sqlConnection, $_page['page_id']);

			$this -> m_storage[] = new $_className($_page, $this -> m_shemeSitemap -> getColumns());
		}	

		return true;
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
								ORDER BY 	p.node_lft ASC
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
	
	private function
	getPagePath(&$_sqlConnection, int $_nodeID, string $_language)
	{
		$_sqlString =	"	SELECT 		p.node_id, 
										p.page_path
							FROM 		tb_page_path n,
										tb_page_path p
							WHERE 		n.node_lft
								BETWEEN p.node_lft 
									AND	p.node_rgt 
								AND 	n.node_id 		= '". $_sqlConnection -> real_escape_string($_nodeID) ."'
								AND 	p.page_language = '". $_sqlConnection -> real_escape_string($_language) ."'
							ORDER BY 	p.node_lft ASC
						";

		$_sqlNodeRes = $_sqlConnection -> query($_sqlString);

		if($_sqlNodeRes === false || !$_sqlNodeRes -> num_rows)
		{
			return '/';
		}

		$_pagePath = '';
		
		while($_sqlNode = $_sqlNodeRes -> fetch_assoc())
		{
			$_pagePath .= $_sqlNode['page_path'];
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