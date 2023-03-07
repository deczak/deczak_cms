<?php

require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSimple.php';	
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelLoginObjects.php';

class controllerLoginForm extends cmsControllerSimple
{
	protected $m_modelSimpleName;

	public function
	__construct(object $_moduleInfo, object &$_objectInfo)
	{
		parent::__construct($_moduleInfo, $_objectInfo);

		##	Set user default right in this module

		$this->setRightOfPublicAccess('view');	
		
		if($this -> isBackendMode())
			$this->m_modelSimpleName = 'modelBackendSimple';
		else
			$this->m_modelSimpleName = 'modelSimple';	
	}

	public function
	logic(CDatabaseConnection &$_pDatabase, array $_rcaTarget, ?object $_xhrInfo, &$_logicResult, bool $_pageEditMode, object $requestInfo) : bool
	{
		##	Get action by request term, can return actions that not listed in module.json

		$action = $this -> getAction($_rcaTarget, $_xhrInfo, $_pageEditMode);

		##	Validate action with user right, xhr request will end in this function

		if(!$this -> validateRight($action, $_xhrInfo, ['loginSuccess']))
			return false;
		
		##	If the user does not have the right, he will not reach this point of process
		##	Public user needs the RightOfPublicAccess call to get here

		## 	Call Logic function, if there goes something wrong, the default view get called (except on xhr calls)

		$logicDone = false;

		if($_xhrInfo === null) // NON XHR
		switch($action)
		{
			case 'edit'		    : $logicDone = $this -> logicEdit($_pDatabase);		break; // cmsControllerSimple::logicEdit
			case 'loginSuccess'	: $logicDone = $this -> logicSuccess($_pDatabase);	break;
		}

		if($_xhrInfo !== null && $_xhrInfo -> objectId === $this -> objectInfo -> object_id) // XHR
		switch($action)
		{
			case 'create' 	: $logicDone = $this -> logicInsert($_pDatabase, $_xhrInfo,); 	break; // page object should exists at this point
			case 'edit'	    :
			case 'update' 	: $logicDone = $this -> logicUpdate($_pDatabase, $_xhrInfo); 	break;
			case 'delete' 	: $logicDone = $this -> logicDelete($_pDatabase, $_xhrInfo); 	break;	
		}

		if(!$logicDone) // Default
			$logicDone = $this -> logicView($_pDatabase); // cmsControllerSimple::logicView
	
		return false;
	}

	/**
	 * 	Overloaded parent ::logicView
	 */
	public function logicView(CDatabaseConnection &$_pDatabase) : bool
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

	/**
	 * 	Output function for page edit mode
	 */
	public function logicEdit(CDatabaseConnection &$_pDatabase) : bool
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

	/**
	 * 	XHR process function to update object data
	 */
	public function logicUpdate(CDatabaseConnection &$_pDatabase, object $_xhrInfo)
	{
		$queryValidationString = QueryValidation::STRIP_TAGS | QueryValidation::IS_NOTEMPTY;
		$queryValidationString2 = QueryValidation::STRIP_TAGS | QueryValidation::TRIM;

		##	Body

		$requestQuery = new cmsRequestQuery(true);
		$requestQuery->post('login-object-redirect')->validate($queryValidationString)->default(20)->out('body')->exec();
		$sOBody = $requestQuery->toObject();
	
		##	Parameters

		$requestQuery = new cmsRequestQuery(true);
		$requestQuery->post('login-object-id')->validate($queryValidationString)->out('object_id')->exec();
		$requestQuery->post('field_label')->validate($queryValidationString2)->out('labels')->exec();
		$sOParams = $requestQuery->toObject();

		if($this->logicUpdateExec(
			$_pDatabase, 
			$_xhrInfo, 
			$sOBody->body, 
			$sOParams,
			cmsControllerSimple::PREVENT_XHRRESPONSE
			)
		) {
			if($this -> getInstallMode())
				return true;
				
			tk::xhrResponse(
				200,
				[],
				);	
		}

		return false;
	}

	/**
	 * 	XHR process function to delete the object
	 */
	public function logicDelete(CDatabaseConnection &$_pDatabase, object $_xhrInfo)
	{
		return $this->logicDeleteExec(
			$_pDatabase, 
			$_xhrInfo
			);
	}

	/**
	 * 	XHR process function to insert the object
	 */
	public function logicInsert(CDatabaseConnection &$_pDatabase, object $_xhrInfo)
	{
		$responseData = 	[];

		$sOParams = new stdClass;
		$sOParams->object_id 		= '';
		$sOParams->labels 			= []; 

		$simpleObject = $this -> m_modelSimpleName::new([
			'object_id' => (int)$this -> objectInfo -> object_id,
			'body' 		=> '',
			'params' 	=> $sOParams,
		], $_pDatabase);
		
		if(!$simpleObject->save())
		{
			tk::xhrResponse(
				200,
				[],
				1, 
				'sql insert failed'
				);	
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

		tk::xhrResponse(
			200,
			$responseData
			);	

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


/*
class	controllerLogiddnForm extends CController
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


			$sOParams = new stdClass;
			$sOParams->object_id 		= '';
			$sOParams->labels 			= []; 

			$simpleObject = $this -> m_modelSimpleName::new([
				'object_id' => (int)$this -> objectInfo -> object_id,
				'body' 		=> '',
				'params' 	=> $sOParams,
			], $_pDatabase);
			

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
									


									$simpleObject = $this -> m_modelSimpleName::where('object_id', '=', $_xhrInfo -> objectId)->one();
									$simpleObject->params->object_id = $_aFormData['login-object-id'];
									$simpleObject->params->labels = $_aFormData['field_label'];
									$simpleObject->body = $_aFormData['body'];

									if($simpleObject->save())
									{


										if($this -> getInstallMode())
											return true;
					
										$validationMsg = 'Object updated';

										$object = modelPageObject::
											  db($_pDatabase)
											->where('object_id', '=', $_xhrInfo -> objectId)
											->one();

										$object->update_time 	= time();
										$object->update_by 		= 0;
										$object->update_reason	= '';
										$object->save();
									
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

										modelPageObject::
											  db($_pDatabase)
											->where('object_id', '=', $_xhrInfo -> objectId)
											->delete();
											
										$validationMsg = 'Object deleted';
																		
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
*/