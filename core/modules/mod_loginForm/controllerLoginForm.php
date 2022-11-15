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
			$this -> m_modelSimpleName = 'modelBackendSimple';
		else
			$this -> m_modelSimpleName = 'modelSimple';


		$this -> moduleInfo -> user_rights[] = 'view';			// add view right as default for everyone
	}
	/*
	public function
	logic(CDatabaseConnection &$_pDatabase, array $_rcaTarget, $_isXHRequest, &$_logicResult, bool $_bEditMode)
	{
		$_controllerAction = $this -> getControllerAction($_rcaTarget, 'view');

		if(!$this -> detectRights($_controllerAction) && $_controllerAction != 'loginSuccess')
		{
			if($_isXHRequest !== false)
			{
				$validationErr =	true;
				$validationMsg =	CLanguage::string('ERR_PERMISSON');
				$responseData = 	[];

				tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
			}

			CMessages::add(CLanguage::string('ERR_PERMISSON') , MSG_WARNING);
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
	*/
	public function
	logic(CDatabaseConnection &$_pDatabase, array $_rcaTarget, ?object $_xhrInfo, &$_logicResult, bool $_bEditMode) : bool
	{
		##	Set default target if not exists

		$controllerAction = $this -> getControllerAction_v2($_rcaTarget, $_xhrInfo, 'view');

		##	Check user rights for this target
		
		if(!$this -> detectRights($controllerAction) && $controllerAction != 'loginSuccess')
		{
			if($_xhrInfo !== null)
			{
				$validationErr =	true;
				$validationMsg =	CLanguage::string('ERR_PERMISSON');
				$responseData  = 	[];


				tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
			}

			CMessages::add(CLanguage::string('ERR_PERMISSON') , MSG_WARNING);
			return false;
		}

		if($_bEditMode && $_xhrInfo === null) 
			$controllerAction = 'edit';
			
		if($_xhrInfo !== null && $_xhrInfo -> isXHR && $_xhrInfo -> objectId === $this -> objectInfo -> object_id)
			$controllerAction = 'xhr_'. $_xhrInfo -> action;

		##	Call sub-logic function by target, if there results are false, we make a fall back to default view

		$enableEdit 	= $this -> existsUserRight('edit');
		$enableDelete	= $enableEdit;



		$logicDone = false;
		switch($controllerAction)
		{
			case 'edit'		    : $logicDone = $this -> logicEdit($_pDatabase); break;
			case 'loginSuccess'	: $logicDone = $this -> logicSuccess($_pDatabase);	break;

			case 'xhr_edit'     : $logicDone = $this -> logicXHREdit($_pDatabase, $_xhrInfo); break;
			case 'xhr_create'   : $logicDone = $this -> logicXHRCreate($_pDatabase, $_xhrInfo); break;	
			case 'xhr_delete'   : $logicDone = $this -> logicXHRDelete($_pDatabase, $_xhrInfo); break;	

		}

		if(!$logicDone) // Default
			$logicDone = $this -> logicView($_pDatabase);	
	
		return $logicDone;
	}

	public function
	logicView(CDatabaseConnection &$_pDatabase) : bool
	{




if(isset($_GET['logout']))
{
	CLogin::logout($_pDatabase);
}






		if(empty($this -> objectInfo -> params))
		{
			

		$simpleObject = $this -> m_modelSimpleName::where('object_id', '=', $this -> objectInfo -> object_id)->one();







			$this -> objectInfo -> params = $simpleObject  -> params;
			$this -> objectInfo -> body = $simpleObject  -> body;

			if(CSession::instance() -> isAuthed($simpleObject  -> params -> object_id) !== false)
			{
				$this -> logicSuccess($_pDatabase);
			}



			$modelCondition = new CModelCondition();
			$modelCondition -> where('object_id', $simpleObject  -> params -> object_id);
			
			$_pModelLoginObjects	 =	new modelLoginObjects();
			$_pModelLoginObjects	->	load($_pDatabase, $modelCondition);	

			$this -> setView(	
							'view',	
							'',
							[
								'object' 	=> $simpleObject ,
								'login_object' => $_pModelLoginObjects -> getResult()[0]
							]
							);

		}
		else
		{
			// set data as property

			$object = new stdClass;
			$object -> params		= $this -> objectInfo -> params;
			$object -> object_id	= $this -> objectInfo -> object_id;

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
	logicXHRCreate(CDatabaseConnection &$_pDatabase, object $_xhrInfo) : bool
	{

			$validationErr =	false;
			$validationMsg =	'';
			$responseData = 	[];


			/*
			$_dataset['object_id'] 	= $this -> objectInfo -> object_id;
			$_dataset['body'] 		= '';

			$_dataset['params']['object_id'] 	= '';
			$_dataset['params']['labels'] 		= [];

			$_dataset['params'] = json_encode($_dataset['params'], JSON_HEX_QUOT | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE);
			*/


			$sOParams = new stdClass;
			$sOParams->object_id 		= '';
			$sOParams->labels 			= []; 

			$simpleObject = $this -> m_modelSimpleName::new([
				'object_id' => (int)$this -> objectInfo -> object_id,
				'body' 		=> '',
				'params' 	=> $sOParams,
			]);
			

			if(!$simpleObject->save())
			{
				$validationErr =	true;
				$validationMsg =	'sql insert failed';
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
									'object' 	=> $simpleObject,
									'login_objects' => $_pModelLoginObjects -> getResult()
								]
								);

				$responseData['html'] = $this -> m_pView -> getHTML();
			}

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call



		return false;
	}

	public function
	logicEdit(CDatabaseConnection &$_pDatabase) : bool
	{



		$simpleObject = $this -> m_modelSimpleName::where('object_id', '=', $this -> objectInfo -> object_id)->one();




		$_pModelLoginObjects	 =	new modelLoginObjects();
		$_pModelLoginObjects	->	load($_pDatabase);	

		$this -> setView(	
						'edit',	
						'',
						[
							'object' 		=> $simpleObject,
							'login_objects' => $_pModelLoginObjects -> getResult()
						]
						);

		return true;


	}

	public function
	logicXHREdit(CDatabaseConnection &$_pDatabase, object $_xhrInfo) : bool
	{

			$validationErr =	false;
			$validationMsg =	'';
			$responseData = 	[];



								$_pFormVariables =	new CURLVariables();
								$_request		 =	[];
								#$_request[] 	 = 	[	"input" => "cms-object-id", "output" => "object_id", 	"validate" => "strip_tags|!empty" ]; 
								$_request[] 	 = 	[	"input" => "login-object-id", "output" => "login-object-id", "validate" => "strip_tags|!empty" ]; 
								$_request[] 	 = 	[	"input" => "login-object-redirect", "output" => "body", "validate" => "strip_tags|!empty" ]; 
								$_request[] 	 = 	[	"input" => "field_label", "output" => "field_label", "validate" => "strip_tags|trim" ]; 
								$_pFormVariables-> retrieve($_request, false, true); // POST 
								$_aFormData		 = $_pFormVariables ->getArray();


								if(empty($_xhrInfo -> objectId))
								{
									$validationErr = true;
									$responseData[] = 'cms-object-id';
								}
								
								if(!$validationErr)
								{
									/*
									$_aFormData['params']['object_id'] 	= $_aFormData['login-object-id'];
									$_aFormData['params']['labels'] 	= $_aFormData['field_label'];

									$_aFormData['params'] = json_encode($_aFormData['params'], JSON_HEX_QUOT | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE);

									unset($_aFormData['login-object-id']);
									unset($_aFormData['field_label']);

									*/


									#$modelCondition -> where('object_id', $_aFormData['object_id']);




									$simpleObject = $this -> m_modelSimpleName::where('object_id', '=', $_xhrInfo -> objectId)->one();
									$simpleObject->params->object_id = $_aFormData['login-object-id'];
									$simpleObject->params->labels = $_aFormData['field_label'];
									$simpleObject->body = $_aFormData['body'];

									if($simpleObject->save())
									{


										if($this -> getInstallMode())
											return true;
					
										$validationMsg = 'Object updated';

									$modelCondition = new CModelCondition();
									$modelCondition -> where('object_id', $_xhrInfo -> objectId);

										$this -> m_modelPageObject = new modelPageObject();

										$_objectUpdate['update_time']		=	time();
										$_objectUpdate['update_by']			=	0;
										$_objectUpdate['update_reason']		=	'';

										$this -> m_modelPageObject -> update($_pDatabase, $_objectUpdate, $modelCondition);
									
									}
									else
									{
										$validationMsg .= 'Unknown error on sql query';
										$validationErr = true;
									}											
								}
								else	// Validation Failed
								{
									$validationMsg .= 'Data validation failed - object was not updated';
									$validationErr = true;
								}

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call


		return false;


	}

	public function
	logicXHRDelete(CDatabaseConnection &$_pDatabase, object $_xhrInfo) : bool
	{

			$validationErr =	false;
			$validationMsg =	'';
			$responseData = 	[];
		
	

									if(empty($_xhrInfo -> objectId))
									{
										$validationErr = true;
										$responseData[] = 'cms-object-id';
									}

									if(!$validationErr)
									{

						$simpleObject = $this -> m_modelSimpleName::where('object_id', '=', $_xhrInfo -> objectId)->one();

						if($simpleObject->delete())

										{
										$modelCondition = new CModelCondition();
										$modelCondition -> where('object_id', $_xhrInfo -> objectId);
											$_objectModel  	 = new modelPageObject();
											$_objectModel	-> delete($_pDatabase, $modelCondition);

											$validationMsg = 'Object deleted';
										
										}
										else
										{
											$validationMsg .= 'Unknown error on sql query';
											$validationErr = true;
										}											
									}
									else	// Validation Failed
									{
										$validationMsg .= 'Data validation failed - object was not updated';
										$validationErr = true;
									}

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call


		return false;
	}

	public function
	logicSuccess(CDatabaseConnection &$_pDatabase)
	{
	
		$_redirectTarget = (empty($this -> objectInfo -> body) ? $_SERVER['REQUEST_URI'] : CMS_SERVER_URL . $this -> objectInfo -> body );

		header("Location: ". $_redirectTarget ); 
		exit;
	}
}
