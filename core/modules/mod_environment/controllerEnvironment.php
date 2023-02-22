<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CHTAccess.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_PHP_CLASS.'CXMLSitemap.php';	

require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUserGroups.php';	
require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUsersRegister.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelMediathek.php';	

class	controllerEnvironment extends CController
{
	public function
	__construct($_module, &$_object)
	{		
		parent::__construct($_module, $_object);

		CPageRequest::instance() -> subs = $this -> getSubSection();
	}
	
	public function
	logic(CDatabaseConnection &$_pDatabase, array $_rcaTarget, ?object $_xhrInfo) : bool
	{
		##	Set default target if not exists

		$controllerAction = $this -> getControllerAction($_rcaTarget, 'index');
		
		##	Check user rights for this target
		
		if(!$this -> detectRights($controllerAction))
		{
			if($_xhrInfo !== null)
			{
				$validationErr =	true;
				$validationMsg =	CLanguage::string('ERR_PERMISSON');
				$responseData  = 	[];


				tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
			}

			CMessages::add(CLanguage::string('ERR_PERMISSON') , MSG_WARNING);
			return false;
		}

		$controllerAction = $this -> getControllerAction_v2($_rcaTarget, $_xhrInfo, 'index');

			
		if($_xhrInfo !== null && $_xhrInfo -> isXHR && $_xhrInfo -> objectId === $this -> objectInfo -> object_id)
			$controllerAction = 'xhr_'. $_xhrInfo -> action;

		##	Call sub-logic function by target, if there results are false, we make a fall back to default view

		$enableEdit 	= $this -> existsUserRight('edit');
		$enableDelete	= $enableEdit;
	

		$logicDone = false;
		switch($controllerAction)
		{



			case 'xhr_edit_remoteuser':  $logicDone = $this -> logicXHREditRemoteUser($_pDatabase);	break;	
			case 'xhr_edit_backend': 	 $logicDone = $this -> logicXHREditBackend($_pDatabase); break;	
			case 'xhr_edit_error': 		 $logicDone = $this -> logicXHREditError($_pDatabase); break;	
			case 'xhr_update_htaccess':  $logicDone = $this -> logicXHRUpdateHTAccess($_pDatabase);	break;	
			case 'xhr_update_sitemap': 	 $logicDone = $this -> logicXHRUpdateSitemap($_pDatabase); break;	
			case 'xhr_update_resources': $logicDone = $this -> logicXHRUpdateResources($_pDatabase); break;	
			case 'xhr_error_clear': 	 $logicDone = $this -> logicXHRClearError($_pDatabase);	break;	
			case 'xhr_edit_header': 	 $logicDone = $this -> logicXHREditHeader($_pDatabase);	break;	
			case 'xhr_mediathek_wipe': 	 $logicDone = $this -> logicXHRMediathekWipe($_pDatabase);	break;	
			case 'xhr_temp_wipe': 	 	 $logicDone = $this -> logicXHRTempWipe($_pDatabase);	break;	
		
		
		}

		if(!$logicDone) // Default
			$logicDone = $this -> logicIndex($_pDatabase, $enableEdit, $enableDelete);	
	
		return $logicDone;
	}

	private function
	logicIndex(CDatabaseConnection &$_pDatabase, bool $_enableEdit = false) : bool
	{	
		$this -> setView(	
						'index',	
						'',
						[
							'enableEdit'	=> $_enableEdit
						]
						);

		return true;
		
	}

	private function
	logicXHREditRemoteUser(CDatabaseConnection &$_dbConnection) : bool
	{	
		$systemId = $this -> querySystemId();

		if($systemId !== false)
		{	
			$validationErr 	= false;
			$validationMsg 	= '';
			$responseData 	= [];

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
				$modelUsersRegister -> delete($_dbConnection, $registerCondition);

				$usergroupCondition	 = new CModelCondition();
				$usergroupCondition -> whereNotNull('user_hash');
				$usergroupCondition -> whereNot('user_hash','');
				$modelUserGroups	 = new modelUserGroups();
				$modelUserGroups	-> delete($_dbConnection, $usergroupCondition);
			}


			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		}

		return false;
	}

	private function
	logicXHREditBackend(CDatabaseConnection &$_dbConnection) : bool
	{	
		$systemId = $this -> querySystemId();

		if($systemId !== false)
		{	
			$validationErr 	= false;
			$validationMsg 	= '';
			$responseData 	= [];

			$_pFormVariables	 =	new CURLVariables();
			$_request		 =	[];
			$_request[] 	 = 	[	"input" => "backend_timeformat",   	"validate" => "strip_tags|strip_quote|trim|!empty",	 "use_default" => true, "default_value" => ''  ];
			$_pFormVariables -> retrieve($_request, false, true);
			$_aFormData		 = $_pFormVariables ->getArray();

			$configuration = file_get_contents(CMS_SERVER_ROOT.DIR_DATA.'configuration.json');
			$configuration = json_decode($configuration);

			$configuration -> BACKEND -> TIME_FORMAT	= $_aFormData['backend_timeformat'];

			$configuration = json_encode($configuration, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT);
			file_put_contents(CMS_SERVER_ROOT.DIR_DATA.'configuration.json', $configuration);

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		}

		return false;
	}

	private function
	logicXHREditError(CDatabaseConnection &$_dbConnection) : bool
	{	
		$systemId = $this -> querySystemId();

		if($systemId !== false)
		{	
			$validationErr 	= false;
			$validationMsg 	= '';
			$responseData 	= [];

			$_pFormVariables	 =	new CURLVariables();
			$_request		 =	[];
			$_request[] 	 = 	[	"input" => "enable_error_file", "validate" => "strip_tags|strip_whitespaces|cast_bool|!empty", "use_default" => true, "default_value" => false  ];
			$_request[] 	 = 	[	"input" => "enable_log_file", "validate" => "strip_tags|strip_whitespaces|cast_bool|!empty", "use_default" => true, "default_value" => false  ];
			$_request[] 	 = 	[	"input" => "error_log_file_mode", "validate" => "strip_tags|strip_whitespaces|cast_int|!empty", "use_default" => true, "default_value" => 1  ];
			$_pFormVariables -> retrieve($_request, false, true);
			$_aFormData		 = $_pFormVariables ->getArray();

			$configuration = file_get_contents(CMS_SERVER_ROOT.DIR_DATA.'configuration.json');
			$configuration = json_decode($configuration);

			$configuration -> ERROR_SYSTEM -> ERROR_FILE -> ENABLED	 = $_aFormData['enable_error_file'];
			$configuration -> ERROR_SYSTEM -> LOG_FILE   -> ENABLED	 = $_aFormData['enable_log_file'];
			$configuration -> ERROR_SYSTEM -> LOG_FILE   -> LOG_MODE = $_aFormData['error_log_file_mode'];

			$configuration = json_encode($configuration, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT);
			file_put_contents(CMS_SERVER_ROOT.DIR_DATA.'configuration.json', $configuration);

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		}

		return false;
	}

	private function
	logicXHRClearError(CDatabaseConnection &$_dbConnection) : bool
	{	
		$systemId = $this -> querySystemId();

		if($systemId !== false)
		{	
			$validationErr 	= false;
			$validationMsg 	= '';
			$responseData 	= [];


			$directoryList = new DirectoryIterator(CMS_SERVER_ROOT.DIR_LOG);
			foreach($directoryList as $directory)
			{
				if(!$directory -> isFile())
					continue;

				unlink($directory -> getPathname());
			}
		
			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		}

		return false;
	}

	private function
	logicXHRUpdateHTAccess(CDatabaseConnection &$_dbConnection) : bool
	{	
		$systemId = $this -> querySystemId();

		if($systemId !== false)
		{	
			$validationErr 	= false;
			$validationMsg 	= '';
			$responseData 	= [];

			$_pHTAccess  = new CHTAccess();
			$_pHTAccess -> generatePart4Backend($_dbConnection);
			$_pHTAccess -> generatePart4Frontend($_dbConnection);
			$_pHTAccess -> generatePart4DeniedAddress($_dbConnection);
			$_pHTAccess -> writeHTAccess($_dbConnection);
		
			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		}

		return false;
	}

	private function
	logicXHRUpdateSitemap(CDatabaseConnection &$_dbConnection) : bool
	{	
		$systemId = $this -> querySystemId();

		if($systemId !== false)
		{	
			$validationErr 	= false;
			$validationMsg 	= '';
			$responseData 	= [];

			$sitemap  	 = new CXMLSitemap();
			$sitemap 	-> generate($_dbConnection);
		
			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		}

		return false;
	}

	private function
	logicXHRUpdateResources(CDatabaseConnection &$_dbConnection) : bool
	{	
		$systemId = $this -> querySystemId();

		if($systemId !== false)
		{	
			$validationErr 	= false;
			$validationMsg 	= '';
			$responseData 	= [];

			CModules::generateResources();
		
			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		}

		return false;
	}

	private function
	logicXHREditHeader(CDatabaseConnection &$_dbConnection) : bool
	{	
		$systemId = $this -> querySystemId();

		if($systemId !== false)
		{	
			$validationErr 	= false;
			$validationMsg 	= '';
			$responseData 	= [];

			$_pFormVariables	 =	new CURLVariables();
			$_request		 =	[];
			$_request[] 	 = 	[	"input" => "header_x_content_type_options", "validate" => "strip_tags|strip_whitespaces|!empty", "use_default" => true, "default_value" => '0'  ];
			$_request[] 	 = 	[	"input" => "header_x_frame_options", "validate" => "strip_tags|strip_whitespaces|!empty", "use_default" => true, "default_value" => '0'  ];
			$_pFormVariables -> retrieve($_request, false, true);
			$_aFormData		 = $_pFormVariables ->getArray();

			$configuration = file_get_contents(CMS_SERVER_ROOT.DIR_DATA.'configuration.json');
			$configuration = json_decode($configuration);

			$configuration -> FRONTEND -> HEADER -> X_FRAME_OPTIONS	 	   = $_aFormData['header_x_frame_options'];
			$configuration -> FRONTEND -> HEADER -> X_CONTENT_TYPE_OPTIONS = $_aFormData['header_x_content_type_options'];

			$configuration = json_encode($configuration, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT);
			file_put_contents(CMS_SERVER_ROOT.DIR_DATA.'configuration.json', $configuration);

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		}

		return false;
	}

	private function
	logicXHRMediathekWipe(CDatabaseConnection &$_dbConnection) : bool
	{	
		$systemId = $this -> querySystemId();

		if($systemId !== false)
		{	
			$validationErr 	= false;
			$validationMsg 	= '';
			$responseData 	= [];

			MEDIATHEK::deleteAll();

			modelMediathek::delete();

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		}

		return false;
	}


	private function
	logicXHRTempWipe(CDatabaseConnection &$_dbConnection) : bool
	{	
		$systemId = $this -> querySystemId();

		if($systemId !== false)
		{	
			$validationErr 	= false;
			$validationMsg 	= '';
			$responseData 	= [];


			tk::rrmdir(CMS_SERVER_ROOT. DIR_TEMP, true);


			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		}

		return false;
	}


}

