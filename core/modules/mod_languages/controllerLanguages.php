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
	logic(CDatabaseConnection &$_pDatabase, array $_rcaTarget, ?object $_xhrInfo) : bool
	{
		##	Set default target if not exists

		$controllerAction = $this -> getControllerAction($_rcaTarget, 'index');
		
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

			CMessages::add(CLanguage::get() -> string('ERR_PERMISSON') , MSG_WARNING);
			return false;
		}

		$controllerAction = $this -> getControllerAction_v2($_rcaTarget, $_xhrInfo, 'index');

			
		if($_xhrInfo !== null && $_xhrInfo -> isXHR && $_xhrInfo -> objectId === $this -> objectInfo -> object_id)
			$controllerAction = 'xhr_'. $_xhrInfo -> action;

		##	Call sub-logic function by target, if there results are false, we make a fall back to default view

		$enableEdit 	= $this -> existsUserRight('edit');
		$enableDelete	= $enableEdit;
	
		$logicDone = false;
		switch($controllerAction)
		{
			case 'view'		: $logicDone = $this -> logicView(	$_pDatabase, $enableEdit, $enableDelete);	break;
			case 'create'	: $logicDone = $this -> logicCreate($_pDatabase);	break;

			case 'xhr_index' : $logicDone = $this -> logicXHRIndex($_pDatabase, $_xhrInfo); break;
			case 'xhr_create'	: $logicDone = $this -> logicXHRCreate($_pDatabase);	break;
			case 'xhr_delete'	: $logicDone = $this -> logicXHRDelete($_pDatabase);	break;	
			case 'xhr_ping'		: $logicDone = $this -> logicXHRPing($_pDatabase);	break;	
			case 'xhr_edit'		: $logicDone = $this -> logicXHREdit($_pDatabase);	break;	
		}

		if(!$logicDone) // Default
			$logicDone = $this -> logicIndex($_pDatabase, $enableEdit, $enableDelete);	
	
		return $logicDone;
	}

	private function
	logicIndex(CDatabaseConnection &$_pDatabase, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{		
		$modelCondition = new CModelCondition();

		$this -> m_pModel -> load($_pDatabase, $modelCondition);	
		$this -> setView(	
						'index',	
						'',
						[
							'languagesList'	=> $this -> m_pModel -> getResult(),
							'enableEdit'	=> $_enableEdit,
							'enableDelete'	=> $_enableDelete
						]
						);

		return true;		
	}

	private function
	logicXHRIndex(CDatabaseConnection &$_pDatabase) : bool
	{
			$validationErr =	false;
			$validationMsg =	'';
			$responseData = 	[];


									$_pURLVariables	 =	new CURLVariables();
									$_request		 =	[];
									$_request[] 	 = 	[	"input" => 'q',  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
									$_pURLVariables -> retrieve($_request, false, true);	

									


									$modelCondition  = 	new CModelCondition();

									if($_pURLVariables -> getValue("q") !== false)
									{	
										$conditionSource = 	explode(' ', $_pURLVariables -> getValue("q"));
										foreach($conditionSource as $conditionItem)
										{
											$itemParts = explode(':', $conditionItem);

											if(count($itemParts) == 1)
											{
												$modelCondition -> whereLike('lang_name', $itemParts[0]);
												$modelCondition -> whereLike('lang_name_native', $itemParts[0]);
											}
											else
											{
												if( $itemParts[0] == 'cms-system-id' )
													$itemParts[0] = 'lang_key';
												
												$modelCondition -> where($itemParts[0], $itemParts[1]);
											}
										}										
									}

									if(!$this -> m_pModel -> load($_pDatabase, $modelCondition))
									{
										$validationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
										$validationErr = true;
									}											
						
									$data = $this -> m_pModel -> getResult();

									foreach($data as &$item)
									{
										$item -> creaty_by_name = tk::getBackendUserName($_pDatabase, $item -> create_by);
										$item -> update_by_name = tk::getBackendUserName($_pDatabase, $item -> update_by);
									}

			tk::xhrResult(intval($validationErr), $validationMsg, $data);	// contains exit call
		

		return false;
	}

	private function
	logicCreate(CDatabaseConnection &$_pDatabase) : bool
	{
		$this -> setCrumbData('create');
		$this -> setView(
						'create',
						'create/'
						);

		return true;
	}

	private function
	logicXHRCreate(CDatabaseConnection &$_pDatabase) : bool
	{
		
			$validationErr =	false;
			$validationMsg =	'';
			$responseData = 	[];

			$_pURLVariables	 =	new CURLVariables();
			$_request		 =	[];
			$_request[] 	 = 	[	"input" => "lang_key",  		"validate" => "strip_tags|strip_whitespaces|lowercase|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "lang_name",  		"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "lang_name_native", 	"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "lang_default",  	"validate" => "strip_tags|strip_whitespaces|!empty", "use_default" => true, "default_value" => 0  ]; 	
			$_request[] 	 = 	[	"input" => "lang_hidden",  		"validate" => "strip_tags|strip_whitespaces|!empty", "use_default" => true, "default_value" => 0 ]; 	
			$_request[] 	 = 	[	"input" => "lang_locked",  		"validate" => "strip_tags|strip_whitespaces|!empty", "use_default" => true, "default_value" => 0  ]; 	
			$_pURLVariables -> retrieve($_request, false, true); // POST 
			$_aFormData		 = $_pURLVariables ->getArray();

			if(empty($_aFormData['lang_key'])) { 			$validationErr = true; 	$responseData[] = 'lang_key'; 			}
			if(empty($_aFormData['lang_name'])) { 			$validationErr = true; 	$responseData[] = 'lang_name'; 			}
			if(empty($_aFormData['lang_name_native'])) { 	$validationErr = true; 	$responseData[] = 'lang_name_native'; 	}

			if(!$validationErr)	// Validation OK (by pre check)
			{		



				$uniqueCondition = new CModelCondition();
				$uniqueCondition -> where('lang_key', $_aFormData['lang_key']);

				if(!$this -> m_pModel -> unique($_pDatabase, $uniqueCondition))
				{
					$validationMsg .= CLanguage::get() -> string('M_BERMADDR_MSG_DENIEDEXIST');
					$validationErr = true;
				}
	
				if(intval($_aFormData['lang_default']) === 1)
				{
					$_aFormData['lang_hidden'] = 0;
					$_aFormData['lang_locked'] = 0;
				}
			}
			else	// Validation Failed 
			{
				$validationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
			}

			if(!$validationErr)	// Validation OK
			{
				$_aFormData['create_by'] 	= CSession::instance() -> getValue('user_id');
				$_aFormData['create_time'] 	= time();

				if($this -> m_pModel -> insert($_pDatabase, $_aFormData))
				{
					$validationMsg = CLanguage::get() -> string('M_BELANG_BEENCREATED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
					$responseData['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath .'language/'.$_aFormData['lang_key'];

					##	If this Language is the new default, changes to other to non-default

					if(intval($_aFormData['lang_default']) === 1)
					{
						$modelCondition = new CModelCondition();
						$modelCondition -> whereNot('lang_key', $_aFormData['lang_key']);

						$updateData = [];
						$updateData['lang_default'] = 0;

						$this -> m_pModel -> update($_pDatabase, $updateData, $modelCondition);
					}

					$rootPage = [];
					$rootPage['cms-edit-page-lang'] = $_aFormData['lang_key'];
					$rootPage['cms-edit-page-node'] = '1';

					$rootPage['page_id'] = '1';
					$rootPage['page_name'] = 'Start '. strtoupper($_aFormData['lang_key']);
					$rootPage['page_template'] = 'default';

					$rootPage['create_time']	=	time();
					$rootPage['create_by']		= CSession::instance() -> getValue('user_id');

					$modelPage  = new modelPage();
					$modelPage -> insert($_pDatabase, $rootPage);

					## Update .htacces and sitemap.xml

					$_pHTAccess  = new CHTAccess();
					$_pHTAccess -> generatePart4Frontend($_pDatabase);
					$_pHTAccess -> writeHTAccess($_pDatabase);

					$sitemap  	 = new CXMLSitemap();
					$sitemap 	-> generate($_pDatabase);
				}
				else
				{
					$validationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
				}
			}

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
	
		return false;
	}

	private function
	logicView(CDatabaseConnection &$_pDatabase, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{
		$systemId = $this -> querySystemId();

		if($systemId !== false)
		{	
			$modelCondition = new CModelCondition();
			$modelCondition -> where('lang_key', $systemId);

			if($this -> m_pModel -> load($_pDatabase, $modelCondition))
			{ 
				##	Gathering additional data

				$_crumbName	 = $this -> m_pModel -> getResultItem('lang_key', $systemId, 'lang_name');

				$this -> setCrumbData('edit', $_crumbName, true);
				$this -> setView(
								'edit',
								'language/'. $systemId,								
								[
									'languagesList' 	=> $this -> m_pModel -> getResult(),
									'enableEdit'	=> $_enableEdit,
									'enableDelete'	=> $_enableDelete
								]								
								);
				return true;
			}
		}

		CMessages::add(CLanguage::get() -> string('MOD_BEUSER_ERR_USERID_UK') , MSG_WARNING);
		return false;
	}

	private function
	logicXHREdit(CDatabaseConnection &$_pDatabase) : bool
	{
		$systemId = $this -> querySystemId();

		if($systemId !== false)
		{	
			$validationErr =	false;
			$validationMsg =	'';
			$responseData = 	[];


											$_pFormVariables =	new CURLVariables();
											$_request		 =	[];
											$_request[] 	 = 	[	"input" => "lang_name",  		"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
											$_request[] 	 = 	[	"input" => "lang_name_native", 	"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
											$_request[] 	 = 	[	"input" => "lang_default",  	"validate" => "strip_tags|strip_whitespaces|!empty"  ]; 	
											$_request[] 	 = 	[	"input" => "lang_hidden",  		"validate" => "strip_tags|strip_whitespaces|!empty", "use_default" => true, "default_value" => 0 ]; 	
											$_request[] 	 = 	[	"input" => "lang_locked",  		"validate" => "strip_tags|strip_whitespaces|!empty", "use_default" => true, "default_value" => 0  ]; 	
											$_pFormVariables-> retrieve($_request, false, true); // POST 
											$_aFormData		 = $_pFormVariables ->getArray();

											if(empty($_aFormData['lang_name'])) { 			$validationErr = true; 	$responseData[] = 'lang_name'; 			}
											if(empty($_aFormData['lang_name_native'])) { 	$validationErr = true; 	$responseData[] = 'lang_name_native'; 	}

											if(!$validationErr)	// Validation OK (by pre check)
											{		
												if(isset($_aFormData['lang_default']) && intval($_aFormData['lang_default']) === 1)
												{
													$_aFormData['lang_hidden'] = 0;
													$_aFormData['lang_locked'] = 0;
												}
											}
											else	// Validation Failed 
											{
												$validationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
											}

											if(!$validationErr)
											{
												$_aFormData['update_by'] 	= CSession::instance() -> getValue('user_id');
												$_aFormData['update_time'] 	= time();

												$modelCondition = new CModelCondition();
												$modelCondition -> where('lang_key', $systemId);
												
												if($this -> m_pModel -> update($_pDatabase, $_aFormData, $modelCondition))
												{
													$validationMsg = CLanguage::get() -> string('LANGUAGE') .' '. CLanguage::get() -> string('WAS_UPDATED');

													##	If this Language is the new default, changes to other to non-default

													if(isset($_aFormData['lang_default']) && intval($_aFormData['lang_default']) === 1)
													{
														$modelCondition = new CModelCondition();
														$modelCondition -> whereNot('lang_key', $systemId);

														$_aFormData = [];
														$_aFormData['lang_default'] = 0;

														$this -> m_pModel -> update($_pDatabase, $_aFormData, $modelCondition);
													}

													## Update .htacces and sitemap.xml

													$_pHTAccess  = new CHTAccess();
													$_pHTAccess -> generatePart4Frontend($_pDatabase);
													$_pHTAccess -> writeHTAccess($_pDatabase);

													$sitemap  	 = new CXMLSitemap();
													$sitemap 	-> generate($_pDatabase);
												}
												else
												{
													$validationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
													$validationErr = true;
												}											
											}
											else	// Validation Failed
											{
												$validationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
												$validationErr = true;
											}

		
			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		}

		return false;
	}

	private function
	logicXHRDelete(CDatabaseConnection &$_pDatabase) : bool
	{
		$systemId = $this -> querySystemId();

		if($systemId !== false)
		{	
			$validationErr =	false;
			$validationMsg =	'';
			$responseData = 	[];


									$modelCondition = new CModelCondition();
									$modelCondition -> where('lang_key', $systemId);

									if($this -> m_pModel -> delete($_pDatabase, $modelCondition))
									{
										$validationMsg = CLanguage::get() -> string('M_BELANG_BEENDELETED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
										$responseData['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath;

										##	Delete Pages

										$pageHeaderCondition		 = new CModelCondition();
										$pageHeaderCondition		-> where('page_language', $systemId)
																	-> orderBy('node_id')
																	-> limit(1);

										$dbQuery 	= $_pDatabase		-> query(DB_SELECT) 
																		-> table('tb_page_header') 
																		-> selectColumns(['node_id'])
																		-> condition($pageHeaderCondition);

										$pageHeaderRes = $dbQuery -> exec();

										if($pageHeaderRes !== false && count($pageHeaderRes) !== 0)
										{


											$pageHeaderItm = $pageHeaderRes[0];


											$modelCondition = new CModelCondition();
											$modelCondition -> where('node_id', $pageHeaderItm -> node_id);

											$modelPage  = new modelPage();
											$modelPage -> deleteTree($_pDatabase, $modelCondition);

											## Update .htacces and sitemap.xml

											$_pHTAccess  = new CHTAccess();
											$_pHTAccess -> generatePart4Frontend($_pDatabase);
											$_pHTAccess -> writeHTAccess($_pDatabase);

											$sitemap  	 = new CXMLSitemap();
											$sitemap 	-> generate($_pDatabase);

										}

									}
									else
									{
										$validationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
									}

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		}			

		return false;
	}
	
	public function
	logicXHRPing(CDatabaseConnection &$_pDatabase) : bool
	{
		$systemId 	= $this -> querySystemId();
		$pingId 	= $this -> querySystemId('cms-ping-id', true);

		if($systemId !== false)
		{	
			$validationErr =	false;
			$validationMsg =	'';
		
			$locked	= $this -> m_pModel -> ping($_pDatabase, CSession::instance() -> getValue('user_id'), $systemId, $pingId, MODEL_LOCK_UPDATE);

			tk::xhrResult(intval($validationErr), $validationMsg, $locked);
		}

		return false;
	}
}
