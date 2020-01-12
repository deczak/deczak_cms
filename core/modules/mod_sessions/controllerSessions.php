<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSessions.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSessionsAccess.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUserAgent.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPageHeader.php';	

class	controllerSessions extends CController
{
	public function
	__construct($_module, &$_object)
	{		
		$this -> m_pModel	= new modelSessions();
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
			case 'create'	: $_logicResults = $this -> logicCreate($_sqlConnection, $_isXHRequest);	break;
			case 'edit'		: $_logicResults = $this -> logicEdit(	$_sqlConnection, $_isXHRequest);	break;	
			case 'delete'	: $_logicResults = $this -> logicDelete($_sqlConnection, $_isXHRequest);	break;	
		}

		if(!$_logicResults)
		{
			##	Default View
			$this -> logicIndex($_sqlConnection, $enableEdit, $enableDelete);	
		}
	}

	private function
	logicIndex(&$_sqlConnection, $_enableEdit = false, $_enableDelete = false)
	{
		$modelSACondition  		 = new CModelCondition();
		$modelSACondition		-> groupBy('session_id') 
								-> groupBy('node_id');


			$modelPageHeader	 = new modelPageHeader();
			$modelPageHeader	-> load($_sqlConnection);
			
			$modelComplementaryPH		 = new CModelComplementary();
			$modelComplementaryPH		-> addValue('page_title','node_id', $modelPageHeader -> getDataInstance());


		$modelSessionsAccess	 = new modelSessionsAccess();
		$modelSessionsAccess	-> load($_sqlConnection, $modelSACondition, $modelComplementaryPH);

		$modelComplementary		 = new CModelComplementary();
		$modelComplementary		-> addArray('pages','session_id', $modelSessionsAccess -> getDataInstance());

		$modelCondition  = new CModelCondition();
		$modelCondition	-> orderBy('time_create', 'DESC');

		$this -> m_pModel -> load($_sqlConnection, $modelCondition, $modelComplementary);	

		$modelUserAgent	 = new modelUserAgent();
		$modelUserAgent	-> load($_sqlConnection);

		$this -> setView(	
						'index',	
						'',
						[
							'sessionList' 	=> $this -> m_pModel -> getDataInstance(),
							'agentsList' 	=> $modelUserAgent -> getDataInstance(),
							'enableEdit'	=> $_enableEdit,
							'enableDelete'	=> $_enableDelete
						]
						);
	}

	private function
	logicCreate(&$_sqlConnection, $_isXHRequest)
	{
		/*
		if($_isXHRequest !== false)
		{
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			$_pURLVariables	 =	new CURLVariables();
			$_request		 =	[];
			$_request[] 	 = 	[	"input" => "denied_ip",  	"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "denied_desc",  	"validate" => "strip_tags|!empty" ]; 	
			$_pURLVariables -> retrieve($_request, false, true); // POST 
			$_aFormData		 = $_pURLVariables ->getArray();

			if(empty($_aFormData['denied_ip'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'denied_ip'; 	}

			if(!$_bValidationErr)	// Validation OK (by pre check)
			{		
				if(!$this -> m_pModel -> isUnique($_sqlConnection, ['denied_ip' => $_aFormData['denied_ip']]))
				{
					$_bValidationMsg .= CLanguage::get() -> string('M_BERMADDR_MSG_DENIEDEXIST');
					$_bValidationErr = true;
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
					$_bValidationMsg = CLanguage::get() -> string('M_BERMADDR_MSG_ISCREATED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
					$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath .'address/'.$dataId;

					$_pHTAccess  = new CHTAccess();
					$_pHTAccess -> generatePart4DeniedAddress($_sqlConnection);
					$_pHTAccess -> writeHTAccess();
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
		*/
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
			$modelCondition -> where('data_id', $_pURLVariables -> getValue("cms-system-id"));


			$modelSACondition  		 = new CModelCondition();
			$modelSACondition	#	-> groupBy('session_id') 
								#	-> groupBy('node_id')
									-> orderBy('time_access');


			$modelPageHeader	 = new modelPageHeader();
			$modelPageHeader	-> load($_sqlConnection);

			$modelComplementaryPH		 = new CModelComplementary();
			$modelComplementaryPH		-> addValue('page_title','node_id', $modelPageHeader -> getDataInstance());

			$modelSessionsAccess	 = new modelSessionsAccess();
			$modelSessionsAccess	-> load($_sqlConnection, $modelSACondition, $modelComplementaryPH);

			$modelComplementary		 = new CModelComplementary();
			$modelComplementary		-> addArray('pages','session_id', $modelSessionsAccess -> getDataInstance());

			if($this -> m_pModel -> load($_sqlConnection, $modelCondition, $modelComplementary))
			{
				##	Gathering additional data

				$_crumbName	 = $this -> m_pModel -> searchValue(intval($_pURLVariables -> getValue("cms-system-id")),'data_id','user_ip');

				$this -> setCrumbData('view', $_pURLVariables -> getValue("cms-system-id") .' ('. $_crumbName .')', true);
				$this -> setView(
								'view',
								'session/'. $_pURLVariables -> getValue("cms-system-id"),								
								[
									'sessionList' 	=> $this -> m_pModel -> getDataInstance(),
									'enableEdit'	=> $_enableEdit,
									'enableDelete'	=> $_enableDelete
								]								
								);
				return true;
			}
		}

		CMessages::instance() -> addMessage(CLanguage::get() -> string('SESSION IS_UNKNOWN') , MSG_WARNING);
		return false;
	}

	private function
	logicEdit(&$_sqlConnection, $_isXHRequest = false)
	{	

		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "cms-system-id",  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
		$_pURLVariables -> retrieve($_request, true, false); // POST 
	
		if($_pURLVariables -> getValue("cms-system-id") !== false)
		{	

			/*	
			##	XHR Function call

			if($_isXHRequest !== false)
			{
				$_bValidationErr =	false;
				$_bValidationMsg =	'';
				$_bValidationDta = 	[];

				switch($_isXHRequest)
				{
					case 'denied-address'  :	// Update user data

												$_pFormVariables =	new CURLVariables();
												$_request		 =	[];
												$_request[] 	 = 	[	"input" => "data_id",  	"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
												$_request[] 	 = 	[	"input" => "denied_ip",  	"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
												$_request[] 	 = 	[	"input" => "denied_desc",  	"validate" => "strip_tags|!empty" ]; 	
												$_pFormVariables-> retrieve($_request, false, true); // POST 
												$_aFormData		 = $_pFormVariables ->getArray();

												if(empty($_aFormData['data_id'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'data_id'; 	}
												if(empty($_aFormData['denied_ip'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'denied_ip'; 	}

												if(!$_bValidationErr)	// Validation OK (by pre check)
												{		
													if(!$this -> m_pModel -> isUnique($_sqlConnection, ['denied_ip' => $_aFormData['denied_ip']], ['data_id' => $_aFormData['denied_ip']]))
													{
														$_bValidationMsg .= CLanguage::get() -> string('M_BERMADDR_MSG_DENIEDEXIST');
														$_bValidationErr = true;
													}
												}
												else	// Validation Failed 
												{
													$_bValidationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
												}

												if(!$_bValidationErr)
												{
												#	$_aFormData['update_by'] 	= CSession::instance() -> getValue('user_id');
												#	$_aFormData['update_time'] 	= time();

													$modelCondition = new CModelCondition();
													$modelCondition -> where('data_id', $_pURLVariables -> getValue("cms-system-id"));

													if($this -> m_pModel -> update($_sqlConnection, $_aFormData, $modelCondition))
													{
														$_bValidationMsg = CLanguage::get() -> string('SESSION WAS_UPDATED');

														$_pHTAccess  = new CHTAccess();
														$_pHTAccess -> generatePart4DeniedAddress($_sqlConnection);
														$_pHTAccess -> writeHTAccess();

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
		
			*/
			##	Non XHR call


			$modelCondition = new CModelCondition();
			$modelCondition -> where('data_id', $_pURLVariables -> getValue("cms-system-id"));


			if($this -> m_pModel -> load($_sqlConnection, $modelCondition))
			{
				##	Gathering additional data




				$_crumbName	 = $this -> m_pModel -> searchValue(intval($_pURLVariables -> getValue("cms-system-id")),'data_id','denied_ip');

				$this -> setCrumbData('edit', $_crumbName, true);
				$this -> setView(
								'edit',
								'address/'. $_pURLVariables -> getValue("cms-system-id"),								
								[
									'deniedList' 	=> $this -> m_pModel -> getDataInstance()
								]								
								);
				return true;
			}
		}

		CMessages::instance() -> addMessage(CLanguage::get() -> string('SESSION IS_UNKNOWN') , MSG_WARNING);
		return false;
	}

	private function
	logicDelete(&$_sqlConnection, $_isXHRequest = false)
	{	
		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "cms-system-id",  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
		$_pURLVariables -> retrieve($_request, true, false); // POST 

		if($_pURLVariables -> getValue("cms-system-id") !== false)
		{	
			##	XHR Function call

			if($_isXHRequest !== false)
			{
				$_bValidationErr =	false;
				$_bValidationMsg =	'';
				$_bValidationDta = 	[];

				switch($_isXHRequest)
				{
					case 'session-delete':


										$modelCondition = new CModelCondition();
										$modelCondition -> where('data_id', $_pURLVariables -> getValue("cms-system-id"));


										if($this -> m_pModel -> delete($_sqlConnection, $modelCondition))
										{
											$_bValidationMsg = CLanguage::get() -> string('SESSION WAS_DELETED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
											$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath;

											$_pHTAccess  = new CHTAccess();
											$_pHTAccess -> generatePart4DeniedAddress($_sqlConnection);
											$_pHTAccess -> writeHTAccess();

										}
										else
										{
											$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
										}

										break;
				}

				tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
			}		
		
		}

		CMessages::instance() -> addMessage(CLanguage::get() -> string('SESSION IS_UNKNOWN') , MSG_WARNING);
		return false;
	}

}

?>