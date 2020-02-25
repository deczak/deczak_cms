<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSessions.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSessionsAccess.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUserAgent.php';		

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
	logic(&$_sqlConnection, array $_rcaTarget, $_isXHRequest)
	{
		##	Set default target if not exists

		$_controllerAction = $this -> getControllerAction($_rcaTarget, 'index');

		##	Check user rights for this target

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

		##	Call sub-logic function by target, if there results are false, we make a fall back to default view

		$enableEdit 	= $this -> existsUserRight('edit');
		$enableDelete	= $enableEdit;

		$_logicResults = false;
		switch($_controllerAction)
		{
			case 'view'		: $_logicResults = $this -> logicView(	$_sqlConnection, $_isXHRequest, $enableEdit, $enableDelete);	break;
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
		$conditionPages		 = new CModelCondition();
		$conditionPages		-> where('tb_page_header.node_id', 'tb_sessions_access.node_id');

		$modelSessionsAccess = new modelSessionsAccess();
		$modelSessionsAccess-> addSelectColumns('tb_sessions_access.*','tb_page_header.page_title');
		$modelSessionsAccess-> addRelation('left join', 'tb_page_header', $conditionPages);

		$modelSACondition  	 = new CModelCondition();
		$modelSACondition	-> groupBy('session_id') 
							-> groupBy('node_id');

		$modelSessionsAccess-> load($_sqlConnection, $modelSACondition);

		##
		
		$modelComplementary	 = new CModelComplementary();
		$modelComplementary	-> addComplemantary('pages','session_id', $modelSessionsAccess -> getDataInstance());

		$modelCondition  	 = new CModelCondition();
		$modelCondition		-> orderBy('time_create', 'DESC');

		$this -> m_pModel 	-> load($_sqlConnection, $modelCondition, $modelComplementary);	

		$modelUserAgent	 	 = new modelUserAgent();
		$modelUserAgent		-> load($_sqlConnection);

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

			$conditionPages = new CModelCondition();
			$conditionPages -> where('tb_page_header.node_id', 'tb_sessions_access.node_id');

			$modelSessionsAccess	 = new modelSessionsAccess();

			$modelSessionsAccess -> addSelectColumns('tb_sessions_access.*','tb_page_header.page_title');
			$modelSessionsAccess -> addRelation('left join', 'tb_page_header', $conditionPages);

			$modelSessionsAccess	-> load($_sqlConnection, $modelSACondition);

			$modelComplementary		 = new CModelComplementary();
			$modelComplementary		-> addComplemantary('pages','session_id', $modelSessionsAccess -> getDataInstance());

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