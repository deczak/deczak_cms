<?php


include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSitemap.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPage.php';	

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CHTAccess.php';	


class	controllerSites extends CController
{


	private		$m_modelSitemap;

	public function
	__construct(array $_module, &$_object)
	{		
		parent::__construct($_module, $_object);
	}
	
	public function
	logic(&$_sqlConnection, array $_rcaTarget, array $_userRights, $_isXHRequest, &$_logicResult)
	{
		##	Set default target if not exists

		$_controllerAction = $this -> getControllerAction($_rcaTarget,'view');

		##	Check user rights for this target

		if(!$this -> hasRights($_userRights, $_controllerAction))	
		{ 
			if($_isXHRequest !== false && $_isXHRequest === 'update-site') // update-site check benötigt weil im bearbeitungs modus zwei controller actions erzeugt werden wenn ein modul bearbeitet wird.
			{
				$_bValidationErr =	true;
				$_bValidationMsg =	CLanguage::instance() -> getString('ERR_PERMISSON');
				$_bValidationDta = 	[];

				tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
			}

			CMessages::instance() -> addMessage(CLanguage::instance() -> getString('ERR_PERMISSON') , MSG_WARNING);
			return;
		}

		##	Call sub-logic function by target, if there results are false, we make a fall back to default view

		$_logicResults = false;
		switch($_controllerAction)
		{
			case 'create': 		/* Create new user   */	$_logicResults = $this -> logicCreate($_sqlConnection, $_isXHRequest, $_logicResult);		break;
			case 'delete': 		/* Create new user   */	$_logicResults = $this -> logicDelete($_sqlConnection, $_isXHRequest, $_logicResult);		break;
			case 'deletetree': 	/* Create new user   */	$_logicResults = $this -> logicDeleteTree($_sqlConnection, $_isXHRequest, $_logicResult);	break;
			case 'edit': 		/* Edit user 		 */	$_logicResults = $this -> logicEdit($_sqlConnection, $_isXHRequest, $_logicResult);			break;	
		}

		if(!$_logicResults)
		{
			##	Default View
			$this -> logicIndex($_sqlConnection, $_isXHRequest, $_logicResult);	
		}
	}

	private function
	logicIndex(&$_sqlConnection, $_isXHRequest, &$_logicResult)
	{
		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "language", "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => 'en'  ];
		$_pURLVariables -> retrieve($_request, true, false); 
		$_aFormData		 = $_pURLVariables ->getArray();

		$this -> m_modelSitemap  = new modelSitemap();
		$this -> m_modelSitemap -> load($_sqlConnection, $_aFormData['language']);
		$this -> setView(	
						'index',	
						'',
						[
							'pages' 	=> $this -> m_modelSitemap -> getDataInstance()
						]
						);
	}

	private function
	logicEdit(&$_sqlConnection, $_isXHRequest, &$_logicResult)
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
				Ist der Pfad gesetzt, schreiben wir die Daten in die logicResult Referenz die der Controller auswerten kann 
				um dann die Public Funktionen zu laden
			*/

			$this -> m_modelPage  = new modelPage();
			$this -> m_modelPage -> load($_sqlConnection, $_aFormData['cms-edit-page-node']);

			$_logicResult['state']			=	1;
			$_logicResult['node_id']		=	$this -> m_modelPage -> getDataInstance() -> node_id;
			$_logicResult['page_version']	=	$this -> m_modelPage -> getDataInstance() -> page_version;
			$_logicResult['page_language']	=	$this -> m_modelPage -> getDataInstance() -> page_language;

			##	XHR Function call

			if($_isXHRequest !== false && $_isXHRequest === 'update-site')
			{


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
											$_pFormVariables-> retrieve($_request, false, true); // POST 
											$_aFormData		 = $_pFormVariables ->getArray();

											if(empty($_aFormData['page_name'])) 		{ 	$_bValidationErr = true; 	$_bValidationDta[] = 'page_name'; 			}
											if(empty($_aFormData['page_title'])) 		{ 	$_bValidationErr = true; 	$_bValidationDta[] = 'page_title'; 	}
										#	if(empty($_aFormData['page_description'])) 	{ 	$_bValidationErr = true; 	$_bValidationDta[] = 'page_description'; 	}
											if(empty($_aFormData['page_template'])) 	{ 	$_bValidationErr = true; 	$_bValidationDta[] = 'page_template'; 	}

											if(!$_bValidationErr)
											{
												$_aFormData['node_id'] 			= $_logicResult['node_id'];
												$_aFormData['page_version'] 	= $_logicResult['page_version'];
												$_aFormData['page_language'] 	= $_logicResult['page_language'];

												if($this -> m_modelPage -> update($_sqlConnection, $_aFormData))
												{
													$_bValidationMsg = 'Site was updated';

													$_pHTAccess  = new CHTAccess();
													$_pHTAccess -> generatePart4Frontend($_sqlConnection);
													$_pHTAccess -> writeHTAccess();
												
												}
												else
												{
													$_bValidationMsg .= 'Unknown error on sql query';
													$_bValidationErr = true;
												}											
											}
											else	// Validation Failed
											{
												$_bValidationMsg .= 'Data validation failed - site was not updated';
												$_bValidationErr = true;
											}

											break;
				}
				
				tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call

			}

			return true;

		}

		return false;
	}

	private function
	logicCreate(&$_sqlConnection, $_isXHRequest, &$_logicResult)
	{
		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "cms-edit-page-lang", "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => 'en'  ];
		$_request[] 	 = 	[	"input" => "cms-edit-page-node",   "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => false  ];
		$_pURLVariables -> retrieve($_request, true, false); 
		$_aFormData		 = $_pURLVariables ->getArray();

		$_aFormData['page_name'] = CLanguage::instance() -> getString('MOD_SITES_NEWPAGE_NAME');
		$_aFormData['page_template'] = 'default';

		$this -> m_modelPage  = new modelPage();
		if($this -> m_modelPage -> insert($_sqlConnection, $_aFormData))
		{
			$_pHTAccess  = new CHTAccess();
			$_pHTAccess -> generatePart4Frontend($_sqlConnection);
			$_pHTAccess -> writeHTAccess();

			CMessages::instance() -> addMessage(CLanguage::instance() -> getString('MOD_SITES_PAGECREATED') , MSG_OK);
		}
		else
		{
			CMessages::instance() -> addMessage(CLanguage::instance() -> getString('MOD_SITES_ERR_NOTCREATED') , MSG_WARNING);
		}
		return false;
	}

	private function
	logicDelete(&$_sqlConnection, $_isXHRequest, &$_logicResult)
	{
		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "cms-edit-page-node",   "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => false  ];
		$_pURLVariables -> retrieve($_request, true, false); 
		$_aFormData		 = $_pURLVariables ->getArray();

		$this -> m_modelPage  = new modelPage();
		if($this -> m_modelPage -> delete($_sqlConnection, $_aFormData))
		{
			$_pHTAccess  = new CHTAccess();
			$_pHTAccess -> generatePart4Frontend($_sqlConnection);
			$_pHTAccess -> writeHTAccess();

			CMessages::instance() -> addMessage(CLanguage::instance() -> getString('MOD_SITES_PAGEDELETED') , MSG_OK);
		}
		else
		{
			CMessages::instance() -> addMessage(CLanguage::instance() -> getString('MOD_SITES_ERR_NOTDELETED') , MSG_WARNING);
		}
		return false;
	}	

	private function
	logicDeleteTree(&$_sqlConnection, $_isXHRequest, &$_logicResult)
	{
		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "cms-edit-page-node",   "validate" => "strip_tags|!empty", 	"use_default" => true, "default_value" => false  ];
		$_pURLVariables -> retrieve($_request, true, false); 
		$_aFormData		 = $_pURLVariables ->getArray();

		$this -> m_modelPage  = new modelPage();
		if($this -> m_modelPage -> deleteTree($_sqlConnection, $_aFormData))
		{
			$_pHTAccess  = new CHTAccess();
			$_pHTAccess -> generatePart4Frontend($_sqlConnection);
			$_pHTAccess -> writeHTAccess();
			
			CMessages::instance() -> addMessage(CLanguage::instance() -> getString('MOD_SITES_PAGEDELETED') , MSG_OK);
		}
		else
		{
			CMessages::instance() -> addMessage(CLanguage::instance() -> getString('MOD_SITES_ERR_NOTDELETED') , MSG_WARNING);
		}
		return false;
	}	

	private function
	setView(string $_view, string $_moduleTarget,  array $_dataInstances = [])
	{
		$this -> m_pView = new CView( CMS_SERVER_ROOT . DIR_CORE . DIR_MODULES . $this -> m_aModule['module_location'].'/view/'. $_view, $_moduleTarget , $_dataInstances );	
	}

	private function
	setCrumbData(string $_ctrlTarget, string $_customMenuName = '', bool $_noLink = false)
	{
		$_sectionIndex = array_search($_ctrlTarget, array_column($this -> m_aModule['sub'], 'ctl_target'));
		if($_sectionIndex !== false)
		{		
			if(!empty($_customMenuName))
				$this -> m_aCrumb['page_name'] 	= $_customMenuName;
			else
				$this -> m_aCrumb['page_name'] 	= CLanguage::instance() -> getString($this -> m_aModule['sub'][$_sectionIndex]['menu_name']);

			if(!$_noLink)
				$this -> m_aCrumb['page_path'] 	= $this -> m_aModule['sub'][$_sectionIndex]['url_name'] .'/';
			else
				$this -> m_aCrumb['no_link'] 	= true;
		}
	}


}

?>