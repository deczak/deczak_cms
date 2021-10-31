<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelCategories.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelCategoriesAllocation.php';	

class	controllerCategories extends CController
{

	public function
	__construct($_module, &$_object)
	{		
		$this -> m_pModel	= new modelCategories();
		parent::__construct($_module, $_object);

		CPageRequest::instance() -> subs = $this -> getSubSection();
	}
	
	public function
	logic(CDatabaseConnection &$_pDatabase, array $_rcaTarget, ?object $_xhrInfo) : bool
	{
		##	Set default target if not exists

		$controllerAction = $this -> getControllerAction_v2($_rcaTarget, $_xhrInfo, 'index');

		##	Check user rights for this target
		
		if(!$this -> detectRights($controllerAction))
		{
			if($_xhrInfo !== null)
			{
				$validationErr =	true;
				$validationMsg =	CLanguage::get() -> string('ERR_PERMISSON');
				$responseData  = 	[];


				tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
			}

			CMessages::add(CLanguage::get() -> string('ERR_PERMISSON') , MSG_WARNING);
			return false;
		}

			
		if($_xhrInfo !== null && $_xhrInfo -> isXHR && $_xhrInfo -> objectId === $this -> objectInfo -> object_id)
			$controllerAction = 'xhr_'. $_xhrInfo -> action;

		##	Call sub-logic function by target, if there results are false, we make a fall back to default view

		$enableEdit 	= $this -> existsUserRight('edit');
		$enableDelete	= $enableEdit;
	
		$logicDone = false;
		switch($controllerAction)
		{
		#	case 'view'		  : $logicDone = $this -> logicView($_pDatabase, $enableEdit, $enableDelete); break;
		#	case 'delete' : $logicDone = $this -> logicXHRDelete($_pDatabase); break;	

			case 'view'		: $logicDone = $this -> logicView(	$_pDatabase, $enableEdit, $enableDelete);	break;
			case 'xhr_edit'		: $logicDone = $this -> logicXHREdit(	$_pDatabase, $_xhrInfo);	break;
			case 'xhr_delete'	: $logicDone = $this -> logicXHRDelete($_pDatabase);	break;	
			case 'xhr_ping'		: $logicDone = $this -> logicXHRPing($_pDatabase);	break;	
			case 'xhr_index' : $logicDone = $this -> logicXHRIndex($_pDatabase, $_xhrInfo); break;		
			case 'create'	: $logicDone = $this -> logicCreate($_pDatabase);	break;
			case 'xhr_create'	: $logicDone = $this -> logicXHRCreate($_pDatabase);	break;
		}

		if(!$logicDone) // Default
			$logicDone = $this -> logicIndex($_pDatabase, $enableEdit, $enableDelete);	
	
		return $logicDone;
	}

	private function
	logicIndex(CDatabaseConnection &$_pDatabase, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{
		$this -> setView(	
						'index',	
						'',
						[
							'enableEdit'		=> $_enableEdit,
							'enableDelete'		=> $_enableDelete
						]
						);

		return true;
	}

	private function
	logicXHRIndex(CDatabaseConnection &$_pDatabase) : bool
	{
		$validationErr =	false;
		$validationMsg =	'';
		$responseData = 	[];

		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => 'q',  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
		$_pURLVariables -> retrieve($_request, false, true);	

		$modelCondition  = 	new CModelCondition();

		if($_pURLVariables -> getValue("q") !== false)
		{	
			$conditionSource = 	explode(' ', $_pURLVariables -> getValue("q"));
			foreach($conditionSource as $conditionItem)
			{
				$itemParts = explode(':', $conditionItem);

				if(count($itemParts) == 1)
				{
												$modelCondition -> whereLike('category_name', $itemParts[0]);
				}
				else
				{
					if( $itemParts[0] == 'cms-system-id' )
							$itemParts[0] = 'tb_categories.category_id';
					
					$modelCondition -> where($itemParts[0], $itemParts[1]);
				}
			}										
		}

									$modelCondition -> groupBy('category_id');

		if(!$this -> m_pModel -> load($_pDatabase, $modelCondition, MODEL_CATEGORIES_ALLOCATION_COUNT))
		{
			$validationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
			$validationErr = true;
		}											

		$data = $this -> m_pModel -> getResult();

		tk::xhrResult(intval($validationErr), $validationMsg, $data);	// contains exit call
	
		return false;
	}

	private function
	logicCreate(CDatabaseConnection &$_pDatabase) : bool
	{
		$this -> setCrumbData('create');
		$this -> setView(
						'create',
						'create/'
						);

		return true;
	}

	private function
	logicXHRCreate(CDatabaseConnection &$_pDatabase) : bool
	{

		$validationErr =	false;
		$validationMsg =	'';
		$responseData = 	[];

		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
			$_request[] 	 = 	[	"input" => "category_name",  	"validate" => "strip_tags|trim|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "category_hidden",  	"validate" => "strip_tags|trim|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "category_disabled", "validate" => "strip_tags|trim|!empty" ]; 		
		$_pURLVariables -> retrieve($_request, false, true); // POST 
		$_aFormData		 = $_pURLVariables ->getArray();

			if(empty($_aFormData['category_name'])) 	{ 	$_bValidationErr = true; 	$_bValidationDta[] = 'category_name'; 	}
			if(!isset($_aFormData['category_hidden'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'category_hidden'; 	}
			if(!isset($_aFormData['category_disabled'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'category_disabled'; 	}

		if(!$validationErr)	// Validation OK (by pre check)
		{		
		}
		else	// Validation Failed 
		{
			$validationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
		}

		if(!$validationErr)	// Validation OK
		{
				$_aFormData['category_url'] 	= tk::normalizeFilename($_aFormData['category_name'], true);

			$dataId = $this -> m_pModel -> insert($_pDatabase, $_aFormData);

			if($dataId !== false)
			{					
				$validationMsg = CLanguage::get() -> string('MOD_BECATEGORIES_CATEGORY') .' '. CLanguage::get() -> string('WAS_CREATED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
										
				$responseData['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath .'category/'.$dataId;
			}
			else
			{
				$validationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
			}
		}


		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call

		return false;
	}

	private function
	logicView(CDatabaseConnection &$_pDatabase, bool $_enableEdit = false, bool $_enableDelete = false) : bool
	{
		$systemId = $this -> querySystemId();

		if($systemId !== false )
		{	
			$modelCondition = new CModelCondition();
			$modelCondition -> where('category_id', $systemId);

			if($this -> m_pModel -> load($_pDatabase, $modelCondition))
			{
				##	Gathering additional data

				$_crumbName	 = $this -> m_pModel -> getResultItem('category_id', intval($systemId),'category_name');

				$this -> setCrumbData('edit', $_crumbName, true);
				$this -> setView(
								'edit',
								'category/'. $systemId,								
								[
									'categoriesList' 	=> $this -> m_pModel -> getResult(),
									'enableEdit'		=> $_enableEdit,
									'enableDelete'		=> $_enableDelete
								]								
								);
				return true;
			}
		}
		
		CMessages::add(CLanguage::get() -> string('MOD_BECATEGORIES_ERR_USERID_UK'), MSG_WARNING);
		return false;
	}

	private function
	logicXHREdit(CDatabaseConnection &$_pDatabase, $_xhrInfo = false)
	{
		$systemId = $this -> querySystemId();

		if($systemId !== false)
		{	
			$validationErr =	false;
			$validationMsg =	'';
			$responseData = 	[];

			$pingId 	= $this -> querySystemId('cms-ping-id', true);

			## check if dataset is locked, call his own xhrResult() 
			$this -> detectLock($_pDatabase, $systemId, $pingId);


										$_pFormVariables =	new CURLVariables();
										$_request		 =	[];
										$_request[] 	 = 	[	"input" => "category_name",  	"validate" => "strip_tags|trim|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "category_hidden",  	"validate" => "strip_tags|trim|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "category_disabled", "validate" => "strip_tags|trim|!empty" ]; 		
										$_pFormVariables -> retrieve($_request, false, true); // POST 
										$_aFormData		 = $_pFormVariables ->getArray();

										if(empty($_aFormData['category_name'])) 	{ 	$_bValidationErr = true; 	$_bValidationDta[] = 'category_name'; 	}
										if(!isset($_aFormData['category_hidden'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'category_hidden'; 	}
										if(!isset($_aFormData['category_disabled'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'category_disabled'; 	}

										if(!$validationErr)
										{
											$_aFormData['category_url'] 	= tk::normalizeFilename($_aFormData['category_name'], true);

											$modelCondition = new CModelCondition();
											$modelCondition -> where('category_id', $systemId);

											if($this -> m_pModel -> update($_pDatabase, $_aFormData, $modelCondition))
											{
												$validationMsg = CLanguage::get() -> string('MOD_BECATEGORIES_CATEGORY') .' '. CLanguage::get() -> string('WAS_UPDATED');
											}
											else
											{
												$validationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
												$validationErr = true;
											}											
										}
										else	// Validation Failed
										{
											$validationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
											$validationErr = true;
										}

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call		
		}

		return false;
	}

	private function
	logicXHRDelete(CDatabaseConnection &$_pDatabase) : bool
	{
		$systemId = $this -> querySystemId();

		if($systemId !== false)
		{	
			$validationErr =	false;
			$validationMsg =	'';
			$responseData = 	[];

			$pingId 	= $this -> querySystemId('cms-ping-id', true);

			## check if dataset is locked, call his own xhrResult() 
			$this -> detectLock($_pDatabase, $systemId, $pingId);


									$modelCondition = new CModelCondition();
									$modelCondition -> where('category_id', $systemId);

									if($this -> m_pModel -> delete($_pDatabase, $modelCondition))
									{
										$validationMsg = CLanguage::get() -> string('MOD_BECATEGORIES_CATEGORY') .' '. CLanguage::get() -> string('WAS_DELETED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
										$responseData['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath;
									}
									else
									{
										$validationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
									}

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		}

		return false;
	}

	public function
	logicXHRPing(CDatabaseConnection &$_pDatabase) : bool
	{
		$systemId 	= $this -> querySystemId();
		$pingId 	= $this -> querySystemId('cms-ping-id', true);

		if($systemId !== false)
		{	
			$validationErr =	false;
			$validationMsg =	'';
			$responseData = 	[];

				
					$locked	= $this -> m_pModel -> ping($_pDatabase, CSession::instance() -> getValue('user_id'), $systemId, $pingId, MODEL_LOCK_UPDATE);
					tk::xhrResult(intval($validationErr), $validationMsg, $locked);
			
			

		}

		return false;
	}
}
