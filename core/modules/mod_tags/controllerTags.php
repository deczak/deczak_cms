<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelTags.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelTagsAllocation.php';	

class	controllerTags extends CController
{

	public function
	__construct($_module, &$_object)
	{		
		$this -> m_pModel	= new modelTags();
		parent::__construct($_module, $_object);

		CPageRequest::instance() -> subs = $this -> getSubSection();
	}
	
	public function
	logic(&$_sqlConnection, array $_rcaTarget, $_isXHRequest)
	{
		##	Set default target if not exists

		$_controllerAction = $this -> getControllerAction($_rcaTarget, 'index');

		##	Check user rights for this target

		if(!$this -> detectRights($_controllerAction))
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

		$enableEdit 	= $this -> existsUserRight('edit');
		$enableDelete	= $enableEdit;

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
		$conditionAllocation -> where('tb_tags.tag_id', 'tb_tags_allocation.tag_id');

		$this -> m_pModel -> addSelectColumns('tb_tags.*','COUNT(tb_tags_allocation.node_id) AS allocation');
		$this -> m_pModel -> addRelation('left join', 'tb_tags_allocation', $conditionAllocation);

		$modelCondition = new CModelCondition();
		$modelCondition -> groupBy('tag_id');

		$this -> m_pModel -> load($_sqlConnection, $modelCondition);	
		$this -> setView(	
						'index',	
						'',
						[
							'tagsList' 			=> $this -> m_pModel -> getDataInstance(),
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
			$_request[] 	 = 	[	"input" => "tag_name",  	"validate" => "strip_tags|trim|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "tag_hidden",  	"validate" => "strip_tags|trim|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "tag_disabled", "validate" => "strip_tags|trim|!empty" ]; 		
			$_pURLVariables -> retrieve($_request, false, true); // POST 
			$_aFormData		 = $_pURLVariables ->getArray();

			if(empty($_aFormData['tag_name'])) 	{ 	$_bValidationErr = true; 	$_bValidationDta[] = 'tag_name'; 	}
			if(!isset($_aFormData['tag_hidden'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'tag_hidden'; 	}
			if(!isset($_aFormData['tag_disabled'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'tag_disabled'; 	}

			if(!$_bValidationErr)	// Validation OK (by pre check)
			{		
			}
			else	// Validation Failed 
			{
				$_bValidationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
			}

			if(!$_bValidationErr)	// Validation OK
			{
				$_aFormData['tag_url'] 	= tk::normalizeFilename($_aFormData['tag_name'], true);

				$dataId = 0;

				if($this -> m_pModel -> insert($_sqlConnection, $_aFormData, $dataId))
				{					
					$_bValidationMsg = CLanguage::get() -> string('MOD_BETAGS_TAG') .' '. CLanguage::get() -> string('WAS_CREATED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
											
					$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath .'tag/'.$dataId;
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
			$modelCondition -> where('tag_id', $_pURLVariables -> getValue("cms-system-id"));

			if($this -> m_pModel -> load($_sqlConnection, $modelCondition))
			{
				##	Gathering additional data

				$_crumbName	 = $this -> m_pModel -> searchValue(intval($_pURLVariables -> getValue("cms-system-id")),'tag_id','tag_name');

				$this -> setCrumbData('edit', $_crumbName, true);
				$this -> setView(
								'edit',
								'tag/'. $_pURLVariables -> getValue("cms-system-id"),								
								[
									'tagsList' 	=> $this -> m_pModel -> getDataInstance(),
									'enableEdit'		=> $_enableEdit,
									'enableDelete'		=> $_enableDelete
								]								
								);
				return true;
			}
		}
		
		CMessages::instance() -> addMessage(CLanguage::get() -> string('MOD_BETAGS_ERR_USERID_UK'), MSG_WARNING);
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
				case 'edit-tag'  :	

										$_pFormVariables =	new CURLVariables();
										$_request		 =	[];
										$_request[] 	 = 	[	"input" => "tag_name",  	"validate" => "strip_tags|trim|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "tag_hidden",  	"validate" => "strip_tags|trim|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "tag_disabled", "validate" => "strip_tags|trim|!empty" ]; 		
										$_pURLVariables -> retrieve($_request, false, true); // POST 
										$_aFormData		 = $_pURLVariables ->getArray();

										if(empty($_aFormData['tag_name'])) 	{ 	$_bValidationErr = true; 	$_bValidationDta[] = 'tag_name'; 	}
										if(!isset($_aFormData['tag_hidden'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'tag_hidden'; 	}
										if(!isset($_aFormData['tag_disabled'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'tag_disabled'; 	}

										if(!$_bValidationErr)
										{
											$_aFormData['tag_url'] 	= tk::normalizeFilename($_aFormData['tag_name'], true);

											$modelCondition = new CModelCondition();
											$modelCondition -> where('tag_id', $_pURLVariables -> getValue("cms-system-id"));

											if($this -> m_pModel -> update($_sqlConnection, $_aFormData, $modelCondition))
											{
												$_bValidationMsg = CLanguage::get() -> string('MOD_BETAGS_TAG') .' '. CLanguage::get() -> string('WAS_UPDATED');
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
				case 'tag-delete':

									$modelCondition = new CModelCondition();
									$modelCondition -> where('tag_id', $_pURLVariables -> getValue("cms-system-id"));

									if($this -> m_pModel -> delete($_sqlConnection, $modelCondition))
									{
										$_bValidationMsg = CLanguage::get() -> string('MOD_BETAGS_TAG') .' '. CLanguage::get() -> string('WAS_DELETED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
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