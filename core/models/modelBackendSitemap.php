<?php

include_once 'modelSitemap.php';

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SCHEME.'schemePageObject.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SCHEME.'schemeBackendPageObject.php';	

define('SITEMAP_BACKEND_EXTDATA',0x2);

class 	modelBackendSitemap extends modelSitemap
{
	public function
	__construct()
	{		
        parent::__construct();

		$this -> tbPage		 	= 'tb_backend_page';
		$this -> tbPagePath 	= 'tb_backend_page_path';
		$this -> tbPageHeader 	= 'tb_backend_page_header';
	}	
		
	public function
	load(CDatabaseConnection &$_pDatabase, CModelCondition $_pCondition = NULL, $_execFlags = NULL) : bool
	{
	    if(!parent::load($_pDatabase, $_pCondition, $_execFlags))
			return false;

		if(!($_execFlags & SITEMAP_BACKEND_EXTDATA))
			return true;

		##	Append required data for backend handling
			
		foreach($this -> m_resultList as &$page)
		{
			##	Menu Group Link

			$condPg	 	 = new CModelCondition();
			$condPg			-> where('node_id', $page -> node_id);

			$sqlNodeRes 	 = $_pDatabase		-> query(DB_SELECT) 
												-> table($this -> tbPage) 
												-> selectColumns(['menu_group'])
												-> condition($condPg)
												-> exec();
			
			if($sqlNodeRes === false || !count($sqlNodeRes))
			{
				continue;
			}
		
			foreach($sqlNodeRes as $_sqlNode)
			{		
				$page -> menu_group = $_sqlNode -> menu_group;
			}

			##	Object info

			$modelBackendPageObject = modelBackendPageObject::
				  where('node_id', '=', $page->node_id)
				->get();

			#$modelBackendPageObject = new modelBackendPageObject;
			#$modelBackendPageObject -> load($_pDatabase, $condPg);

			$page -> objects = [];
			foreach($modelBackendPageObject as $_sqlNode)
			{		
				$page -> objects[] = $_sqlNode;
			}
		}

		return true;
	}
}
