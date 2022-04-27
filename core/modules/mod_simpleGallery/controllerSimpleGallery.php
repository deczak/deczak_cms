<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSimple.php';	

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CModulesTemplates.php';	

class	controllerSimpleGallery extends CController
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
			case 'xhr_view'   : $logicDone = $this -> logicXHRView($_pDatabase, $_xhrInfo, $enableEdit, $enableDelete); break;	
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


		if(empty($this -> m_modelSimple -> getResult()[0] -> params -> itemList))
			$this -> m_modelSimple -> getResult()[0] -> params -> itemList = [];

		$this -> m_modelSimple -> getResult()[0] -> params -> itemList = (array)$this -> m_modelSimple -> getResult()[0] -> params -> itemList;




		$this -> setView(	
						'view',	
						'',
						[
							'object' 	 => $this -> m_modelSimple -> getResult()[0],
							'itemList'		  => $this -> processGalleryItems($_pDatabase, $this -> m_modelSimple -> getResult()[0] -> params -> itemList),
						]
						);

		return true;
	}

	private function
	logicXHRView(CDatabaseConnection &$_pDatabase, object $_xhrInfo, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{
		$validationErr   = false;
		$validationMsg   = 'OK';
		$responseData    = [];

		$this->logicView($_pDatabase, $_enableEdit, $_enableDelete);

		ob_start();
		$this->view();
		$responseData['html'] = ob_get_contents();
		ob_end_clean();

		$responseData['objectId'] = $_xhrInfo -> objectId;

		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
	
		return true;
	}

	private function
	logicEdit(CDatabaseConnection &$_pDatabase, bool $enableEdit, bool $enableDelete) : bool
	{
		$modelCondition = new CModelCondition();
		$modelCondition -> where('object_id', $this -> objectInfo -> object_id);

		$this -> m_modelSimple -> load($_pDatabase, $modelCondition);
		
		$this -> m_modelSimple -> getResult()[0] -> params = json_decode($this -> m_modelSimple -> getResult()[0] -> params);


		/*
				$moduleTemplate = new CModulesTemplates();
				$moduleTemplate ->	load('simpleGallery', $this -> m_modelSimple -> getResult()[0] -> params -> template ?? '');

				$moduleTemplates = new CModulesTemplates();
				$moduleTemplates ->	load('simpleGallery');
		*/


		if(empty($this -> m_modelSimple -> getResult()[0] -> params -> itemList))
			$this -> m_modelSimple -> getResult()[0] -> params -> itemList = [];

		$this -> m_modelSimple -> getResult()[0] -> params -> itemList = (array)$this -> m_modelSimple -> getResult()[0] -> params -> itemList;



		$this -> setView(	
						'edit',	
						'',
						[
							'object' 	=> $this -> m_modelSimple -> getResult()[0],
			#						'currentTemplate'	=> $moduleTemplate -> templatesList,
			#						'avaiableTemplates'	=> $moduleTemplates -> templatesList,
							'itemList'		  => $this -> processGalleryItems($_pDatabase, $this -> m_modelSimple -> getResult()[0] -> params -> itemList),
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
		$requestList[] 	 = 	[	"input" => "simple-gallery-item", "validate" => "!empty" ]; 
		$requestList[] 	 = 	[	"input" => "simple-gallery-display-divider", "validate" => "!empty" ];
		$requestList[] 	 = 	[	"input" => "simple-gallery-format", "validate" => "!empty" ];
		$pURLVariables-> retrieve($requestList, false, true); // POST 
		$urlVarList		 = $pURLVariables ->getArray();


		if(empty($_xhrInfo -> objectId)) 		{ 	$validationErr = true; 	$responseData[] = 'cms-object-id'; 			}

		if(!$validationErr)
		{
			$modelCondition = new CModelCondition();
			$modelCondition -> where('object_id', $_xhrInfo -> objectId);


			$valuesList = [];

			$valuesList['params']	= 	[
											"display_divider"	=> $urlVarList['simple-gallery-display-divider'],
											"format"			=> $urlVarList['simple-gallery-format'],
											"itemList"			=> $urlVarList['simple-gallery-item'],
										];

			$valuesList['params']	 = 	json_encode($valuesList['params'], JSON_FORCE_OBJECT);

			$valuesList['body']		 = 	'';
			
			$objectId = $_xhrInfo -> objectId;

			if($this -> m_modelSimple -> update($_pDatabase, $valuesList, $modelCondition))
			{
				$validationMsg = 'Object updated';

				$this -> m_modelPageObject = new modelPageObject();

				$_objectUpdate['update_time']		=	time();
				$_objectUpdate['update_by']			=	0;
				$_objectUpdate['update_reason']		=	'';

				$this -> m_modelPageObject -> update($_pDatabase, $_objectUpdate, $modelCondition);


				$this->logicXHRView($_pDatabase, $_xhrInfo, $_enableEdit, $_enableDelete);
			
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
								'object' 	=> $this -> m_modelSimple -> getResult()[0],
								'itemList'			=> [],
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

	private function
	processGalleryItems(CDatabaseConnection &$_pDatabase, &$itemsList) : array
	{
		
		$collectedImageList = [];

		foreach($itemsList as $item)
		{

			
			switch($item -> {'listing-type'})
			{
				case 'image':



					MEDIATHEK::getItem($item -> {'item-path'}.'/', $collectedImageList);


					break;

				case 'folder':




					MEDIATHEK::getItemsList($item -> {'item-path'}.'/', $collectedImageList, true);


					break;
			}

		}
		return $collectedImageList;
	}
}