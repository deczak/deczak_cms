<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CHTAccess.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CXMLSitemap.php';	


require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUserGroups.php';	
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUsersRegister.php';	

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
				case 'set-remoteuser':	// Set remote User settings

										$_pFormVariables	 =	new CURLVariables();
										$_request		 =	[];
										$_request[] 	 = 	[	"input" => "remote_enable",   	"validate" => "strip_tags|strip_whitespaces|cast_bool|!empty",	 "use_default" => true, "default_value" => false  ];
										$_request[] 	 = 	[	"input" => "remote_timeout",   	"validate" => "strip_tags|strip_whitespaces|cast_int|!empty",	 "use_default" => true, "default_value" => '30'  ];
										$_request[] 	 = 	[	"input" => "remote_report",   	"validate" => "strip_tags|strip_whitespaces|cast_bool|!empty",	 "use_default" => true, "default_value" => false  ];
										$_pFormVariables -> retrieve($_request, false, true);
										$_aFormData		 = $_pFormVariables ->getArray();

										$configuration = file_get_contents(CMS_SERVER_ROOT.DIR_DATA.'configuration.json');
										$configuration = json_decode($configuration);

										$configuration -> USER_SYSTEM -> REMOTE_USER -> ENABLED			= $_aFormData['remote_enable'];
										$configuration -> USER_SYSTEM -> REMOTE_USER -> REVOKE_RIGHTS	= $_aFormData['remote_timeout'];
										$configuration -> USER_SYSTEM -> REMOTE_USER -> REPORT_REVOKE	= $_aFormData['remote_report'];

										$configuration = json_encode($configuration, JSON_FORCE_OBJECT);
										file_put_contents(CMS_SERVER_ROOT.DIR_DATA.'configuration.json', $configuration);

										if(!$_aFormData['remote_enable'])
										{
											$registerCondition	 = new CModelCondition();
											$registerCondition 	-> whereNotNull('user_hash');
											$registerCondition 	-> whereNot('user_hash','');
											$modelUsersRegister	 = new modelUsersRegister();
											$modelUsersRegister -> delete($_sqlConnection, $registerCondition);

											$usergroupCondition	 = new CModelCondition();
											$usergroupCondition -> whereNotNull('user_hash');
											$usergroupCondition -> whereNot('user_hash','');
											$modelUserGroups	 = new modelUserGroups();
											$modelUserGroups	-> delete($_sqlConnection, $usergroupCondition);
										}

										break;
				
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