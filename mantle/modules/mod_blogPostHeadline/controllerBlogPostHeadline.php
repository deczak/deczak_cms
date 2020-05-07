<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPageObject.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelCategoriesAllocation.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelTagsAllocation.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSitemap.php';	

class	controllerBlogPostHeadline extends CController
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
		$modelCondition = new CModelCondition();
		$modelCondition -> where('object_id', $this -> m_aObject -> object_id);
 
		$sitemapCondition = new CModelCondition();
		$sitemapCondition -> where('node_id', $this -> m_aObject -> node_id);
		$sitemapCondition -> limit(1);

		$modelSitemap = new modelSitemap();
		$modelSitemap -> load($_pDatabase, $sitemapCondition);

		$sitemap = &$modelSitemap -> getResult();

		foreach($sitemap as $nodeIndex => $node)
		{
			## Categories

			$categorieAllocCondition 	 = new CModelCondition();
			$categorieAllocCondition 	-> where('node_id', $node -> node_id);	
			
			$conditionPages = new CModelCondition();
			$conditionPages -> where('tb_categories_allocation.category_id', 'tb_categories.category_id');	

			$modelCategoriesAllocation	 = new modelCategoriesAllocation();
			$modelCategoriesAllocation -> addSelectColumns('tb_categories.*');
			$modelCategoriesAllocation -> addRelation('join', 'tb_categories', $conditionPages);
			$modelCategoriesAllocation	-> load($_pDatabase, $categorieAllocCondition);

			$sitemap[$nodeIndex] -> categories = &$modelCategoriesAllocation -> getResult();
		
			## Tags

			$tagAllocCondition 	 = new CModelCondition();
			$tagAllocCondition 	-> where('node_id', $node -> node_id);		

			$conditionPages = new CModelCondition();
			$conditionPages -> where('tb_tags_allocation.tag_id', 'tb_tags.tag_id');	

			$modelTagsAllocation	 = new modelTagsAllocation();
			$modelTagsAllocation -> addSelectColumns('tb_tags.*');
			$modelTagsAllocation -> addRelation('join', 'tb_tags', $conditionPages);
			$modelTagsAllocation	-> load($_pDatabase, $tagAllocCondition);
			
			$sitemap[$nodeIndex] -> tags = &$modelTagsAllocation -> getResult();
		}

		$this -> setView(	
						'view',	
						'',
						[
							'object' 	=> $this -> m_aObject,
							'sitemap'	=> $modelSitemap -> getResult()
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
		$sitemapCondition -> limit(1);

		$modelSitemap = new modelSitemap();
		$modelSitemap -> load($_pDatabase, $sitemapCondition);

		$sitemap = &$modelSitemap -> getResult();

		foreach($sitemap as $nodeIndex => $node)
		{
			##	Categories

			$categorieAllocCondition 	 = new CModelCondition();
			$categorieAllocCondition 	-> where('node_id', $node -> node_id);	
			
			$conditionPages = new CModelCondition();
			$conditionPages -> where('tb_categories_allocation.category_id', 'tb_categories.category_id');	

			$modelCategoriesAllocation	 = new modelCategoriesAllocation();
			$modelCategoriesAllocation -> addSelectColumns('tb_categories.*');
			$modelCategoriesAllocation -> addRelation('join', 'tb_categories', $conditionPages);
			$modelCategoriesAllocation	-> load($_pDatabase, $categorieAllocCondition);

			$sitemap[$nodeIndex] -> categories = &$modelCategoriesAllocation -> getResult();
		
			## Tags

			$tagAllocCondition 	 = new CModelCondition();
			$tagAllocCondition 	-> where('node_id', $node -> node_id);		

			$conditionPages = new CModelCondition();
			$conditionPages -> where('tb_tags_allocation.tag_id', 'tb_tags.tag_id');	

			$modelTagsAllocation	 = new modelTagsAllocation();
			$modelTagsAllocation -> addSelectColumns('tb_tags.*');
			$modelTagsAllocation -> addRelation('join', 'tb_tags', $conditionPages);
			$modelTagsAllocation	-> load($_pDatabase, $tagAllocCondition);
			
			$sitemap[$nodeIndex] -> tags = &$modelTagsAllocation -> getResult();

		}

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
				$sitemapCondition -> limit(1);

				$modelSitemap = new modelSitemap();
				$modelSitemap -> load($_pDatabase, $sitemapCondition);

				$sitemap = &$modelSitemap -> getResult();

				foreach($sitemap as $nodeIndex => $node)
				{
					##	Categories

					$categorieAllocCondition 	 = new CModelCondition();
					$categorieAllocCondition 	-> where('node_id', $node -> node_id);	
					
					$conditionPages = new CModelCondition();
					$conditionPages -> where('tb_categories_allocation.category_id', 'tb_categories.category_id');	

					$modelCategoriesAllocation	 = new modelCategoriesAllocation();
					$modelCategoriesAllocation -> addSelectColumns('tb_categories.*');
					$modelCategoriesAllocation -> addRelation('join', 'tb_categories', $conditionPages);
					$modelCategoriesAllocation	-> load($_pDatabase, $categorieAllocCondition);

					$sitemap[$nodeIndex] -> categories = &$modelCategoriesAllocation -> getResult();
				
					## Tags

					$tagAllocCondition 	 = new CModelCondition();
					$tagAllocCondition 	-> where('node_id', $node -> node_id);		

					$conditionPages = new CModelCondition();
					$conditionPages -> where('tb_tags_allocation.tag_id', 'tb_tags.tag_id');	

					$modelTagsAllocation	 = new modelTagsAllocation();
					$modelTagsAllocation -> addSelectColumns('tb_tags.*');
					$modelTagsAllocation -> addRelation('join', 'tb_tags', $conditionPages);
					$modelTagsAllocation	-> load($_pDatabase, $tagAllocCondition);
					
					$sitemap[$nodeIndex] -> tags = &$modelTagsAllocation -> getResult();

				}





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

										$modelCondition = new CModelCondition();
										$modelCondition -> where('object_id', $_aFormData['object_id']);

										#if($this -> m_modelSimple -> delete($_pDatabase, $modelCondition))
										if(true)
										{
											
											$_objectModel  	 = new modelPageObject();
											$_objectModel	-> delete($_pDatabase, $modelCondition);

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