<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSimple.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelCategories.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelCategoriesAllocation.php';	

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CModulesTemplates.php';	


class controllerCategoryCloud extends cmsControllerSimple
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
			case 'create' 	: $logicDone = $this -> logicInsert($_pDatabase, $_xhrInfo, $requestInfo); 	break; // page object should exists at this point
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
		
		$moduleTemplate	 = new CModulesTemplates();
		$moduleTemplate	-> load($this->moduleInfo->modules_path, $this->moduleInfo->module_location, $simpleObject -> params -> template ?? $this->defaultTemplateName);


		##	get node list
		$parentNode = $simpleObject -> params -> parent_node_id;
		$parentNode = (empty($parentNode) ? $this -> objectInfo -> node_id : $simpleObject ->  params -> parent_node_id);

		$modelCondition = new CModelCondition();
		$modelCondition -> where('node_id', $parentNode);		
		$modelSitemap  = new modelSitemap();
		$modelSitemap -> load($_pDatabase, $modelCondition);	

		$collectedNodeIds = [];
		foreach($modelSitemap -> getResult() as $node)
			$collectedNodeIds[] = $node -> node_id;


		##	get tag allocations
		$condTagsAllocation = new CModelCondition();
		$condTagsAllocation -> whereIn('node_id', implode(',', $collectedNodeIds));	
		$condTagsAllocation -> groupBy('category_id');

		$modelCategoriesAllocation = new modelCategoriesAllocation();
		$modelCategoriesAllocation -> load($_pDatabase, $condTagsAllocation);

		$collectedTagIds = [];
		foreach($modelCategoriesAllocation -> getResult() as $tagAlloc)
			$collectedTagIds[] = $tagAlloc -> category_id;

		if(!empty($collectedTagIds))
		{
			##	get tag list
			$condTags = new CModelCondition();
			$condTags -> whereIn('category_id', implode(',', $collectedTagIds));	
			$condTags -> groupBy('category_id');

			$m_modelCategories	= new modelCategories();
			$m_modelCategories -> load($_pDatabase, $condTags);
			$tagList = $m_modelCategories -> getResult();
		}

		##	get parent node
		$parentNode= tk::getNodeFromSitemap($modelSitemap -> getResult(), $parentNode);

		$this -> setView(	
						'view',	
						'',
						[
							'object' 	=> $simpleObject,
							'termList' 			=> $tagList ?? [],
							'parentNode' 		=> $parentNode,
							'currentTemplate' => $moduleTemplate -> templatesList,
						]
						);

		return true;
	}

	/**
	 * 	Overloaded parent ::logicEdit
	 */
	public function logicEdit(CDatabaseConnection &$_pDatabase) : bool
	{
		$simpleObject = modelSimple::db($_pDatabase)->where('object_id', '=', $this -> objectInfo -> object_id)->one();
			
		$moduleTemplate = new CModulesTemplates();
		$moduleTemplate ->	load($this->moduleInfo->modules_path, $this->moduleInfo->module_location, $simpleObject -> params -> template ?? 'thumbnails-ratio');

		$moduleTemplates = new CModulesTemplates();
		$moduleTemplates ->	load($this->moduleInfo->modules_path, $this->moduleInfo->module_location);

		##	get node list
		$parentNode = $simpleObject -> params -> parent_node_id ?? 0;
		$parentNode = (empty($parentNode) ? $this -> objectInfo -> node_id : $simpleObject ->  params -> parent_node_id);

		$modelCondition = new CModelCondition();
		$modelCondition -> where('node_id', $parentNode);		
		$modelSitemap  = new modelSitemap();
		$modelSitemap -> load($_pDatabase, $modelCondition);	

		$collectedNodeIds = [];
		foreach($modelSitemap -> getResult() as $node)
			$collectedNodeIds[] = $node -> node_id;

		##	get tag allocations
		$condTagsAllocation = new CModelCondition();
		$condTagsAllocation -> whereIn('node_id', implode(',', $collectedNodeIds));	
		$condTagsAllocation -> groupBy('category_id');

		$modelCategoriesAllocation = new modelCategoriesAllocation();
		$modelCategoriesAllocation -> load($_pDatabase, $condTagsAllocation);

		$collectedTagIds = [];
		if(is_array($modelCategoriesAllocation -> getResult()))
		foreach($modelCategoriesAllocation -> getResult() as $tagAlloc)
			$collectedTagIds[] = $tagAlloc -> category_id;
	

		if(!empty($collectedTagIds))
		{
			##	get tag list
			$condTags = new CModelCondition();
			$condTags -> whereIn('category_id', implode(',', $collectedTagIds));	
			$condTags -> groupBy('category_id');

			$m_modelCategories	= new modelCategories();
			$m_modelCategories -> load($_pDatabase, $condTags);
			$tagList = $m_modelCategories -> getResult();
		}

		##	get parent node
		$parentNode = tk::getNodeFromSitemap($modelSitemap -> getResult(), $parentNode);




		$this -> setView(	
						'edit',	
						'',
						[
							'object' 	=> $simpleObject,
							'termList' 			=> $tagList ?? [],
							'parentNode' 		=> $parentNode,
							'currentTemplate'	=> $moduleTemplate -> templatesList,
							'avaiableTemplates'	=> $moduleTemplates -> templatesList,
						]
						);

		return true;
	}

	/**
	 * 	XHR process function to update object data
	 */
	public function logicUpdate(CDatabaseConnection &$_pDatabase, object $_xhrInfo)
	{
		$queryValidationString = QueryValidation::IS_NOTEMPTY;

		##	Body

		$sOBody = '';
	
		##	Parameters

		$requestQuery = new cmsRequestQuery(true);
		$requestQuery->post('categorycloud-template')->validate($queryValidationString)->default($this->defaultTemplateName)->out('template')->exec();
		$requestQuery->post('categorycloud-parent-node-id')->validate($queryValidationString)->default(0)->out('parent_node_id')->exec();
		$sOParams = $requestQuery->toObject();

		return $this->logicUpdateExec(
			$_pDatabase, 
			$_xhrInfo, 
			$sOBody, 
			$sOParams
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
		$sOParams -> parent_node_id = $requestInfo->node_id;

		$responseData = [];
		
		$simpleObject = modelSimple::new([
			'object_id' => (int)$this -> objectInfo -> object_id,
			'body' 		=> $sOBody,
			'params' 	=> $sOParams,
		], $_pDatabase);

		if(!$simpleObject->save())
		{
			tk::xhrResult(
				1, 
				'sql insert failed', 
				[]
				);	
		}
		else
		{
			$moduleTemplate = new CModulesTemplates();
			$moduleTemplate ->	load($this->moduleInfo->modules_path, $this->moduleInfo->module_location, $simpleObject -> params -> template);

			$moduleTemplates = new CModulesTemplates();
			$moduleTemplates ->	load($this->moduleInfo->modules_path, $this->moduleInfo->module_location);


			##	get node list
			$parentNode = $simpleObject -> params -> parent_node_id;
			$parentNode = (empty($parentNode) ? $this -> objectInfo -> node_id : $simpleObject ->  params -> parent_node_id);

			$modelCondition = new CModelCondition();
			$modelCondition -> where('node_id', $parentNode);		
			$modelSitemap  = new modelSitemap();
			$modelSitemap -> load($_pDatabase, $modelCondition);	

			$collectedNodeIds = [];
			foreach($modelSitemap -> getResult() as $node)
				$collectedNodeIds[] = $node -> node_id;

			##	get tag allocations
			$condTagsAllocation = new CModelCondition();
			$condTagsAllocation -> whereIn('node_id', implode(',', $collectedNodeIds));	
			$condTagsAllocation -> groupBy('category_id');

			$modelCategoriesAllocation = new modelCategoriesAllocation();
			$modelCategoriesAllocation -> load($_pDatabase, $condTagsAllocation);

			$collectedTagIds = [];
			if(is_array($modelCategoriesAllocation -> getResult()))
			foreach($modelCategoriesAllocation -> getResult() as $tagAlloc)
				$collectedTagIds[] = $tagAlloc -> category_id;

			if(!empty($collectedTagIds))
			{
				##	get tag list
				$condTags = new CModelCondition();
				$condTags -> whereIn('category_id', implode(',', $collectedTagIds));	
				$condTags -> groupBy('category_id');
			
			$m_modelCategories	= new modelCategories();
				$m_modelCategories -> load($_pDatabase, $condTags);
				$tagList = $m_modelCategories -> getResult();
			}




			##	create fake pageRequest
			$parentNode = tk::getNodeFromSitemap($modelSitemap -> getResult(), $parentNode);

			$this -> setView(	
							'edit',	
							'',
							[
								'object' 	=> $simpleObject,
							'parentNode' 		=> $parentNode,
							'termList' 			=> $tagList ?? [],
								'currentTemplate'	=> $moduleTemplate -> templatesList,
								'avaiableTemplates'	=> $moduleTemplates -> templatesList,
							]
							);

			$responseData['html'] = $this -> m_pView -> getHTML();
		}

		tk::xhrResult(
			0, 
			'OK', 
			$responseData
			);	
		
		return false;
	}
}
