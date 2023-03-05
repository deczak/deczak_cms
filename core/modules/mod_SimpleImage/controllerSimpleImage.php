<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSimple.php';	

// edit geht noch nicht auf das template

class controllerSimpleImage extends cmsControllerSimple
{
	private string $defaultTemplateName;

	public function
	__construct(object $_moduleInfo, object &$_objectInfo)
	{
		parent::__construct($_moduleInfo, $_objectInfo);

		##	Set user default right in this module

		$this->setRightOfPublicAccess('view');	

		##	Default template

		$this->defaultTemplateName = 'standard';	
	}

	public function
	logic(CDatabaseConnection &$_pDatabase, array $_rcaTarget, ?object $_xhrInfo, &$_logicResult, bool $_pageEditMode, object $requestInfo) : bool
	{
		##	Get action by request term, can return actions that not listed in module.json

		$action = $this -> getAction($_rcaTarget, $_xhrInfo, $_pageEditMode);

		##	Validate action with user right, xhr request will end in this function

		if(!$this -> validateRight($action, $_xhrInfo))
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

		$this -> setView(	
						'view',	
						'',
						[
							'object' 	=> $simpleObject,
							'currentTemplate' => $moduleTemplate -> templatesList,
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

		##	Body

		$sOBody = '';
	
		##	Parameters

		$requestQuery = new cmsRequestQuery(true);
		$requestQuery->post('simple-image-template')->validate($queryValidationString)->default($this->defaultTemplateName)->out('template')->exec();
		$requestQuery->post('simple-image-id')->validate($queryValidationString)->out('id')->exec();
		$requestQuery->post('simple-image-position-x')->validate($queryValidationString)->out('position_x')->exec();
		$requestQuery->post('simple-image-position-x-unit')->validate($queryValidationString)->out('position_x_unit')->exec();
		$requestQuery->post('simple-image-position-y')->validate($queryValidationString)->out('position_y')->exec();
		$requestQuery->post('simple-image-position-y-unit')->validate($queryValidationString)->out('position_y_unit')->exec();
		$requestQuery->post('simple-image-height')->validate($queryValidationString)->out('height')->exec();
		$requestQuery->post('simple-image-height-unit')->validate($queryValidationString)->out('height_unit')->exec();
		$requestQuery->post('simple-image-fit')->validate($queryValidationString)->out('fit')->exec();
		$sOParams = $requestQuery->toObject();

		return $this->logicUpdateExec(
			$_pDatabase, 
			$_xhrInfo, 
			$sOBody, 
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
}
