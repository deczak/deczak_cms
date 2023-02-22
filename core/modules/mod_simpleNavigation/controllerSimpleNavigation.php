<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSimple.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSitemap.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CModulesTemplates.php';	

class	controllerSimpleNavigation extends CController
{		
	public function
	__construct(object $_module, object &$_object)
	{
		parent::__construct($_module, $_object);
		$this -> moduleInfo -> user_rights[] = 'view';	// add view right as default for everyone

		switch($this -> moduleInfo -> module_type) 
		{
			case 'core':	
				$this -> moduleRootDir = CMS_SERVER_ROOT.DIR_CORE.DIR_MODULES;
				break;
							
			case 'mantle':
				$this -> moduleRootDir = CMS_SERVER_ROOT.DIR_MANTLE.DIR_MODULES;
				break;
		}
	}
	
	public function
	logic(CDatabaseConnection &$_pDatabase, array $_rcaTarget, ?object $_xhrInfo, &$_logicResult, bool $_bEditMode) : bool
	{
		##	Set default target if not exists

		$controllerAction = $this -> getControllerAction_v2($_rcaTarget, $_xhrInfo, 'view');

		##	Check user rights for this target
		
		if(!$this -> detectRights($controllerAction))
		{
			if($_xhrInfo !== null && $_xhrInfo -> isXHR)
			{
				$validationErr =	true;
				$validationMsg =	CLanguage::string('ERR_PERMISSON');
				$responseData = 	[];

				tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
			}

			CMessages::add(CLanguage::string('ERR_PERMISSON') , MSG_WARNING);
			return false;
		}

		if($_bEditMode && $_xhrInfo === null) 
			$controllerAction = 'edit';

		if($_xhrInfo !== null && $_xhrInfo -> isXHR && $_xhrInfo -> objectId === $this -> objectInfo -> object_id)
			$controllerAction = 'xhr_'. $_xhrInfo -> action;

		##	Call sub-logic function by target, if there results are false, we make a fall back to default view

		$enableEdit 	= $this -> existsUserRight('edit');
		$enableDelete	= $enableEdit;

		$logicDone = false;
		switch($controllerAction)
		{
			case 'edit' 	  : $logicDone = $this -> logicEdit($_pDatabase, $enableEdit, $enableDelete); break;	
			case 'xhr_create' : $logicDone = $this -> logicXHRCreate($_pDatabase, $_xhrInfo, $enableEdit, $enableDelete); break;	
			case 'xhr_edit'   : $logicDone = $this -> logicXHREdit($_pDatabase, $_xhrInfo, $enableEdit, $enableDelete); break;	
			case 'xhr_delete' : $logicDone = $this -> logicXHRDelete($_pDatabase, $_xhrInfo, $enableEdit, $enableDelete); break;	
			case 'xhr_view'   : $logicDone = $this -> logicXHRView($_pDatabase, $_xhrInfo, $enableEdit, $enableDelete); break;	
		}

		if(!$logicDone)
			$logicDone = $this -> logicView($_pDatabase, $enableEdit, $enableDelete);	

		return $logicDone;
	}

	private function
	logicView(CDatabaseConnection &$_pDatabase, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{

		$simpleObject = modelSimple::where('object_id', '=', $this -> objectInfo -> object_id)->one();




		##	gathering child nodes

		$parentNode = (empty($simpleObject -> params -> parent_node_id) ? $this -> objectInfo -> node_id : $simpleObject -> params -> parent_node_id);

		$moduleTemplate	 = new CModulesTemplates();
		$moduleTemplate	-> load($this -> moduleRootDir, $this->moduleInfo->module_location, $simpleObject -> params -> template);

		if(empty($simpleObject -> params -> nodeList))
			$simpleObject -> params -> nodeList = [];

		$simpleObject -> params -> nodeList = (array)$simpleObject -> params -> nodeList;

		$this -> setView(	
						'view',	
						'',
						[
							'object' 		  => $simpleObject,
							'sitemap'		  => null,
							'currentTemplate' => $moduleTemplate -> templatesList,
							'nodeList'		  => $this -> processNavigationItems($_pDatabase, $simpleObject -> params -> nodeList),
						]
						);

		return true;
	}

	private function
	logicXHRView(CDatabaseConnection &$_pDatabase, object $_xhrInfo, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{
		$validationErr   = false;
		$validationMsg   = 'OK';
		$responseData    = [];

		$this->logicView($_pDatabase, $_enableEdit, $_enableDelete);

		ob_start();
		$this->view();
		$responseData['html'] = ob_get_contents();
		ob_end_clean();

		$responseData['objectId'] = $_xhrInfo -> objectId;

		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
	
		return true;
	}

	private function
	logicEdit(CDatabaseConnection &$_pDatabase, bool $enableEdit, bool $enableDelete) : bool
	{


		$simpleObject = modelSimple::where('object_id', '=', $this -> objectInfo -> object_id)->one();





		##	gathering child nodes

		$parentNode = (empty($simpleObject -> params -> parent_node_id) ? $this -> objectInfo -> node_id : $simpleObject -> params -> parent_node_id);

		$moduleTemplate = new CModulesTemplates();
		$moduleTemplate ->	load($this -> moduleRootDir, $this->moduleInfo->module_location, $simpleObject -> params -> template);

		$moduleTemplates = new CModulesTemplates();
		$moduleTemplates ->	load($this -> moduleRootDir, $this->moduleInfo->module_location);






		if(empty($simpleObject -> params -> nodeList))
			$simpleObject -> params -> nodeList = [];

		$simpleObject -> params -> nodeList = (array)$simpleObject -> params -> nodeList;





		$this -> setView(	
						'edit',	
						'',
						[
							'object' 			=> $simpleObject,
							'sitemap'			=> null,
							'currentTemplate'	=> $moduleTemplate -> templatesList,
							'avaiableTemplates'	=> $moduleTemplates -> templatesList,
							'nodeList'			=> $this -> processNavigationItems($_pDatabase, $simpleObject -> params -> nodeList),
						]
						);

		return true;
	}

	private function
	processNavigationItems(CDatabaseConnection &$_pDatabase, &$itemsList) : array
	{
		
		$nodeList = [];
		foreach($itemsList as $nodeIndex => $node)
		{

					$modelCondition = new CModelCondition();
					$modelCondition -> where('node_id', $node -> {'node-id'});		

					$modelSitemap  = new modelSitemap();
					$modelSitemap -> load($_pDatabase, $modelCondition, NULL, SITEMAP_OWN_CHILDS_ONLY);	


			switch($node -> {'listing-type'})
			{
				case 'page':



					if(!empty($modelSitemap -> getResult()))
					{
						$sitemapNode = reset($modelSitemap -> getResult());

						$itemsList[$nodeIndex] -> page_name = $sitemapNode -> page_name;

						$sitemapNode -> listing_hidden = $node -> {'listing-hidden'};
						$sitemapNode -> listing_type = $node -> {'listing-type'};


						$nodeList[] = [$sitemapNode];

					}

					break;

				case 'subpages':


					foreach($modelSitemap -> getResult() as &$sitemapNode)
					{

						if((int)$sitemapNode -> node_id === (int)$node -> {'node-id'})
						{
							$itemsList[$nodeIndex] -> page_name = $sitemapNode -> page_name;
						
						}

						$sitemapNode -> listing_hidden = $node -> {'listing-hidden'};
						$sitemapNode -> listing_type = $node -> {'listing-type'};
					}



					$nodeList[] = $modelSitemap -> getResult();
					if(isset($sitemapNode))
						unset($sitemapNode);

					break;
			}

		}
		return $nodeList;
	}

	private function
	logicXHREdit(CDatabaseConnection &$_pDatabase, object $_xhrInfo, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{
		$validationErr   = false;
		$validationMsg   = 'OK';
		$responseData    = [];

		$pURLVariables	 =	new CURLVariables();
		$requestList	 =	[];
		$requestList[] 	 = 	[	"input" => "simple-navigation-template",  		"validate" => "strip_tags|!empty" ]; 
		$requestList[] 	 = 	[	"input" => "simple-navigation-item",  			"validate" => "!empty",	"use_default" => true, "default_value" => [] ]; 
		//	$requestList[] 	 = 	[	"input" => "navigation-display-hidden", 	"validate" => "strip_tags|!empty" ]; 
		//	$requestList[] 	 = 	[	"input" => "navigation-parent-node-id", 	"validate" => "strip_tags|!empty" ]; 

		$pURLVariables-> retrieve($requestList, false, true); // POST 
		$urlVarList		 = $pURLVariables -> getArray();

		if(empty($_xhrInfo -> objectId)) 		{ 	$validationErr = true; 	$responseData[] = 'cms-object-id'; 			}

		if(!$validationErr)
		{

			$urlVarList['params']	= 	[
											"template"			=> $urlVarList['simple-navigation-template'],
											"nodeList"			=> $urlVarList['simple-navigation-item'],
		//										"display_hidden"	=> $urlVarList['navigation-display-hidden'],
		//										"parent_node_id"	=> $urlVarList['navigation-parent-node-id']
										];
			$urlVarList['params']	 = 	json_encode($urlVarList['params'], JSON_FORCE_OBJECT);

			$simpleObject = modelSimple::where('object_id', '=', $_xhrInfo -> objectId)->one();
			$simpleObject->params->template = $urlVarList['simple-navigation-template'];
			$simpleObject->params->nodeList = $urlVarList['simple-navigation-item'];

		
			if($simpleObject->save())
			{
				$validationMsg = 'Object updated';

				$object = modelPageObject::
					  db($_pDatabase)
					->where('object_id', '=', $_xhrInfo -> objectId)
					->one();

				$object->update_time 	= time();
				$object->update_by 		= 0;
				$object->update_reason	= '';
				$object->save();

				$this->logicXHRView($_pDatabase, $_xhrInfo, $_enableEdit, $_enableDelete);
			}
			else
			{
				$validationMsg .= 'Unknown error on sql query';
				$validationErr = true;
			}											
		}
		else	// Validation Failed
		{
			$validationMsg .= 'Data validation failed - object was not updated';
			$validationErr = true;
		}

		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
	
		return true;
	}
	
	private function
	logicXHRCreate(CDatabaseConnection &$_pDatabase, object $_xhrInfo, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{
		$validationErr =	false;
		$validationMsg =	'';
		$responseData = 	[];

			$sOParams = new stdClass;
			$sOParams->template 		= '';
			$sOParams->display_hidden 	= ''; 
			$sOParams->parent_node_id 	= '';



			$simpleObject = modelSimple::new([
				'object_id' => (int)$this -> objectInfo -> object_id,
				'body' 		=> '',
				'params' 	=> $sOParams,
			], $_pDatabase);
			



		if(!$simpleObject->save())
		{
			$validationErr =	true;
			$validationMsg =	'sql insert failed';
		}
		else
		{

			##	gathering child nodes


			$modelCondition = new CModelCondition();
			$modelCondition -> where('node_id', $this -> objectInfo -> node_id);		

			$modelSitemap = new modelSitemap();
			$modelSitemap -> load($_pDatabase, $modelCondition, NULL, SITEMAP_OWN_CHILDS_ONLY);	



			$moduleTemplate = new CModulesTemplates();
			$moduleTemplate ->	load($this -> moduleRootDir, $this->moduleInfo->module_location, $simpleObject -> params -> template);

			$moduleTemplates = new CModulesTemplates();
			$moduleTemplates ->	load($this -> moduleRootDir, $this->moduleInfo->module_location);

			$this -> setView(	
							'edit',	
							'',
							[
								'object' 	=> $simpleObject,
								'sitemap'	=> null,
						'currentTemplate'	=> $moduleTemplate -> templatesList,
						'avaiableTemplates'	=> $moduleTemplates -> templatesList,
						'nodeList'			=> [],
							]
							);


			$pageRequest = new stdClass;
			$pageRequest -> crumbsList = $modelSitemap -> getResult();

			$responseData['html'] = $this -> m_pView -> getHTML($pageRequest);
		}

		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call

		return false;
	}
	
	private function
	logicXHRDelete(CDatabaseConnection &$_pDatabase, object $_xhrInfo, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{
		$validationErr	= false;
		$validationMsg	= 'OK';
		$responseData	= [];
	
		if(empty($_xhrInfo -> objectId))
		{ 	
			$validationErr	= true; 	
			$responseData[] = 'cms-object-id'; 			
		}

		if(!$validationErr)
		{


			modelPageObject::
				  db($_pDatabase)
				->where('object_id', '=', $_xhrInfo -> objectId)
				->delete();

				$validationMsg = 'Object deleted';
										
		}
		else	// Validation Failed
		{
			$validationMsg = 'Data validation failed - object was not updated';
			$validationErr = true;
		}

		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call

		return false;
	}
}