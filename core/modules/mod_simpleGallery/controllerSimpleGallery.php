<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSimple.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CModulesTemplates.php';	

class controllerSimpleGallery extends cmsControllerSimple
{
	private string $defaultTemplateName;

	public function
	__construct(object $_moduleInfo, object &$_objectInfo)
	{
		parent::__construct($_moduleInfo, $_objectInfo);

		##	Set user default right in this module

		$this->setRightOfPublicAccess('view');		
		$this->setRightOfPublicAccess('getItems');	

		##	Default template

		$this->defaultTemplateName = 'thumbnails-ratio';

	}

	public function
	logic(CDatabaseConnection &$_pDatabase, array $_rcaTarget, ?object $_xhrInfo, &$_logicResult, bool $_pageEditMode, object $requestInfo) : bool
	{
		##	Get action by request term, can return actions that not listed in module.json

		$action = $this -> getAction($_rcaTarget, $_xhrInfo, $_pageEditMode);

		##	Validate action with user right, xhr request will end in this function

		if(!$this -> validateRight($action, $_xhrInfo, ['getItems']))
			return false;
		
		##	If the user does not have the right, he will not reach this point of process
		##	Public user needs the RightOfPublicAccess call to get here

		## 	Call Logic function, if there goes something wrong, the default view get called (except on xhr calls)

		$logicDone = false;

		if($_xhrInfo === null) // NON XHR
		switch($action)
		{
			case 'edit'		: $logicDone = $this -> logicEdit($_pDatabase); 				break; // cmsControllerSimple::logicEdit
		}

		if($_xhrInfo !== null && $_xhrInfo -> objectId === $this -> objectInfo -> object_id) // XHR
		switch($action)
		{
			case 'create' 	: $logicDone = $this -> logicInsert($_pDatabase, $_xhrInfo,); 	break; // page object should exists at this point
			case 'edit'	    :
			case 'update' 	: $logicDone = $this -> logicUpdate($_pDatabase, $_xhrInfo); 	break;
			case 'getItems' : $logicDone = $this -> logicGetItems($_pDatabase, $_xhrInfo); 	break;	
			case 'view' 	: $logicDone = $this -> logicViewHTML($_pDatabase, $_xhrInfo); 	break;	
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
		$simpleObject = modelSimple::db($_pDatabase)->where('object_id', '=', $this -> objectInfo -> object_id)->one();
		
		$moduleTemplate	 = new CModulesTemplates();
		$moduleTemplate	-> load($this->moduleInfo->modules_path, $this->moduleInfo->module_location, $simpleObject -> params -> template ?? $this->defaultTemplateName);

		if(empty($simpleObject -> params -> itemList))
			$simpleObject -> params -> itemList = [];
		$simpleObject -> params -> itemList = (array)$simpleObject -> params -> itemList;

		$this -> setView(	
						'view',	
						'',
						[
							'object' 	=> $simpleObject,
							'currentTemplate' => $moduleTemplate -> templatesList,
							'itemList'	=> $this -> processGalleryItems($_pDatabase, $simpleObject -> params -> itemList),
						]
						);

		return true;
	}

	/**
	 * 	Overloaded parent ::logicEdit
	 */
	public function logicEdit(CDatabaseConnection &$_pDatabase) : bool
	{
		$simpleObject = modelSimple::db($_pDatabase)->where('object_id', '=', $this -> objectInfo -> object_id)->one();
		
		if(empty($simpleObject -> params -> itemList))
			$simpleObject -> params -> itemList = [];

		$simpleObject -> params -> itemList = (array)$simpleObject -> params -> itemList;

		$moduleTemplate = new CModulesTemplates();
		$moduleTemplate ->	load($this->moduleInfo->modules_path, $this->moduleInfo->module_location, $simpleObject -> params -> template ?? $this->defaultTemplateName);

		$moduleTemplates = new CModulesTemplates();
		$moduleTemplates ->	load($this->moduleInfo->modules_path, $this->moduleInfo->module_location);

		$this -> setView(	
						'edit',	
						'',
						[
							'object' 	=> $simpleObject,
							'currentTemplate'	=> $moduleTemplate -> templatesList,
							'avaiableTemplates'	=> $moduleTemplates -> templatesList,
							'itemList'	=> $this -> processGalleryItems($_pDatabase, $simpleObject -> params -> itemList),
						]
						);

		return true;
	}

	/**
	 * 	XHR process function to get object output
	 */
	public function logicViewHTML(CDatabaseConnection &$_pDatabase, object $_xhrInfo) : bool
	{
		$responseData    = [];

		$this->logicView($_pDatabase);

		ob_start();
		$this->view();
		$responseData['html'] = ob_get_contents();
		ob_end_clean();

		$responseData['objectId'] = $_xhrInfo -> objectId;

		tk::xhrResult(
			0, 
			'OK', 
			$responseData
			);
	
		return false;
	}

	public function logicGetItems(CDatabaseConnection &$_pDatabase, object $_xhrInfo)
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

	/**
	 * 	XHR process function to update object data
	 */
	public function logicUpdate(CDatabaseConnection &$_pDatabase, object $_xhrInfo)
	{
		$queryValidationString = QueryValidation::IS_NOTEMPTY;
	
		##	Parameters

		$requestQuery = new cmsRequestQuery(true);
		$requestQuery->post('simple-gallery-template')->validate($queryValidationString)->default($this->defaultTemplateName)->out('template')->exec();
		$requestQuery->post('simple-gallery-thumb-height')->validate($queryValidationString)->default(0)->out('thumb_height')->exec();
		$requestQuery->post('simple-gallery-item')->validate($queryValidationString)->default(0)->out('itemList')->exec();
		$sOParams = $requestQuery->toObject();

		return $this->logicUpdateExec(
			$_pDatabase, 
			$_xhrInfo, 
			'', 
			$sOParams
			);
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
		$sOBody    = '';
		$sOParams  = new stdClass;
		$sOParams -> template = $this->defaultTemplateName;

		$responseData = [];
		
		$simpleObject = modelSimple::new([
			'object_id' => (int)$this -> objectInfo -> object_id,
			'body' 		=> $sOBody,
			'params' 	=> $sOParams,
		], $_pDatabase);

		if(!$simpleObject->save())
		{
			tk::xhrResult(
				1, 
				'sql insert failed', 
				[]
				);	
		}
		else
		{
			$moduleTemplate = new CModulesTemplates();
			$moduleTemplate ->	load($this->moduleInfo->modules_path, $this->moduleInfo->module_location, $simpleObject -> params -> template);

			$moduleTemplates = new CModulesTemplates();
			$moduleTemplates ->	load($this->moduleInfo->modules_path, $this->moduleInfo->module_location);

			$this -> setView(	
							'edit',	
							'',
							[
								'object' 	=> $simpleObject,
								'currentTemplate'	=> $moduleTemplate -> templatesList,
								'avaiableTemplates'	=> $moduleTemplates -> templatesList,
								'itemList'	=> [],
							]
							);

			$responseData['html'] = $this -> m_pView -> getHTML();
		}

		tk::xhrResult(
			0, 
			'OK', 
			$responseData
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
