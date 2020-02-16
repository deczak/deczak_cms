<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPageObject.php';	

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelCategoriesAllocation.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelTagsAllocation.php';	


include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSitemap.php';	

class	controllerBlog extends CController
{

	#private	$m_modelSimple;
		
	public function
	__construct($_module, &$_object)
	{
		parent::__construct($_module, $_object);

		#$this -> m_modelSimple = new modelSimple();

		$this -> m_aModule -> user_rights[] = 'view';	// add view right as default for everyone
	}
	
	public function
	logic(&$_sqlConnection, array $_rcaTarget, $_isXHRequest, &$_logicResult, bool $_bEditMode)
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
			case 'view'		: $_logicResults = $this -> logicView(	$_sqlConnection, $_isXHRequest, $_logicResult);	break;
			case 'edit'		: $_logicResults = $this -> logicEdit(	$_sqlConnection, $_isXHRequest, $_logicResult);	break;	
			case 'create'	: $_logicResults = $this -> logicCreate($_sqlConnection, $_isXHRequest, $_logicResult);	break;
			case 'delete'	: $_logicResults = $this -> logicDelete($_sqlConnection, $_isXHRequest, $_logicResult);	break;	
		}

		if(!$_logicResults)
		{
			##	Default View
			$_logicResults = $this -> logicView($_sqlConnection, $_isXHRequest, $_logicResult);	
		}
	}

	private function
	logicView(&$_sqlConnection, $_isXHRequest, &$_logicResult)
	{
		$modelCondition = new CModelCondition();
		$modelCondition -> where('object_id', $this -> m_aObject -> object_id);

		#$this -> m_modelSimple -> load($_sqlConnection, $modelCondition);

 
		$sitemapCondition = new CModelCondition();
		$sitemapCondition -> where('node_id', $this -> m_aObject -> node_id);

		$modelSitemap = new modelSitemap();
		$modelSitemap -> load($_sqlConnection, $sitemapCondition);

		$sitemap = $modelSitemap -> getDataInstance();

		foreach($sitemap as $nodeIndex => $node)
		{
			$nodeCondition = new CModelCondition();
			$nodeCondition -> where('node_id', $node -> node_id);
			$nodeCondition -> where('module_controller', 'controllerSimpleText');
			$nodeCondition -> orderBy('object_order_by');
			$nodeCondition -> limit(1);

			$modelPageObject  = new modelPageObject();


			$modelPageObject -> addSelectColumns('tb_page_object.*','tb_page_object_simple.body','tb_page_object_simple.params');

			$conditionPages = new CModelCondition();
			$conditionPages -> where('tb_page_object_simple.object_id', 'tb_page_object.object_id');


			$modelPageObject -> addRelation('join', 'tb_page_object_simple', $conditionPages);


			$conditionPages = new CModelCondition();
			$conditionPages -> where('tb_modules.module_id', 'tb_page_object.module_id');
			$modelPageObject -> addRelation('join', 'tb_modules', $conditionPages);


			$modelPageObject -> load($_sqlConnection, $nodeCondition);

			$sitemap[$nodeIndex] -> text = (count($modelPageObject -> getDataInstance()) != 0 ? $modelPageObject -> getDataInstance()[0] : NULL);


##



			$nodeCondition = new CModelCondition();
			$nodeCondition -> where('node_id', $node -> node_id);
			$nodeCondition -> where('module_controller', 'controllerSimpleHeadline');
			$nodeCondition -> orderBy('object_order_by');
			$nodeCondition -> limit(1);

			$modelPageObject  = new modelPageObject();


			$modelPageObject -> addSelectColumns('tb_page_object.*','tb_page_object_simple.body','tb_page_object_simple.params');

			$conditionPages = new CModelCondition();
			$conditionPages -> where('tb_page_object_simple.object_id', 'tb_page_object.object_id');


			$modelPageObject -> addRelation('join', 'tb_page_object_simple', $conditionPages);


			$conditionPages = new CModelCondition();
			$conditionPages -> where('tb_modules.module_id', 'tb_page_object.module_id');
			$modelPageObject -> addRelation('join', 'tb_modules', $conditionPages);


			$modelPageObject -> load($_sqlConnection, $nodeCondition);

			$sitemap[$nodeIndex] -> headline = (count($modelPageObject -> getDataInstance()) != 0 ? $modelPageObject -> getDataInstance()[0] : NULL);




			$categorieAllocCondition 	 = new CModelCondition();
			$categorieAllocCondition 	-> where('node_id', $node -> node_id);	
			
			$conditionPages = new CModelCondition();
			$conditionPages -> where('tb_categories_allocation.category_id', 'tb_categories.category_id');	

			$modelCategoriesAllocation	 = new modelCategoriesAllocation();
			$modelCategoriesAllocation -> addSelectColumns('tb_categories.*');
			$modelCategoriesAllocation -> addRelation('join', 'tb_categories', $conditionPages);
			$modelCategoriesAllocation	-> load($_sqlConnection, $categorieAllocCondition);

			$sitemap[$nodeIndex] -> categories = $modelCategoriesAllocation -> getDataInstance();
		




			$tagAllocCondition 	 = new CModelCondition();
			$tagAllocCondition 	-> where('node_id', $node -> node_id);		

			$conditionPages = new CModelCondition();
			$conditionPages -> where('tb_tags_allocation.tag_id', 'tb_tags.tag_id');	

			$modelTagsAllocation	 = new modelTagsAllocation();
			$modelTagsAllocation -> addSelectColumns('tb_tags.*');
			$modelTagsAllocation -> addRelation('join', 'tb_tags', $conditionPages);
			$modelTagsAllocation	-> load($_sqlConnection, $tagAllocCondition);

			
			$sitemap[$nodeIndex] -> tags = &$modelTagsAllocation -> getDataInstance();




		}


		$this -> setView(	
						'view',	
						'',
						[
							'object' 	=> $this -> m_aObject,
							'sitemap'	=> $modelSitemap -> getDataInstance()
						]
						);

		return true;
	}

	private function
	logicEdit(&$_sqlConnection, $_isXHRequest, &$_logicResult)
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
									#if($this -> m_modelSimple -> update($_sqlConnection, $_aFormData, $modelCondition))
									{
										$_bValidationMsg = 'Object updated';

										$this -> m_modelPageObject = new modelPageObject();

										$_objectUpdate['object_id']		=	$objectId;
										$_objectUpdate['time_update']		=	time();
										$_objectUpdate['update_by']			=	0;
										$_objectUpdate['update_reason']		=	'';

										$this -> m_modelPageObject -> updateOld($_sqlConnection, $_objectUpdate);
									
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

	#	$modelCondition = new CModelCondition();
	#	$modelCondition -> where('object_id', $this -> m_aObject -> object_id);

	#	$this -> m_modelSimple -> load($_sqlConnection, $modelCondition);
 
		$sitemapCondition = new CModelCondition();
		$sitemapCondition -> where('node_id', $this -> m_aObject -> node_id);

		$modelSitemap = new modelSitemap();
		$modelSitemap -> load($_sqlConnection, $sitemapCondition);

		$sitemap = $modelSitemap -> getDataInstance();

		foreach($sitemap as $nodeIndex => $node)
		{
			$nodeCondition = new CModelCondition();
			$nodeCondition -> where('node_id', $node -> node_id);
			$nodeCondition -> where('module_controller', 'controllerSimpleText');
			$nodeCondition -> orderBy('object_order_by');
			$nodeCondition -> limit(1);

			$modelPageObject  = new modelPageObject();


			$modelPageObject -> addSelectColumns('tb_page_object.*','tb_page_object_simple.body','tb_page_object_simple.params');

			$conditionPages = new CModelCondition();
			$conditionPages -> where('tb_page_object_simple.object_id', 'tb_page_object.object_id');


			$modelPageObject -> addRelation('join', 'tb_page_object_simple', $conditionPages);


			$conditionPages = new CModelCondition();
			$conditionPages -> where('tb_modules.module_id', 'tb_page_object.module_id');
			$modelPageObject -> addRelation('join', 'tb_modules', $conditionPages);


			$modelPageObject -> load($_sqlConnection, $nodeCondition);


			$sitemap[$nodeIndex] -> text = (count($modelPageObject -> getDataInstance()) != 0 ? $modelPageObject -> getDataInstance()[0] : NULL);


##



			$nodeCondition = new CModelCondition();
			$nodeCondition -> where('node_id', $node -> node_id);
			$nodeCondition -> where('module_controller', 'controllerSimpleHeadline');
			$nodeCondition -> orderBy('object_order_by');
			$nodeCondition -> limit(1);

			$modelPageObject  = new modelPageObject();


			$modelPageObject -> addSelectColumns('tb_page_object.*','tb_page_object_simple.body','tb_page_object_simple.params');

			$conditionPages = new CModelCondition();
			$conditionPages -> where('tb_page_object_simple.object_id', 'tb_page_object.object_id');


			$modelPageObject -> addRelation('join', 'tb_page_object_simple', $conditionPages);


			$conditionPages = new CModelCondition();
			$conditionPages -> where('tb_modules.module_id', 'tb_page_object.module_id');
			$modelPageObject -> addRelation('join', 'tb_modules', $conditionPages);


			$modelPageObject -> load($_sqlConnection, $nodeCondition);


			$sitemap[$nodeIndex] -> headline = (count($modelPageObject -> getDataInstance()) != 0 ? $modelPageObject -> getDataInstance()[0] : NULL);

##






			$categorieAllocCondition 	 = new CModelCondition();
			$categorieAllocCondition 	-> where('node_id', $node -> node_id);	
			
			$conditionPages = new CModelCondition();
			$conditionPages -> where('tb_categories_allocation.category_id', 'tb_categories.category_id');	

			$modelCategoriesAllocation	 = new modelCategoriesAllocation();
			$modelCategoriesAllocation -> addSelectColumns('tb_categories.*');
			$modelCategoriesAllocation -> addRelation('join', 'tb_categories', $conditionPages);
			$modelCategoriesAllocation	-> load($_sqlConnection, $categorieAllocCondition);

			$sitemap[$nodeIndex] -> categories = $modelCategoriesAllocation -> getDataInstance();
		




			$tagAllocCondition 	 = new CModelCondition();
			$tagAllocCondition 	-> where('node_id', $node -> node_id);		

			$conditionPages = new CModelCondition();
			$conditionPages -> where('tb_tags_allocation.tag_id', 'tb_tags.tag_id');	

			$modelTagsAllocation	 = new modelTagsAllocation();
			$modelTagsAllocation -> addSelectColumns('tb_tags.*');
			$modelTagsAllocation -> addRelation('join', 'tb_tags', $conditionPages);
			$modelTagsAllocation	-> load($_sqlConnection, $tagAllocCondition);

			
			$sitemap[$nodeIndex] -> tags = &$modelTagsAllocation -> getDataInstance();


		}

		$this -> setView(	
						'edit',	
						'',
						[
							'object' 	=> $this -> m_aObject,
							'sitemap'	=> $modelSitemap -> getDataInstance()
						]
						);

		return true;
	}

	private function
	logicCreate(&$_sqlConnection, $_isXHRequest, &$_logicResult)
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
		
			#if(!$this -> m_modelSimple -> create($_sqlConnection, $_dataset))
			if(false)
			{
				$_bValidationErr =	true;
				$_bValidationMsg =	'sql insert failed';
			}
			else
			{
				$this -> setView(	
								'edit',	
								'',
								[
									'object' 	=> $this -> m_aObject
								]
								);

				$_bValidationDta['html'] = $this -> m_pView -> getHTML();
			}

			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call

		}	

	}
	
	private function
	logicDelete(&$_sqlConnection, $_isXHRequest, &$_logicResult)
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

										$modelCondition = new CModelCondition();
										$modelCondition -> where('object_id', $_aFormData['object_id']);

										#if($this -> m_modelSimple -> delete($_sqlConnection, $modelCondition))
										if(true)
										{
											
											$_objectModel  	 = new modelPageObject();
											$_objectModel	-> deleteOld($_sqlConnection, $_aFormData);

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


}

?>