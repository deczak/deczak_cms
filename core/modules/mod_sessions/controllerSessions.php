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
	logic(CDatabaseConnection &$_pDatabase, array $_rcaTarget, ?object $_xhrInfo) : bool
	{
		##	Set default target if not exists

		$controllerAction = $this -> getControllerAction_v2($_rcaTarget, $_xhrInfo, 'index');

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
			
		if($_xhrInfo !== null && $_xhrInfo -> isXHR && $_xhrInfo -> objectId === $this -> objectInfo -> object_id)
			$controllerAction = 'xhr_'. $_xhrInfo -> action;

		##	Call sub-logic function by target, if there results are false, we make a fall back to default view

		$enableEdit 	= $this -> existsUserRight('edit');
		$enableDelete	= $enableEdit;
		
		$logicDone = false;
		switch($controllerAction)
		{
			case 'view'		  : $logicDone = $this -> logicView($_pDatabase, $enableEdit, $enableDelete); break;
			case 'xhr_delete' : $logicDone = $this -> logicXHRDelete($_pDatabase); break;	
			case 'xhr_index' : $logicDone = $this -> logicXHRIndex($_pDatabase, $_xhrInfo); break;	
		}

		if(!$logicDone) // Default
			$logicDone = $this -> logicIndex($_pDatabase, $enableEdit, $enableDelete);	
	
		return $logicDone;
	}

	private function
	logicIndex(CDatabaseConnection &$_pDatabase, $_enableEdit = false, $_enableDelete = false) : bool
	{
		$this -> setView(	
						'index',	
						'',
						[
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
												#$modelCondition -> whereLike('', $itemParts[0]);
											}
											else
											{
												if( $itemParts[0] == 'cms-system-id' )
													$itemParts[0] = 'data_id';
												
												$modelCondition -> where($itemParts[0], $itemParts[1]);
											}
										}										
									}
			
									$modelCondition -> groupBy('data_id');

									if(!$this -> m_pModel -> load($_pDatabase, $modelCondition, MODEL_SESSIONS_APPEND_ACCESS_DATA | MODEL_SESSIONS_APPEND_AGENT_NAME))
									{
										$validationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
										$validationErr = true;
									}											
						
									$data = $this -> m_pModel -> getResult();

			tk::xhrResult(intval($validationErr), $validationMsg, $data);	// contains exit call
		
		return false;

	}

	private function
	logicView(CDatabaseConnection &$_pDatabase, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{	

		$systemId = $this -> querySystemId();

		if($systemId !== false)
		{
	
			$modelCondition = new CModelCondition();
			$modelCondition -> where('data_id', $systemId);



			if($this -> m_pModel -> load($_pDatabase, $modelCondition, MODEL_SESSIONS_APPEND_ACCESS_DATA))
			{
				##	Gathering additional data

				$_crumbName	 = $this -> m_pModel -> getResultItem('data_id', intval($systemId),'user_ip');

				$this -> setCrumbData('view', $systemId .' ('. $_crumbName .')', true);
				$this -> setView(
								'view',
								'session/'. $systemId,								
								[
									'sessionList' 	=> $this -> m_pModel -> getResult(),
									'enableEdit'	=> $_enableEdit,
									'enableDelete'	=> $_enableDelete
								]								
								);
				return true;
			}
		}

		CMessages::add(CLanguage::get() -> string('SESSION IS_UNKNOWN') , MSG_WARNING);
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
										$modelCondition -> where('data_id', $systemId);


										if($this -> m_pModel -> delete($_pDatabase, $modelCondition))
										{
											$validationMsg = CLanguage::get() -> string('SESSION WAS_DELETED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
											$responseData['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath;

											$_pHTAccess  = new CHTAccess();
											$_pHTAccess -> generatePart4DeniedAddress($_pDatabase);
											$_pHTAccess -> writeHTAccess($_pDatabase);

										}
										else
										{
											$validationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
										}

				tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
					
		
		}

		CMessages::add(CLanguage::get() -> string('SESSION IS_UNKNOWN') , MSG_WARNING);
		return false;
	}
}
