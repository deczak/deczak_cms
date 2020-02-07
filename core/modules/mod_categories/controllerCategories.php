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
	logic(&$_sqlConnection, array $_rcaTarget, array $_userRights, $_isXHRequest)
	{
		##	Set default target if not exists

		$_controllerAction = $this -> getControllerAction($_rcaTarget, 'index');

		##	Check user rights for this target

		if(!$this -> hasRights($_userRights, $_controllerAction))
		{ 
			if($_isXHRequest !== false)
			{
				$_bValidationErr =	true;
				$_bValidationMsg =	CLanguage::get() -> string('ERR_PERMISSON');
				$_bValidationDta = 	[];

				tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
			}

			CMessages::instance() -> addMessage(CLanguage::get() -> string('ERR_PERMISSON') , MSG_WARNING);
			return;
		}

		##	Call sub-logic function by target, if there results are false, we make a fall back to default view

		$enableEdit 	= $this -> hasRights($_userRights, 'edit');
		$enableDelete	= $this -> hasRights($_userRights, 'delete');

		$_logicResults = false;
		switch($_controllerAction)
		{
			case 'view'		: $_logicResults = $this -> logicView(	$_sqlConnection, $_isXHRequest, $enableEdit, $enableDelete);	break;
			case 'edit'		: $_logicResults = $this -> logicEdit(	$_sqlConnection, $_isXHRequest);	break;	
			case 'create'	: $_logicResults = $this -> logicCreate($_sqlConnection, $_isXHRequest);	break;
			case 'delete'	: $_logicResults = $this -> logicDelete($_sqlConnection, $_isXHRequest);	break;	
		}

		if(!$_logicResults)
		{
			##	Default View
			$this -> logicIndex($_sqlConnection, $enableEdit, $enableDelete);	
		}
	}

	private function
	logicIndex(&$_sqlConnection, $_enableEdit = false, $_enableDelete = false)
	{
		$conditionAllocation = new CModelCondition();
		$conditionAllocation -> where('tb_categories.category_id', 'tb_categories_allocation.category_id');

		$this -> m_pModel -> addSelectColumns('tb_categories.*','COUNT(tb_categories_allocation.node_id) AS allocation');
		$this -> m_pModel -> addRelation('left join', 'tb_categories_allocation', $conditionAllocation);

		$modelCondition = new CModelCondition();
		$modelCondition -> groupBy('category_id');

		$this -> m_pModel -> load($_sqlConnection, $modelCondition);	
		$this -> setView(	
						'index',	
						'',
						[
							'categoriesList' 	=> $this -> m_pModel -> getDataInstance(),
							'enableEdit'		=> $_enableEdit,
							'enableDelete'		=> $_enableDelete
						]
						);
	}

	private function
	logicCreate(&$_sqlConnection, $_isXHRequest)
	{
		if($_isXHRequest !== false)
		{
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

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

			if(!$_bValidationErr)	// Validation OK (by pre check)
			{		
			}
			else	// Validation Failed 
			{
				$_bValidationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
			}

			if(!$_bValidationErr)	// Validation OK
			{
				#$_aFormData['create_by'] 	= CSession::instance() -> getValue('user_id');
				#$_aFormData['create_time'] 	= time();

				$_aFormData['category_url'] 	= tk::normalizeFilename($_aFormData['category_name'], true);

				$dataId = 0;

				if($this -> m_pModel -> insert($_sqlConnection, $_aFormData, $dataId))
				{					
					$_bValidationMsg = CLanguage::get() -> string('MOD_BECATEGORIES_CATEGORY') .' '. CLanguage::get() -> string('WAS_CREATED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
											
					$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath .'category/'.$dataId;
				}
				else
				{
					$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
				}
			}


			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
		}

		$this -> setCrumbData('create');
		$this -> setView(
						'create',
						'create/'
						);

		return true;
	}

	private function
	logicView(&$_sqlConnection, $_isXHRequest = false, $_enableEdit = false, $_enableDelete = false)
	{	
		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "cms-system-id",  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
		$_pURLVariables -> retrieve($_request, true, false); // POST 

		if($_pURLVariables -> getValue("cms-system-id") !== false )
		{	
			$modelCondition = new CModelCondition();
			$modelCondition -> where('category_id', $_pURLVariables -> getValue("cms-system-id"));

			if($this -> m_pModel -> load($_sqlConnection, $modelCondition))
			{
				##	Gathering additional data

				$_crumbName	 = $this -> m_pModel -> searchValue(intval($_pURLVariables -> getValue("cms-system-id")),'category_id','category_name');

				$this -> setCrumbData('edit', $_crumbName, true);
				$this -> setView(
								'edit',
								'category/'. $_pURLVariables -> getValue("cms-system-id"),								
								[
									'categoriesList' 	=> $this -> m_pModel -> getDataInstance(),
									'enableEdit'		=> $_enableEdit,
									'enableDelete'		=> $_enableDelete
								]								
								);
				return true;
			}
		}
		
		CMessages::instance() -> addMessage(CLanguage::get() -> string('MOD_BECATEGORIES_ERR_USERID_UK'), MSG_WARNING);
		return false;
	}

	private function
	logicEdit(&$_sqlConnection, $_isXHRequest = false)
	{	
		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "cms-system-id",  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
		$_pURLVariables -> retrieve($_request, true, false); // POST 

		if($_pURLVariables -> getValue("cms-system-id") !== false && $_isXHRequest !== false)
		{	
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			switch($_isXHRequest)
			{
				case 'edit-category'  :	

										$_pFormVariables =	new CURLVariables();
										$_request		 =	[];
										$_request[] 	 = 	[	"input" => "category_name",  	"validate" => "strip_tags|trim|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "category_hidden",  	"validate" => "strip_tags|trim|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "category_disabled", "validate" => "strip_tags|trim|!empty" ]; 		
										$_pURLVariables -> retrieve($_request, false, true); // POST 
										$_aFormData		 = $_pURLVariables ->getArray();

										if(empty($_aFormData['category_name'])) 	{ 	$_bValidationErr = true; 	$_bValidationDta[] = 'category_name'; 	}
										if(!isset($_aFormData['category_hidden'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'category_hidden'; 	}
										if(!isset($_aFormData['category_disabled'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'category_disabled'; 	}

										if(!$_bValidationErr)
										{
											#$_aFormData['update_by'] 	= CSession::instance() -> getValue('user_id');
											#$_aFormData['update_time'] 	= time();

											$_aFormData['category_url'] 	= tk::normalizeFilename($_aFormData['category_name'], true);

											$modelCondition = new CModelCondition();
											$modelCondition -> where('category_id', $_pURLVariables -> getValue("cms-system-id"));

											if($this -> m_pModel -> update($_sqlConnection, $_aFormData, $modelCondition))
											{
												$_bValidationMsg = CLanguage::get() -> string('MOD_BECATEGORIES_CATEGORY') .' '. CLanguage::get() -> string('WAS_UPDATED');
											}
											else
											{
												$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
												$_bValidationErr = true;
											}											
										}
										else	// Validation Failed
										{
											$_bValidationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
											$_bValidationErr = true;
										}

										break;
			}

			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call		
		}

		return false;
	}

	private function
	logicDelete(&$_sqlConnection, $_isXHRequest = false)
	{	
		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "cms-system-id",  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
		$_pURLVariables -> retrieve($_request, true, false); // POST 

		if($_pURLVariables -> getValue("cms-system-id") !== false && $_isXHRequest !== false)
		{	
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			switch($_isXHRequest)
			{
				case 'category-delete':

									$modelCondition = new CModelCondition();
									$modelCondition -> where('category_id', $_pURLVariables -> getValue("cms-system-id"));

									if($this -> m_pModel -> delete($_sqlConnection, $modelCondition))
									{
										$_bValidationMsg = CLanguage::get() -> string('MOD_BECATEGORIES_CATEGORY') .' '. CLanguage::get() -> string('WAS_DELETED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
										$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath;
									}
									else
									{
										$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
									}

									break;
			}

			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
		}

		return false;
	}
}

?>