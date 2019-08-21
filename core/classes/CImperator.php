<?php

require_once 'CBasic.php';

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelBackend.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPage.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPageObject.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSitemap.php';	

class	CImperator extends CBasic
{
	private	$m_sqlConnection;
	private $m_bEditMode;
	public	$m_page;
	public	$m_sitemap;

	public function
	__construct(&$_sqlConnection)
	{
		if(empty($_sqlConnection) || !property_exists($_sqlConnection, 'errno') || $_sqlConnection -> errno != 0)
			trigger_error("CImperator::__construct -- Invalid SQL connection", E_USER_ERROR);

		parent::__construct();

		$this -> m_sqlConnection 	= &$_sqlConnection;
		$this -> m_bEditMode 		= false;
		$this -> m_page				= NULL;
		$this -> m_sitemap			= NULL;
	}

	private function
	initPage(&$pageModel)
	{
		$this -> m_page = &$pageModel -> getDataInstance();

		##	Crumb information for requested Page

		##	This part is for backend, those pages are not in the database and is always level 2

		$this -> m_aStorage['crumbs'][] 	= 	[
												"page_name"		=>	$this -> m_page -> page_name,
												"page_path"		=>	$this -> m_page -> page_path .'/'
												];


		##	Crumb data for public pages
	
		$_pSitemap = new modelSitemap();
		$_pSitemap -> load($this -> m_sqlConnection, $this -> m_page -> page_language);
		$this -> m_sitemap = &$_pSitemap -> getDataInstance();

		##	Find requested page in sitemap and get array key

		foreach($this -> m_sitemap as $_mapIndex =>  $_mapItem)
		{
			if($_mapItem -> node_id === $this -> m_page -> node_id)
			{
				$_pageIndex = $_mapIndex;
				break;
			}
		}

		if(isset($_pageIndex))
		{

		##	Loop Array back to start page and grab the pages for crumb path

		$_level = $this -> m_sitemap[$_pageIndex] -> level;

		for($i = $_pageIndex; $i >= 0; $i--)
		{
			if($this -> m_sitemap[$i] -> level == $_level)
			{
				$this -> m_page -> crumb_path[] = $this -> m_sitemap[$i];
				$_level--;
			}
		}
	
		##	Reverse array

		$this -> m_page -> crumb_path = array_reverse($this -> m_page -> crumb_path);
		}	
	}

	public function
	logic(array &$_userRequest, $_modules, array $_rcaTarget, bool $_isBackendMode)
	{
		if($_isBackendMode)
		{
			if(!$this -> logic_backend($_userRequest, $_modules, $_rcaTarget))
			{
				if(!isset($_userRequest['public_view']) || $_userRequest['public_view'] === false)
					return;		

			}
		}		
		$this -> logic_public($_userRequest, $_modules, $_rcaTarget);
	}

	private function
	logic_public(array &$_userRequest, $_modules, array $_rcaTarget)
	{
		$this -> m_modelPage  = new modelPage();
		if(!$this -> m_modelPage -> load($this -> m_sqlConnection, $_userRequest['node_id']))
		{
			/*
			TODO :: Seite nicht gefunden, kein Fallback 	->	 404

			CMessages::instance() -> addMessage('Database table (1) error: '. $this -> m_sqlConnection -> error, MSG_LOG);
			trigger_error("CImperator::logic -- There is a page table (1) issue, the query failed",E_USER_ERROR);
			*/
			echo '404';
			return;
		}
		$this -> initPage($this -> m_modelPage);

		##	Gathering active modules data

		#if(!$this -> m_page -> objects) return;

		$_aActiveModules = $_modules -> getModules();		

		if(!empty( $this -> m_page -> objects ))
		foreach($this -> m_page -> objects as $_objectIndex =>  $_object)
		{	
			$_iModuleIndex = $_modules -> isLoaded( $_object -> module_id );
			if( $_iModuleIndex === false) continue;

			$_logicResult =	false;

			$this -> m_page -> objects[$_objectIndex] -> instance	 = 	new $_aActiveModules[$_iModuleIndex]['module_controller']($_aActiveModules[$_iModuleIndex], $_object);
			$this -> m_page -> objects[$_objectIndex] -> instance	->	logic(
																				$this -> m_sqlConnection, 
																				$_rcaTarget, 
																				CSession::instance() -> getUserRights($_object -> module_id), 
																				$_userRequest['xhrequest'], 
																				$_logicResult, 
																				$this -> m_bEditMode
																				);
		}

		$this -> defineConstants((isset($_userRequest['origin_path']) ? $_userRequest['origin_path'] . $this -> m_page -> page_language .'/'. $this -> m_page -> node_id : $this -> m_page -> page_path .'/' ));
	}

	private function
	logic_backend(array &$_userRequest, $_modules, array $_rcaTarget)
	{

		$this -> m_modelBackend  = new modelBackend();
		if(!$this -> m_modelBackend -> load($this -> m_sqlConnection, $_userRequest['node_id']))
		{
			/*
			Backend 404 nicht gefunden
			*/
			echo '404';
			return;
		}

		$this -> initPage($this -> m_modelBackend);

		##	Gathering active modules data
	
		$_aActiveModules = $_modules -> getModules();	

		##	XHR call

		if($_userRequest['xhrequest'] !== false)
		{
			$_pURLVariables	 =	new CURLVariables();
			$_request		 =	[];
			$_request[] 	 = 	[	"input" => "cms-insert-module",  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
			$_request[] 	 = 	[	"input" => "cms-order-by-modules",  "validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
			$_pURLVariables -> retrieve($_request, false, true); // POST 

			if($_pURLVariables -> getValue("cms-insert-module") !== false)
			{	
				##	XHR Function call

				$_request[] 	 = 	[	"input" => "cms-insert-after",  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => 0 ]; 		
				$_request[] 	 = 	[	"input" => "cms-insert-node-id",  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => 0 ]; 		
				$_pURLVariables -> retrieve($_request, false, true); // POST 

				$_bValidationErr =	false;
				$_bValidationMsg =	'';
				$_bValidationDta = 	[];



				$_aActiveModules = $_modules -> getModules();	

				$_iModuleIndex = $_modules -> isLoaded( $_pURLVariables -> getValue("cms-insert-module") );
				if( $_iModuleIndex === false)
				{
					$_bValidationErr =	true;
					$_bValidationMsg =	'Module is unknown';					
				}
				else
				{

					// page version eintragen, das sollen nicht die x module machen
					// komm ja nich drum rum, model erstellen mit dieser id laden und wir dann die aktuelle version bekommen
					/*

					Wir brauchen die aktuelle page_version + 1

					Wir müssen alle Objekte dieser Page duplizieren mit der neuen Page Version

					Die Objekte werden aber in der public logic behandelt


					*/


					$_initObj	 =	[
										'page_version'		=>	'1',
										'object_id'			=>	0,
										'module_id'			=>	$_pURLVariables -> getValue("cms-insert-module"),
										'object_order_by'	=>	$_pURLVariables -> getValue("cms-insert-after"),
										'node_id'			=>	$_pURLVariables -> getValue("cms-insert-node-id"),
										'time_create'		=>	time(),
										'create_by'			=>	0
									];

					$_objectModel  = new modelPageObject();
					$_objectModel -> create($this -> m_sqlConnection, $_initObj);
					$objectData	   = $_objectModel -> getDataInstance();

					$_logicResult 	  = [];
					$_objectInstance  = new $_aActiveModules[$_iModuleIndex]['module_controller']($_aActiveModules[$_iModuleIndex], $objectData);
					$_objectInstance -> logic(
												$this -> m_sqlConnection, 
												[ $objectData -> object_id => 'create' ], 
												CSession::instance() -> getUserRights($_pURLVariables -> getValue("cms-insert-module")), 
												$_userRequest['xhrequest'], 
												$_logicResult, 
												true
												);
					
				}

				tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
			}

			if($_pURLVariables -> getValue("cms-order-by-modules") !== false)
			{	
				##	XHR Function call
	
				$_request[] 	 = 	[	"input" => "cms-order-by-node-id",  "validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => 0 ]; 		
				$_pURLVariables -> retrieve($_request, false, true); // POST 

				$_bValidationErr =	false;
				$_bValidationMsg =	'';
				$_bValidationDta = 	[];

				$_pageNodeID	= $_pURLVariables -> getValue("cms-order-by-node-id");
				$_objectModel  	= new modelPageObject();

				foreach($_pURLVariables -> getValue("cms-order-by-modules") as $_objectIndex =>  $_objectID)
				{
					$_updateSet['node_id']			= $_pageNodeID;
					$_updateSet['object_id']		= $_objectID;
					$_updateSet['object_order_by']	= ($_objectIndex + 1);

					$_objectModel -> updateOrderBy($this -> m_sqlConnection, $_updateSet);					
				}

				tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
			}

		}



		##	Looping objects

		foreach($this -> m_page -> objects as $_objectKey =>  $_object)
		{
			
			$_iModuleIndex = $_modules -> isLoaded( $_object -> module_id );
			if( $_iModuleIndex === false) continue;

			##	Read additional module files (module.json and *.lang)

			switch($_aActiveModules[$_iModuleIndex]['module_type'])
			{
				case 'core'  :	$_modLocation	= CMS_SERVER_ROOT . DIR_CORE . DIR_MODULES . $_aActiveModules[$_iModuleIndex]['module_location'] .'/';									
								break;

				case 'mantle':	$_modLocation	= CMS_SERVER_ROOT . DIR_MANTLE . DIR_MODULES . $_aActiveModules[$_iModuleIndex]['module_location'] .'/';
								break;
			}

			if(isset($_modLocation) && file_exists($_modLocation .'module.json'))
			{
				$_moduleConfig 	= file_get_contents($_modLocation .'module.json');
				$_moduleConfig	= json_decode($_moduleConfig,true);
				$_aActiveModules[$_iModuleIndex] = array_merge($_aActiveModules[$_iModuleIndex], $_moduleConfig);
			}			

			CLanguage::instance() -> loadLanguageFile($_modLocation .'/lang/', $_userRequest['page_language']);

			##	Create object and call logic

			$_logicResult =	false;
			$this -> m_page -> objects[$_objectKey] -> instance 	 = 	new $_aActiveModules[$_iModuleIndex]['module_controller']($_aActiveModules[$_iModuleIndex], $_object);
			$this -> m_page -> objects[$_objectKey] -> instance	->	logic($this -> m_sqlConnection, $_rcaTarget, CSession::instance() -> getUserRights($_object -> module_id), $_userRequest['xhrequest'], $_logicResult, false);

			if($_logicResult !== false && $_logicResult['state'] === 1)
			{	## 	This means exit function and recall imperator public logic

				$_userRequest['node_id']		=	$_logicResult['node_id'];
				$_userRequest['page_language']	=	$_logicResult['page_language'];
				$_userRequest['page_version']	=	$_logicResult['page_version'];
				$_userRequest['public_view']	=	true;
				$_userRequest['origin_path']	=	$this -> m_page -> page_path .'/'.$_rcaTarget[ $this -> m_page -> objects[$_objectKey] -> object_id ] .'/';
				$_userRequest['origin_index']	=	$this -> m_page -> page_path .'/';
				$_userRequest['origin_rca']		=	$_rcaTarget;

				$this -> m_bEditMode = true;
				return false;
			}

			$this -> m_aStorage['crumbs'][]					 =	$this -> m_page -> objects[$_objectKey] -> instance -> getCrumb();
			$this -> m_aStorage['sub_section']				 =	$this -> m_page -> objects[$_objectKey] -> instance -> getSubSection();
		}
		
		##	Set define for page path 

		$this -> defineConstants($this -> m_page -> page_path .'/');

	}

	private function
	defineConstants($_pagePath)
	{
		define('REQUESTED_PAGE_PATH', $_pagePath);
	}

	public function
	view()
	{
		if($this -> m_bEditMode)
		{
			echo '<div class="cms-edit-content-container">';

			foreach($this -> m_page -> objects as $_objectIndex =>  &$_object)
			{
				echo '<div class="cms-content-object">';
				$_object -> instance -> view();
				echo '</div>';
			}

			echo '</div>';
		}
		else
		{
			if($this -> m_page -> objects === NULL) return;

			foreach($this -> m_page -> objects as $_objectIndex =>  &$_object)
				$_object -> instance -> view();
		}
	}

	public function
	getCrumbPath()
	{
		if(isset($this -> m_aStorage['crumbs'])) return $this -> m_aStorage['crumbs'];
		return [];
	}

	public function
	getSubSection()
	{
		if(isset($this -> m_aStorage['sub_section'])) return $this -> m_aStorage['sub_section'];
		return [];
	}

}

?>