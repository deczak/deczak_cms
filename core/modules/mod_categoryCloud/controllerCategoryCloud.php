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
		$this -> m_modelSimple	= new modelSimple();

		parent::__construct($_module, $_object);

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
			if($_xhrInfo !== null)
			{
				$validationErr =	true;
				$validationMsg =	CLanguage::get() -> string('ERR_PERMISSON');
				$responseData  = 	[];


				tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
			}

			CMessages::instance() -> addMessage(CLanguage::get() -> string('ERR_PERMISSON') , MSG_WARNING);
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
		$objectCondition = new CModelCondition();
		$objectCondition -> where('object_id', $this -> objectInfo -> object_id);
		$this -> m_modelSimple -> load($_pDatabase, $objectCondition);
		$this -> m_modelSimple -> getResult()[0] -> params = json_decode($this -> m_modelSimple -> getResult()[0] -> params);
		
		##	get node list
		$parentNode = $this -> m_modelSimple -> getResult()[0] -> params -> parent_node_id;
		$parentNode = (empty($parentNode) ? $this -> objectInfo -> node_id : $this -> m_modelSimple -> getResult()[0] ->  params -> parent_node_id);

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
		$moduleTemplate		->	load('simpleCategorieCloud', $this -> m_modelSimple -> getResult()[0] -> params -> template);

		##	get parent node
		$parentNode= tk::getNodeFromSitemap($modelSitemap -> getResult(), $parentNode);

		$this -> setView(	
						'view',	
						'',
						[
							'object' 	=> $this -> m_modelSimple -> getResult()[0],
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
		$objectCondition = new CModelCondition();
		$objectCondition -> where('object_id', $this -> objectInfo -> object_id);
		$this -> m_modelSimple -> load($_pDatabase, $objectCondition);
		$this -> m_modelSimple -> getResult()[0] -> params = json_decode($this -> m_modelSimple -> getResult()[0] -> params);
		
		##	get node list
		$parentNode = $this -> m_modelSimple -> getResult()[0] -> params -> parent_node_id;
		$parentNode = (empty($parentNode) ? $this -> objectInfo -> node_id : $this -> m_modelSimple -> getResult()[0] ->  params -> parent_node_id);

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
		$moduleTemplate		-> load('simpleCategorieCloud', $this -> m_modelSimple -> getResult()[0] -> params -> template);

		$moduleTemplates	 = new CModulesTemplates();
		$moduleTemplates	-> load('simpleCategorieCloud');

		##	get parent node
		$parentNode = tk::getNodeFromSitemap($modelSitemap -> getResult(), $parentNode);


		$this -> setView(	
						'edit',	
						'',
						[
							'object' 	=> $this -> m_modelSimple -> getResult()[0],
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
									$modelCondition = new CModelCondition();
									$modelCondition -> where('object_id', $_xhrInfo -> objectId);

									$_aFormData['params']	= 	[
																	"template"			=> $_aFormData['categorycloud-template'],
																	"parent_node_id"	=> $_aFormData['categorycloud-parent-node-id']
																];
									$_aFormData['params']	 = 	json_encode($_aFormData['params'], JSON_FORCE_OBJECT);



									$objectId = $_xhrInfo -> objectId;

									if($this -> m_modelSimple -> update($_pDatabase, $_aFormData, $modelCondition))
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

		return false;
	}

	private function
	logicXHRCreate(CDatabaseConnection &$_pDatabase, object $_xhrInfo) : bool
	{

			$validationErr =	false;
			$validationMsg =	'';
			$responseData = 	[];

			$_dataset['object_id'] 	= $this -> objectInfo -> object_id;
			$_dataset['body'] 		= '';
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
				
				##	get node list
				$parentNode = $this -> m_modelSimple -> getResult()[0] -> params -> parent_node_id;
				$parentNode = (empty($parentNode) ? $this -> objectInfo -> node_id : $this -> m_modelSimple -> getResult()[0] ->  params -> parent_node_id);

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
				$moduleTemplate		-> load('simpleCategorieCloud', $this -> m_modelSimple -> getResult()[0] -> params -> template);

				$moduleTemplates	 = new CModulesTemplates();
				$moduleTemplates	-> load('simpleCategorieCloud');

				$this -> setView(	
								'edit',	
								'',
								[
								'object' 			=> $this -> m_modelSimple -> getResult()[0],
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
										$modelCondition = new CModelCondition();
										$modelCondition -> where('object_id', $_xhrInfo -> objectId);

										if($this -> m_modelSimple -> delete($_pDatabase, $modelCondition))
										{
											$_objectModel  	 = new modelPageObject();
											$_objectModel	-> delete($_pDatabase, $modelCondition);

											$validationMsg = 'Object deleted';
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


									$pRouter  = CRouter::instance();
									$pRouter -> createRoutes($_pDatabase);

			
			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call

		return false;
	}
}
