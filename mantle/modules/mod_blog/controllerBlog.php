<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPageObject.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelCategoriesAllocation.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelTagsAllocation.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSitemap.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPage.php';

class	controllerBlog extends CController
{
	public function
	__construct($_module, &$_object)
	{
		parent::__construct($_module, $_object);
		$this -> m_aModule -> user_rights[] = 'view';	// add view right as default for everyone
	}
	
	public function
	logic(CDatabaseConnection &$_pDatabase, array $_rcaTarget, $_isXHRequest, &$_logicResult, bool $_bEditMode)
	{
		##	Set default target if not exists

		$_controllerAction = $this -> getControllerAction($_rcaTarget, 'view');

		if(!$this -> detectRights($_controllerAction))
		{
			if($_isXHRequest !== false)
			{
				$_bValidationErr =	true;
				$_bValidationMsg =	CLanguage::get() -> string('ERR_PERMISSON');
				$_bValidationDta = 	[];

				tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
			}

			CMessages::instance() -> addMessage(CLanguage::get() -> string('ERR_PERMISSON') , MSG_WARNING);
			return;
		}

		if($_bEditMode && $_isXHRequest === false) 
			$_controllerAction = 'edit';

		##	Call sub-logic function by target, if there results are false, we make a fall back to default view

		$enableEdit 	= $this -> existsUserRight('edit');
		$enableDelete	= $enableEdit;

		$_logicResults = false;
		switch($_controllerAction)
		{
			case 'view'		: $_logicResults = $this -> logicView(	$_pDatabase, $_isXHRequest, $_logicResult);	break;
			case 'edit'		: $_logicResults = $this -> logicEdit(	$_pDatabase, $_isXHRequest, $_logicResult);	break;	
			case 'create'	: $_logicResults = $this -> logicCreate($_pDatabase, $_isXHRequest, $_logicResult);	break;
			case 'delete'	: $_logicResults = $this -> logicDelete($_pDatabase, $_isXHRequest, $_logicResult);	break;	
		}

		if(!$_logicResults)
		{
			##	Default View
			$_logicResults = $this -> logicView($_pDatabase, $_isXHRequest, $_logicResult);	
		}
	}

	private function
	logicView(CDatabaseConnection &$_pDatabase, $_isXHRequest, &$_logicResult)
	{
		$nodeList = $this -> getNodesList($_pDatabase, $this -> m_aObject -> node_id);

		$this -> setView(	
						'view',	
						'',
						[
							'object' 	=> $this -> m_aObject,
							'nodeList'	=> $nodeList
						]
						);

		return true;
	}

	private function
	logicEdit(CDatabaseConnection &$_pDatabase, $_isXHRequest, &$_logicResult)
	{
		##	XHR Function call

		if($_isXHRequest !== false)
		{
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

		
			switch($_isXHRequest)
			{
				case 'edit'  :	// Update object

								$_pFormVariables =	new CURLVariables();
								$_request		 =	[];
								$_request[] 	 = 	[	"input" => "cms-object-id",  "output" => "object_id", 	"validate" => "strip_tags|!empty" ]; 
								$_request[] 	 = 	[	"input" => "simple-text",  "output" => "body", 			"validate" => "!empty" ]; 
								$_pFormVariables-> retrieve($_request, false, true); // POST 
								$_aFormData		 = $_pFormVariables ->getArray();

								if(empty($_aFormData['object_id'])) 		{ 	$_bValidationErr = true; 	$_bValidationDta[] = 'cms-object-id'; 			}

								if(!$_bValidationErr)
								{
									$modelCondition = new CModelCondition();
									$modelCondition -> where('object_id', $_aFormData['object_id']);

									$objectId = $_aFormData['object_id'];
									unset($_aFormData['object_id']);

									if(true)
									#if($this -> m_modelSimple -> update($_pDatabase, $_aFormData, $modelCondition))
									{
										$_bValidationMsg = 'Object updated';

										$this -> m_modelPageObject = new modelPageObject();

										$_objectUpdate['update_time']		=	time();
										$_objectUpdate['update_by']			=	0;
										$_objectUpdate['update_reason']		=	'';

										$this -> m_modelPageObject -> update($_pDatabase, $_objectUpdate, $modelCondition);
									
									}
									else
									{
										$_bValidationMsg .= 'Unknown error on sql query';
										$_bValidationErr = true;
									}	


								}
								else	// Validation Failed
								{
									$_bValidationMsg .= 'Data validation failed - object was not updated';
									$_bValidationErr = true;
								}

								break;
			}
			
			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
		}	

 
		$sitemapCondition = new CModelCondition();
		$sitemapCondition -> where('node_id', $this -> m_aObject -> node_id);

		$modelSitemap = new modelSitemap();
		$modelSitemap -> load($_pDatabase, $sitemapCondition);

		$sitemap = &$modelSitemap -> getResult();

		$this -> appendAdditionNodeData($_pDatabase, $sitemap);



		$this -> setView(	
						'edit',	
						'',
						[
							'object' 	=> $this -> m_aObject,
							'sitemap'	=> $modelSitemap -> getResult()
						]
						);

		return true;
	}

	private function
	logicCreate(CDatabaseConnection &$_pDatabase, $_isXHRequest, &$_logicResult)
	{

		##	XHR Function call

		if($_isXHRequest !== false && $_isXHRequest === 'cms-insert-module')
		{
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			$_dataset['object_id'] 	= $this -> m_aObject -> object_id;
			$_dataset['body'] 		= '';
			$_dataset['params'] 	= '';
		
			#if(!$this -> m_modelSimple -> create($_pDatabase, $_dataset))
			if(false)
			{
				$_bValidationErr =	true;
				$_bValidationMsg =	'sql insert failed';
			}
			else
			{
				$sitemapCondition = new CModelCondition();
				$sitemapCondition -> where('node_id', $this -> m_aObject -> node_id);

				$modelSitemap = new modelSitemap();
				$modelSitemap -> load($_pDatabase, $sitemapCondition);


				$sitemap = $modelSitemap -> getResult();


			
				$this -> appendAdditionNodeData($_pDatabase, $sitemap);



				$this -> setView(	
								'edit',	
								'',
								[
									'object' 	=> $this -> m_aObject,
									'sitemap'	=> $modelSitemap -> getResult()
								]
								);

				$_bValidationDta['html'] = $this -> m_pView -> getHTML();
			}

			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call

		}	

	}
	
	private function
	logicDelete(CDatabaseConnection &$_pDatabase, $_isXHRequest, &$_logicResult)
	{
		##	XHR Function call

		if($_isXHRequest !== false)
		{
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];
		
			switch($_isXHRequest)
			{
				case 'delete'  :	// Update object

									$_pFormVariables =	new CURLVariables();
									$_request		 =	[];
									$_request[] 	 = 	[	"input" => "cms-object-id",  "output" => "object_id", 	"validate" => "strip_tags|!empty" ]; 
									$_pFormVariables-> retrieve($_request, false, true); // POST 
									$_aFormData		 = $_pFormVariables ->getArray();

									if(empty($_aFormData['object_id'])) 		{ 	$_bValidationErr = true; 	$_bValidationDta[] = 'cms-object-id'; 			}

									if(!$_bValidationErr)
									{
										$modelCondition  = new CModelCondition();
										$modelCondition -> where('object_id', $_aFormData['object_id']);
										$_objectModel  	 = new modelPageObject();

										if($_objectModel -> delete($_pDatabase, $modelCondition))
										{
											$_bValidationMsg = 'Object deleted';
										}
										else
										{
											$_bValidationMsg .= 'Unknown error on sql query';
											$_bValidationErr = true;
										}
									}
									else	// Validation Failed
									{
										$_bValidationMsg .= 'Data validation failed - object was not updated';
										$_bValidationErr = true;
									}

									break;
			}
			
			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
		}	

		return false;
	}

	protected function
	getNodesList(CDatabaseConnection &$_pDatabase, int $_rootNodeId)
	{
		$nodesSearch = new CNodesSearch;
		if($nodesSearch -> detectSearch())
		{
			$modelPage  = new modelPage;
			$modelPage -> loadByNodeSearch($_pDatabase, $nodesSearch, $_rootNodeId);

			$nodeList = &$modelPage -> getResult();

			$this -> appendAdditionNodeData($_pDatabase, $nodeList);
		}
		else
		{
			$sitemapCondition = new CModelCondition();
			$sitemapCondition -> where('node_id', $_rootNodeId);

			$modelSitemap = new modelSitemap();
			$modelSitemap -> load($_pDatabase, $sitemapCondition);

			$nodeList = &$modelSitemap -> getResult();
		
			$this -> appendAdditionNodeData($_pDatabase, $nodeList);
		}
		
			##

			$timestamp = time();
			$rootLevel = false;

			foreach($nodeList as $nodeIndex => $node)
			{

				if(property_exists($node, 'level'))
				{
					if($rootLevel === false)
					{
						$rootLevel = $node -> level + 1;
						unset($nodeList[$nodeIndex]);
						continue;
					}
					
					if($rootLevel != $node -> level)
					{
						unset($nodeList[$nodeIndex]);
						continue;
					}
				}

				if(
						(		$node -> hidden_state == 0
							|| 	$node -> hidden_state == 2
						)
					&&	(empty($node -> page_auth) || (!empty($node -> page_auth) && CSession::instance() -> isAuthed($node -> page_auth) === true))
					||	(	($node -> hidden_state == 5 && $node -> publish_from  < $timestamp)
						&&	($node -> hidden_state == 5 && $node -> publish_until > $timestamp && $node -> publish_until != 0)
						)
					||  CMS_BACKEND
					); else unset($nodeList[$nodeIndex]);

				if(empty($node -> text))
					unset($nodeList[$nodeIndex]);
			}

			$createTime = [];

			foreach ($nodeList as $key => $node)
				$createTime[$key] = $node -> create_time;

			array_multisort($createTime, SORT_DESC, $nodeList);
		

		return $nodeList;
	}

	protected function
	appendAdditionNodeData(CDatabaseConnection &$_pDatabase, array &$nodeList)
	{
		foreach($nodeList as $nodeIndex => $node)
		{
			## append text

			$nodeList[$nodeIndex] -> text = $this -> getNodeText($_pDatabase, $node -> node_id);

			## append headline

			$nodeList[$nodeIndex] -> headline = $this -> getNodeHeatline($_pDatabase, $node -> node_id);

			## append categories
	
			$nodeList[$nodeIndex] -> categories = $this -> getNodeCategories($_pDatabase, $node -> node_id);

			## append tags

			$nodeList[$nodeIndex] -> tags = $this -> getNodeTags($_pDatabase, $node -> node_id);
		}
	}

	protected function
	getNodeCategories(CDatabaseConnection &$_pDatabase, int $_nodeId)
	{
		$categorieAllocCondition  = new CModelCondition();
		$categorieAllocCondition -> where('node_id', $_nodeId);	
		
		$conditionPages  = new CModelCondition();
		$conditionPages -> where('tb_categories_allocation.category_id', 'tb_categories.category_id');	

		$modelCategoriesAllocation  = new modelCategoriesAllocation();
		$modelCategoriesAllocation -> addSelectColumns('tb_categories.*');
		$modelCategoriesAllocation -> addRelation('join', 'tb_categories', $conditionPages);
		$modelCategoriesAllocation -> load($_pDatabase, $categorieAllocCondition);

		return $modelCategoriesAllocation -> getResult();
	}

	protected function
	getNodeTags(CDatabaseConnection &$_pDatabase, int $_nodeId)
	{
		$tagAllocCondition  = new CModelCondition();
		$tagAllocCondition -> where('node_id', $_nodeId);		

		$conditionPages  = new CModelCondition();
		$conditionPages -> where('tb_tags_allocation.tag_id', 'tb_tags.tag_id');	

		$modelTagsAllocation  = new modelTagsAllocation();
		$modelTagsAllocation -> addSelectColumns('tb_tags.*');
		$modelTagsAllocation -> addRelation('join', 'tb_tags', $conditionPages);
		$modelTagsAllocation -> load($_pDatabase, $tagAllocCondition);

		return $modelTagsAllocation -> getResult();
	}
	
	protected function
	getNodeHeatline(CDatabaseConnection &$_pDatabase, int $_nodeId)
	{
		$nodeCondition    = new CModelCondition();
		$nodeCondition   -> where('node_id', $_nodeId);
		$nodeCondition   -> where('module_controller', 'controllerSimpleHeadline');
		$nodeCondition   -> orderBy('object_order_by');
		$nodeCondition   -> limit(1);

		$conditionPages	  = new CModelCondition();
		$conditionPages  -> where('tb_page_object_simple.object_id', 'tb_page_object.object_id');
		$modelPageObject  = new modelPageObject();
		$modelPageObject -> addSelectColumns('tb_page_object.*','tb_page_object_simple.body','tb_page_object_simple.params');
		$modelPageObject -> addRelation('join', 'tb_page_object_simple', $conditionPages);

		$conditionPages   = new CModelCondition();
		$conditionPages  -> where('tb_modules.module_id', 'tb_page_object.module_id');
		$modelPageObject -> addRelation('join', 'tb_modules', $conditionPages);

		$modelPageObject -> load($_pDatabase, $nodeCondition);

		return (count($modelPageObject -> getResult()) != 0 ? $modelPageObject -> getResult()[0] : NULL);
	}

	protected function
	getNodeText(CDatabaseConnection &$_pDatabase, int $_nodeId)
	{
		$nodeCondition    = new CModelCondition();
		$nodeCondition   -> where('node_id', $_nodeId);
		$nodeCondition   -> where('module_controller', 'controllerSimpleText');
		$nodeCondition   -> orderBy('object_order_by');
		$nodeCondition   -> limit(1);

		$conditionPages   = new CModelCondition();
		$conditionPages  -> where('tb_page_object_simple.object_id', 'tb_page_object.object_id');
		$modelPageObject  = new modelPageObject();
		$modelPageObject -> addSelectColumns('tb_page_object.*','tb_page_object_simple.body','tb_page_object_simple.params');
		$modelPageObject -> addRelation('join', 'tb_page_object_simple', $conditionPages);

		$conditionPages   = new CModelCondition();
		$conditionPages  -> where('tb_modules.module_id', 'tb_page_object.module_id');
		$modelPageObject -> addRelation('join', 'tb_modules', $conditionPages);

		$modelPageObject -> load($_pDatabase, $nodeCondition);

		return (count($modelPageObject -> getResult()) != 0 ? $modelPageObject -> getResult()[0] : NULL);
	}
}

?>