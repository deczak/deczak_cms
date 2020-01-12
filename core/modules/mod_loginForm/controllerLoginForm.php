<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSimple.php';	

require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelLoginObjects.php';

class	controllerLoginForm extends CController
{
	public function
	__construct($_module, &$_object)
	{		
		$this -> m_modelSimple = new modelSimple();
		parent::__construct($_module, $_object);
	}
	
	public function
	logic(&$_sqlConnection, array $_rcaTarget, array $_userRights, $_isXHRequest, &$_logicResult, bool $_bEditMode)
	{

		$_controllerAction = $this -> getControllerAction($_rcaTarget, 'view');

		##	Check user rights for this target

		$_userRights[] = 'view';			// Default Right for everyone
		$_userRights[] = 'loginSuccess';		// Default Right for everyone

		if(!$this -> hasRights($_userRights, $_controllerAction))	
		{ 
			if($_isXHRequest !== false)
			{
				$_bValidationErr =	true;
				$_bValidationMsg =	CLanguage::instance() -> getString('ERR_PERMISSON');
				$_bValidationDta = 	[];

				tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
			}

			CMessages::instance() -> addMessage(CLanguage::instance() -> getString('ERR_PERMISSON') , MSG_WARNING);
			return;
		}

		##	

		if($_bEditMode && $_isXHRequest === false) 
			$_controllerAction = 'edit';

		##	Call sub-logic function by target, if there results are false, we make a fall back to default view
		
		$enableEdit 	= $this -> hasRights($_userRights, 'edit');
		$enableDelete	= $this -> hasRights($_userRights, 'delete');

		$_logicResults = false;
		switch($_controllerAction)
		{
			case 'loginSuccess'	: $_logicResults = $this -> logicSuccess();	break;
			case 'view'			: $_logicResults = $this -> logicView(	$_sqlConnection, $_isXHRequest, $_logicResult);	break;
			case 'edit'			: $_logicResults = $this -> logicEdit(	$_sqlConnection, $_isXHRequest, $_logicResult);	break;	
			case 'create'		: $_logicResults = $this -> logicCreate($_sqlConnection, $_isXHRequest, $_logicResult);	break;
			case 'delete'		: $_logicResults = $this -> logicDelete($_sqlConnection, $_isXHRequest, $_logicResult);	break;	
		}

		if(!$_logicResults)
		{
			##	Default View
			$_logicResults = $this -> logicView($_sqlConnection, $_isXHRequest, $_logicResult);	
		}

	}

	public function
	logicView(&$_sqlConnection, $_isXHRequest, &$_logicResult)
	{

		if(!isset($this -> m_aObject -> params))
		{
			$modelCondition = new CModelCondition();
			$modelCondition -> where('object_id', $this -> m_aObject -> object_id);

			$this -> m_modelSimple -> load($_sqlConnection, $modelCondition);




			if(CSession::instance() -> isAuthed($this -> m_modelSimple -> getDataInstance() -> params) !== false)
			{
				$this -> logicSuccess();
			}


			$this -> m_modelSimple -> getDataInstance() -> params = json_decode($this -> m_modelSimple -> getDataInstance() -> params);


			$modelCondition = new CModelCondition();
			$modelCondition -> where('object_id', $this -> m_modelSimple -> getDataInstance() -> params -> object_id);
			
			$_pModelLoginObjects	 =	new modelLoginObjects();
			$_pModelLoginObjects	->	load($_sqlConnection, $modelCondition);	

			$this -> setView(	
							'view',	
							'',
							[
								'object' 	=> $this -> m_modelSimple -> getDataInstance(),
								'login_objects' => $_pModelLoginObjects -> getDataInstance()
							]
							);

		}
		else
		{
			// set data as property

			$object		= json_encode($this -> m_aObject -> params,JSON_HEX_QUOT | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE);

			unset($this -> m_aObject -> params);

			$this -> m_aObject -> params		= json_decode($object);

			// authed check
			if(CSession::instance() -> isAuthed($this -> m_aObject -> params -> object_id) !== false)
			{
				$this -> logicSuccess();
			}


			$modelCondition = new CModelCondition();
			$modelCondition -> where('object_id', $this -> m_aObject -> params -> object_id);
			
			// default view

			$_pModelLoginObjects	 =	new modelLoginObjects();
			$_pModelLoginObjects	->	load($_sqlConnection, $modelCondition);	

			$this -> setView(
							'view',
							'', 
							[ 
								'object' => $this -> m_aObject,
								'login_objects' => $_pModelLoginObjects -> getDataInstance()
							]
							);
		}

	}

	public function
	logicCreate(&$_sqlConnection, $_isXHRequest, &$_logicResult)
	{
		##	XHR Function call

		if($_isXHRequest !== false && $_isXHRequest === 'cms-insert-module')
		{
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			$_dataset['object_id'] 	= $this -> m_aObject -> object_id;
			$_dataset['body'] 		= '';

			$_dataset['params']['object_id'] 	= '';
			$_dataset['params']['labels'] 		= [];

			$_dataset['params'] = json_encode($_dataset['params'], JSON_HEX_QUOT | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE);

			if(!$this -> m_modelSimple -> create($_sqlConnection, $_dataset))
			{
				$_bValidationErr =	true;
				$_bValidationMsg =	'sql insert failed';
			}
			else
			{
				$this -> setView(	
								'edit',	
								'',
								[
									'object' 	=> $this -> m_modelSimple -> getDataInstance()
								]
								);

				$_bValidationDta['html'] = $this -> m_pView -> getHTML();
			}

			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call

		}	
	}


	public function
	logicEdit(&$_sqlConnection, $_isXHRequest, &$_logicResult)
	{


		##	XHR Function call

		if($_isXHRequest !== false)
		{
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

		
			switch($_isXHRequest)
			{
				case 'edit'  :	// Update object


								$_pFormVariables =	new CURLVariables();
								$_request		 =	[];
								$_request[] 	 = 	[	"input" => "cms-object-id", "output" => "object_id", 	"validate" => "strip_tags|!empty" ]; 
								$_request[] 	 = 	[	"input" => "login-object-id", "output" => "login-object-id", "validate" => "strip_tags|!empty" ]; 
								$_request[] 	 = 	[	"input" => "login-object-redirect", "output" => "body", "validate" => "strip_tags|!empty" ]; 
								$_request[] 	 = 	[	"input" => "field_label", "output" => "field_label", "validate" => "strip_tags|trim" ]; 
								$_pFormVariables-> retrieve($_request, false, true); // POST 
								$_aFormData		 = $_pFormVariables ->getArray();


								if(empty($_aFormData['object_id'])) 		{ 	$_bValidationErr = true; 	$_bValidationDta[] = 'cms-object-id'; 			}

								if(!$_bValidationErr)
								{

									$_aFormData['params']['object_id'] 	= $_aFormData['login-object-id'];
									$_aFormData['params']['labels'] 	= $_aFormData['field_label'];

									$_aFormData['params'] = json_encode($_aFormData['params'], JSON_HEX_QUOT | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE);

									unset($_aFormData['login-object-id']);
									unset($_aFormData['field_label']);

									if($this -> m_modelSimple -> update($_sqlConnection, $_aFormData))
									{
										$_bValidationMsg = 'Object updated';

										$this -> m_modelPageObject = new modelPageObject();

										$_objectUpdate['object_id']		=	$_aFormData['object_id'];
										$_objectUpdate['time_update']		=	time();
										$_objectUpdate['update_by']			=	0;
										$_objectUpdate['update_reason']		=	'';

										$this -> m_modelPageObject -> update($_sqlConnection, $_objectUpdate);
									
									}
									else
									{
										$_bValidationMsg .= 'Unknown error on sql query';
										$_bValidationErr = true;
									}											
								}
								else	// Validation Failed
								{
									$_bValidationMsg .= 'Data validation failed - object was not updated';
									$_bValidationErr = true;
								}

								break;
			}
			
			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call

		}	

		$modelCondition = new CModelCondition();
		$modelCondition -> where('object_id', $this -> m_aObject -> object_id);

		$this -> m_modelSimple -> load($_sqlConnection, $modelCondition);

		$_pModelLoginObjects	 =	new modelLoginObjects();
		$_pModelLoginObjects	->	load($_sqlConnection);	

		$this -> setView(	
						'edit',	
						'',
						[
							'object' 		=> $this -> m_modelSimple -> getDataInstance(),
							'login_objects' => $_pModelLoginObjects -> getDataInstance()
						]
						);

		return true;


	}


	public function
	logicDelete(&$_sqlConnection, $_isXHRequest, &$_logicResult)
	{

		##	XHR Function call

		if($_isXHRequest !== false)
		{
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];
		
			switch($_isXHRequest)
			{
				case 'delete'  :	// Update object

									$_pFormVariables =	new CURLVariables();
									$_request		 =	[];
									$_request[] 	 = 	[	"input" => "cms-object-id",  "output" => "object_id", 	"validate" => "strip_tags|!empty" ]; 
									$_pFormVariables-> retrieve($_request, false, true); // POST 
									$_aFormData		 = $_pFormVariables ->getArray();

									if(empty($_aFormData['object_id'])) 		{ 	$_bValidationErr = true; 	$_bValidationDta[] = 'cms-object-id'; 			}

									if(!$_bValidationErr)
									{
										$modelCondition = new CModelCondition();
										$modelCondition -> where('object_id', $_aFormData['object_id']);

										if($this -> m_modelSimple -> delete($_sqlConnection, $modelCondition))
										{
											$_objectModel  	 = new modelPageObject();
											$_objectModel	-> delete($_sqlConnection, $_aFormData);

											$_bValidationMsg = 'Object deleted';
										
										}
										else
										{
											$_bValidationMsg .= 'Unknown error on sql query';
											$_bValidationErr = true;
										}											
									}
									else	// Validation Failed
									{
										$_bValidationMsg .= 'Data validation failed - object was not updated';
										$_bValidationErr = true;
									}

									break;
			}
			
			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call

		}	

		return false;
	}

	public function
	logicSuccess()
	{
		$_redirectTarget = (empty($this -> m_aObject -> body) ? $_SERVER['REQUEST_URI'] : CMS_SERVER_URL . $this -> m_aObject -> body );
		header("Location: ". $_redirectTarget ); 
		exit;
	}

			
}

?>