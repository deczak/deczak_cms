<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSitemap.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPage.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelCategoriesAllocation.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelTagsAllocation.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelRedirect.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CHTAccess.php';	

class	controllerPages extends CController
{
	public function
	__construct( $_module, &$_object)
	{		
		parent::__construct($_module, $_object);

		CPageRequest::instance() -> subs = $this -> getSubSection();
	}
	
	public function
	logic(CDatabaseConnection &$_pDatabase, array $_rcaTarget, ?object $_xhrInfo, &$_logicResult) : bool
	{
		##	Set default target if not exists


		$controllerAction = $this -> getControllerAction_v2($_rcaTarget, $_xhrInfo, 'view');


			// temp .. needs a rewrite ...

		#if($controllerAction !== 'delete')
			$rca = (isset($_rcaTarget[$this -> objectInfo -> object_id]) ? $_rcaTarget[$this -> objectInfo -> object_id] : 'view');
		#else 
		#	$rca = $controllerAction;



		##	Check user rights for this target

		if(!$this -> detectRights($rca))
		{
			if($_xhrInfo !== null && $_xhrInfo -> action === 'update-site') // update-site check benötigt weil im bearbeitungs modus zwei controller actions erzeugt werden wenn ein modul bearbeitet wird.
			{
				$validationErr =	true;
				$validationMsg =	CLanguage::string('ERR_PERMISSON');
				$responseData = 	[];
		
				tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
			}

			CMessages::add(CLanguage::string('ERR_PERMISSON') , MSG_WARNING);
			return false;
		}


		## false edit if module gets edited, probably this needs additional fixes



		if($_xhrInfo !== null && $_xhrInfo -> isXHR && $_xhrInfo -> action !== 'update-site' && $_xhrInfo -> action !== 'delete' && $_xhrInfo -> action !== 'movesub' && $_xhrInfo -> action !== 'deletetree' && $_xhrInfo -> action !== 'create' && !empty($_GET['cms-edit-page-node']))
			$_xhrInfo -> action = 'view';
		


		if($_xhrInfo !== null && $_xhrInfo -> isXHR && $_xhrInfo -> objectId !== $this -> objectInfo -> object_id)
			$_xhrInfo -> action = 'view';


		if($_xhrInfo !== null && $_xhrInfo -> isXHR && $_xhrInfo -> action === 'update-site')
			$_xhrInfo -> action = 'edit';



		

		if($_xhrInfo !== null && $_xhrInfo -> isXHR && $_xhrInfo -> objectId === $this -> objectInfo -> object_id)
			$controllerAction = 'xhr_'. $_xhrInfo -> action;

		##	Call sub-logic function by target, if there results are false, we make a fall back to default view

		$enableEdit 	= $this -> existsUserRight('edit');
		$enableDelete	= $enableEdit;

		$logicDone = false;
		switch($controllerAction)
		{
			case 'xhr_view':
			case 'view': $logicDone = $this -> logicView($_pDatabase, $_logicResult, $enableEdit, $enableDelete); break;
				
			case 'xhr_create': $logicDone = $this -> logicXHRCreate($_pDatabase); break;
			case 'xhr_movesub': $logicDone = $this -> logicXHRMovesub($_pDatabase); break;


			case 'xhr_delete': $logicDone = $this -> logicXHRDelete($_pDatabase, $_xhrInfo); break;	
			case 'xhr_deletetree': $logicDone = $this -> logicXHRDeleteTree($_pDatabase, $_xhrInfo); break;	

			case 'xhr_edit'     : $logicDone = $this -> logicXHREdit($_pDatabase, $_xhrInfo); break;
			case 'xhr_index'    : 	  $logicDone = $this -> logicXHRIndex($_pDatabase, $enableEdit, $enableDelete);	break;
			case 'xhr_indexAlt' : $logicDone = $this -> logicXHRIndexAlternates($_pDatabase, $enableEdit, $enableDelete);	break;
		}

		if(!$logicDone)
		{
			##	Default View
			$logicDone = $this -> logicIndex($_pDatabase, $enableEdit, $enableDelete);	
		}

		return $logicDone;
	}

	private function
	logicIndex(CDatabaseConnection &$_pDatabase, $_xhrInfo, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{ 
		$pURLVariables	 =	new CURLVariables();
		$requestList		 =	[];
		$requestList[] 	 = 	[	"input" => "language", "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => 'en'  ];
		$pURLVariables -> retrieve($requestList, true, false); 
		$urlVarList		 = $pURLVariables ->getArray();

		$this -> setView(	
						'index',	
						'',
						[
							'language'	=>	$urlVarList['language'],
							'enableEdit'	=> $_enableEdit,
							'enableDelete'	=> $_enableDelete
						]
						);

		return true;
	}

	private function
	logicXHRIndex(CDatabaseConnection &$_pDatabase, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{ 
		$validationErr   = false;
		$validationMsg   = 'OK';
		$responseData    = [];

		$pURLVariables	 =	new CURLVariables();
		$requestList		 =	[];
		$requestList[] 	 = 	[	"input" => "language", "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => 'en'  ];
		$requestList[] 	 = 	[	"input" => "listtype", "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => 'structured'  ];
		$pURLVariables -> retrieve($requestList, false, true); 
		$urlVarList		 = $pURLVariables ->getArray();

		$modelCondition = new CModelCondition();
		$modelCondition -> where('page_language', $urlVarList['language']);
		$modelCondition -> where('page_path', '/');		

		$modelSitemap  = new modelSitemap();
		if(!$modelSitemap -> load($_pDatabase, $modelCondition))
		{
			$validationMsg .= CLanguage::string('ERR_SQL_ERROR');
			$validationErr = true;
		}											

		$responseData = [];

		if($urlVarList['listtype'] === 'structured')
			$this->getStructuredArray($modelSitemap -> getResult(), 0, 1, 0, $responseData);
		else
			$responseData = $modelSitemap -> getResult();

		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
	
		return false;
	}

	private function
	logicXHRIndexAlternates(CDatabaseConnection &$_pDatabase, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{ 
		$validationErr   = false;
		$validationMsg   = 'OK';
		$responseData    = [];

		$pURLVariables	 =	new CURLVariables();
		$requestList		 =	[];
		$requestList[] 	 = 	[	"input" => "page_id", "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => false  ];
		$pURLVariables -> retrieve($requestList, false, true); 
		$urlVarList		 = $pURLVariables ->getArray();

		$modelCondition = new CModelCondition();
		$modelCondition -> where('tb_page.page_id', $urlVarList['page_id']);		

		$modelPage  = new modelPage();
		if(!$modelPage -> load($_pDatabase, $modelCondition))
		{
			$validationMsg .= CLanguage::string('ERR_SQL_ERROR');
			$validationErr = true;
		}											

		$responseData = $modelPage -> getResult();

		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
	
		return false;
	}

	private function
	logicView(CDatabaseConnection &$_pDatabase, &$_logicResult, $_enableEdit = false, $_enableDelete = false)
	{
		$pURLVariables	 =	new CURLVariables();
		$requestList		 =	[];
		$requestList[] 	 = 	[	"input" => "cms-edit-page-lang", "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => 'en'  ];
		$requestList[] 	 = 	[	"input" => "cms-edit-page-node",   "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => false  ];
		$pURLVariables -> retrieve($requestList, true, false); 
		$urlVarList		 = $pURLVariables ->getArray();

		if($urlVarList['cms-edit-page-node'] !== false)
		{	
			/*
				If the node-id is set, we write the page data into the logicResult data
			*/

			$modelCondition = new CModelCondition();
			$modelCondition -> where('tb_page_path.node_id', $urlVarList['cms-edit-page-node']);		
		#	$modelCondition -> orderBy('tb_page.page_version', 'DESC');		
			$modelCondition -> limit(1);		

			$this -> m_modelPage  = new modelPage();
			$this -> m_modelPage -> load($_pDatabase, $modelCondition);

			if(empty($this -> m_modelPage -> getResult()))
				return false;

			$_logicResult['state']			=	1;
			$_logicResult['node_id']		=	$this -> m_modelPage -> getResult()[0] -> node_id;
			$_logicResult['page_version']	=	$this -> m_modelPage -> getResult()[0] -> page_version;
			$_logicResult['page_language']	=	$this -> m_modelPage -> getResult()[0] -> page_language;
			$_logicResult['enablePageEdit']	=	$_enableEdit;

			return true;
		}

		return false;
	}

	private function
	logicXHREdit(CDatabaseConnection &$_pDatabase, object $_xhrInfo) : bool
	{
		$pURLVariables	 =	new CURLVariables();
		$requestList		 =	[];
		$requestList[] 	 = 	[	"input" => "cms-edit-page-lang", "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => 'en'  ];
		$requestList[] 	 = 	[	"input" => "cms-edit-page-node",   "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => false  ];
		$pURLVariables -> retrieve($requestList, true, false); 
		$urlVarList		 = $pURLVariables ->getArray();


			$nodeId = $urlVarList['cms-edit-page-node'];

			$modelCondition = new CModelCondition();
			$modelCondition -> where('tb_page_path.node_id', $nodeId);		

			$this -> m_modelPage  = new modelPage();
			$this -> m_modelPage -> load($_pDatabase, $modelCondition);

			$_logicResult['state']			=	1;
			$_logicResult['node_id']		=	$this -> m_modelPage -> getResult()[0] -> node_id;
			$_logicResult['page_version']	=	$this -> m_modelPage -> getResult()[0] -> page_version;
			$_logicResult['page_language']	=	$this -> m_modelPage -> getResult()[0] -> page_language;



			$validationErr =	false;
			$validationMsg =	'';
			$responseData = 	[];

										$_pFormVariables =	new CURLVariables();
										$requestList		 =	[];
										$requestList[] 	 = 	[	"input" => "page_name",  		"validate" => "strip_tags|!empty" ]; 	
										$requestList[] 	 = 	[	"input" => "crumb_name",  		"validate" => "strip_tags|!empty" ]; 	
										$requestList[] 	 = 	[	"input" => "page_title",   		"validate" => "strip_tags|!empty" ]; 	
										$requestList[] 	 = 	[	"input" => "page_description",  "validate" => "strip_tags|!empty" ]; 	
										$requestList[] 	 = 	[	"input" => "page_template", 	"validate" => "strip_tags|!empty" ]; 	
										$requestList[] 	 = 	[	"input" => "hidden_state", 		"validate" => "strip_tags|!empty" ]; 	
										$requestList[] 	 = 	[	"input" => "cache_disabled", 	"validate" => "strip_tags|!empty" ]; 	
										$requestList[] 	 = 	[	"input" => "crawler_index", 	"validate" => "strip_tags|!empty" ]; 	
										$requestList[] 	 = 	[	"input" => "crawler_follow", 	"validate" => "strip_tags|!empty" ]; 	
										$requestList[] 	 = 	[	"input" => "menu_follow", 		"validate" => "strip_tags|!empty" ]; 	
										$requestList[] 	 = 	[	"input" => "page_id", 			"validate" => "strip_tags|!empty" , 		"use_default" => true, "default_value" => false ]; 	
										$requestList[] 	 = 	[	"input" => "page_image",		"validate" => "strip_tags|trim|!empty" , 	"use_default" => true, "default_value" => 0 ]; 	
										$requestList[] 	 = 	[	"input" => "publish_from",		"validate" => "strip_tags|trim|!empty" , 	"use_default" => true, "default_value" => 0 ]; 	
										$requestList[] 	 = 	[	"input" => "publish_until", 	"validate" => "strip_tags|trim|!empty" , 	"use_default" => true, "default_value" => 0 ]; 	
										$requestList[] 	 = 	[	"input" => "publish_expired", 	"validate" => "strip_tags|trim|!empty" , 	"use_default" => true, "default_value" => 0 ]; 	
										$requestList[] 	 = 	[	"input" => "page_auth", 		"validate" => "strip_tags|trim|!empty" , 	"use_default" => true, "default_value" => 0 ]; 	
										$requestList[] 	 = 	[	"input" => "apply_childs_auth", "validate" => "strip_tags|trim|!empty" , 	"use_default" => true, "default_value" => 0 ]; 	
										$requestList[] 	 = 	[	"input" => "page_categories", 	"validate" => "strip_tags|trim|!empty" , 	"use_default" => true, "default_value" => [] ]; 	
										$requestList[] 	 = 	[	"input" => "page_tags", 		"validate" => "strip_tags|trim|!empty" , 	"use_default" => true, "default_value" => [] ]; 	
										$requestList[] 	 = 	[	"input" => "page_redirect", 	"validate" => "strip_tags|trim|!empty" , 	"use_default" => true, "default_value" => '' ]; 	
										$_pFormVariables-> retrieve($requestList, false, true); // POST 
										$urlVarList		 = $_pFormVariables ->getArray();

										if(empty($urlVarList['page_name'])) 		{ 	$validationErr = true; 	$responseData['page_name'] = CLanguage::string('BEPE_PANEL_MSG_PAGENAME');			}
										if(empty($urlVarList['page_title'])) 		{ 	$validationErr = true; 	$responseData['page_title'] = CLanguage::string('BEPE_PANEL_MSG_PAGETITLE');	}
									#	if(empty($urlVarList['page_description'])) 	{ 	$validationErr = true; 	$responseData['page_description'] = 'Not valid value'; 	}
										if(empty($urlVarList['page_template'])) 	{ 	$validationErr = true; 	$responseData['page_template'] = CLanguage::string('BEPE_PANEL_MSG_PAGETEMPLATE');	}

										if(!isset($urlVarList['hidden_state']) || strlen($urlVarList['hidden_state']) == 0) 	{ 	$validationErr = true; 	$responseData['hidden_state'] 	= 'Not valid value'; 	}
										if(!isset($urlVarList['cache_disabled']) || strlen($urlVarList['cache_disabled']) == 0) { 	$validationErr = true; 	$responseData['cache_disabled'] 	= 'Not valid value'; 	}
										if(!isset($urlVarList['crawler_index']) || strlen($urlVarList['crawler_index']) == 0) 	{ 	$validationErr = true; 	$responseData['crawler_index'] 	= 'Not valid value'; 	}
										if(!isset($urlVarList['crawler_follow']) || strlen($urlVarList['crawler_follow']) == 0) { 	$validationErr = true; 	$responseData['crawler_follow'] 	= 'Not valid value'; 	}
										if(!isset($urlVarList['menu_follow']) || strlen($urlVarList['menu_follow']) == 0) 		{	$validationErr = true; 	$responseData['menu_follow'] 	= 'Not valid value'; 	}

										##	Set off publish expiration if start is not set (keep structure clear)
										if(empty($urlVarList['publish_from']))
										{
											$urlVarList['publish_from']		= 0;
											$urlVarList['publish_until']	= 0;
											$urlVarList['publish_expired']	= 0;
										}

										if($urlVarList['publish_from']  != 0) { $urlVarList['publish_from']  = strtotime($urlVarList['publish_from']);  }
										if($urlVarList['publish_until'] != 0) { $urlVarList['publish_until'] = strtotime($urlVarList['publish_until']) + 79200; }


										if($urlVarList['publish_until'] != 0 && $urlVarList['publish_until'] < $urlVarList['publish_from'])
										{
											$validationErr = true;
											$responseData['publish_until'] = CLanguage::string('BEPE_PANEL_MSG_PUBEXPIRE');	
										}

										##	checking of languages to prevent alternative links to same language

										if($urlVarList['page_id'] !== false)
										{
											$pageCondition 	 = new CModelCondition();
											$pageCondition 	-> where('tb_page_path.page_id', $urlVarList['page_id']);		

											$pageCheck  	 = new modelPage();
											$pageCheck 		-> load($_pDatabase, $pageCondition);

											foreach($pageCheck -> getResult() as $pageItm)
											{
												if($pageItm -> page_language === $_logicResult['page_language'])
												{
													$urlVarList['page_id'] 		= false;
													$responseData['page_id'] = CLanguage::string('BEPE_PANEL_MSG_ALTLANGEVEN');
													break;
												}
											}
										}
										
										if(!$validationErr)
										{
											$urlVarList['node_id'] 			= $_logicResult['node_id'];
											$urlVarList['page_version'] 	= $_logicResult['page_version'];
											$urlVarList['page_language'] 	= $_logicResult['page_language'];

											$urlVarList['update_time']	= time();
											$urlVarList['update_by']		= CSession::instance() -> getValue('user_id');
											$urlVarList['update_reason']	= '';


											##	unset page-id if it contains false

											if($urlVarList['page_id'] === false)
												unset($urlVarList['page_id']);

											##	apply auth settings to target node and child nodes
											
											if(intval($urlVarList['apply_childs_auth']) === 1)
											{
												$authFields = [];
												$authFields['page_auth'] = $urlVarList['page_auth'];

												$authCondition = new CModelCondition();
												$authCondition -> where('node_id', $nodeId );	
												$this -> m_modelPage -> updateChilds($_pDatabase, $authFields, $authCondition);
											}

											unset($urlVarList['apply_childs_auth']);

											$condition 			 = new CModelCondition(); // Null Condition, model creates own instances

											if($this -> m_modelPage -> update($_pDatabase, $urlVarList, $condition))
											{
												## update categorie allocations

												$categorieAllocCondition 	 = new CModelCondition();
												$categorieAllocCondition 	-> where('node_id', $nodeId);		

												$modelCategoriesAllocation	 = new modelCategoriesAllocation();
												$modelCategoriesAllocation	-> delete($_pDatabase, $categorieAllocCondition);

												foreach($urlVarList['page_categories'] as $categorie)
												{
													$alloc_id = 0;
													$newAlloc = ["category_id" => $categorie, "node_id" => $nodeId];
													$modelCategoriesAllocation	-> insert($_pDatabase, $newAlloc, $alloc_id);
												}

												## update tag allocations

												$tagAllocCondition 	 = new CModelCondition();
												$tagAllocCondition 	-> where('node_id', $nodeId);		

												$modelTagsAllocation	 = new modelTagsAllocation();
												$modelTagsAllocation	-> delete($_pDatabase, $tagAllocCondition);

												foreach($urlVarList['page_tags'] as $tag)
												{
													$alloc_id = 0;
													$newAlloc = ["tag_id" => $tag, "node_id" => $nodeId];
													$modelTagsAllocation	-> insert($_pDatabase, $newAlloc, $alloc_id);
												}

												## update redirection

												$redirectCondition 	 = new CModelCondition();
												$redirectCondition 	-> where('node_id', $nodeId);	

												$modelRedirect	 = new modelRedirect();
												$modelRedirect	-> delete($_pDatabase, $redirectCondition);

												if(!empty($urlVarList['page_redirect']))
												{
													$redirect_id = 0;
													$newRedirect["node_id"] 		= $nodeId;
													$newRedirect["redirect_target"]	= $urlVarList['page_redirect'];
													$newRedirect["create_time"] 	= time();
													$newRedirect["create_by"]	 	= CSession::instance() -> getValue('user_id');
													$modelRedirect				   -> insert($_pDatabase, $newRedirect, $redirect_id);
												}

												## update htaccess and sitemap

												$_pHTAccess  = new CHTAccess();
												$_pHTAccess -> generatePart4Frontend($_pDatabase);
												$_pHTAccess -> writeHTAccess($_pDatabase);

												$sitemap  	 = new CXMLSitemap();
												$sitemap 	-> generate($_pDatabase);												
											}
											else
											{
												$validationMsg .= CLanguage::string('ERR_SQL_ERROR');
												$validationErr = true;
											}											
										}
										else	// Validation Failed
										{
											$validationMsg .= CLanguage::string('ERR_VALIDATIONFAIL');
											$validationErr = true;
										}

		
			
			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call


		return true;
	}

	private function
	logicXHRCreate(CDatabaseConnection &$_pDatabase) : bool
	{
		$validationErr =	false;
		$validationMsg =	'';
		$responseData = 	[];

		$pURLVariables	 =	new CURLVariables();
		$requestList		 =	[];
		$requestList[] 	 = 	[	"input" => "cms-edit-page-lang", "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => 'en'  ];
		$requestList[] 	 = 	[	"input" => "cms-edit-page-node",   "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => false  ];
		$pURLVariables -> retrieve($requestList, true, false); 

		$requestList[] 	 = 	[	"input" => "page_name",   "validate" => "trim|strip_tags|!empty", 	"use_default" => true, "default_value" => false  ];
		$requestList[] 	 = 	[	"input" => "page_description",   "validate" => "trim|strip_tags|!empty", 	"use_default" => true, "default_value" => false  ];
		$pURLVariables -> retrieve($requestList, false, true); 
		$urlVarList		 = $pURLVariables ->getArray();

		$urlVarList['page_name'] = (empty($urlVarList['page_name']) ? CLanguage::string('MOD_SITES_NEWPAGE_NAME') : $urlVarList['page_name']);
		$urlVarList['page_template'] = 'default';
		$urlVarList['page_description'] = (!empty($urlVarList['page_description']) ? $urlVarList['page_description'] : '');

		$urlVarList['hidden_state']	=	4;

		$urlVarList['create_time']	=	time();
		$urlVarList['create_by']		= CSession::instance() -> getValue('user_id');

		$this -> m_modelPage  = new modelPage();

		if($this -> m_modelPage -> insert($_pDatabase, $urlVarList))
		{
			$_pHTAccess  = new CHTAccess();
			$_pHTAccess -> generatePart4Frontend($_pDatabase);
			$_pHTAccess -> writeHTAccess($_pDatabase);

			$sitemap  	 = new CXMLSitemap();
			$sitemap 	-> generate($_pDatabase);	

			CMessages::add(CLanguage::string('MOD_SITES_PAGECREATED') , MSG_OK);
			$validationMsg = CLanguage::string('MOD_SITES_PAGECREATED');
		}
		else
		{
			CMessages::add(CLanguage::string('MOD_SITES_ERR_NOTCREATED') , MSG_WARNING);
			$validationMsg = CLanguage::string('MOD_SITES_ERR_NOTCREATED');
		}
		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		return false;
	}


	
	private function
	logicXHRMovesub(CDatabaseConnection &$_pDatabase) : bool
	{
		$validationErr =	false;
		$validationMsg =	'';
		$responseData = 	[];

		$pURLVariables	 =	new CURLVariables();
		$requestList		 =	[];
		$requestList[] 	 = 	[	"input" => "cms-edit-page-node",   "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => false  ];
		$pURLVariables -> retrieve($requestList, true, false); 

		$requestList[] 	 = 	[	"input" => "new-parent-node-id",   "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => false  ];
		$pURLVariables -> retrieve($requestList, false, true); 
		$urlVarList		 = $pURLVariables ->getArray();

		$modelCondition = new CModelCondition();
		$modelCondition -> where('node_id', $urlVarList['cms-edit-page-node']);		
		


		$this -> m_modelPage  = new modelPage();

		$this -> m_modelPage -> move($_pDatabase, $urlVarList['cms-edit-page-node'], $urlVarList['new-parent-node-id']);





		/*
		
				Bewege 		$urlVarList['cms-edit-page-node']			zu 			$urlVarList['new-parent-node-id']
		
		

				->	von der Zielparent Seite das letzte child finden

				->	right vom letzten child +1 ist das neue left der zu verschiebenen seite

				->	die menge der childs der zu verschiebenden seite +1 ergibt das neue right der zu verschiebenen seite

				-> 	alle seiten ab right Zielparent müssen um die menge verschoben werden die bei der zuverschiebenden seite vorhanden sind 

				->	dem ehemaligem parent muss dann die lücke wieder geschlossen werden durch die 


				-----

				->	Daten der zu verschiebenen Seite abrufen mit left und right

				-> 	Daten der neuen parent Seite abrufen mit left und right

				->	Menge der Punkte ermitteln für die Korrekturen




		
		
		*/


		$_pHTAccess  = new CHTAccess();
		$_pHTAccess -> generatePart4Frontend($_pDatabase);
		$_pHTAccess -> writeHTAccess($_pDatabase);

		$sitemap  	 = new CXMLSitemap();
		$sitemap 	-> generate($_pDatabase);	



		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		return false;
	}	

	private function
	logicXHRDelete(CDatabaseConnection &$_pDatabase, object $_xhrInfo) : bool
	{
		$validationErr =	false;
		$validationMsg =	'';
		$responseData = 	[];

		$pURLVariables	 =	new CURLVariables();
		$requestList		 =	[];
		$requestList[] 	 = 	[	"input" => "cms-edit-page-node",   "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => false  ];
		$pURLVariables -> retrieve($requestList, true, false); 
		$urlVarList		 = $pURLVariables ->getArray();

		$modelCondition = new CModelCondition();
		$modelCondition -> where('node_id', $urlVarList['cms-edit-page-node']);		
		
		$this -> m_modelPage  = new modelPage();
		if($this -> m_modelPage -> delete($_pDatabase, $modelCondition))
		{
			$_pHTAccess  = new CHTAccess();
			$_pHTAccess -> generatePart4Frontend($_pDatabase);
			$_pHTAccess -> writeHTAccess($_pDatabase);

			$sitemap  	 = new CXMLSitemap();
			$sitemap 	-> generate($_pDatabase);	

			#CMessages::add(CLanguage::string('MOD_SITES_PAGEDELETED') , MSG_OK);
			$validationMsg = CLanguage::string('MOD_SITES_PAGEDELETED');
		}
		else
		{
			#CMessages::add(CLanguage::string('MOD_SITES_ERR_NOTDELETED') , MSG_WARNING);
			$validationMsg = CLanguage::string('MOD_SITES_ERR_NOTDELETED');
		}

		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		return false;
	}	

	private function
	logicXHRDeleteTree(CDatabaseConnection &$_pDatabase, object $_xhrInfo) : bool
	{
		$validationErr =	false;
		$validationMsg =	'';
		$responseData = 	[];

		$pURLVariables	 =	new CURLVariables();
		$requestList		 =	[];
		$requestList[] 	 = 	[	"input" => "cms-edit-page-node",   "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => false  ];
		$pURLVariables -> retrieve($requestList, true, false); 
		$urlVarList		 = $pURLVariables ->getArray();

		$modelCondition = new CModelCondition();
		$modelCondition -> where('node_id', $urlVarList['cms-edit-page-node']);	

		$this -> m_modelPage  = new modelPage();
		if($this -> m_modelPage -> deleteTree($_pDatabase, $modelCondition))
		{
			$_pHTAccess  = new CHTAccess();
			$_pHTAccess -> generatePart4Frontend($_pDatabase);
			$_pHTAccess -> writeHTAccess($_pDatabase);

			$sitemap  	 = new CXMLSitemap();
			$sitemap 	-> generate($_pDatabase);	
			
			#CMessages::add(CLanguage::string('MOD_SITES_PAGEDELETED') , MSG_OK);
			$validationMsg = CLanguage::string('MOD_SITES_PAGEDELETED');
		}
		else
		{
			#CMessages::add(CLanguage::string('MOD_SITES_ERR_NOTDELETED') , MSG_WARNING);
			$validationMsg = CLanguage::string('MOD_SITES_ERR_NOTDELETED');
		}

		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		return false;
	}	


	private function getStructuredArray(array $sitemapList, int $index, int $level, int $parentIndex, array &$resultArray)
	{

		$parentIndex = $index;
		for($i = $index; $i < count($sitemapList); $i++)
		{
			if($sitemapList[$i] -> level === $level)
			{
				$resultArray[$i] = $sitemapList[$i];
				$parentIndex = $i;
			}
			elseif($sitemapList[$i] -> level > $level)
			{
				if(!property_exists($resultArray[$parentIndex], 'childnodes'))
					$resultArray[$parentIndex] -> childnodes = [];

				$i = $this -> getStructuredArray($sitemapList, $i, $sitemapList[$i] -> level, $i, $resultArray[$parentIndex] -> childnodes);
				$i--;
			}
			elseif($sitemapList[$i] -> level < $level)
			{
				break;
			}
		}

		return $i;
	}


}
