<?php

require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelLoginObjects.php';

class	controllerLoginObjects extends CController
{
	#private		$m_pModel;

	public function
	__construct($_module, &$_object)
	{		
		$this -> m_pModel	= new modelLoginObjects();

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
		#$modelCondition = new CModelCondition();
		#$modelCondition -> orderBy('data_id', 'DESC');

		$this -> m_pModel -> load($_sqlConnection);	
		$this -> setView(	
						'index',	
						'',
						[
							'login_objects' 		=> $this -> m_pModel -> getDataInstance(),
							'enableEdit'	=> $_enableEdit,
							'enableDelete'	=> $_enableDelete
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
			$_request[] 	 = 	[	"input" => "object_id",  				"validate" => "strip_tags|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "object_description",	   	"validate" => "strip_tags|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "is_disabled",   			"validate" => "strip_tags|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "object_databases",  	 	"validate" => "strip_tags|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "object_table",   			"validate" => "strip_tags|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "object_fields",   			"validate" => "strip_tags|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "object_session_ext",   		"validate" => "strip_tags|!empty",	 "use_default" => true, "default_value" => '[]'  ]; 	
			$_request[] 	 = 	[	"input" => "object_field_is_username",  "validate" => "strip_tags|!empty" ]; 	
			$_pURLVariables -> retrieve($_request, false, true);
			$_aFormData		 = $_pURLVariables ->getArray();

			if(empty($_aFormData['object_id'])) { 			$_bValidationErr = true; 	$_bValidationDta[] = 'object_id'; 	}
			if(empty($_aFormData['object_databases'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'object_databases'; 	}
			if(empty($_aFormData['object_table'])) { 		$_bValidationErr = true; 	$_bValidationDta[] = 'object_table'; 	}
			if(empty($_aFormData['object_fields'])) { 		$_bValidationErr = true; 	$_bValidationDta[] = 'object_fields'; 	}

			if(!$_bValidationErr)	// Validation OK (by pre check)
			{		
				if(!$this -> m_pModel -> isUnique($_sqlConnection, ['object_id' => $_aFormData['object_id']]))
				{
					$_bValidationMsg .= CLanguage::get() -> string('M_BERMADDR_MSG_OBJEXIST');
					$_bValidationErr = true;
				}
			}
			else	// Validation Failed
			{
				$_bValidationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
			}

			if(!$_bValidationErr)	// Validation OK
			{

				foreach($_aFormData['object_fields'] as $key => $fields)
				{
					if($key == intval($_aFormData['object_field_is_username'])) 
						$_aFormData['object_fields'][ $key ]['is_username'] = '1';
					else
						$_aFormData['object_fields'][ $key ]['is_username'] = '0';
				}

				// Re-Index Array for Javascript
				$tempArray = $_aFormData['object_fields'];
				$_aFormData['object_fields'] = [];
				foreach($tempArray as $key => $fields)
					$_aFormData['object_fields'][] = $fields;

				$_aFormData['object_databases'] 	= json_encode($_aFormData['object_databases']);
				$_aFormData['object_fields'] 	= json_encode($_aFormData['object_fields']);
				$_aFormData['object_session_ext'] 	= json_encode($_aFormData['object_session_ext']);

				$_aFormData['create_by'] 	= CSession::instance() -> getValue('user_id');
				$_aFormData['create_time'] 	= time();

				$insertedId = '0';
				if($this -> m_pModel -> insert($_sqlConnection, $_aFormData, $insertedId))
				{


					$_pPageRequest 	= CPageRequest::instance();


					$_bValidationMsg = CLanguage::get() -> string('MOD_LOGINO_OBJECT WAS_CREATED'). ' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
					$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . $_pPageRequest -> urlPath .'object/'. $_pURLVariables -> getValue("object_id");
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
		$_pURLVariables -> retrieve($_request, true, false); 

		if($_pURLVariables -> getValue("cms-system-id") !== false)
		{	

			$modelCondition = new CModelCondition();
			$modelCondition -> where('object_id', $_pURLVariables -> getValue("cms-system-id"));

			if($this -> m_pModel -> load($_sqlConnection, $modelCondition))
			{
				##	Gathering additional data

				

				$this -> setCrumbData('edit', $_pURLVariables -> getValue("cms-system-id"), true);
				$this -> setView(
								'edit',
								'object/'. $_pURLVariables -> getValue("cms-system-id"),								
								[
									'login_objects' 	=> $this -> m_pModel -> getDataInstance(),
									'enableEdit'	=> $_enableEdit,
									'enableDelete'	=> $_enableDelete
								]								
								);
				return true;
			}
		}

		CMessages::instance() -> addMessage(CLanguage::get() -> string('MOD_LOGINO_ERR_OBJECT_ID_UK') , MSG_WARNING);
		return false;
	}

	private function
	logicEdit(&$_sqlConnection, $_isXHRequest = false)
	{	

		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => "cms-system-id",  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
		$_pURLVariables -> retrieve($_request, true, false); 

		if($_pURLVariables -> getValue("cms-system-id") !== false && $_isXHRequest !== false)
		{	
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			switch($_isXHRequest)
			{
				case 'login-data'  :	// Update user data

										$_pFormVariables	 =	new CURLVariables();
										$_request		 =	[];
										$_request[] 	 = 	[	"input" => "object_description",	   	"validate" => "strip_tags|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "is_disabled",   			"validate" => "strip_tags|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "object_databases",  	 	"validate" => "strip_tags|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "object_table",   			"validate" => "strip_tags|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "object_fields",   			"validate" => "strip_tags|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "object_session_ext",   		"validate" => "strip_tags|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "object_field_is_username",  "validate" => "strip_tags|!empty" ]; 	
										$_pFormVariables -> retrieve($_request, false, true);
										$_aFormData		 = $_pFormVariables ->getArray();

										if(empty($_aFormData['object_databases'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'object_databases'; 	}
									#	if(empty($_aFormData['object_table'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'object_table'; 	}
									#	if(empty($_aFormData['object_fields'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'object_fields'; 	}


										if(!$_bValidationErr)	// Validation OK (by pre check)
										{	
										}
										else	// Validation Failed
										{
											$_bValidationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
										}

										if(!$_bValidationErr)	// Validation OK
										{
											if(!empty($_aFormData['object_fields']))
											{
												foreach($_aFormData['object_fields'] as $key => $fields)
												{
													if($key == intval($_aFormData['object_field_is_username'])) 
														$_aFormData['object_fields'][ $key ]['is_username'] = '1';
													else
														$_aFormData['object_fields'][ $key ]['is_username'] = '0';
												}

												// Re-Index Array for Javascript
												$tempArray = $_aFormData['object_fields'];
												$_aFormData['object_fields'] = [];
												foreach($tempArray as $key => $fields)
													$_aFormData['object_fields'][] = $fields;

												$_aFormData['object_fields'] 		= json_encode($_aFormData['object_fields']);
											}

											$_aFormData['object_databases'] 	= json_encode($_aFormData['object_databases']);

											if(!empty($_aFormData['object_session_ext']))
												$_aFormData['object_session_ext'] 	= json_encode($_aFormData['object_session_ext']);

											$_aFormData['update_by'] 	= CSession::instance() -> getValue('user_id');
											$_aFormData['update_time'] 	= time();

											$modelCondition = new CModelCondition();
											$modelCondition -> where('object_id', $_pURLVariables -> getValue("cms-system-id"));											

											if($this -> m_pModel -> update($_sqlConnection, $_aFormData, $modelCondition))
											{
												$_bValidationMsg = CLanguage::get() -> string('MOD_LOGINO_OBJECT WAS_UPDATED');
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

		if($_pURLVariables -> getValue("cms-system-id") !== false)
		{	
			if($_isXHRequest !== false)
			{
				$_bValidationErr =	false;
				$_bValidationMsg =	'';
				$_bValidationDta = 	[];

				switch($_isXHRequest)
				{
					case 'object-delete':

										$modelCondition = new CModelCondition();
										$modelCondition -> where('object_id', $_pURLVariables -> getValue("cms-system-id"));

										if($this -> m_pModel -> delete($_sqlConnection, $modelCondition))
										{
											$_bValidationMsg = CLanguage::get() -> string('MOD_LOGINO_OBJECT WAS_DELETED'). ' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
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
		
		}

		CMessages::instance() -> addMessage(CLanguage::get() -> string('MOD_LOGINO_ERR_OBJECT_ID_UK') , MSG_WARNING);
		return false;
	}
	


}

?>