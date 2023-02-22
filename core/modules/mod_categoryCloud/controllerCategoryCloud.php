<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSimple.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelCategories.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelCategoriesAllocation.php';	

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CModulesTemplates.php';	

class	controllerCategoryCloud extends CController
{
	public function
	__construct($_module, &$_object)
	{		
		$this -> m_modelCategories	= new modelCategories();
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
			if($_xhrInfo !== null)
			{
				$validationErr =	true;
				$validationMsg =	CLanguage::string('ERR_PERMISSON');
				$responseData  = 	[];


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
			case 'edit'		  : $logicDone = $this -> logicEdit($_pDatabase); break;

			case 'xhr_edit'   : $logicDone = $this -> logicXHREdit($_pDatabase, $_xhrInfo); break;
			case 'xhr_create' : $logicDone = $this -> logicXHRCreate($_pDatabase, $_xhrInfo); break;	
			case 'xhr_delete' : $logicDone = $this -> logicXHRDelete($_pDatabase, $_xhrInfo); break;	

		}

		if(!$logicDone) // Default
			$logicDone = $this -> logicView($_pDatabase);	
	
		return $logicDone;
	}

	private function
	logicView(CDatabaseConnection &$_pDatabase) : bool
	{
		##	get object
		$simpleObject = modelSimple::where('object_id', '=', $this -> objectInfo -> object_id)->one();
		
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

		##	get categories allocations
		$condCategoriesAllocation = new CModelCondition();
		$condCategoriesAllocation -> whereIn('node_id', implode(',', $collectedNodeIds));	
		$condCategoriesAllocation -> groupBy('category_id');

		$modelCategoriesAllocation = new modelCategoriesAllocation();
		$modelCategoriesAllocation -> load($_pDatabase, $condCategoriesAllocation);

		$collectedCategoryIds = [];
		foreach($modelCategoriesAllocation -> getResult() as $categoryAlloc)
			$collectedCategoryIds[] = $categoryAlloc -> category_id;

		if(!empty($collectedCategoryIds))
		{
			##	get categorie list
			$condCategories = new CModelCondition();
			$condCategories -> whereIn('category_id', implode(',', $collectedCategoryIds));	
			$condCategories -> groupBy('category_id');
			$this -> m_modelCategories -> load($_pDatabase, $condCategories);
			$categoriesList = $this -> m_modelTags -> getResult();
		}

		##	get module templates
		$moduleTemplate		 = new CModulesTemplates();
		$moduleTemplate		->	load($this -> moduleRootDir, $this->moduleInfo->module_location, $simpleObject -> params -> template);

		##	get parent node
		$parentNode= tk::getNodeFromSitemap($modelSitemap -> getResult(), $parentNode);

		$this -> setView(	
						'view',	
						'',
						[
							'object' 	=> $simpleObject,
							'termList' 	=> $categoriesList ?? [],
							'parentNode' 	=> $parentNode,
							'currentTemplate'	=> $moduleTemplate -> templatesList
						]
						);

		return true;
	}

	private function
	logicEdit(CDatabaseConnection &$_pDatabase) : bool
	{

		##	get object
		$simpleObject = modelSimple::where('object_id', '=', $this -> objectInfo -> object_id)->one();
		
		##	get node list
		$parentNode = $$simpleObject -> params -> parent_node_id;
		$parentNode = (empty($parentNode) ? $this -> objectInfo -> node_id : $simpleObject ->  params -> parent_node_id);

		$modelCondition = new CModelCondition();
		$modelCondition -> where('node_id', $parentNode);		
		$modelSitemap  = new modelSitemap();
		$modelSitemap -> load($_pDatabase, $modelCondition);	

		$collectedNodeIds = [];
		foreach($modelSitemap -> getResult() as $node)
			$collectedNodeIds[] = $node -> node_id;

		##	get categorie allocations
		$condCategoriesAllocation = new CModelCondition();
		$condCategoriesAllocation -> whereIn('node_id', implode(',', $collectedNodeIds));	
		$condCategoriesAllocation -> groupBy('category_id');

		$modelCategoriesAllocation = new modelCategoriesAllocation();
		$modelCategoriesAllocation -> load($_pDatabase, $condCategoriesAllocation);

		$collectedCategoryIds = [];
		foreach($modelCategoriesAllocation -> getResult() as $categorieAlloc)
			$collectedCategoryIds[] = $categorieAlloc -> category_id;

		if(!empty($collectedCategoryIds))
		{

			##	get categorie list
			$condCategories = new CModelCondition();
			$condCategories -> whereIn('category_id', implode(',', $collectedCategoryIds));	
			$condCategories -> groupBy('category_id');
			$this -> m_modelCategories -> load($_pDatabase, $condCategories);
			$categoriesList = $this -> m_modelTags -> getResult();
		}

		##	get module templates
		$moduleTemplate		 = new CModulesTemplates();
		$moduleTemplate		-> load($this -> moduleRootDir, $this->moduleInfo->module_location, $simpleObject -> params -> template);

		$moduleTemplates	 = new CModulesTemplates();
		$moduleTemplates	-> load($this -> moduleRootDir, $this->moduleInfo->module_location);

		##	get parent node
		$parentNode = tk::getNodeFromSitemap($modelSitemap -> getResult(), $parentNode);


		$this -> setView(	
						'edit',	
						'',
						[
							'object' 	=> $simpleObject,
							'termList' 	=> $categoriesList ?? [],
							'parentNode' 	=> $parentNode,
							'currentTemplate'	=> $moduleTemplate -> templatesList,
							'avaiableTemplates'	=> $moduleTemplates -> templatesList
						]
						);

		return true;
	}

	private function
	logicXHREdit(CDatabaseConnection &$_pDatabase, object $_xhrInfo) : bool
	{
		$validationErr =	false;
		$validationMsg =	'';
		$responseData = 	[];

		$_pFormVariables =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "categorycloud-template",  		"validate" => "strip_tags|!empty" ]; 
		$_request[] 	 = 	[	"input" => "categorycloud-parent-node-id", 	"validate" => "strip_tags|!empty" ]; 
		$_pFormVariables-> retrieve($_request, false, true); // POST 
		$_aFormData		 = $_pFormVariables ->getArray();

		if(empty($_xhrInfo -> objectId))
		{ 	
			$validationErr = true; 	
			$responseData[] = 'cms-object-id';
		}

		if(!$validationErr)
		{
			$simpleObject = modelSimple::where('object_id', '=', $_xhrInfo -> objectId)->one();

			$sOParams = new stdClass;
			$sOParams->template 		= $_aFormData['categorycloud-template'];
			$sOParams->parent_node_id 	= $_aFormData['categorycloud-parent-node-id'];

			$simpleObject->params	= $sOParams;
			$simpleObject->body 	= '';

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

		return false;
	}

	private function
	logicXHRCreate(CDatabaseConnection &$_pDatabase, object $_xhrInfo) : bool
	{
		$validationErr =	false;
		$validationMsg =	'';
		$responseData = 	[];
			
		$sOParams = new stdClass;
		$sOParams->template = '';
		$sOParams->display_hidden = '';
		$sOParams->parent_node_id = '';

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

			##	get categorie allocations
			$condCategoriesAllocation = new CModelCondition();
			$condCategoriesAllocation -> whereIn('node_id', implode(',', $collectedNodeIds));	
			$condCategoriesAllocation -> groupBy('category_id');

			$modelCategoriesAllocation = new modelCategoriesAllocation();
			$modelCategoriesAllocation -> load($_pDatabase, $condCategoriesAllocation);

			$collectedCategoryIds = [];
			foreach($modelCategoriesAllocation -> getResult() as $categorieAlloc)
				$collectedCategoryIds[] = $categorieAlloc -> category_id;

			if(!empty($collectedCategoryIds))
			{
					##	get categorie list
					$condCategories = new CModelCondition();
					$condCategories -> whereIn('category_id', implode(',', $collectedCategoryIds));	
					$condCategories -> groupBy('category_id');
					$this -> m_modelCategories -> load($_pDatabase, $condCategories);
				$categoriesList = $this -> m_modelTags -> getResult();
			}

				##	create fake pageRequest
				$parentNode = tk::getNodeFromSitemap($modelSitemap -> getResult(), $parentNode);

				$pageRequest = new stdClass;
				$pageRequest -> page_language 	= $parentNode -> page_language;
				$pageRequest -> node_id 		= $this -> objectInfo -> node_id;
				$pageRequest -> sitemap 		= $this -> m_modelCategories -> getResult();

				##	get module templates
				$moduleTemplate		 = new CModulesTemplates();
				$moduleTemplate		-> load($this -> moduleRootDir, $this->moduleInfo->module_location, $simpleObject -> params -> template);

				$moduleTemplates	 = new CModulesTemplates();
				$moduleTemplates	-> load($this -> moduleRootDir, $this->moduleInfo->module_location);

				$this -> setView(	
								'edit',	
								'',
								[
								'object' 			=> $simpleObject,
								'parentNode' 		=> $parentNode,
								'termList' 			=> $categoriesList ?? [],
								'currentTemplate'	=> $moduleTemplate -> templatesList,
								'avaiableTemplates'	=> $moduleTemplates -> templatesList
								]
								);

				$responseData['html'] = $this -> m_pView -> getHTML($pageRequest);


				$pRouter  = CRouter::instance();
				$pRouter -> createRoutes($_pDatabase);
			}

		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call

		return false;
	}
	
	private function
	logicXHRDelete(CDatabaseConnection &$_pDatabase, object $_xhrInfo, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{
		$validationErr =	false;
		$validationMsg =	'';
		$responseData = 	[];
	
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
			$validationMsg .= 'Data validation failed - object was not updated';
			$validationErr = true;
		}

		$pRouter  = CRouter::instance();
		$pRouter -> createRoutes($_pDatabase);
			
		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call

		return false;
	}
}
