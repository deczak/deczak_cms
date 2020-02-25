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
			case 'edit'		: $_logicResults = $this -> logicEdit(	$_sqlConnection, $_isXHRequest);	break;	
			case 'create'	: $_logicResults = $this -> logicCreate($_sqlConnection, $_isXHRequest);	break;
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
		#$modelCondition = new CModelCondition();
		#$modelCondition -> orderBy('data_id', 'DESC');
		
		$this -> m_pModel -> load($_sqlConnection);	
		$this -> setView(	
						'index',	
						'',
						[
							'deniedList' 	=> $this -> m_pModel -> getDataInstance(),
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

				$dataId = 0;

				if($this -> m_pModel -> insert($_sqlConnection, $_aFormData, $dataId))
				{
					$_bValidationMsg = CLanguage::get() -> string('MOD_BE_RMADDR_DENIEDADDR WAS_CREATED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
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

			if($this -> m_pModel -> load($_sqlConnection, $modelCondition))
			{
				##	Gathering additional data

				$_crumbName	 = $this -> m_pModel -> searchValue(intval($_pURLVariables -> getValue("cms-system-id")),'data_id','denied_ip');

				$this -> setCrumbData('edit', $_crumbName, true);
				$this -> setView(
								'edit',
								'address/'. $_pURLVariables -> getValue("cms-system-id"),								
								[
									'deniedList' 	=> $this -> m_pModel -> getDataInstance(),
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
												if(!$this -> m_pModel -> isUnique($_sqlConnection, ['denied_ip' => $_aFormData['denied_ip']], ['data_id' => $_aFormData['data_id']]))
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
												$modelCondition -> where('data_id', $_pURLVariables -> getValue("cms-system-id"));

												if($this -> m_pModel -> update($_sqlConnection, $_aFormData, $modelCondition))
												{
													$_bValidationMsg = CLanguage::get() -> string('MOD_BE_RMADDR_DENIEDADDR WAS_UPDATED');

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
				case 'address-delete':

									$modelCondition = new CModelCondition();
									$modelCondition -> where('data_id', $_pURLVariables -> getValue("cms-system-id"));

									if($this -> m_pModel -> delete($_sqlConnection, $modelCondition))
									{
										$_bValidationMsg = CLanguage::get() -> string('MOD_BE_RMADDR_DENIEDADDR WAS_DELETED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
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

		return false;
	}

}

?>