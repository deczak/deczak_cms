<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSimple.php';	

class	controllerSimpleSource extends CController
{
	public function
	__construct(object $_module, object &$_object)
	{
		parent::__construct($_module, $_object);
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
				$validationMsg =	CLanguage::string('ERR_PERMISSON');
				$responseData = 	[];

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
		$simpleObject = modelSimple::where('object_id', '=', $this -> objectInfo -> object_id)->one();
		
		$this -> setView(	
						'view',	
						'',
						[
							'object' 	=> $simpleObject
						]
						);

		return true;
	}

	private function
	logicEdit(CDatabaseConnection &$_pDatabase, bool $enableEdit, bool $enableDelete) : bool
	{
		$simpleObject = modelSimple::where('object_id', '=', $this -> objectInfo -> object_id)->one();
		
		$this -> setView(	
						'edit',	
						'',
						[
							'object' 	=> $simpleObject 
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
		$requestList[] 	 = 	[	"input" => "simple-text",  "output" => "body", 			"validate" => "!empty" ]; 
		$pURLVariables-> retrieve($requestList, false, true); // POST 
		$urlVarList		 = $pURLVariables ->getArray();

		if(empty($_xhrInfo -> objectId)) 		
		{ 	
			$validationErr = true; 	
			$responseData[] = 'cms-object-id'; 			
		}

		if(!$validationErr)
		{
			$simpleObject = modelSimple::where('object_id', '=', $_xhrInfo -> objectId)->one();
			#$simpleObject->params->... = ;
			$simpleObject->body = $urlVarList['simple-text'];

			if($simpleObject->save())
			{
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

	private function
	logicXHRCreate(CDatabaseConnection &$_pDatabase, object $_xhrInfo, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{
		$validationErr =	false;
		$validationMsg =	'';
		$responseData = 	[];

		$sOParams = new stdClass;

		$simpleObject = modelSimple::new([
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
			$this -> setView(	
							'edit',	
							'',
							[
								'object' 	=> $simpleObject
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
}