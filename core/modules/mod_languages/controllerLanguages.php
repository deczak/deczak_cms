<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelLanguages.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPage.php';	

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CHTAccess.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CXMLSitemap.php';	

class	controllerLanguages extends CController
{

	public function
	__construct($_module, &$_object)
	{		
		$this -> m_pModel	= new modelLanguages();
		parent::__construct($_module, $_object);

		CPageRequest::instance() -> subs = $this -> getSubSection();
	}
	
	public function
	logic(&$_sqlConnection, array $_rcaTarget, array $_userRights, $_isXHRequest)
	{
		##	Set default target if not exists

		$_controllerAction = $this -> getControllerAction($_rcaTarget, 'index');

		##	Check user rights for this target

		if(!$this -> hasRights($_userRights, $_controllerAction))
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

		##	Call sub-logic function by target, if there results are false, we make a fall back to default view

		$enableEdit 	= $this -> hasRights($_userRights, 'edit');
		$enableDelete	= $this -> hasRights($_userRights, 'delete');

		$_logicResults = false;
		switch($_controllerAction)
		{
			case 'view'		: $_logicResults = $this -> logicView(	$_sqlConnection, $_isXHRequest, $enableEdit, $enableDelete);	break;
			case 'edit'		: $_logicResults = $this -> logicEdit(	$_sqlConnection, $_isXHRequest);	break;	
			case 'create'	: $_logicResults = $this -> logicCreate($_sqlConnection, $_isXHRequest);	break;
			case 'delete'	: $_logicResults = $this -> logicDelete($_sqlConnection, $_isXHRequest);	break;	
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
									$_request[] 	 = 	[	"input" => "cms-system-id",  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
									$_pURLVariables -> 	retrieve($_request, true, false); // POST 

									$modelCondition  = 	new CModelCondition();

									if($_pURLVariables -> getValue("cms-system-id") !== false)
									{	
										$modelCondition -> where('lang_key', $_pURLVariables -> getValue("cms-system-id"));
									}

									if(!$this -> m_pModel -> load($_sqlConnection, $modelCondition))
									{
										$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
										$_bValidationErr = true;
									}											
						
										$data = $this -> m_pModel -> getDataInstance();


									break;

			}

			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $data);	// contains exit call
		}

		##	Non XHR request
		
		$modelCondition = new CModelCondition();

		$this -> m_pModel -> load($_sqlConnection, $modelCondition);	
		$this -> setView(	
						'index',	
						'',
						[
							'enableEdit'	=> $_enableEdit,
							'enableDelete'	=> $_enableDelete
						]
						);
		
	}

	private function
	logicCreate(&$_sqlConnection, $_isXHRequest)
	{
		if($_isXHRequest !== false)
		{
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			$_pURLVariables	 =	new CURLVariables();
			$_request		 =	[];
			$_request[] 	 = 	[	"input" => "lang_key",  		"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "lang_name",  		"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "lang_name_native", 	"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "lang_default",  	"validate" => "strip_tags|strip_whitespaces|!empty", "use_default" => true, "default_value" => 0  ]; 	
			$_request[] 	 = 	[	"input" => "lang_hidden",  		"validate" => "strip_tags|strip_whitespaces|!empty", "use_default" => true, "default_value" => 0 ]; 	
			$_request[] 	 = 	[	"input" => "lang_locked",  		"validate" => "strip_tags|strip_whitespaces|!empty", "use_default" => true, "default_value" => 0  ]; 	
			$_pURLVariables -> retrieve($_request, false, true); // POST 
			$_aFormData		 = $_pURLVariables ->getArray();

			if(empty($_aFormData['lang_key'])) { 			$_bValidationErr = true; 	$_bValidationDta[] = 'lang_key'; 			}
			if(empty($_aFormData['lang_name'])) { 			$_bValidationErr = true; 	$_bValidationDta[] = 'lang_name'; 			}
			if(empty($_aFormData['lang_name_native'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'lang_name_native'; 	}

			if(!$_bValidationErr)	// Validation OK (by pre check)
			{		

				if(!$this -> m_pModel -> isUnique($_sqlConnection, ['lang_key' => $_aFormData['lang_key']]))
				{
					$_bValidationMsg .= CLanguage::get() -> string('M_BERMADDR_MSG_DENIEDEXIST');
					$_bValidationErr = true;
				}
	
				if(intval($_aFormData['lang_default']) === 1)
				{
					$_aFormData['lang_hidden'] = 0;
					$_aFormData['lang_locked'] = 0;
				}
			}
			else	// Validation Failed 
			{
				$_bValidationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
			}

			if(!$_bValidationErr)	// Validation OK
			{

				$_aFormData['create_by'] 	= CSession::instance() -> getValue('user_id');
				$_aFormData['create_time'] 	= time();

				$dataId = 0;

				if($this -> m_pModel -> insert($_sqlConnection, $_aFormData, $dataId))
				{
					$_bValidationMsg = CLanguage::get() -> string('M_BELANG_BEENCREATED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
					$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath .'language/'.$_aFormData['lang_key'];

					##	If this Language is the new default, changes to other to non-default

					if(intval($_aFormData['lang_default']) === 1)
					{
						$modelCondition = new CModelCondition();
						$modelCondition -> whereNot('lang_key', $_pURLVariables -> getValue("cms-system-id"));

						$_aFormData = [];
						$_aFormData['lang_default'] = 0;

						$this -> m_pModel -> update($_sqlConnection, $_aFormData, $modelCondition);
					}

					$rootPage = [];
					$rootPage['cms-edit-page-lang'] = $_aFormData['lang_key'];
					$rootPage['cms-edit-page-node'] = '1';

					$rootPage['page_id'] = '1';
					$rootPage['page_name'] = 'Start '. strtoupper($_aFormData['lang_key']);
					$rootPage['page_template'] = 'default';

					$rootPage['create_time']	=	time();
					$rootPage['create_by']		= CSession::instance() -> getValue('user_id');

					$nodeId = 0;

					$modelPage  = new modelPage();
					$modelPage -> insert($_sqlConnection, $rootPage, $nodeId);

					## Update .htacces and sitemap.xml

					$_pHTAccess  = new CHTAccess();
					$_pHTAccess -> generatePart4Frontend($_sqlConnection);
					$_pHTAccess -> writeHTAccess();

					$sitemap  	 = new CXMLSitemap();
					$sitemap 	-> generate($_sqlConnection);
				}
				else
				{
					$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
				}
			}

			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
		}

		$this -> setCrumbData('create');
		$this -> setView(
						'create',
						'create/'
						);

		return true;
	}

	private function
	logicView(&$_sqlConnection, $_isXHRequest = false, $_enableEdit = false, $_enableDelete = false)
	{	
		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "cms-system-id",  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
		$_pURLVariables -> retrieve($_request, true, false); // POST 

		if($_pURLVariables -> getValue("cms-system-id") !== false)
		{	
			$modelCondition = new CModelCondition();
			$modelCondition -> where('lang_key', $_pURLVariables -> getValue("cms-system-id"));

			if($this -> m_pModel -> load($_sqlConnection, $modelCondition))
			{ 
				##	Gathering additional data

				$_crumbName	 = $this -> m_pModel -> searchValue($_pURLVariables -> getValue("cms-system-id"),'lang_key','lang_name');

				$this -> setCrumbData('edit', $_crumbName, true);
				$this -> setView(
								'edit',
								'language/'. $_pURLVariables -> getValue("cms-system-id"),								
								[
									'languagesList' 	=> $this -> m_pModel -> getDataInstance(),
									'enableEdit'	=> $_enableEdit,
									'enableDelete'	=> $_enableDelete
								]								
								);
				return true;
			}
		}

		CMessages::instance() -> addMessage(CLanguage::get() -> string('MOD_BEUSER_ERR_USERID_UK') , MSG_WARNING);
		return false;
	}

	private function
	logicEdit(&$_sqlConnection, $_isXHRequest = false)
	{	
		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "cms-system-id",  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
		$_pURLVariables -> retrieve($_request, true, false); // POST 

		if($_pURLVariables -> getValue("cms-system-id") !== false && $_isXHRequest !== false)
		{	
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			switch($_isXHRequest)
			{
				case 'language'  :		// Update user data

											$_pFormVariables =	new CURLVariables();
											$_request		 =	[];
											$_request[] 	 = 	[	"input" => "lang_name",  		"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
											$_request[] 	 = 	[	"input" => "lang_name_native", 	"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
											$_request[] 	 = 	[	"input" => "lang_default",  	"validate" => "strip_tags|strip_whitespaces|!empty"  ]; 	
											$_request[] 	 = 	[	"input" => "lang_hidden",  		"validate" => "strip_tags|strip_whitespaces|!empty", "use_default" => true, "default_value" => 0 ]; 	
											$_request[] 	 = 	[	"input" => "lang_locked",  		"validate" => "strip_tags|strip_whitespaces|!empty", "use_default" => true, "default_value" => 0  ]; 	
											$_pFormVariables-> retrieve($_request, false, true); // POST 
											$_aFormData		 = $_pFormVariables ->getArray();

											if(empty($_aFormData['lang_name'])) { 			$_bValidationErr = true; 	$_bValidationDta[] = 'lang_name'; 			}
											if(empty($_aFormData['lang_name_native'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'lang_name_native'; 	}

											if(!$_bValidationErr)	// Validation OK (by pre check)
											{		
												if(isset($_aFormData['lang_default']) && intval($_aFormData['lang_default']) === 1)
												{
													$_aFormData['lang_hidden'] = 0;
													$_aFormData['lang_locked'] = 0;
												}
											}
											else	// Validation Failed 
											{
												$_bValidationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
											}

											if(!$_bValidationErr)
											{
												$_aFormData['update_by'] 	= CSession::instance() -> getValue('user_id');
												$_aFormData['update_time'] 	= time();

												$modelCondition = new CModelCondition();
												$modelCondition -> where('lang_key', $_pURLVariables -> getValue("cms-system-id"));
												
												if($this -> m_pModel -> update($_sqlConnection, $_aFormData, $modelCondition))
												{
													$_bValidationMsg = CLanguage::get() -> string('LANGUAGE') .' '. CLanguage::get() -> string('WAS_UPDATED');

													##	If this Language is the new default, changes to other to non-default

													if(isset($_aFormData['lang_default']) && intval($_aFormData['lang_default']) === 1)
													{
														$modelCondition = new CModelCondition();
														$modelCondition -> whereNot('lang_key', $_pURLVariables -> getValue("cms-system-id"));

														$_aFormData = [];
														$_aFormData['lang_default'] = 0;

														$this -> m_pModel -> update($_sqlConnection, $_aFormData, $modelCondition);
													}

													## Update .htacces and sitemap.xml

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

		return false;
	}

	private function
	logicDelete(&$_sqlConnection, $_isXHRequest = false)
	{	
		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "cms-system-id",  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
		$_pURLVariables -> retrieve($_request, true, false); // POST 

		if($_pURLVariables -> getValue("cms-system-id") !== false && $_isXHRequest !== false)
		{	
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			switch($_isXHRequest)
			{
				case 'delete':	//	Delete

									$modelCondition = new CModelCondition();
									$modelCondition -> where('lang_key', $_pURLVariables -> getValue("cms-system-id"));

									if($this -> m_pModel -> delete($_sqlConnection, $modelCondition))
									{
										$_bValidationMsg = CLanguage::get() -> string('M_BELANG_BEENDELETED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
										$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath;

										##	Delete Pages

										$sqlRootNodeRes	= $_sqlConnection -> query("SELECT node_id FROM tb_page_header WHERE page_language = '". $_pURLVariables -> getValue("cms-system-id") ."'");
										$sqlRootNodeItm = $sqlRootNodeRes -> fetch_assoc();

										$modelCondition = new CModelCondition();
										$modelCondition -> where('node_id', $sqlRootNodeItm['node_id']);

										$modelPage  = new modelPage();
										$modelPage -> deleteTree($_sqlConnection, $modelCondition);

										## Update .htacces and sitemap.xml

										$_pHTAccess  = new CHTAccess();
										$_pHTAccess -> generatePart4Frontend($_sqlConnection);
										$_pHTAccess -> writeHTAccess();

										$sitemap  	 = new CXMLSitemap();
										$sitemap 	-> generate($_sqlConnection);

									}
									else
									{
										$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
									}

									break;
			}

			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
		}			

		return false;
	}

}

?>