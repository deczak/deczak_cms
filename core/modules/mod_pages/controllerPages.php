<?php


include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSitemap.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPage.php';	

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelCategoriesAllocation.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelTagsAllocation.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelRedirect.php';	

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CHTAccess.php';	


class	controllerPages extends CController
{

	private		$m_modelSitemap;

	public function
	__construct( $_module, &$_object)
	{		
		parent::__construct($_module, $_object);

		CPageRequest::instance() -> subs = $this -> getSubSection();
	}
	
	public function
	logic(&$_sqlConnection, array $_rcaTarget, $_isXHRequest, &$_logicResult)
	{
		##	Set default target if not exists
	
		$_controllerAction = $this -> getControllerAction($_rcaTarget,'view');

		##	Check user rights for this target

		if(!$this -> detectRights($_controllerAction))
		{
			if($_isXHRequest !== false && $_isXHRequest === 'update-site') // update-site check benötigt weil im bearbeitungs modus zwei controller actions erzeugt werden wenn ein modul bearbeitet wird.
			{
				$_bValidationErr =	true;
				$_bValidationMsg =	CLanguage::get() -> string('ERR_PERMISSON');
				$_bValidationDta = 	[];
		
				tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
			}

			CMessages::instance() -> addMessage(CLanguage::get() -> string('ERR_PERMISSON') , MSG_WARNING);
			return;
		}

		##	Call sub-logic function by target, if there results are false, we make a fall back to default view

		$enableEdit 	= $this -> existsUserRight('edit');
		$enableDelete	= $enableEdit;

		switch($_controllerAction)
		{
			case 'view'			: $_logicResults = $this -> logicView($_sqlConnection, $_isXHRequest, $_logicResult, $enableEdit, $enableDelete); break;
			case 'edit'			: $_logicResults = $this -> logicEdit($_sqlConnection, $_isXHRequest); break;	
			case 'create'		: $_logicResults = $this -> logicCreate($_sqlConnection, $_isXHRequest); break;
			case 'delete'		: $_logicResults = $this -> logicDelete($_sqlConnection, $_isXHRequest); break;	
			case 'deletetree'	: $_logicResults = $this -> logicDeleteTree($_sqlConnection, $_isXHRequest); break;	
		}

		if(!$_logicResults)
		{
			##	Default View
			$this -> logicIndex($_sqlConnection, $_isXHRequest, $enableEdit, $enableDelete);
		}
	}

	private function
	logicIndex(&$_sqlConnection, $_isXHRequest, $_enableEdit = false, $_enableDelete = false)
	{
		##	XHR request
	
		if($_isXHRequest !== false)
		{	
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			switch($_isXHRequest)
			{
				case 'raw-data'  :	// Request raw data

									$_pURLVariables	 =	new CURLVariables();
									$_request		 =	[];
									$_request[] 	 = 	[	"input" => "language", "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => 'en'  ];
									$_pURLVariables -> retrieve($_request, false, true); 
									$_aFormData		 = $_pURLVariables ->getArray();

									$modelCondition = new CModelCondition();
									$modelCondition -> where('page_language', $_aFormData['language']);
									$modelCondition -> where('page_path', '/');		

									$this -> m_modelSitemap  = new modelSitemap();
									if(!$this -> m_modelSitemap -> load($_sqlConnection, $modelCondition))
									{
										$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
										$_bValidationErr = true;
									}											
						
									$data = $this -> m_modelSitemap -> getDataInstance();

									break;

				case 'raw-alternate' :	// Request raw alternates version

									$_pURLVariables	 =	new CURLVariables();
									$_request		 =	[];
									$_request[] 	 = 	[	"input" => "page_id", "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => false  ];
									$_pURLVariables -> retrieve($_request, false, true); 
									$_aFormData		 = $_pURLVariables ->getArray();

									$modelCondition = new CModelCondition();
									$modelCondition -> where('tb_page.page_id', $_aFormData['page_id']);		

									$modelPage  = new modelPage();
									if(!$modelPage -> loadOld($_sqlConnection, $modelCondition))
									{
										$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
										$_bValidationErr = true;
									}											
						
									$data = $modelPage -> getDataInstance();

									break;
			}

			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $data);	// contains exit call
		}

		##	Non XHR request

		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "language", "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => 'en'  ];
		$_pURLVariables -> retrieve($_request, true, false); 
		$_aFormData		 = $_pURLVariables ->getArray();

		$this -> setView(	
						'index',	
						'',
						[
							'language'	=>	$_aFormData['language'],
							'enableEdit'	=> $_enableEdit,
							'enableDelete'	=> $_enableDelete
						]
						);
	}

	private function
	logicView(&$_sqlConnection, $_isXHRequest, &$_logicResult, $_enableEdit = false, $_enableDelete = false)
	{
		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "cms-edit-page-lang", "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => 'en'  ];
		$_request[] 	 = 	[	"input" => "cms-edit-page-node",   "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => false  ];
		$_pURLVariables -> retrieve($_request, true, false); 
		$_aFormData		 = $_pURLVariables ->getArray();

		if($_aFormData['cms-edit-page-node'] !== false)
		{	
			/*
				If the node-id is set, we write the page data into the logicResult data
			*/

			$modelCondition = new CModelCondition();
			$modelCondition -> where('tb_page_path.node_id', $_aFormData['cms-edit-page-node']);		
		#	$modelCondition -> orderBy('tb_page.page_version', 'DESC');		
			$modelCondition -> limit(1);		

			$this -> m_modelPage  = new modelPage();
			$this -> m_modelPage -> loadOld($_sqlConnection, $modelCondition);

			$_logicResult['state']			=	1;
			$_logicResult['node_id']		=	$this -> m_modelPage -> getDataInstance()[0] -> node_id;
			$_logicResult['page_version']	=	$this -> m_modelPage -> getDataInstance()[0] -> page_version;
			$_logicResult['page_language']	=	$this -> m_modelPage -> getDataInstance()[0] -> page_language;
			$_logicResult['enablePageEdit']	=	$_enableEdit;

			return true;
		}

		return false;
	}

	private function
	logicEdit(&$_sqlConnection, $_isXHRequest)
	{
		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "cms-edit-page-lang", "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => 'en'  ];
		$_request[] 	 = 	[	"input" => "cms-edit-page-node",   "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => false  ];
		$_pURLVariables -> retrieve($_request, true, false); 
		$_aFormData		 = $_pURLVariables ->getArray();

		##	XHR Function call

		if($_isXHRequest !== false && $_isXHRequest === 'update-site' && $_aFormData['cms-edit-page-node'] !== false)
		{
			$nodeId = $_aFormData['cms-edit-page-node'];

			$modelCondition = new CModelCondition();
			$modelCondition -> where('tb_page_path.node_id', $nodeId);		

			$this -> m_modelPage  = new modelPage();
			$this -> m_modelPage -> loadOld($_sqlConnection, $modelCondition);

			$_logicResult['state']			=	1;
			$_logicResult['node_id']		=	$this -> m_modelPage -> getDataInstance()[0] -> node_id;
			$_logicResult['page_version']	=	$this -> m_modelPage -> getDataInstance()[0] -> page_version;
			$_logicResult['page_language']	=	$this -> m_modelPage -> getDataInstance()[0] -> page_language;



			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			switch($_isXHRequest)
			{
				case 'update-site'  :	// Update site data

										$_pFormVariables =	new CURLVariables();
										$_request		 =	[];
										$_request[] 	 = 	[	"input" => "page_name",  		"validate" => "strip_tags|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "page_title",   		"validate" => "strip_tags|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "page_description",  "validate" => "strip_tags|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "page_template", 	"validate" => "strip_tags|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "hidden_state", 		"validate" => "strip_tags|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "cache_disabled", 	"validate" => "strip_tags|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "crawler_index", 	"validate" => "strip_tags|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "crawler_follow", 	"validate" => "strip_tags|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "menu_follow", 		"validate" => "strip_tags|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "page_id", 			"validate" => "strip_tags|!empty" , 		"use_default" => true, "default_value" => false ]; 	
										$_request[] 	 = 	[	"input" => "publish_from",		"validate" => "strip_tags|trim|!empty" , 	"use_default" => true, "default_value" => 0 ]; 	
										$_request[] 	 = 	[	"input" => "publish_until", 	"validate" => "strip_tags|trim|!empty" , 	"use_default" => true, "default_value" => 0 ]; 	
										$_request[] 	 = 	[	"input" => "publish_expired", 	"validate" => "strip_tags|trim|!empty" , 	"use_default" => true, "default_value" => 0 ]; 	
										$_request[] 	 = 	[	"input" => "page_auth", 		"validate" => "strip_tags|trim|!empty" , 	"use_default" => true, "default_value" => 0 ]; 	
										$_request[] 	 = 	[	"input" => "apply_childs_auth", "validate" => "strip_tags|trim|!empty" , 	"use_default" => true, "default_value" => 0 ]; 	
										$_request[] 	 = 	[	"input" => "page_categories", 	"validate" => "strip_tags|trim|!empty" , 	"use_default" => true, "default_value" => [] ]; 	
										$_request[] 	 = 	[	"input" => "page_tags", 		"validate" => "strip_tags|trim|!empty" , 	"use_default" => true, "default_value" => [] ]; 	
										$_request[] 	 = 	[	"input" => "page_redirect", 	"validate" => "strip_tags|trim|!empty" , 	"use_default" => true, "default_value" => '' ]; 	
										$_pFormVariables-> retrieve($_request, false, true); // POST 
										$_aFormData		 = $_pFormVariables ->getArray();

										if(empty($_aFormData['page_name'])) 		{ 	$_bValidationErr = true; 	$_bValidationDta['page_name'] = CLanguage::get() -> string('BEPE_PANEL_MSG_PAGENAME');			}
										if(empty($_aFormData['page_title'])) 		{ 	$_bValidationErr = true; 	$_bValidationDta['page_title'] = CLanguage::get() -> string('BEPE_PANEL_MSG_PAGETITLE');	}
									#	if(empty($_aFormData['page_description'])) 	{ 	$_bValidationErr = true; 	$_bValidationDta['page_description'] = 'Not valid value'; 	}
										if(empty($_aFormData['page_template'])) 	{ 	$_bValidationErr = true; 	$_bValidationDta['page_template'] = CLanguage::get() -> string('BEPE_PANEL_MSG_PAGETEMPLATE');	}

										if(!isset($_aFormData['hidden_state']) || strlen($_aFormData['hidden_state']) == 0) 	{ 	$_bValidationErr = true; 	$_bValidationDta['hidden_state'] 	= 'Not valid value'; 	}
										if(!isset($_aFormData['cache_disabled']) || strlen($_aFormData['cache_disabled']) == 0) { 	$_bValidationErr = true; 	$_bValidationDta['cache_disabled'] 	= 'Not valid value'; 	}
										if(!isset($_aFormData['crawler_index']) || strlen($_aFormData['crawler_index']) == 0) 	{ 	$_bValidationErr = true; 	$_bValidationDta['crawler_index'] 	= 'Not valid value'; 	}
										if(!isset($_aFormData['crawler_follow']) || strlen($_aFormData['crawler_follow']) == 0) { 	$_bValidationErr = true; 	$_bValidationDta['crawler_follow'] 	= 'Not valid value'; 	}
										if(!isset($_aFormData['menu_follow']) || strlen($_aFormData['menu_follow']) == 0) 		{	$_bValidationErr = true; 	$_bValidationDta['menu_follow'] 	= 'Not valid value'; 	}

										##	Set off publish expiration if start is not set (keep structure clear)
										if(empty($_aFormData['publish_from']))
										{
											$_aFormData['publish_from']		= 0;
											$_aFormData['publish_until']	= 0;
											$_aFormData['publish_expired']	= 0;
										}

										if($_aFormData['publish_from']  != 0) { $_aFormData['publish_from']  = strtotime($_aFormData['publish_from']);  }
										if($_aFormData['publish_until'] != 0) { $_aFormData['publish_until'] = strtotime($_aFormData['publish_until']) + 79200; }


										if($_aFormData['publish_until'] != 0 && $_aFormData['publish_until'] < $_aFormData['publish_from'])
										{
											$_bValidationErr = true;
											$_bValidationDta['publish_until'] = CLanguage::get() -> string('BEPE_PANEL_MSG_PUBEXPIRE');	
										}





										##	checking of languages to prevent alternative links to same language

										if($_aFormData['page_id'] !== false)
										{
											$pageCondition 	 = new CModelCondition();
											$pageCondition 	-> where('tb_page_path.page_id', $_aFormData['page_id']);		

											$pageCheck  	 = new modelPage();
											$pageCheck 		-> loadOld($_sqlConnection, $pageCondition);

											foreach($pageCheck -> getDataInstance() as $pageItm)
											{
												if($pageItm -> page_language === $_logicResult['page_language'])
												{
													$_aFormData['page_id'] 		= false;
													$_bValidationDta['page_id'] = CLanguage::get() -> string('BEPE_PANEL_MSG_ALTLANGEVEN');
													break;
												}
											}
										}



										if(!$_bValidationErr)
										{
											$_aFormData['node_id'] 			= $_logicResult['node_id'];
											$_aFormData['page_version'] 	= $_logicResult['page_version'];
											$_aFormData['page_language'] 	= $_logicResult['page_language'];

											$_aFormData['update_time']	= time();
											$_aFormData['update_by']		= CSession::instance() -> getValue('user_id');
											$_aFormData['update_reason']	= '';


											##	unset page-id if it contains false

											if($_aFormData['page_id'] === false)
												unset($_aFormData['page_id']);

											##	apply auth settings to target node and child nodes
											
											if(intval($_aFormData['apply_childs_auth']) === 1)
											{
												$authFields = [];
												$authFields['page_auth'] = $_aFormData['page_auth'];

												$authCondition = new CModelCondition();
												$authCondition -> where('node_id', $nodeId );	
												$this -> m_modelPage -> updateChilds($_sqlConnection, $authFields, $authCondition);
											}

											unset($_aFormData['apply_childs_auth']);

											if($this -> m_modelPage -> updateOld($_sqlConnection, $_aFormData))
											{
												## update categorie allocations

												$categorieAllocCondition 	 = new CModelCondition();
												$categorieAllocCondition 	-> where('node_id', $nodeId);		

												$modelCategoriesAllocation	 = new modelCategoriesAllocation();
												$modelCategoriesAllocation	-> delete($_sqlConnection, $categorieAllocCondition);

												foreach($_aFormData['page_categories'] as $categorie)
												{
													$alloc_id = 0;
													$newAlloc = ["category_id" => $categorie, "node_id" => $nodeId];
													$modelCategoriesAllocation	-> insert($_sqlConnection, $newAlloc, $alloc_id);
												}

												## update tag allocations

												$tagAllocCondition 	 = new CModelCondition();
												$tagAllocCondition 	-> where('node_id', $nodeId);		

												$modelTagsAllocation	 = new modelTagsAllocation();
												$modelTagsAllocation	-> delete($_sqlConnection, $tagAllocCondition);

												foreach($_aFormData['page_tags'] as $tag)
												{
													$alloc_id = 0;
													$newAlloc = ["tag_id" => $tag, "node_id" => $nodeId];
													$modelTagsAllocation	-> insert($_sqlConnection, $newAlloc, $alloc_id);
												}

												## update redirection

												$redirectCondition 	 = new CModelCondition();
												$redirectCondition 	-> where('node_id', $nodeId);	

												$modelRedirect	 = new modelRedirect();
												$modelRedirect	-> delete($_sqlConnection, $redirectCondition);

												if(!empty($_aFormData['page_redirect']))
												{
													$redirect_id = 0;
													$newRedirect["node_id"] 		= $nodeId;
													$newRedirect["redirect_target"]	= $_aFormData['page_redirect'];
													$newRedirect["create_time"] 	= time();
													$newRedirect["create_by"]	 	= CSession::instance() -> getValue('user_id');
													$modelRedirect				   -> insert($_sqlConnection, $newRedirect, $redirect_id);
												}

												## update htaccess and sitemap

												$_pHTAccess  = new CHTAccess();
												$_pHTAccess -> generatePart4Frontend($_sqlConnection);
												$_pHTAccess -> writeHTAccess();

												$sitemap  	 = new CXMLSitemap();
												$sitemap 	-> generate($_sqlConnection);												
											}
											else
											{
												$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
												$_bValidationErr = true;
											}											
										}
										else	// Validation Failed
										{
											$_bValidationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
											$_bValidationErr = true;
										}

										break;
			}
			
			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call

		}

		return true;
	}

	private function
	logicCreate(&$_sqlConnection, $_isXHRequest)
	{
		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "cms-edit-page-lang", "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => 'en'  ];
		$_request[] 	 = 	[	"input" => "cms-edit-page-node",   "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => false  ];
		$_pURLVariables -> retrieve($_request, true, false); 
		$_aFormData		 = $_pURLVariables ->getArray();

		$_aFormData['page_name'] = CLanguage::instance() -> getString('MOD_SITES_NEWPAGE_NAME');
		$_aFormData['page_template'] = 'default';

		$_aFormData['create_time']	=	time();
		$_aFormData['create_by']		= CSession::instance() -> getValue('user_id');

		$this -> m_modelPage  = new modelPage();

		$nodeId = 0;

		if($this -> m_modelPage -> insert($_sqlConnection, $_aFormData, $nodeId))
		{
			$_pHTAccess  = new CHTAccess();
			$_pHTAccess -> generatePart4Frontend($_sqlConnection);
			$_pHTAccess -> writeHTAccess();

			$sitemap  	 = new CXMLSitemap();
			$sitemap 	-> generate($_sqlConnection);	

			CMessages::instance() -> addMessage(CLanguage::instance() -> getString('MOD_SITES_PAGECREATED') , MSG_OK);
		}
		else
		{
			CMessages::instance() -> addMessage(CLanguage::instance() -> getString('MOD_SITES_ERR_NOTCREATED') , MSG_WARNING);
		}
		return false;
	}

	private function
	logicDelete(&$_sqlConnection, $_isXHRequest)
	{
		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "cms-edit-page-node",   "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => false  ];
		$_pURLVariables -> retrieve($_request, true, false); 
		$_aFormData		 = $_pURLVariables ->getArray();

		$modelCondition = new CModelCondition();
		$modelCondition -> where('node_id', $_aFormData['cms-edit-page-node']);		
		
		$this -> m_modelPage  = new modelPage();
		if($this -> m_modelPage -> delete($_sqlConnection, $modelCondition))
		{
			$_pHTAccess  = new CHTAccess();
			$_pHTAccess -> generatePart4Frontend($_sqlConnection);
			$_pHTAccess -> writeHTAccess();

			$sitemap  	 = new CXMLSitemap();
			$sitemap 	-> generate($_sqlConnection);	

			CMessages::instance() -> addMessage(CLanguage::instance() -> getString('MOD_SITES_PAGEDELETED') , MSG_OK);
		}
		else
		{
			CMessages::instance() -> addMessage(CLanguage::instance() -> getString('MOD_SITES_ERR_NOTDELETED') , MSG_WARNING);
		}
		return false;
	}	

	private function
	logicDeleteTree(&$_sqlConnection, $_isXHRequest)
	{
		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "cms-edit-page-node",   "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => false  ];
		$_pURLVariables -> retrieve($_request, true, false); 
		$_aFormData		 = $_pURLVariables ->getArray();

		$modelCondition = new CModelCondition();
		$modelCondition -> where('node_id', $_aFormData['cms-edit-page-node']);	

		$this -> m_modelPage  = new modelPage();
		if($this -> m_modelPage -> deleteTree($_sqlConnection, $modelCondition))
		{
			$_pHTAccess  = new CHTAccess();
			$_pHTAccess -> generatePart4Frontend($_sqlConnection);
			$_pHTAccess -> writeHTAccess();

			$sitemap  	 = new CXMLSitemap();
			$sitemap 	-> generate($_sqlConnection);	
			
			CMessages::instance() -> addMessage(CLanguage::instance() -> getString('MOD_SITES_PAGEDELETED') , MSG_OK);
		}
		else
		{
			CMessages::instance() -> addMessage(CLanguage::instance() -> getString('MOD_SITES_ERR_NOTDELETED') , MSG_WARNING);
		}
		return false;
	}	



}

?>