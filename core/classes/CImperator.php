<?php

require_once 'CBasic.php';

class	CImperator extends CBasic
{
	private	$m_sqlConnection;

	private $m_pUserRights;

	public function
	__construct(&$_sqlConnection)
	{
		parent::__construct();

		$this -> m_sqlConnection 	= &$_sqlConnection;
	}


	public function
	logic(&$_sqlConnection, &$_pPageRequest, $_modules, array $_rcaTarget, bool $_isBackendMode, CUserRights &$_pUserRights)
	{
		if($_isBackendMode)
		{
			if(!$this -> logic_backend($_pPageRequest, $_modules, $_rcaTarget))
			{

				if(!$_pPageRequest -> isEditMode)
					return;		

				$pageRequest = CPageRequest::instance();

				$_pPageRequest -> init($_sqlConnection, $_pPageRequest -> node_id, $_pPageRequest -> page_language, $_pPageRequest -> page_version, $_pPageRequest -> xhRequest);

				$_pPageRequest -> enablePageEdit = ((!empty($pageRequest -> languageInfo) && !$pageRequest -> languageInfo -> lang_locked) ? $_pPageRequest -> enablePageEdit : false);
				
				$_pUserRights -> disableEditRights(!$_pPageRequest -> enablePageEdit);

				$this -> m_pUserRights = $_pUserRights;
			}
		}		

		$this -> logic_public($_pPageRequest, $_modules, $_rcaTarget);
	}

	private function
	logic_public(&$_pPageRequest, $_modules, array $_rcaTarget)
	{
		if($_pPageRequest -> responseCode !== 200)
		{	
			return;
		}

		##	Gathering active modules data

		if(!empty($_pPageRequest -> objectsList))
		foreach($_pPageRequest -> objectsList as $_objectIndex =>  $_object)
		{	
			$module = $_modules -> loadModule((int)$_object -> module_id);

			if( $module === false) continue;

			$_logicResult =	false;

			$_pPageRequest -> objectsList[$_objectIndex] -> instance	 = 	new $module -> module_controller($module, $_object);
			$_pPageRequest -> objectsList[$_objectIndex] -> instance	->	logic(
																				$this -> m_sqlConnection, 
																				$_rcaTarget,
																				$_pPageRequest -> xhRequest, 
																				$_logicResult, 
																				$_pPageRequest -> isEditMode
																				);
		}

		if( $_pPageRequest -> urlPath  === false)
			$_pPageRequest -> urlPath  = $_pPageRequest -> page_path .'/';
		else
			$_pPageRequest -> urlPath .= $_pPageRequest -> page_language .'/'. $_pPageRequest -> node_id;


		$this -> pageRequest = &$_pPageRequest;
	}

	private function
	logic_backend(&$_pPageRequest, $_modules, array $_rcaTarget)
	{
		if($_pPageRequest -> responseCode !== 200)
		{	
			return;
		}

		##	XHR call

		if($_pPageRequest -> xhRequest !== false)
		{
			$_pURLVariables	 =	new CURLVariables();
			$_request		 =	[];
			$_request[] 	 = 	[	"input" => "cms-insert-module",  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
			$_request[] 	 = 	[	"input" => "cms-order-by-modules",  "validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
			$_pURLVariables -> retrieve($_request, false, true); // POST 

			##	Insert Module

			if($_pURLVariables -> getValue("cms-insert-module") !== false)
			{	
				##	XHR Function call

				$_request[] 	 = 	[	"input" => "cms-insert-after",  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => 0 ]; 		
				$_request[] 	 = 	[	"input" => "cms-insert-node-id",  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => 0 ]; 		
				$_pURLVariables -> retrieve($_request, false, true); // POST 

				$_bValidationErr =	false;
				$_bValidationMsg =	'';
				$_bValidationDta = 	[];



				$module = $_modules -> loadModule((int)$_pURLVariables -> getValue("cms-insert-module"));

				if( $module === false)
				{
					$_bValidationErr =	true;
					$_bValidationMsg =	'Module is unknown';					
				}
				else
				{



					$_initObj	 =	[
										'page_version'		=>	'1',
										'object_id'			=>	0,
										'module_id'			=>	$_pURLVariables -> getValue("cms-insert-module"),
										'object_order_by'	=>	$_pURLVariables -> getValue("cms-insert-after"),
										'node_id'			=>	$_pURLVariables -> getValue("cms-insert-node-id"),
										'create_time'		=>	time(),
										'create_by'			=>	CSession::instance() -> getValue('user_id')
									];

					$_objectModel  = new modelPageObject();
					$_objectModel -> create($this -> m_sqlConnection, $_initObj);
					$objectData	   = $_objectModel -> getDataInstance();

					$_logicResult 	  = [];
					$_objectInstance  = new $module -> module_controller($module, $objectData);
					$_objectInstance -> logic(
												$this -> m_sqlConnection, 
												[ $objectData -> object_id => 'create' ],
												$_pPageRequest -> xhRequest, 
												$_logicResult, 
												true
												);
					
				}

				tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
			}

			##	Move Module 

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

		$_pPageRequest -> urlPath		=	$_pPageRequest -> page_path .'/';


		##	Looping objects

		foreach($_pPageRequest -> objectsList as $_objectKey =>  $_object)
		{
			
		#	$_iModuleIndex = $_modules -> isLoaded( $_object -> module_id );

			$module = $_modules -> loadModule((int)$_object -> module_id);

			if( $module === false) continue;

			##	Read additional module files (module.json and *.lang)

			switch($module -> module_type)
			{
				case 'core'  :	$_modLocation	= CMS_SERVER_ROOT . DIR_CORE . DIR_MODULES . $module -> module_location .'/';									
								break;

				case 'mantle':	$_modLocation	= CMS_SERVER_ROOT . DIR_MANTLE . DIR_MODULES . $module -> module_location .'/';
								break;
			}

			CLanguage::instance() -> loadLanguageFile($_modLocation.'lang/', $_pPageRequest -> page_language);

			##	Create object and call logic

			$_logicResult =	false;
			$_pPageRequest -> objectsList[$_objectKey] -> instance 	 = 	new $module -> module_controller($module, $_object);
			$_pPageRequest -> objectsList[$_objectKey] -> instance	->	logic($this -> m_sqlConnection, $_rcaTarget, $_pPageRequest -> xhRequest, $_logicResult, false);

			if($_logicResult !== false && $_logicResult['state'] === 1)
			{	## 	This means exit function and recall imperator public logic

				$_pPageRequest -> node_id		=	$_logicResult['node_id'];
				$_pPageRequest -> page_language	=	$_logicResult['page_language'];
				$_pPageRequest -> page_version	=	$_logicResult['page_version'];
				$_pPageRequest -> isEditMode	=	true;
				$_pPageRequest -> urlPath		=	$_pPageRequest -> page_path .'/'.$_rcaTarget[ $_pPageRequest -> objectsList[$_objectKey] -> object_id ] .'/';

				$_pPageRequest -> enablePageEdit	=	$_logicResult['enablePageEdit'];

				$_pPageRequest -> isEditMode = true;
				return false;
			}
		}
		
		##

		$_pPageRequest -> urlPath		=	$_pPageRequest -> page_path .'/';

		$this -> pageRequest = &$_pPageRequest;
	}

	public function
	view()
	{
		if($this -> pageRequest -> isEditMode)
		{
			echo '<div class="cms-edit-content-container">';

			foreach($this -> pageRequest -> objectsList as $_objectIndex =>  &$_object)
			{
				if($_object -> instance === NULL)
					continue; 

				$rightsString = json_encode($this -> m_pUserRights -> getModuleRights($_object -> module_id));
				$rightsString = str_replace('"', "", $rightsString);
				$rightsString = str_replace('[', "", $rightsString);
				$rightsString = str_replace(']', "", $rightsString);

				echo '<div class="cms-content-object" data-rights="'. $rightsString .'">';
				$_object -> instance -> view();
				echo '</div>';
			}

			echo '</div>';
		}
		else
		{
			if($this -> pageRequest  -> objectsList === NULL) return;

			foreach($this -> pageRequest  -> objectsList as $_objectIndex =>  &$_object)
			{
				if($_object -> instance === NULL)
					continue; 

				$_object -> instance -> view();
			}
		}
	}
}

?>