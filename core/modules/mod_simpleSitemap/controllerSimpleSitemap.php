<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSimple.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSitemap.php';	

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CModulesTemplates.php';	


class	controllerSimpleSitemap extends CController
{

	private	$m_modelSimple;
		
	public function
	__construct($_module, &$_object)
	{
		parent::__construct($_module, $_object);

		$this -> m_modelSimple = new modelSimple();
		
		$this -> m_aModule -> user_rights[] = 'view';	// add view right as default for everyone
	}
	
	public function
	logic(&$_sqlConnection, array $_rcaTarget, $_isXHRequest, &$_logicResult, bool $_bEditMode)
	{
		##	Set default target if not exists

		$_controllerAction = $this -> getControllerAction($_rcaTarget, 'view');

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

		if($_bEditMode && $_isXHRequest === false) 
			$_controllerAction = 'edit';

		##	Call sub-logic function by target, if there results are false, we make a fall back to default view

		$enableEdit 	= $this -> existsUserRight('edit');
		$enableDelete	= $enableEdit;

		$_logicResults = false;
		switch($_controllerAction)
		{
			case 'view'		: $_logicResults = $this -> logicView(	$_sqlConnection, $_isXHRequest, $_logicResult);	break;
			case 'edit'		: $_logicResults = $this -> logicEdit(	$_sqlConnection, $_isXHRequest, $_logicResult);	break;	
			case 'create'	: $_logicResults = $this -> logicCreate($_sqlConnection, $_isXHRequest, $_logicResult);	break;
			case 'delete'	: $_logicResults = $this -> logicDelete($_sqlConnection, $_isXHRequest, $_logicResult);	break;	
		}

		if(!$_logicResults)
		{
			##	Default View
			$_logicResults = $this -> logicView($_sqlConnection, $_isXHRequest, $_logicResult);	
		}
	}

	private function
	logicView(&$_sqlConnection, $_isXHRequest, &$_logicResult)
	{
		$modelCondition = new CModelCondition();
		$modelCondition -> where('object_id', $this -> m_aObject -> object_id);

		$this -> m_modelSimple -> load($_sqlConnection, $modelCondition);

		$this -> m_modelSimple -> getDataInstance()[0] -> params = json_decode($this -> m_modelSimple -> getDataInstance()[0] -> params);

		##	gathering child nodes

		$modelCondition = new CModelCondition();
		$modelCondition -> where('node_id', $this -> m_aObject -> node_id);		

		$modelSitemap  = new modelSitemap();
		$modelSitemap -> load($_sqlConnection, $modelCondition, NULL, SITEMAP_OWN_CHILDS_ONLY);	

		$moduleTemplate		 = new CModulesTemplates();
		$moduleTemplate		->	load('simpleSitemap', $this -> m_modelSimple -> getDataInstance()[0] -> params -> template);

		$this -> setView(	
						'view',	
						'',
						[
							'object' 	=> $this -> m_modelSimple -> getDataInstance()[0],
							'sitemap'	=> $modelSitemap -> getDataInstance(),
							'currentTemplate'	=> $moduleTemplate -> templatesList
						]
						);

		return true;
	}

	private function
	logicEdit(&$_sqlConnection, $_isXHRequest, &$_logicResult)
	{
		##	XHR Function call

		if($_isXHRequest !== false)
		{
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

		
			switch($_isXHRequest)
			{
				case 'edit'  :	// Update object

								$_pFormVariables =	new CURLVariables();
								$_request		 =	[];
								$_request[] 	 = 	[	"input" => "cms-object-id", 			"output" => "object_id", 	"validate" => "strip_tags|!empty" ]; 
								$_request[] 	 = 	[	"input" => "sitemap-template",  		"validate" => "strip_tags|!empty" ]; 
								$_request[] 	 = 	[	"input" => "sitemap-display-hidden", 	"validate" => "strip_tags|!empty" ]; 
								$_pFormVariables-> retrieve($_request, false, true); // POST 
								$_aFormData		 = $_pFormVariables ->getArray();

								if(empty($_aFormData['object_id'])) 		{ 	$_bValidationErr = true; 	$_bValidationDta[] = 'cms-object-id'; 			}

								if(!$_bValidationErr)
								{
									$modelCondition = new CModelCondition();
									$modelCondition -> where('object_id', $_aFormData['object_id']);

									$_aFormData['params']	= 	[
																	"template"			=> $_aFormData['sitemap-template'],
																	"display_hidden"	=> $_aFormData['sitemap-display-hidden']
																];
									$_aFormData['params']	 = 	json_encode($_aFormData['params'], JSON_FORCE_OBJECT);



									$objectId = $_aFormData['object_id'];
									unset($_aFormData['object_id']);

									if($this -> m_modelSimple -> update($_sqlConnection, $_aFormData, $modelCondition))
									{
										$_bValidationMsg = 'Object updated';

										$this -> m_modelPageObject = new modelPageObject();

										$_objectUpdate['update_time']		=	time();
										$_objectUpdate['update_by']			=	0;
										$_objectUpdate['update_reason']		=	'';

										$this -> m_modelPageObject -> update($_sqlConnection, $_objectUpdate, $modelCondition);
									
									}
									else
									{
										$_bValidationMsg .= 'Unknown error on sql query';
										$_bValidationErr = true;
									}											
								}
								else	// Validation Failed
								{
									$_bValidationMsg .= 'Data validation failed - object was not updated';
									$_bValidationErr = true;
								}

								break;
			}
			
			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
		}	

		$modelCondition = new CModelCondition();
		$modelCondition -> where('object_id', $this -> m_aObject -> object_id);

		$this -> m_modelSimple -> load($_sqlConnection, $modelCondition);


		$this -> m_modelSimple -> getDataInstance()[0] -> params = json_decode($this -> m_modelSimple -> getDataInstance()[0] -> params);

		##	gathering child nodes

		$modelCondition = new CModelCondition();
		$modelCondition -> where('node_id', $this -> m_aObject -> node_id);		

		$modelSitemap  = new modelSitemap();
		$modelSitemap -> load($_sqlConnection, $modelCondition, NULL, SITEMAP_OWN_CHILDS_ONLY);	

		$moduleTemplate		 = new CModulesTemplates();
		$moduleTemplate		->	load('simpleSitemap', $this -> m_modelSimple -> getDataInstance()[0] -> params -> template);

		$moduleTemplates	 = new CModulesTemplates();
		$moduleTemplates		->	load('simpleSitemap');


		$this -> setView(	
						'edit',	
						'',
						[
							'object' 			=> $this -> m_modelSimple -> getDataInstance()[0],
							'sitemap'			=> $modelSitemap -> getDataInstance(),
							'currentTemplate'	=> $moduleTemplate -> templatesList,
							'avaiableTemplates'	=> $moduleTemplates -> templatesList
						]
						);

		return true;
	}

	private function
	logicCreate(&$_sqlConnection, $_isXHRequest, &$_logicResult)
	{

		##	XHR Function call

		if($_isXHRequest !== false && $_isXHRequest === 'cms-insert-module')
		{
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			$_dataset['object_id'] 	= 	$this -> m_aObject -> object_id;
			$_dataset['body'] 		= 	'';

			$_dataset['params']		= 	[
											"template"			=> '',
											"display_hidden"	=> ''
										];
			$_dataset['params']	 	= 	json_encode($_dataset['params'], JSON_FORCE_OBJECT);




			$insertedId = 0;
			if(!$this -> m_modelSimple -> insert($_sqlConnection, $_dataset, $insertedId))
			{
				$_bValidationErr =	true;
				$_bValidationMsg =	'sql insert failed';
			}
			else
			{


		$this -> m_modelSimple -> getDataInstance()[0] -> params = json_decode($this -> m_modelSimple -> getDataInstance()[0] -> params);


				##	gathering child nodes

				$modelCondition = new CModelCondition();
				$modelCondition -> where('node_id', $this -> m_aObject -> node_id);		

				$modelSitemap  = new modelSitemap();
				$modelSitemap -> load($_sqlConnection, $modelCondition, NULL, SITEMAP_OWN_CHILDS_ONLY);	


		$moduleTemplate		 = new CModulesTemplates();
		$moduleTemplate		->	load('simpleSitemap', $this -> m_modelSimple -> getDataInstance()[0] -> params -> template);

		$moduleTemplates	 = new CModulesTemplates();
		$moduleTemplates		->	load('simpleSitemap');





				$this -> setView(	
								'edit',	
								'',
								[
									'object' 	=> $this -> m_modelSimple -> getDataInstance()[0],
									'sitemap'	=> $modelSitemap -> getDataInstance(),
							'currentTemplate'	=> $moduleTemplate -> templatesList,
							'avaiableTemplates'	=> $moduleTemplates -> templatesList
								]
								);

				$_bValidationDta['html'] = $this -> m_pView -> getHTML();
			}

			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call

		}	

	}
	
	private function
	logicDelete(&$_sqlConnection, $_isXHRequest, &$_logicResult)
	{
		##	XHR Function call

		if($_isXHRequest !== false)
		{
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];
		
			switch($_isXHRequest)
			{
				case 'delete'  :	// Update object

									$_pFormVariables =	new CURLVariables();
									$_request		 =	[];
									$_request[] 	 = 	[	"input" => "cms-object-id",  "output" => "object_id", 	"validate" => "strip_tags|!empty" ]; 
									$_pFormVariables-> retrieve($_request, false, true); // POST 
									$_aFormData		 = $_pFormVariables ->getArray();

									if(empty($_aFormData['object_id'])) 		{ 	$_bValidationErr = true; 	$_bValidationDta[] = 'cms-object-id'; 			}

									if(!$_bValidationErr)
									{

										$modelCondition = new CModelCondition();
										$modelCondition -> where('object_id', $_aFormData['object_id']);

										if($this -> m_modelSimple -> delete($_sqlConnection, $modelCondition))
										{
											$_objectModel  	 = new modelPageObject();
											$_objectModel	-> delete($_sqlConnection, $modelCondition);

											$_bValidationMsg = 'Object deleted';
										
										}
										else
										{
											$_bValidationMsg .= 'Unknown error on sql query';
											$_bValidationErr = true;
										}											
									}
									else	// Validation Failed
									{
										$_bValidationMsg .= 'Data validation failed - object was not updated';
										$_bValidationErr = true;
									}

									break;
			}
			
			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call

		}	

		return false;
		
	}


}

?>