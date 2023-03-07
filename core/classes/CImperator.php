<?php

class	CImperator 
{
	private	$pDatabase;
	private $pUserRights;
	private $pDirector;

	public function
	__construct(?CDatabaseConnection &$_pDatabase)
	{
		$this -> pDatabase	= &$_pDatabase;
		$this -> pDirector	= new CDirector;
	}












	##	code below this point is for refactoring/revision




	public function
	logic(&$_pPageRequest, array $_rcaTarget, bool $_isBackendMode, CUserRights &$_pUserRights) : void
	{
		if($_isBackendMode)
		{
			if(!$this -> logic_backend($_pPageRequest, $_rcaTarget))
			{
				if(!$_pPageRequest -> isEditMode)
					return;		

				$pageRequest = CPageRequest::instance();

				$_pPageRequest -> init($this -> pDatabase, $_pPageRequest -> node_id, $_pPageRequest -> page_language, $_pPageRequest -> page_version);

				$_pPageRequest -> enablePageEdit = ((!empty($pageRequest -> languageInfo) && !$pageRequest -> languageInfo -> lang_locked) ? $_pPageRequest -> enablePageEdit : false);
				
				$_pUserRights -> disableEditRights(!$_pPageRequest -> enablePageEdit);

				$this -> pUserRights = $_pUserRights;
			}
		}		

		$this -> logic_public($_pPageRequest, $_rcaTarget, $_isBackendMode);
	}

	private function
	logic_public(&$_pPageRequest, array $_rcaTarget, bool $_isBackendMode)
	{
		if($_pPageRequest -> responseCode !== 200)
		{	
			return;
		}

		$xhrInfo     = $_pPageRequest->detectXHRequest();
		$requestInfo = $_pPageRequest->getShortRequestInfo();

		##	Gathering active modules data
		if(!empty($_pPageRequest -> objectsList))
		foreach($_pPageRequest -> objectsList as $_objectIndex =>  $_object)
		{	
			$_modules = CModules::instance();
			$module = $_modules -> loadModule((int)$_object -> module_id, $_pPageRequest -> page_language);

			if($module === null)
				continue;

			$_logicResult =	false;

			$_pPageRequest -> objectsList[$_objectIndex] -> instance	 = 	new $module -> module_controller($module, $_object);
			$_pPageRequest -> objectsList[$_objectIndex] -> instance	->	logic(
																				$this -> pDatabase, 
																				$_rcaTarget,
																				$xhrInfo, 
																				$_logicResult, 
																				$_pPageRequest -> isEditMode,
																				$requestInfo
																				);
		}

		if( $_isBackendMode)
			$_pPageRequest -> urlPath .= $_pPageRequest -> page_language .'/'. $_pPageRequest -> node_id;
		else
			$_pPageRequest -> urlPath .= $_pPageRequest -> url;

		$this -> pageRequest = &$_pPageRequest;
	}

	private function
	logic_backend(&$_pPageRequest, array $_rcaTarget) : bool
	{
		if($_pPageRequest -> responseCode !== 200)
		{	
			return false;
		}

		$xhrInfo     = $_pPageRequest->detectXHRequest();
		$requestInfo = $_pPageRequest->getShortRequestInfo();

		##	XHR call

		if($xhrInfo !== null)
		{
			$_pURLVariables	 =	new CURLVariables();
			$_request		 =	[];
			$_request[] 	 = 	[	"input" => "cms-insert-module",  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
			$_request[] 	 = 	[	"input" => "cms-order-by-modules",  "validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
			$_pURLVariables -> retrieve($_request, false, true); // POST 

			##	Insert Module

			if($xhrInfo -> action === 'cms-insert-module' && $_pURLVariables -> getValue("cms-insert-module") !== false)
			{
				##	XHR Function call

				$_request[] 	 = 	[	"input" => "cms-insert-after",  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => 0 ]; 		
				$_request[] 	 = 	[	"input" => "cms-insert-node-id",  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => 0 ]; 		
				$_request[] 	 = 	[	"input" => "cms-insert-content-id", "validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => 0 ]; 		
				$_pURLVariables -> retrieve($_request, false, true); // POST 

				$_bValidationErr =	false;
				$_bValidationMsg =	'';
				$_bValidationDta = 	[];



				$_modules = CModules::instance();
				$module = $_modules -> loadModule((int)$_pURLVariables -> getValue("cms-insert-module"), $_pPageRequest -> page_language);

				if( $module === null)
				{
					$_bValidationErr =	true;
					$_bValidationMsg =	'Module is unknown';					
				}
				else
				{



					$_initObj	 =	[
										'page_version'		=>	'1',
										'module_id'			=>	$_pURLVariables -> getValue("cms-insert-module"),
										'content_id'		=>	$_pURLVariables -> getValue("cms-insert-content-id"),
										'object_order_by'	=>	$_pURLVariables -> getValue("cms-insert-after"),
										'node_id'			=>	$_pURLVariables -> getValue("cms-insert-node-id"),
										'create_time'		=>	time(),
										'create_by'			=>	CSession::instance() -> getValue('user_id')
									];
			


					$pageObject = modelPageObject::new($_initObj, $this->pDatabase);
					$pageObject->save();

					/*
					$_objectModel  = new model_PageObject();
					$_initObj['object_id'] = $_objectModel -> insert($this -> pDatabase, $_initObj);
					*/
					$_initObj['object_id'] = $pageObject->object_id;


					$objectData = new stdClass();
					foreach ($_initObj as $key => $value)
					{
						$objectData -> $key = $value;
					}


					$xhrInfo -> objectId = $_initObj['object_id'];


					// insert module gets initObj as requestInfo to deliver the frontent page node_id, but it contains not alle data
					// like requestInfo .. its todo

					$_logicResult 	  = [];
					$_objectInstance  = new $module -> module_controller($module, $objectData);
					$_objectInstance -> logic(
												$this -> pDatabase, 
												[ $objectData -> object_id => 'create' ],
												$xhrInfo, 
												$_logicResult, 
												true,
												(object)$_initObj
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
				#$_objectModel  	= new model_PageObject();

				foreach($_pURLVariables -> getValue("cms-order-by-modules") as $_objectIndex =>  $_objectID)
				{
					/*
					$_updateSet['object_order_by']	= ($_objectIndex + 1);

					$modelCondition = new CModelCondition();
					$modelCondition -> where('node_id', $_pageNodeID);
					$modelCondition -> where('object_id', $_objectID);
					
					$_objectModel -> update($this -> pDatabase, $_updateSet, $modelCondition);		
					*/



					$_objectModel = modelPageObject::
						  db($this->pDatabase)
						->where('node_id', '=', $_pageNodeID)
						->where('object_id', '=', $_objectID)
						->one();

					$_objectModel->object_order_by = ($_objectIndex + 1);
					$_objectModel->save();


				}

				tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
			}

		}

		$_pPageRequest -> urlPath		=	$_pPageRequest -> page_path .'';

		##	Looping objects

		foreach($_pPageRequest -> objectsList as $_objectKey =>  $_object)
		{
			

			$_modules = CModules::instance();
			$module = $_modules -> loadModule((int)$_object -> module_id, $_pPageRequest -> page_language);

			if( $module === null) continue;


			if($xhrInfo !== null && $xhrInfo -> isXHR)
				$xhrInfo -> objectId = $_object -> object_id;


			##	Create object and call logic

			$_logicResult =	false;
			$_pPageRequest -> objectsList[$_objectKey] -> instance 	 = 	new $module -> module_controller($module, $_object, true);
			$_pPageRequest -> objectsList[$_objectKey] -> instance	->	logic(
				$this -> pDatabase, 
				$_rcaTarget, 
				$xhrInfo,
				$_logicResult, 
				false,
				$_pPageRequest
				);

			if($_logicResult !== false && $_logicResult['state'] === 1)
			{	## 	This means exit function and recall imperator public logic

				$_pPageRequest -> node_id		=	$_logicResult['node_id'];
				$_pPageRequest -> page_language	=	$_logicResult['page_language'];
				$_pPageRequest -> page_version	=	$_logicResult['page_version'];
				$_pPageRequest -> isEditMode	=	true;
				$_pPageRequest -> urlPath		=	$_pPageRequest -> page_path .''.$_rcaTarget[ $_pPageRequest -> objectsList[$_objectKey] -> object_id ] .'/';

				$_pPageRequest -> enablePageEdit	=	$_logicResult['enablePageEdit'];

				$_pPageRequest -> isEditMode = true;
				return false;
			}
		}
		
		##

		$_pPageRequest -> urlPath		=	$_pPageRequest -> page_path .'';

		$this -> pageRequest = &$_pPageRequest;

		return false;
	}

	public function
	view(string $_viewId = '') : void
	{
		$_viewId = $this -> pDirector -> register($_viewId);
		$this -> pDirector -> view($_viewId, $this -> pageRequest, $this -> pUserRights);
	}
}
