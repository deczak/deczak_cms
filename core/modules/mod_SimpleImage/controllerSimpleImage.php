<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSimple.php';	

class	controllerSimpleImage extends CController
{
	private	$m_modelSimple;
		
	public function
	__construct(object $_module, object &$_object)
	{
		parent::__construct($_module, $_object);

		$this -> m_modelSimple = new modelSimple();

		$this -> moduleInfo -> user_rights[] = 'view';	// add view right as default for everyone
	}
	
	public function
	logic(CDatabaseConnection &$_pDatabase, array $_rcaTarget, ?object $_xhrInfo, &$_logicResult, bool $_bEditMode) : bool
	{
		##	Set default target if not exists

		$controllerAction = $this -> getControllerAction_v2($_rcaTarget, $_xhrInfo, 'view');

		##	Check user rights for this target
	
		if(!$this -> detectRights($controllerAction))
		{
			if($_xhrInfo !== null && $_xhrInfo -> isXHR)
			{
				$validationErr =	true;
				$validationMsg =	CLanguage::get() -> string('ERR_PERMISSON');
				$responseData = 	[];

				tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
			}

			CMessages::instance() -> addMessage(CLanguage::get() -> string('ERR_PERMISSON') , MSG_WARNING);
			return false;
		}

		if($_bEditMode && $_xhrInfo === null) 
			$controllerAction = 'edit';
			
		if($_xhrInfo !== null && $_xhrInfo -> isXHR && $_xhrInfo -> objectId === $this -> objectInfo -> object_id)
			$controllerAction = 'xhr_'. $_xhrInfo -> action;

		##	Call sub-logic function by target, if there results are false, we make a fall back to default view

		$enableEdit 	= $this -> existsUserRight('edit');
		$enableDelete	= $enableEdit;

		if(!$enableEdit)
			$controllerAction = 'view';
			
		$logicDone = false;
		switch($controllerAction)
		{
			case 'edit'		  : $logicDone = $this -> logicEdit($_pDatabase, $enableEdit, $enableDelete); break;
			case 'xhr_create' : $logicDone = $this -> logicXHRCreate($_pDatabase, $_xhrInfo, $enableEdit, $enableDelete); break;	
			case 'xhr_edit'   : $logicDone = $this -> logicXHREdit($_pDatabase, $_xhrInfo, $enableEdit, $enableDelete); break;
			case 'xhr_delete' : $logicDone = $this -> logicXHRDelete($_pDatabase, $_xhrInfo, $enableEdit, $enableDelete); break;	
		}

		if(!$logicDone) // Default
			$logicDone = $this -> logicView($_pDatabase, $enableEdit, $enableDelete);	
	
		return $logicDone;
	}

	private function
	logicView(CDatabaseConnection &$_pDatabase, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{
		$modelCondition = new CModelCondition();
		$modelCondition -> where('object_id', $this -> objectInfo -> object_id);

		$this -> m_modelSimple -> load($_pDatabase, $modelCondition);

		$this -> m_modelSimple -> getResult()[0] -> params = json_decode($this -> m_modelSimple -> getResult()[0] -> params);

		$this -> setView(	
						'view',	
						'',
						[
							'object' 	=> $this -> m_modelSimple -> getResult()[0]
						]
						);

		return true;
	}

	private function
	logicEdit(CDatabaseConnection &$_pDatabase, bool $enableEdit, bool $enableDelete) : bool
	{
		$modelCondition = new CModelCondition();
		$modelCondition -> where('object_id', $this -> objectInfo -> object_id);

		$this -> m_modelSimple -> load($_pDatabase, $modelCondition);

		$this -> m_modelSimple -> getResult()[0] -> params = json_decode($this -> m_modelSimple -> getResult()[0] -> params);

		$this -> setView(	
						'edit',	
						'',
						[
							'object' 	=> $this -> m_modelSimple -> getResult()[0]
						]
						);

		return true;
	}

	private function
	logicXHREdit(CDatabaseConnection &$_pDatabase, object $_xhrInfo, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{
		$validationErr   = false;
		$validationMsg   = 'OK';
		$responseData    = [];
	
		$pURLVariables =	new CURLVariables();
		$requestList		 =	[];
		$requestList[] 	 = 	[	"input" => "simple-image-id", "validate" => "!empty" ]; 
		$requestList[] 	 = 	[	"input" => "simple-image-position-x", "validate" => "!empty" ]; 
		$requestList[] 	 = 	[	"input" => "simple-image-position-x-unit", "validate" => "!empty" ]; 
		$requestList[] 	 = 	[	"input" => "simple-image-position-y", "validate" => "!empty" ]; 
		$requestList[] 	 = 	[	"input" => "simple-image-position-y-unit", "validate" => "!empty" ]; 
		$requestList[] 	 = 	[	"input" => "simple-image-height", "validate" => "!empty" ]; 
		$requestList[] 	 = 	[	"input" => "simple-image-height-unit", "validate" => "!empty" ]; 
		$requestList[] 	 = 	[	"input" => "simple-image-fit", "validate" => "!empty" ]; 
		$pURLVariables-> retrieve($requestList, false, true); // POST 
		$urlVarList		 = $pURLVariables ->getArray();


		if(empty($_xhrInfo -> objectId)) 		{ 	$validationErr = true; 	$responseData[] = 'cms-object-id'; 			}

		if(!$validationErr)
		{
			$modelCondition = new CModelCondition();
			$modelCondition -> where('object_id', $_xhrInfo -> objectId);
					
			$valuesList = [];
			
			$valuesList['params']	= 	[
											"id"				=> $urlVarList['simple-image-id'],
											"position_x"		=> $urlVarList['simple-image-position-x'],
											"position_x_unit"	=> $urlVarList['simple-image-position-x-unit'],
											"position_y"		=> $urlVarList['simple-image-position-y'],
											"position_y_unit"	=> $urlVarList['simple-image-position-y-unit'],
											"height"			=> $urlVarList['simple-image-height'],
											"height_unit"		=> $urlVarList['simple-image-height-unit'],
											"fit"				=> $urlVarList['simple-image-fit'],
										];

			$valuesList['params']	 = 	json_encode($valuesList['params'], JSON_FORCE_OBJECT);
			
			$objectId = $_xhrInfo -> objectId;

			if($this -> m_modelSimple -> update($_pDatabase, $valuesList, $modelCondition))
			{
				$validationMsg = 'Object updated';

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

	private function
	logicXHRCreate(CDatabaseConnection &$_pDatabase, object $_xhrInfo, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{

			$validationErr =	false;
			$validationMsg =	'';
			$responseData = 	[];

			$_dataset['object_id'] 	= $this -> objectInfo -> object_id;
			$_dataset['body'] 		= '';
			$_dataset['params'] 	= '';


/*

		$_dataset['params']		= 	[
										"template"			=> '',
										"display_hidden"	=> '',
										"parent_node_id"	=> ''
									];
*/

			
			if(!$this -> m_modelSimple -> insert($_pDatabase, $_dataset, MODEL_RESULT_APPEND_DTAOBJECT))
			{
				$validationErr =	true;
				$validationMsg =	'sql insert failed';
			}
			else
			{
				$this -> setView(	
								'edit',	
								'',
								[
									'object' 	=> $this -> m_modelSimple -> getResult()[0]
								]
								);

				$responseData['html'] = $this -> m_pView -> getHTML();
			}

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		
		return false;
	}
	
	private function
	logicXHRDelete(CDatabaseConnection &$_pDatabase, object $_xhrInfo, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{
		$validationErr =	false;
		$validationMsg =	'';
		$responseData = 	[];
		
		if(empty($_xhrInfo -> objectId))
		{ 	
			$validationErr	= true; 	
			$responseData[] = 'cms-object-id'; 			
		}

		if(!$validationErr)
		{
			$modelCondition = new CModelCondition();
			$modelCondition -> where('object_id', $_xhrInfo -> objectId);

			if($this -> m_modelSimple -> delete($_pDatabase, $modelCondition))
			{
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
}