<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CHTAccess.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CXMLSitemap.php';	

class	controllerEnvironment extends CController
{

	public function
	__construct($_module, &$_object)
	{		
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
			case 'edit'		: $_logicResults = $this -> logicEdit(	$_sqlConnection, $_isXHRequest);	break;	
		}

		if(!$_logicResults)
		{
			##	Default View
			$this -> logicIndex($_sqlConnection, $_isXHRequest, $enableEdit, $enableDelete);	
		}
	}

	private function
	logicIndex(&$_sqlConnection, $_isXHRequest, $_enableEdit = false, $_enableDelete = false)
	{
		##	Non XHR request
		
		$modelCondition = new CModelCondition();
	
		$this -> setView(	
						'index',	
						'',
						[
							'enableEdit'	=> $_enableEdit
						]
						);
		
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

				case 'update-htaccess':	// Update htaccess

										$_pHTAccess  = new CHTAccess();
										$_pHTAccess -> generatePart4Backend();
										$_pHTAccess -> generatePart4Frontend($_sqlConnection);
										$_pHTAccess -> generatePart4DeniedAddress($_sqlConnection);
										$_pHTAccess -> writeHTAccess();
									
										break;

				case 'update-sitemap':	// Update sitemap

										$sitemap  	 = new CXMLSitemap();
										$sitemap 	-> generate($_sqlConnection);
									
										break;

				}

			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
		}

		return false;
	}
}

?>