<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSimple.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSitemap.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CModulesTemplates.php';	

class controllerSimpleNavigation extends cmsControllerSimple
{
	private string $defaultTemplateName;

	public function
	__construct(object $_moduleInfo, object &$_objectInfo)
	{
		parent::__construct($_moduleInfo, $_objectInfo);

		##	Set user default right in this module

		$this->setRightOfPublicAccess('view');	

		##	Default template

		$this->defaultTemplateName = 'list';	
	}

	public function
	logic(CDatabaseConnection &$_pDatabase, array $_rcaTarget, ?object $_xhrInfo, &$_logicResult, bool $_pageEditMode, object $requestInfo) : bool
	{
		##	Get action by request term, can return actions that not listed in module.json

		$action = $this -> getAction($_rcaTarget, $_xhrInfo, $_pageEditMode);

		##	Validate action with user right, xhr request will end in this function

		if(!$this -> validateRight($action, $_xhrInfo))
			return false;
		
		##	If the user does not have the right, he will not reach this point of process
		##	Public user needs the RightOfPublicAccess call to get here

		## 	Call Logic function, if there goes something wrong, the default view get called (except on xhr calls)

		$logicDone = false;

		if($_xhrInfo === null) // NON XHR
		switch($action)
		{
			case 'edit'		: $logicDone = $this -> logicEdit($_pDatabase); 				break; // cmsControllerSimple::logicEdit
		}

		if($_xhrInfo !== null && $_xhrInfo -> objectId === $this -> objectInfo -> object_id) // XHR
		switch($action)
		{
			case 'create' 	: $logicDone = $this -> logicInsert($_pDatabase, $_xhrInfo, $requestInfo); 	break;
			case 'edit'	    :
			case 'update' 	: $logicDone = $this -> logicUpdate($_pDatabase, $_xhrInfo); 	break;
			case 'delete' 	: $logicDone = $this -> logicDelete($_pDatabase, $_xhrInfo); 	break;	
		}

		if(!$logicDone) // Default
			$logicDone = $this -> logicView($_pDatabase); // cmsControllerSimple::logicView
	
		return false;
	}

	/**
	 * 	Overloaded parent ::logicView
	 */
	public function logicView(CDatabaseConnection &$_pDatabase) : bool
	{
		$simpleObject = modelSimple::db($_pDatabase)->where('object_id', '=', $this -> objectInfo -> object_id)->one();

		##	gathering child nodes

		if(empty($simpleObject -> params -> nodeList))
			$simpleObject -> params -> nodeList = [];

		$simpleObject -> params -> nodeList = (array)$simpleObject -> params -> nodeList;
		
		$this->setViewSimple($_pDatabase, 'view', $simpleObject);

		return true;
	}

	/**
	 * 	Overloaded parent ::logicEdit
	 */
	public function logicEdit(CDatabaseConnection &$_pDatabase) : bool
	{
		$simpleObject = modelSimple::db($_pDatabase)->where('object_id', '=', $this -> objectInfo -> object_id)->one();
			
		if(empty($simpleObject -> params -> nodeList))
			$simpleObject -> params -> nodeList = [];

		$simpleObject -> params -> nodeList = (array)$simpleObject -> params -> nodeList;

		$this->setViewSimple($_pDatabase, 'edit', $simpleObject);

		return true;
	}

	/**
	 * 	XHR process function to update object data
	 */
	public function logicUpdate(CDatabaseConnection &$_pDatabase, object $_xhrInfo)
	{
		#$queryValidationString = QueryValidation::STRIP_TAGS | QueryValidation::IS_NOTEMPTY;
		$queryValidationString = QueryValidation::IS_NOTEMPTY;

		##	Body

		$sOBody = '';
	
		##	Parameters

		$requestQuery = new cmsRequestQuery(true);
		$requestQuery->post('simple-navigation-template')->validate($queryValidationString)->default($this->defaultTemplateName)->out('template')->exec();
		$requestQuery->post('simple-navigation-item')->validate($queryValidationString)->default([])->out('nodeList')->exec();
		$sOParams = $requestQuery->toObject();

		if($simpleObject = $this->logicUpdateExec(
			$_pDatabase, 
			$_xhrInfo, 
			$sOBody, 
			$sOParams,
			cmsControllerSimple::PREVENT_XHRRESPONSE
			)
		) {
			$this->setViewSimple($_pDatabase, 'view', $simpleObject);

			tk::xhrResponse(
				200,
				[
					'objectId' 	=> $this -> objectInfo -> object_id,
					'html' 		=> $this -> m_pView -> getHTML(),
				]);	
		}

		tk::xhrResponse(
			200,
			[],
			1, 
			'Unknown error on sql query'
			);	
	}

	/**
	 * 	XHR process function to delete the object
	 */
	public function logicDelete(CDatabaseConnection &$_pDatabase, object $_xhrInfo)
	{
		return $this->logicDeleteExec(
			$_pDatabase, 
			$_xhrInfo
			);
	}

	/**
	 * 	XHR process function to insert the object
	 */
	public function logicInsert(CDatabaseConnection &$_pDatabase, object $_xhrInfo, object $requestInfo)
	{
		$sOBody    = '';
		$sOParams  = new stdClass;
		$sOParams -> template = $this->defaultTemplateName;
		$sOParams -> nodeList = [];
		/* until the problem is fixed
		$sOParams -> nodeList = [ (object)[
			'listing-type' => 'subpages',
			'listing-hidden' => 0,
			'node-id' => $requestInfo->node_id,
		]];
		*/

		$responseData = [];
		
		$simpleObject = modelSimple::new([
			'object_id' => (int)$this -> objectInfo -> object_id,
			'body' 		=> $sOBody,
			'params' 	=> $sOParams,
		], $_pDatabase);

		if(!$simpleObject->save())
		{
			tk::xhrResponse(
				200,
				[],
				1, 
				'sql insert failed'
				);	
		}
		else
		{
			$this->setViewSimple($_pDatabase, 'edit', $simpleObject);

			$responseData['html'] = $this -> m_pView -> getHTML();
		}

		tk::xhrResponse(
			200,
			$responseData,
			0, 
			'OK'
			);	
		
		return false;
	}

	protected function setViewSimple(CDatabaseConnection &$_pDatabase, string $view, object $simpleObject)
	{
		$dataInstances = [];
		
		switch($view)
		{
			case 'edit':

				$moduleTemplates = new CModulesTemplates();
				$moduleTemplates ->	load($this->moduleInfo->modules_path, $this->moduleInfo->module_location);

				$dataInstances['avaiableTemplates']	= $moduleTemplates -> templatesList;

			case 'view':
			default:

				$moduleTemplate = new CModulesTemplates();
				$moduleTemplate ->	load($this->moduleInfo->modules_path, $this->moduleInfo->module_location, $simpleObject -> params -> template);

				$dataInstances['object'] 			= $simpleObject;
				$dataInstances['currentTemplate'] 	= $moduleTemplate -> templatesList;
				$dataInstances['nodeList'] 			= $this -> processNavigationItems($_pDatabase, $simpleObject -> params -> nodeList);

				break;			
		}

		$this -> setView(	
						$view,	
						'',
						$dataInstances
						);
	}

	private function
	processNavigationItems(CDatabaseConnection &$_pDatabase, &$itemsList) : array
	{
		$nodeList = [];
		foreach($itemsList as $nodeIndex => $node)
		{
			if(is_array($node)) 
			{
				$node = (object)$node;
				$itemsList[$nodeIndex] = (object)$itemsList[$nodeIndex];
			}

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
}
