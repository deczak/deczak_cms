<?php

require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSimple.php';	
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelLoginObjects.php';

class	controllerLoginForm extends CController
{
	public function
	__construct($_module, &$_object, bool $_backendCall = false)
	{		
		parent::__construct($_module, $_object, $_backendCall);

		if($this -> isBackendCall())
			$this -> m_modelSimple = new modelBackendSimple();
		else
			$this -> m_modelSimple = new modelSimple();


		$this -> m_aModule -> user_rights[] = 'view';			// add view right as default for everyone
	}
	
	public function
	logic(CDatabaseConnection &$_pDatabase, array $_rcaTarget, $_isXHRequest, &$_logicResult, bool $_bEditMode)
	{
		$_controllerAction = $this -> getControllerAction($_rcaTarget, 'view');

		if(!$this -> detectRights($_controllerAction) && $_controllerAction != 'loginSuccess')
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

		##	

		if($_bEditMode && $_isXHRequest === false) 
			$_controllerAction = 'edit';

		##	Call sub-logic function by target, if there results are false, we make a fall back to default view
		
		$enableEdit 	= $this -> existsUserRight('edit');
		$enableDelete	= $enableEdit;

		$logicResults = false;
		switch($_controllerAction)
		{
			case 'loginSuccess'	: $logicResults = $this -> logicSuccess($_pDatabase);	break;
			case 'view'			: $logicResults = $this -> logicView(	$_pDatabase, $_isXHRequest, $_logicResult);	break;
			case 'edit'			: $logicResults = $this -> logicEdit(	$_pDatabase, $_isXHRequest, $_logicResult);	break;	
			case 'create'		: $logicResults = $this -> logicCreate($_pDatabase, $_isXHRequest, $_logicResult);	break;
			case 'delete'		: $logicResults = $this -> logicDelete($_pDatabase, $_isXHRequest, $_logicResult);	break;	
		}


		if(!$logicResults)
		{
			##	Default View
			$logicResults = $this -> logicView($_pDatabase, $_isXHRequest, $_logicResult);	
		}

	}

	public function
	logicView(CDatabaseConnection &$_pDatabase, $_isXHRequest, &$_logicResult)
	{
		if(empty($this -> m_aObject -> params))
		{
			$modelCondition = new CModelCondition();
			$modelCondition -> where('object_id', $this -> m_aObject -> object_id);

			$this -> m_modelSimple -> load($_pDatabase, $modelCondition);


			$this -> m_modelSimple -> getResult()[0] -> params = json_decode($this -> m_modelSimple -> getResult()[0] -> params);

			$this -> m_aObject -> params = $this -> m_modelSimple -> getResult()[0] -> params;
			$this -> m_aObject -> body = $this -> m_modelSimple -> getResult()[0] -> body;

			if(CSession::instance() -> isAuthed($this -> m_modelSimple -> getResult()[0] -> params -> object_id) !== false)
			{
				$this -> logicSuccess($_pDatabase);
			}



			$modelCondition = new CModelCondition();
			$modelCondition -> where('object_id', $this -> m_modelSimple -> getResult()[0] -> params -> object_id);
			
			$_pModelLoginObjects	 =	new modelLoginObjects();
			$_pModelLoginObjects	->	load($_pDatabase, $modelCondition);	

			$this -> setView(	
							'view',	
							'',
							[
								'object' 	=> $this -> m_modelSimple -> getResult()[0],
								'login_object' => $_pModelLoginObjects -> getResult()[0]
							]
							);

		}
		else
		{
			// set data as property

			$object = new stdClass;
			$object -> params		= json_decode($this -> m_aObject -> params);
			$object -> object_id	= $this -> m_aObject -> object_id;

			// authed check
			if(CSession::instance() -> isAuthed($object -> params -> object_id) !== false)
			{
				$this -> logicSuccess($_pDatabase);
			}

			$modelCondition = new CModelCondition();
			$modelCondition -> where('object_id', $object -> params -> object_id);
			
			// default view

			$_pModelLoginObjects	 =	new modelLoginObjects();
			$_pModelLoginObjects	->	load($_pDatabase, $modelCondition);	

			$this -> setView(
							'view',
							'', 
							[ 
								'object' => $object,
								'login_object' => $_pModelLoginObjects -> getResult()[0]
							]
							);
		}

		return true;

	}

	public function
	logicCreate(CDatabaseConnection &$_pDatabase, $_isXHRequest, &$_logicResult)
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

			if(!$this -> m_modelSimple -> insert($_pDatabase, $_dataset, MODEL_RESULT_APPEND_DTAOBJECT))
			{
				$_bValidationErr =	true;
				$_bValidationMsg =	'sql insert failed';
			}
			else
			{

				if($this -> getInstallMode())
					return true;

				$_pModelLoginObjects	 =	new modelLoginObjects();
				$_pModelLoginObjects	->	load($_pDatabase);	

				$this -> setView(	
								'edit',	
								'',
								[
									'object' 	=> $this -> m_modelSimple -> getResult()[0],
									'login_objects' => $_pModelLoginObjects -> getResult()
								]
								);

				$_bValidationDta['html'] = $this -> m_pView -> getHTML();
			}

			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call

		}	

		return true;
	}

	public function
	logicEdit(CDatabaseConnection &$_pDatabase, $_isXHRequest, &$_logicResult)
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



									$modelCondition = new CModelCondition();
									$modelCondition -> where('object_id', $_aFormData['object_id']);

									if($this -> m_modelSimple -> update($_pDatabase, $_aFormData, $modelCondition))
									{


										if($this -> getInstallMode())
											return true;
					
										$_bValidationMsg = 'Object updated';

										$this -> m_modelPageObject = new modelPageObject();

										$_objectUpdate['update_time']		=	time();
										$_objectUpdate['update_by']			=	0;
										$_objectUpdate['update_reason']		=	'';

										$this -> m_modelPageObject -> update($_pDatabase, $_objectUpdate, $modelCondition);
									
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

		$this -> m_modelSimple -> load($_pDatabase, $modelCondition);

		$_pModelLoginObjects	 =	new modelLoginObjects();
		$_pModelLoginObjects	->	load($_pDatabase);	

		$this -> setView(	
						'edit',	
						'',
						[
							'object' 		=> $this -> m_modelSimple -> getResult()[0],
							'login_objects' => $_pModelLoginObjects -> getResult()
						]
						);

		return true;


	}

	public function
	logicDelete(CDatabaseConnection &$_pDatabase, $_isXHRequest, &$_logicResult)
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

										if($this -> m_modelSimple -> delete($_pDatabase, $modelCondition))
										{
											$_objectModel  	 = new modelPageObject();
											$_objectModel	-> delete($_pDatabase, $modelCondition);

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
	logicSuccess(CDatabaseConnection &$_pDatabase)
	{
	
		$_redirectTarget = (empty($this -> m_aObject -> body) ? $_SERVER['REQUEST_URI'] : CMS_SERVER_URL . $this -> m_aObject -> body );

		header("Location: ". $_redirectTarget ); 
		exit;
	}
}

?>