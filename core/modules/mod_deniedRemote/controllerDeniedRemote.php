<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelDeniedRemote.php';	

class	controllerDeniedRemote extends CController
{
	public function
	__construct($_module, &$_object)
	{		
		$this -> m_pModel	= new modelDeniedRemote();
		parent::__construct($_module, $_object);

		CPageRequest::instance() -> subs = $this -> getSubSection();
	}
	
	public function
	logic(CDatabaseConnection &$_pDatabase, array $_rcaTarget, $_isXHRequest)
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
			case 'view'		: $_logicResults = $this -> logicView(	$_pDatabase, $_isXHRequest, $enableEdit, $enableDelete);	break;
			case 'edit'		: $_logicResults = $this -> logicEdit(	$_pDatabase, $_isXHRequest);	break;	
			case 'create'	: $_logicResults = $this -> logicCreate($_pDatabase, $_isXHRequest);	break;
			case 'delete'	: $_logicResults = $this -> logicDelete($_pDatabase, $_isXHRequest);	break;	
			case 'ping'		: $_logicResults = $this -> logicPing($_pDatabase, $_isXHRequest, $enableEdit, $enableDelete);	break;	
		}

		if(!$_logicResults)
		{
			##	Default View
			$this -> logicIndex($_pDatabase, $_isXHRequest, $enableEdit, $enableDelete);	
		}
	}

	private function
	logicIndex(CDatabaseConnection &$_pDatabase, $_isXHRequest, $_enableEdit = false, $_enableDelete = false)
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
												$modelCondition -> whereLike('agent_name', $itemParts[0]);
												$modelCondition -> whereLike('agent_suffix', $itemParts[0]);
											}
											else
											{
												if( $itemParts[0] == 'cms-system-id' )
													$itemParts[0] = 'data_id';
												
												$modelCondition -> where($itemParts[0], $itemParts[1]);
											}
										}										
									}

									if(!$this -> m_pModel -> load($_pDatabase, $modelCondition))
									{
										$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
										$_bValidationErr = true;
									}											
						
									$data = $this -> m_pModel -> getResult();

									foreach($data as &$item)
									{
										$item -> creaty_by_name = tk::getBackendUserName($_pDatabase, $item -> create_by);
										$item -> update_by_name = tk::getBackendUserName($_pDatabase, $item -> update_by);
									}

									break;
			}

			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $data);	// contains exit call
		}

		##	No XHR request
		
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
	logicCreate(CDatabaseConnection &$_pDatabase, $_isXHRequest)
	{
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


				$uniqueCondition = new CModelCondition();
				$uniqueCondition -> where('denied_ip', $_aFormData['denied_ip']);


				if(!$this -> m_pModel -> unique($_pDatabase, $uniqueCondition))
				{
					$_bValidationMsg .= CLanguage::get() -> string('M_BERMADDR_MSG_DENIEDEXIST');
					$_bValidationErr = true;
				}


				if(strpos($_aFormData['denied_ip'], ':') === false && strpos($_aFormData['denied_ip'], '.') === false)
				{
					$_bValidationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
					$_bValidationErr = true;
				}
				else
				{
					if(strpos($_aFormData['denied_ip'], ':') !== false)
					{
						##	IPv6

						// TODO :: Add custom error messages to describe the problem and change elseif to standalone if
						
						if(filter_var($_aFormData['denied_ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false)
						{
							$_bValidationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
							$_bValidationDta[] = 'denied_ip'; 
							$_bValidationErr = true;
						}

					} elseif(strpos($_aFormData['denied_ip'], '.') !== false)
					{
						##	IPv4

						$ipBuffer 		= explode('/', $_aFormData['denied_ip']);

						// TODO :: Add custom error messages to describe the problem and change elseif to standalone if
		
						$ipSegements = explode('.', $ipBuffer[0]);

						if(count($ipSegements) !== 4)
						{
							$_bValidationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
							$_bValidationDta[] = 'denied_ip'; 
							$_bValidationErr = true;
						}			
						elseif(filter_var($ipBuffer[0],FILTER_VALIDATE_IP) === false)
						{
							$_bValidationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
							$_bValidationDta[] = 'denied_ip'; 
							$_bValidationErr = true;
						}
					}
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

				$dataId = $this -> m_pModel -> insert($_pDatabase, $_aFormData);

				if($dataId !== false)
				{
					$_bValidationMsg = CLanguage::get() -> string('MOD_BE_RMADDR_DENIEDADDR WAS_CREATED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
					$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath .'address/'.$dataId;

					$_pHTAccess  = new CHTAccess();
					$_pHTAccess -> generatePart4DeniedAddress($_pDatabase);
					$_pHTAccess -> writeHTAccess($_pDatabase);
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
	logicView(CDatabaseConnection &$_pDatabase, $_isXHRequest = false, $_enableEdit = false, $_enableDelete = false)
	{
		$systemId = $this -> querySystemId();

		if($systemId !== false)
		{	
			$modelCondition = new CModelCondition();
			$modelCondition -> where('data_id', $systemId);

			if($this -> m_pModel -> load($_pDatabase, $modelCondition))
			{
				##	Gathering additional data

				$_crumbName	 = $this -> m_pModel -> getResultItem('data_id', intval($systemId), 'denied_ip');

				$this -> setCrumbData('edit', $_crumbName, true);
				$this -> setView(
								'edit',
								'address/'. $systemId,								
								[
									'deniedList' 	=> $this -> m_pModel -> getResult(),
									'enableEdit'	=> $_enableEdit,
									'enableDelete'	=> $_enableDelete
								]								
								);
				return true;
			}
		}

		CMessages::instance() -> addMessage(CLanguage::get() -> string('M_BERMADDR_MSG_DENIEDUK') , MSG_WARNING);
		return false;
	}

	private function
	logicEdit(CDatabaseConnection &$_pDatabase, $_isXHRequest = false)
	{
		$systemId = $this -> querySystemId();

		if($systemId !== false && $_isXHRequest !== false)
		{	
			
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			$pingId 	= $this -> querySystemId('cms-ping-id', true);

			## check if dataset is locked, call his own xhrResult() 
			$this -> detectLock($_pDatabase, $systemId, $pingId);

			switch($_isXHRequest)
			{
				case 'denied-address'  :	// Update user data

											$_pFormVariables =	new CURLVariables();
											$_request		 =	[];
											$_request[] 	 = 	[	"input" => "denied_ip",  	"validate" => "strip_tags|strip_whitespaces|!empty" ]; 	
											$_request[] 	 = 	[	"input" => "denied_desc",  	"validate" => "strip_tags|!empty" ]; 	
											$_pFormVariables-> retrieve($_request, false, true); // POST 
											$_aFormData		 = $_pFormVariables ->getArray();

											if(empty($_aFormData['denied_ip'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'denied_ip'; 	}

											if(!$_bValidationErr)	// Validation OK (by pre check)
											{		




										$uniqueCondition = new CModelCondition();
										$uniqueCondition -> where('denied_ip', $_aFormData['denied_ip']);
										$uniqueCondition -> whereNot('data_id', $systemId);


												if(!$this -> m_pModel -> unique($_pDatabase, $uniqueCondition))
												{
													$_bValidationMsg .= CLanguage::get() -> string('M_BERMADDR_MSG_DENIEDEXIST');
													$_bValidationErr = true;
												}



												if(strpos($_aFormData['denied_ip'], ':') === false && strpos($_aFormData['denied_ip'], '.') === false)
												{
													$_bValidationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
													$_bValidationErr = true;
												}
												else
												{
													if(strpos($_aFormData['denied_ip'], ':') !== false)
													{
														##	IPv6

														// TODO :: Add custom error messages to describe the problem and change elseif to standalone if
														
														if(filter_var($_aFormData['denied_ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false)
														{
															$_bValidationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
															$_bValidationDta[] = 'denied_ip'; 
															$_bValidationErr = true;
														}

													} elseif(strpos($_aFormData['denied_ip'], '.') !== false)
													{
														##	IPv4

														$ipBuffer 		= explode('/', $_aFormData['denied_ip']);

														// TODO :: Add custom error messages to describe the problem and change elseif to standalone if
										
														$ipSegements = explode('.', $ipBuffer[0]);

														if(count($ipSegements) !== 4)
														{
														#	$_bValidationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
															$_bValidationDta[] = 'denied_ip'; 
															$_bValidationErr = true;
														}			
														elseif(filter_var($ipBuffer[0],FILTER_VALIDATE_IP) === false)
														{
														#	$_bValidationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
															$_bValidationDta[] = 'denied_ip'; 
															$_bValidationErr = true;
														}
													}
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
												$modelCondition -> where('data_id', $systemId);

												if($this -> m_pModel -> update($_pDatabase, $_aFormData, $modelCondition))
												{
													$_bValidationMsg = CLanguage::get() -> string('MOD_BE_RMADDR_DENIEDADDR WAS_UPDATED');

													$_pHTAccess  = new CHTAccess();
													$_pHTAccess -> generatePart4DeniedAddress($_pDatabase);
													$_pHTAccess -> writeHTAccess($_pDatabase);

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
	logicDelete(CDatabaseConnection &$_pDatabase, $_isXHRequest = false)
	{
		$systemId = $this -> querySystemId();

		if($systemId !== false && $_isXHRequest !== false)
		{	
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			$pingId 	= $this -> querySystemId('cms-ping-id', true);

			## check if dataset is locked, call his own xhrResult() 
			$this -> detectLock($_pDatabase, $systemId, $pingId);

			switch($_isXHRequest)
			{
				case 'address-delete':

									$modelCondition = new CModelCondition();
									$modelCondition -> where('data_id', $systemId);

									if($this -> m_pModel -> delete($_pDatabase, $modelCondition))
									{
										$_bValidationMsg = CLanguage::get() -> string('MOD_BE_RMADDR_DENIEDADDR WAS_DELETED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
										$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath;

										$_pHTAccess  = new CHTAccess();
										$_pHTAccess -> generatePart4DeniedAddress($_pDatabase);
										$_pHTAccess -> writeHTAccess($_pDatabase);
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

	public function
	logicPing(CDatabaseConnection &$_pDatabase, $_isXHRequest = false, $_enableEdit = false, $_enableDelete = false)
	{
		$systemId 	= $this -> querySystemId();
		$pingId 	= $this -> querySystemId('cms-ping-id', true);

		if($systemId !== false && $_isXHRequest !== false)
		{	
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			switch($_isXHRequest)
			{
				case 'lockState':	
				
					$locked	= $this -> m_pModel -> ping($_pDatabase, CSession::instance() -> getValue('user_id'), $systemId, $pingId, MODEL_LOCK_UPDATE);
					tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $locked);
					break;
			}

			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);
		}
	}


}

?>