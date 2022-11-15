<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPageObject.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelCategoriesAllocation.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelTagsAllocation.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSitemap.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPage.php';

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelRedirect.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CModulesTemplates.php';	
include_once 'modelBlog.php';

class	controllerBlog extends CController
{
	public function
	__construct($_module, &$_object)
	{
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
		$simpleObject = modelSimple::where('object_id', '=', $this -> objectInfo -> object_id)->one();

		$moduleTemplate = new CModulesTemplates();
		$moduleTemplate ->	load('blog', $simpleObject -> params -> template ?? 'list');
	
		$nodeList = $this -> getNodesList($_pDatabase, $this -> objectInfo -> node_id);

		$this -> setView(	
						'view',	
						'',
						[
							'object' 	=> $this -> objectInfo,
							'currentTemplate'	=> $moduleTemplate -> templatesList,
							'nodeList'	=> $nodeList
						]
						);

		return true;
	}

	private function
	logicEdit(CDatabaseConnection &$_pDatabase) : bool
	{
		$simpleObject = modelSimple::where('object_id', '=', $this -> objectInfo -> object_id)->one();

		$moduleTemplate = new CModulesTemplates();
		$moduleTemplate ->	load('blog', $simpleObject -> params -> template ?? 'list');

		$moduleTemplates = new CModulesTemplates();
		$moduleTemplates ->	load('blog');

		$nodeList = $this -> getNodesList($_pDatabase, $this -> objectInfo -> node_id);

		$this -> setView(	
						'edit',	
						'',
						[
							'object' 	=> $simpleObject,
							'currentTemplate'	=> $moduleTemplate -> templatesList,
							'avaiableTemplates'	=> $moduleTemplates -> templatesList,
							'nodeList'	=> $nodeList
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


		/*
		$_pFormVariables =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "blog-template",  		"validate" => "strip_tags|!empty" ]; 
		$_pFormVariables-> retrieve($_request, false, true); // POST 
		$urlVarList		 = $_pFormVariables ->getArray();
		*/

		$requestQuery = new cmsRequestQuery(true);
		$requestQuery->post('blog-template')->validate(QueryValidation::STRIP_TAGS | QueryValidation::IS_NOTEMPTY)->out('template')->exec();
		$sOParams = $requestQuery->toObject();


		if(empty($_xhrInfo -> objectId))
		{
			$validationErr = true;
			$responseData[] = 'cms-object-id';
		}

		if(!$validationErr)
		{
			$simpleObject = modelSimple::where('object_id', '=', $_xhrInfo -> objectId)->one();
			$simpleObject->params = $sOParams;
			$simpleObject->body = '';

			if($simpleObject->save())
			{
				$validationMsg = 'Object updated';

				$this -> m_modelPageObject = new modelPageObject();

				$_objectUpdate['update_time']		=	time();
				$_objectUpdate['update_by']			=	0;
				$_objectUpdate['update_reason']		=	'';

				$modelCondition = new CModelCondition();
				$modelCondition -> where('object_id', $_xhrInfo -> objectId);

				$this -> m_modelPageObject -> update($_pDatabase, $_objectUpdate, $modelCondition);

				$this->logicXHRView($_pDatabase, $_xhrInfo);
			
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
	logicXHRCreate(CDatabaseConnection &$_pDatabase, object $_xhrInfo) : bool
	{
		$validationErr =	false;
		$validationMsg =	'';
		$responseData = 	[];

		$simpleObject = modelSimple::new();
		$simpleObject->body			= '';
		$simpleObject->object_id	= $this -> objectInfo -> object_id;
		$simpleObject->params->template		= '';
	
		if(!$simpleObject->save())
		{
			$validationErr =	true;
			$validationMsg =	'sql insert failed';
		}
		else
		{
			$sitemapCondition = new CModelCondition();
			$sitemapCondition -> where('node_id', $this -> objectInfo -> node_id);

			$modelSitemap = new modelSitemap();
			$modelSitemap -> load($_pDatabase, $sitemapCondition);

			$sitemap = $modelSitemap -> getResult();
		
			$this -> appendAdditionNodeData($_pDatabase, $sitemap);

			$this -> setView(	
							'edit',	
							'',
							[
								'object' 	=> $this -> objectInfo,
								'sitemap'	=> $modelSitemap -> getResult()
							]
							);

			$responseData['html'] = $this -> m_pView -> getHTML();
		}

		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call

		return false;

	}
	
	private function
	logicXHRDelete(CDatabaseConnection &$_pDatabase, object $_xhrInfo) : bool
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
			
			$_objectModel  	 = new modelPageObject();

			if($_objectModel->delete($_pDatabase, $modelCondition))
			{

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

		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
	
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
			$redirectCondition = new CModelCondition();
			$redirectCondition -> where('node_id', $node -> node_id);			

			$modelRedirect	= new modelRedirect();
			$modelRedirect -> load($_pDatabase, $redirectCondition);

			$redirectTarget = $modelRedirect -> getResult();

			if(!empty($redirectTarget))
			{
				$redirectTarget = reset($redirectTarget)->redirect_target;
				if(!ctype_digit($redirectTarget) && strpos($redirectTarget, 'http') !== false)
				{
					$nodeList[$nodeIndex] -> page_redirect	= $redirectTarget;
				}
			}

			## append page image url small & large
			
			if(!empty($node -> page_image))
			{
				$nodeList[$nodeIndex] -> page_image_url = MEDIATHEK::getItemUrl($node -> page_image ?? 0);
				$nodeList[$nodeIndex] -> page_image_url_s = ($nodeList[$nodeIndex] -> page_image_url !== null ? $nodeList[$nodeIndex] -> page_image_url .'?binary&size=small' : $nodeList[$nodeIndex] -> page_image_url);
				$nodeList[$nodeIndex] -> page_image_url_m = ($nodeList[$nodeIndex] -> page_image_url !== null ? $nodeList[$nodeIndex] -> page_image_url .'?binary&size=medium' : $nodeList[$nodeIndex] -> page_image_url);
			}
	
			## append text

			$nodeList[$nodeIndex] -> text = $this -> getNodeText($_pDatabase, $node -> node_id);

			## append headline

			$nodeList[$nodeIndex] -> headline = $this -> getNodeHeatline($_pDatabase, $node -> node_id);

			## append categories
	
			$nodeList[$nodeIndex] -> categories = $this -> getNodeCategories($_pDatabase, $node -> node_id);

			## append tags

			$nodeList[$nodeIndex] -> tags = $this -> getNodeTags($_pDatabase, $node -> node_id);

			## append post settings

			$nodeList[$nodeIndex] -> postSetting = modelBlog::where('node_id', '=', $node -> node_id)->one();
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

	public function
	registerSystemFunction(cmsSystemModules $cmsSystemModules)
	{
		$cmsSystemModules -> register(cmsSystemModules::SECTION_TOOLBAR, [$this, 'systemFunctionToolbar']);
		$cmsSystemModules -> register(cmsSystemModules::SECTION_TOOLBAR_EDIT, [$this, 'systemFunctionToolbarEdit']);
	}

	public function
	systemFunctionToolbar(array $params = [])
	{	
		switch($this -> moduleInfo -> module_type) 
		{
			case 'core':	

				$_modLocation	= CMS_SERVER_ROOT . DIR_CORE . DIR_MODULES . $this -> moduleInfo -> module_location .'/';	
				CLanguage::loadLanguageFile($_modLocation.'lang/', CPageRequest::instance() -> getPageLanguage());
				break;

			case 'mantle':

				$_modLocation	= CMS_SERVER_ROOT . DIR_MANTLE . DIR_MODULES . $this -> moduleInfo -> module_location .'/';
				CLanguage::loadLanguageFile($_modLocation.'lang/', CPageRequest::instance() -> getPageLanguage());
				break;
		}

		$postSettings = modelBlog::where('node_id', '=', $params['node_id'])->one();

		require 'view/toolbar.php';
	}

	public function
	systemFunctionToolbarEdit(array $params = [])
	{
		$_pFormVariables =	new CURLVariables();
		$requestList		 =	[];

		$requestList[] 	 = 	[	"input" => "cms-edit-page-node",			"output" => 'node_id', "validate" => "strip_tags|trim|is_digit|!empty" , 	"use_default" => true, "default_value" => 0 ]; 	
		$requestList[] 	 = 	[	"input" => "modBlog_page_color",			"output" => 'post_page_color', "validate" => "strip_tags|trim|!empty" , 	"use_default" => true, "default_value" => 0 ]; 	
		$requestList[] 	 = 	[	"input" => "modBlog_text_color",			"output" => 'post_text_color', "validate" => "strip_tags|trim|!empty" , 	"use_default" => true, "default_value" => 0 ]; 	
		$requestList[] 	 = 	[	"input" => "modBlog_background_mode",		"output" => 'post_background_mode', "validate" => "strip_tags|trim|is_digit|!empty" , 	"use_default" => true, "default_value" => 0 ]; 	
		$requestList[] 	 = 	[	"input" => "modBlog_teasertext_mode",		"output" => 'post_teasertext_mode', "validate" => "strip_tags|trim|!empty" , 	"use_default" => true, "default_value" => 1 ]; 	
		$requestList[] 	 = 	[	"input" => "modBlog_post_size_length_min",	"output" => 'post_size_length_min', "validate" => "strip_tags|trim|is_digit|!empty" , 	"use_default" => true, "default_value" => 0 ]; 	
		$requestList[] 	 = 	[	"input" => "modBlog_post_size_height",		"output" => 'post_size_height', "validate" => "strip_tags|trim|is_digit|!empty" , 	"use_default" => true, "default_value" => 0 ]; 	
		$requestList[] 	 = 	[	"input" => "modBlog_post_display_categorie","output" => 'post_display_category', "validate" => "strip_tags|trim|is_digit|!empty" , 	"use_default" => true, "default_value" => 0 ]; 	

		$_pFormVariables-> retrieve($requestList, true, true); // POST 
		$urlVarList		 = $_pFormVariables ->getArray();



/*
	request validation für string und digit



		$queryValidationString = cmsRequestQueryValidation::set(
			QueryValidation::STRIP_TAGS | QueryValidation::TRIM | QueryValidation::IS_NOTEMPTY
		);

		$queryValidationDigit = cmsRequestQueryValidation::set(
			QueryValidation::STRIP_TAGS | QueryValidation::TRIM | QueryValidation::IS_DIGIT | QueryValidation::IS_NOTEMPTY
		);


		$requestQuery = new cmsRequestQuery(true);
		$requestQuery->post('')->out('')->validate($queryValidationString)->exec();

		$requestQuery->post('')->out('')->validate($queryValidationDigit)->exec();
		$sOParams = $requestQuery->toObject();
*/



		$postSettings = modelBlog::where('node_id', '=', $urlVarList['node_id'])->one();

		if(!$postSettings)
			$postSettings = modelBlog::new();

		$urlVarList['post_page_color'] 		= (string)$urlVarList['post_page_color'];
		$urlVarList['post_text_color'] 		= (string)$urlVarList['post_text_color'];
		$urlVarList['post_background_mode'] = (int)$urlVarList['post_background_mode'];
		$urlVarList['post_teasertext_mode'] = (int)$urlVarList['post_teasertext_mode'];
		$urlVarList['post_size_length_min'] = (int)$urlVarList['post_size_length_min'];
		$urlVarList['post_size_height'] 	= (int)$urlVarList['post_size_height'];
		$urlVarList['post_display_category']= (int)$urlVarList['post_display_category'];

		$postSettings->pull($urlVarList);
		$postSettings->save();
		 
	}
}
