<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSimple.php';	

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CModulesTemplates.php';	

class	controllerSimpleGallery extends CController
{
	public function
	__construct(object $_module, object &$_object)
	{
		parent::__construct($_module, $_object);
		$this -> moduleInfo -> user_rights[] = 'view';	// add view right as default for everyone

		$this->publicActionList = [
			'getItems'
		];

		switch($this -> moduleInfo -> module_type) 
		{
			case 'core':	
				$this -> moduleRootDir = CMS_SERVER_ROOT.DIR_CORE.DIR_MODULES;
				break;
							
			case 'mantle':
				$this -> moduleRootDir = CMS_SERVER_ROOT.DIR_MANTLE.DIR_MODULES;
				break;
		}
	}
	
	public function
	logic(CDatabaseConnection &$_pDatabase, array $_rcaTarget, ?object $_xhrInfo, &$_logicResult, bool $_bEditMode) : bool
	{
		##	Set default target if not exists

		$controllerAction = $this -> getControllerAction_v2($_rcaTarget, $_xhrInfo, 'view');

		##	Check user rights for this target
	
		if(!$this -> detectRights($controllerAction, $this->publicActionList))
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
			case 'xhr_getItems' : $logicDone = $this -> logicXHRIndex($_pDatabase, $_xhrInfo); break;	
		}

		if(!$logicDone) // Default
			$logicDone = $this -> logicView($_pDatabase, $enableEdit, $enableDelete);	
	
		return $logicDone;
	}

	private function
	logicView(CDatabaseConnection &$_pDatabase, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{
		$simpleObject = modelSimple::where('object_id', '=', $this -> objectInfo -> object_id)->one();

		if(empty($simpleObject -> params -> itemList))
			$simpleObject -> params -> itemList = [];


		$moduleTemplate	 = new CModulesTemplates();
		$moduleTemplate	-> load($this -> moduleRootDir, $this->moduleInfo->module_location, $simpleObject -> params -> template ?? 'thumbnails-ratio');

		$simpleObject -> params -> itemList = (array)$simpleObject -> params -> itemList;

		$this -> setView(	
						'view',	
						'',
						[
							'object'	=> $simpleObject,
							'currentTemplate' => $moduleTemplate -> templatesList,
							'itemList'	=> $this -> processGalleryItems($_pDatabase, $simpleObject -> params -> itemList),
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
		$simpleObject = modelSimple::where('object_id', '=', $this -> objectInfo -> object_id)->one();

		if(empty($simpleObject -> params -> itemList))
			$simpleObject -> params -> itemList = [];

		$simpleObject -> params -> itemList = (array)$simpleObject -> params -> itemList;


		$moduleTemplate = new CModulesTemplates();
		$moduleTemplate ->	load($this -> moduleRootDir, $this->moduleInfo->module_location, $simpleObject -> params -> template ?? 'thumbnails-ratio');

		$moduleTemplates = new CModulesTemplates();
		$moduleTemplates ->	load($this -> moduleRootDir, $this->moduleInfo->module_location);




		$this -> setView(	
						'edit',	
						'',
						[
							'object'	=> $simpleObject,
							'currentTemplate'	=> $moduleTemplate -> templatesList,
							'avaiableTemplates'	=> $moduleTemplates -> templatesList,
							'itemList'	=> $this -> processGalleryItems($_pDatabase, $simpleObject -> params -> itemList),
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
		$requestList[] 	 = 	[	"input" => "simple-gallery-template", "validate" => "!empty" ];
		$requestList[] 	 = 	[	"input" => "simple-gallery-thumb-height", "validate" => "!empty" ];
		$pURLVariables-> retrieve($requestList, false, true); // POST 
		$urlVarList		 = $pURLVariables ->getArray();


		if(empty($_xhrInfo -> objectId)) 		{ 	$validationErr = true; 	$responseData[] = 'cms-object-id'; 			}

		if(!$validationErr)
		{
			$simpleObject = modelSimple::where('object_id', '=', $_xhrInfo -> objectId)->one();

			#$simpleObject->params->format 			= $urlVarList['simple-gallery-template'];
			$simpleObject->params->template			= $urlVarList['simple-gallery-template'];
			$simpleObject->params->thumb_height		= $urlVarList['simple-gallery-thumb-height'];
			$simpleObject->params->itemList 		= $urlVarList['simple-gallery-item'];
			$simpleObject->body = '';

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

		$sOParams = new stdClass;
		$sOParams -> template = 'thumbnails-ratio';

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



			$moduleTemplate = new CModulesTemplates();
			$moduleTemplate ->	load($this -> moduleRootDir, $this->moduleInfo->module_location, $simpleObject -> params -> template);

			$moduleTemplates = new CModulesTemplates();
			$moduleTemplates ->	load($this -> moduleRootDir, $this->moduleInfo->module_location);


			$this -> setView(	
							'edit',	
							'',
							[
								'object'	=> $simpleObject,
						'currentTemplate'	=> $moduleTemplate -> templatesList,
						'avaiableTemplates'	=> $moduleTemplates -> templatesList,
								'itemList'	=> [],
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


	private function
	logicXHRIndex(CDatabaseConnection &$_pDatabase, object $_xhrInfo) : bool
	{
		$queryValidationString = QueryValidation::STRIP_TAGS | QueryValidation::TRIM | QueryValidation::IS_NOTEMPTY | QueryValidation::IS_DIGIT;

		$requestQuery = new cmsRequestQuery(true);
		$requestQuery->post('requestLimit')->validate($queryValidationString)->default(20)->exec();
		$requestQuery->post('requestOffset')->validate($queryValidationString)->default(0)->exec();
		$requestItems = $requestQuery->toObject();
	
	

		$simpleObject = modelSimple::where('object_id', '=', $this -> objectInfo -> object_id)->one();

		if(empty($simpleObject -> params -> itemList))
			$simpleObject -> params -> itemList = [];

		$simpleObject -> params -> itemList = (array)$simpleObject -> params -> itemList;

		tk::xhrResult(
			0,
			'',
			$this -> processGalleryItems($_pDatabase, $simpleObject -> params -> itemList, $requestItems->requestLimit, $requestItems->requestOffset)
		);
	
		return false;
	}
	


	private function
	processGalleryItems(CDatabaseConnection &$_pDatabase, &$itemsList, int $limit = 20, int $offset = 0) : array
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


		if($limit !== 0)
			$collectedImageList = array_slice($collectedImageList, $offset, $limit);


		return $collectedImageList;
	}
}