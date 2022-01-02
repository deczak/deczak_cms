<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSimple.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSitemap.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CModulesTemplates.php';	

class	controllerSimpleNavigation extends CController
{
	private	$m_modelSimple;
		
	public function
	__construct(object $_module, object &$_object)
	{
		parent::__construct($_module, $_object);

		$this -> m_modelSimple = new modelSimple();
		
		$this -> moduleInfo -> user_rights[] = 'view';	// add view right as default for everyone
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
		}

		if(!$logicDone)
			$logicDone = $this -> logicView($_pDatabase, $enableEdit, $enableDelete);	

		return $logicDone;
	}

	private function
	logicView(CDatabaseConnection &$_pDatabase, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{
		$modelCondition = new CModelCondition();
		$modelCondition -> where('object_id', $this -> objectInfo -> object_id);

		$this -> m_modelSimple -> load($_pDatabase, $modelCondition);

		$this -> m_modelSimple -> getResult()[0] -> params = json_decode($this -> m_modelSimple -> getResult()[0] -> params);

		##	gathering child nodes

		$parentNode = (empty($this -> m_modelSimple -> getResult()[0] -> params -> parent_node_id) ? $this -> objectInfo -> node_id : $this -> m_modelSimple -> getResult()[0] -> params -> parent_node_id);

/*
		$modelCondition  = new CModelCondition();
		$modelCondition -> where('node_id', $parentNode);		

		$modelSitemap    = new modelSitemap();
		$modelSitemap 	-> load($_pDatabase, $modelCondition, NULL, SITEMAP_OWN_CHILDS_ONLY);	
*/
		$moduleTemplate	 = new CModulesTemplates();
		$moduleTemplate	-> load('simpleNavigation', $this -> m_modelSimple -> getResult()[0] -> params -> template);




		if(empty($this -> m_modelSimple -> getResult()[0] -> params -> nodeList))
			$this -> m_modelSimple -> getResult()[0] -> params -> nodeList = [];

		$this -> m_modelSimple -> getResult()[0] -> params -> nodeList = (array)$this -> m_modelSimple -> getResult()[0] -> params -> nodeList;







		$this -> setView(	
						'view',	
						'',
						[
							'object' 		  => $this -> m_modelSimple -> getResult()[0],
							'sitemap'		  => null,
							'currentTemplate' => $moduleTemplate -> templatesList,
							'nodeList'		  => $this -> processNavigationItems($_pDatabase, $this -> m_modelSimple -> getResult()[0] -> params -> nodeList),
						]
						);

		return true;
	}

	private function
	logicEdit(CDatabaseConnection &$_pDatabase, bool $enableEdit, bool $enableDelete) : bool
	{


		$modelCondition = new CModelCondition();
		$modelCondition -> where('object_id', $this -> objectInfo -> object_id);

		$this -> m_modelSimple -> load($_pDatabase, $modelCondition);

		$this -> m_modelSimple -> getResult()[0] -> params = json_decode($this -> m_modelSimple -> getResult()[0] -> params);

		##	gathering child nodes

		$parentNode = (empty($this -> m_modelSimple -> getResult()[0] -> params -> parent_node_id) ? $this -> objectInfo -> node_id : $this -> m_modelSimple -> getResult()[0] -> params -> parent_node_id);

		$moduleTemplate = new CModulesTemplates();
		$moduleTemplate ->	load('simpleNavigation', $this -> m_modelSimple -> getResult()[0] -> params -> template);

		$moduleTemplates = new CModulesTemplates();
		$moduleTemplates ->	load('simpleNavigation');






		if(empty($this -> m_modelSimple -> getResult()[0] -> params -> nodeList))
			$this -> m_modelSimple -> getResult()[0] -> params -> nodeList = [];

		$this -> m_modelSimple -> getResult()[0] -> params -> nodeList = (array)$this -> m_modelSimple -> getResult()[0] -> params -> nodeList;










		$this -> setView(	
						'edit',	
						'',
						[
							'object' 			=> $this -> m_modelSimple -> getResult()[0],
							'sitemap'			=> null,
							'currentTemplate'	=> $moduleTemplate -> templatesList,
							'avaiableTemplates'	=> $moduleTemplates -> templatesList,
							'nodeList'			=> $this -> processNavigationItems($_pDatabase, $this -> m_modelSimple -> getResult()[0] -> params -> nodeList),
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
			$modelCondition = new CModelCondition();
			$modelCondition -> where('object_id', $_xhrInfo -> objectId);

			$urlVarList['params']	= 	[
											"template"			=> $urlVarList['simple-navigation-template'],
											"nodeList"			=> $urlVarList['simple-navigation-item'],
	//										"display_hidden"	=> $urlVarList['navigation-display-hidden'],
	//										"parent_node_id"	=> $urlVarList['navigation-parent-node-id']
										];
			$urlVarList['params']	 = 	json_encode($urlVarList['params'], JSON_FORCE_OBJECT);

			$objectId = $_xhrInfo -> objectId;

			if($this -> m_modelSimple -> update($_pDatabase, $urlVarList, $modelCondition))
			{
				$validationMsg = 'Object updated';

				$this -> m_modelPageObject = new modelPageObject();

				$_objectUpdate['update_time']		=	time();
				$_objectUpdate['update_by']			=	0;
				$_objectUpdate['update_reason']		=	'';

				$this -> m_modelPageObject -> update($_pDatabase, $_objectUpdate, $modelCondition);
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

		$_dataset['object_id'] 	= 	$this -> objectInfo -> object_id;

		$_dataset['body'] 		= 	'';

		$_dataset['params']		= 	[
										"template"			=> '',
										"display_hidden"	=> '',
										"parent_node_id"	=> ''
									];
		$_dataset['params']	 	= 	json_encode($_dataset['params'], JSON_FORCE_OBJECT);

		if(!$this -> m_modelSimple -> insert($_pDatabase, $_dataset, MODEL_RESULT_APPEND_DTAOBJECT))
		{
			$validationErr =	true;
			$validationMsg =	'sql insert failed';
		}
		else
		{
			$this -> m_modelSimple -> getResult()[0] -> params = json_decode($this -> m_modelSimple -> getResult()[0] -> params);

			##	gathering child nodes


			$modelCondition = new CModelCondition();
			$modelCondition -> where('node_id', $this -> objectInfo -> node_id);		

			$modelSitemap = new modelSitemap();
			$modelSitemap -> load($_pDatabase, $modelCondition, NULL, SITEMAP_OWN_CHILDS_ONLY);	



			$moduleTemplate = new CModulesTemplates();
			$moduleTemplate ->	load('simpleNavigation', $this -> m_modelSimple -> getResult()[0] -> params -> template);

			$moduleTemplates = new CModulesTemplates();
			$moduleTemplates ->	load('simpleNavigation');

			$this -> setView(	
							'edit',	
							'',
							[
								'object' 	=> $this -> m_modelSimple -> getResult()[0],
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
			$modelCondition = new CModelCondition();
			$modelCondition -> where('object_id', $_xhrInfo -> objectId);

			if($this -> m_modelSimple -> delete($_pDatabase, $modelCondition))
			{
				$modelPageObject  = new modelPageObject();
				$modelPageObject -> delete($_pDatabase, $modelCondition);

				$validationMsg = 'Object deleted';
			}
			else
			{
				$validationMsg = 'Unknown error on sql query';
				$validationErr = true;
			}											
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