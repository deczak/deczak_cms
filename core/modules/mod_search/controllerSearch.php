<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSimple.php';	

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CModulesTemplates.php';	

class	controllerSearch extends CController
{
	public function
	__construct($_module, &$_object)
	{
		parent::__construct($_module, $_object);
		$this -> moduleInfo -> user_rights[] = 'view';	// add view right as default for everyone

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
	logicView(CDatabaseConnection &$_pDatabase) : bool
	{
		##	get object
		$simpleObject = modelSimple::where('object_id', '=', $this -> objectInfo -> object_id)->one();
		
		##	get node list
		$parentNode = $simpleObject -> params -> parent_node_id;
		$parentNode = (empty($parentNode) ? $this -> objectInfo -> node_id : $simpleObject ->  params -> parent_node_id);

		$modelCondition = new CModelCondition();
		$modelCondition -> where('node_id', $parentNode);		
		$modelSitemap  = new modelSitemap();
		$modelSitemap -> load($_pDatabase, $modelCondition);	
	
		##	get module templates
		$moduleTemplate		 = new CModulesTemplates();
		$moduleTemplate		->	load($this -> moduleRootDir, $this->moduleInfo->module_location, $simpleObject -> params -> template);

		##	get parent node
		$parentNode= tk::getNodeFromSitemap($modelSitemap -> getResult(), $parentNode);

		$this -> setView(	
						'view',	
						'',
						[
							'object' 			=> $simpleObject,
							'parentNode' 		=> $parentNode,
							'currentTemplate'	=> $moduleTemplate -> templatesList
						]
						);

		return true;
	}

	private function
	logicEdit(CDatabaseConnection &$_pDatabase) : bool
	{
		##	get object
		$simpleObject = modelSimple::where('object_id', '=', $this -> objectInfo -> object_id)->one();
		
		##	get node list
		$parentNode = $simpleObject -> params -> parent_node_id;
		$parentNode = (empty($parentNode) ? $this -> objectInfo -> node_id : $simpleObject ->  params -> parent_node_id);

		$modelCondition = new CModelCondition();
		$modelCondition -> where('node_id', $parentNode);		
		$modelSitemap  = new modelSitemap();
		$modelSitemap -> load($_pDatabase, $modelCondition);	

		##	get parent node
		$parentNode= tk::getNodeFromSitemap($modelSitemap -> getResult(), (int)$parentNode);

		##	get module templates
		$moduleTemplate		 = new CModulesTemplates();
		$moduleTemplate		-> load($this -> moduleRootDir, $this->moduleInfo->module_location, $simpleObject -> params -> template);

		$moduleTemplates	 = new CModulesTemplates();
		$moduleTemplates	-> load($this -> moduleRootDir, $this->moduleInfo->module_location);

		$this -> setView(	
						'edit',	
						'',
						[
							'object' 			=> $simpleObject,
							'parentNode' 		=> $parentNode,
							'currentTemplate'	=> $moduleTemplate -> templatesList,
							'avaiableTemplates'	=> $moduleTemplates -> templatesList
						]
						);

		return true;
	}

	private function
	logicXHREdit(CDatabaseConnection &$_pDatabase, object $_xhrInfo) : bool
	{
		$validationErr =	false;
		$validationMsg =	'';
		$responseData = 	[];

		$_pFormVariables =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "search-template",  		"validate" => "strip_tags|!empty" ]; 
		$_request[] 	 = 	[	"input" => "search-parent-node-id", 	"validate" => "strip_tags|!empty" ]; 
		$_pFormVariables-> retrieve($_request, false, true); // POST 
		$_aFormData		 = $_pFormVariables ->getArray();

		if(empty($_xhrInfo -> objectId))
		{
			$validationErr = true;
			$responseData[] = 'cms-object-id';
		}

		if(!$validationErr)
		{
			$simpleObject = modelSimple::where('object_id', '=', $_xhrInfo -> objectId)->one();

			$sOParams = new stdClass;
			$sOParams->template 		= $_aFormData['search-template'];
			$sOParams->parent_node_id 	= $_aFormData['search-parent-node-id'];

			$simpleObject->params	= $sOParams;
			$simpleObject->body 	= '';

			if($simpleObject->save())
			{
				
				$object = modelPageObject::
					  db($_pDatabase)
					->where('object_id', '=', $_xhrInfo -> objectId)
					->one();

				$object->update_time 	= time();
				$object->update_by 		= 0;
				$object->update_reason	= '';
				$object->save();
				
				$validationMsg = 'Object updated';

			
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
	logicXHRCreate(CDatabaseConnection &$_pDatabase, object $_xhrInfo) : bool
	{
		$validationErr =	false;
		$validationMsg =	'';
		$responseData = 	[];
	
		$sOParams = new stdClass;
		$sOParams->template = '';
		$sOParams->parent_node_id = '';

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
			##	get node list
			$parentNode = $simpleObject -> params -> parent_node_id;
			$parentNode = (empty($parentNode) ? $this -> objectInfo -> node_id : $simpleObject ->  params -> parent_node_id);

			$modelCondition = new CModelCondition();
			$modelCondition -> where('node_id', $parentNode);		
			$modelSitemap  = new modelSitemap();
			$modelSitemap -> load($_pDatabase, $modelCondition);	

			##	get parent node
			$parentNode= tk::getNodeFromSitemap($modelSitemap -> getResult(), $parentNode);

			##	get module templates
			$moduleTemplate		 = new CModulesTemplates();
			$moduleTemplate		-> load($this -> moduleRootDir, $this->moduleInfo->module_location, $simpleObject -> params -> template);

			$moduleTemplates	 = new CModulesTemplates();
			$moduleTemplates	-> load($this -> moduleRootDir, $this->moduleInfo->module_location);

			$this -> setView(	
							'edit',	
							'',
							[
							'object' 			=> $simpleObject,
							'parentNode' 		=> $parentNode,
							'currentTemplate'	=> $moduleTemplate -> templatesList,
							'avaiableTemplates'	=> $moduleTemplates -> templatesList
							]
							);

			$responseData['html'] = $this -> m_pView -> getHTML();

			$pRouter  = CRouter::instance();
			$pRouter -> createRoutes($_pDatabase);
		}

		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call

		return false;
	}
	
	private function
	logicXHRDelete(CDatabaseConnection &$_pDatabase, object $_xhrInfo) : bool
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

		$pRouter  = CRouter::instance();
		$pRouter -> createRoutes($_pDatabase);
	
		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call

		return false;
	}
}
