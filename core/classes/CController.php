<?php

class	CController
{
	protected	$moduleInfo;
	protected	$objectInfo;
	protected 	$isBackendCall;
	protected	$isInstallMode;

	/**
	 * 	Constructor
	 * 
	 * 	@param object $_module A stdClass-object with info about this module
	 * 	@param object $_object A stdClass-object with info about this object
	 * 	@param bool $_backendCall Boolean flag if this instance is called by the backend logic
	 */
	public function
	__construct(object $_moduleInfo, object &$_objectInfo, bool $_isBackendCall = false)
	{
		$this -> moduleInfo		= $_moduleInfo;
		$this -> objectInfo		= $_objectInfo;	
		$this -> isBackendCall 	= $_isBackendCall;
		$this -> isInstallMode  = false;
	}	

	/**
	 * 	Return the requested action-name
	 * 
	 * 	@return string the requested action in ctl_target in module.json
	 */
	protected function
	getControllerAction(array $_rcaTarget, string $_defaultAction = 'view') : string
	{
		if(!isset($_rcaTarget[$this -> objectInfo -> object_id])) return $_defaultAction;
		return $_rcaTarget[$this -> objectInfo -> object_id];
	}

	/**
	 * 	Get a list of sub-sections in this module for a sub-menu
	 * 
	 * 	@return array An array of sections. If no sections exists, the array is empty.
	 */
	public function
	getSubSection() : array
	{
		$sectionInfo = [];

		if(empty($this -> moduleInfo -> sections))
			return $sectionInfo;	

		usort($this -> moduleInfo -> sections, function($a, $b) { return $a -> menu_order <=> $b -> menu_order; });

		if(!isset($this -> moduleInfo -> sections)) return $sectionInfo;
		foreach($this -> moduleInfo -> sections as $sub)
		{
			if(empty($sub -> url_name) || empty($sub -> menu_name))
				continue;

			$sectionInfo[] = [ "page_path" => $sub -> url_name .'/', "menu_name" => CLanguage::instance() -> getString($sub -> menu_name) ];
	
		}	
		return $sectionInfo;	
	}














	##	code below this point is for refactoring/revision




	protected	$m_pModel;
	protected 	$m_pView;

	public function isBackendCall() : bool
	{
		return $this -> isBackendCall;
	}

	public function setInstallMode(bool $_isInstallMode = true)
	{
		$this -> isInstallMode = $_isInstallMode;
	}

	public function getInstallMode() : bool
	{
		return $this -> isInstallMode;
	}

	protected function
	detectRights(string $_controllerAction)
	{
		##	get requested inner module path by controller action
		
		$modulePath = $this -> _getModulePath($_controllerAction);

		if($modulePath === false)
			return false;

		##	check if linked right exists in module rights

		if(!$this -> _existsModuleRight($modulePath -> use_right))
			return false;

		##	check if linked right is in users his rights

		if(!$this -> existsUserRight($modulePath -> use_right))
			return false;


		return true;
	}
	protected function
	existsUserRight(string $_rightId) : bool
	{
		foreach($this -> moduleInfo -> rights as $rightInfo)
			if($rightInfo -> name === $_rightId)
				return true;
		return false;
		//return in_array($_rightId, $this -> moduleInfo -> rights, true);
	}

	private function
	_getModulePath(string $_controllerAction)
	{
		foreach($this -> moduleInfo -> sections as $path)
		{
			if($path -> ctl_target === $_controllerAction)
				return $path;
		}
		return false;
	}

	private function
	_existsModuleRight(string $_rightId) : bool
	{
		foreach($this -> moduleInfo -> rights as $right)
		{
			if($right -> name === $_rightId)
				return true;
		}
		return false;
	}

	protected function
	detectLock(CDatabaseConnection &$_pDatabase,string $_systemId, string $_pingId)
	{
		$locked	= $this -> m_pModel -> ping($_pDatabase, CSession::instance() -> getValue('user_id'), $_systemId, $_pingId);

		if($locked['lockedState'] !== 0)
		{
			switch($locked['lockedState'])
			{
				default: $_bValidationMsg = CLanguage::get() -> string('LOCK_IS_LOCKED');
			}

			tk::xhrResult(1, $_bValidationMsg, []);
		}
	}

	protected function
	setCrumbData(string $_ctrlTarget, string $_customMenuName = '', bool $_noLink = false)
	{
		$_sectionIndex = array_search($_ctrlTarget, array_column($this -> moduleInfo -> sections, 'ctl_target'));
		if($_sectionIndex !== false)
		{		

			CPageRequest::instance() -> addCrumb(
													(!empty($_customMenuName) ? $_customMenuName : CLanguage::get() -> string($this -> moduleInfo -> sections[$_sectionIndex] -> menu_name)),
													(!$_noLink ? $this -> moduleInfo -> sections[$_sectionIndex] -> url_name .'/' : false)
												);
		}
	}
	
	protected function
	querySystemId(string $variableName = 'cms-system-id', bool $_methodPost = false)
	{
		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => $variableName,  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
		$_pURLVariables -> retrieve($_request, !$_methodPost, $_methodPost);	

		return $_pURLVariables -> getValue($variableName);
	}

	protected function
	setView(string $_view, string $_moduleTarget,  array $_dataInstances = [])
	{
		$moduleType 	= $this -> moduleInfo -> module -> module_type;
		$moduleLocation = $this -> moduleInfo -> module -> module_location;

		switch($moduleType)
		{
			case 'mantle': $moduleTypeLocation = DIR_MANTLE; break;
			default		 : $moduleTypeLocation = DIR_CORE;
		}

		if(		!file_exists(CMS_SERVER_ROOT . $moduleTypeLocation . DIR_MODULES . $moduleLocation .'/view/'. $_view.'.php')
			&&	$this -> moduleInfo -> parentModule !== NULL)
		{
			$moduleType 	= $this -> moduleInfo -> parentModule -> module_type;
			$moduleLocation = $this -> moduleInfo -> parentModule -> module_location;
		}

		switch($moduleType)
		{
			case 'mantle': $moduleTypeLocation = DIR_MANTLE; break;
			default		 : $moduleTypeLocation = DIR_CORE;
		}

		$this -> m_pView = new CView( CMS_SERVER_ROOT . $moduleTypeLocation . DIR_MODULES . $moduleLocation .'/view/'. $_view, $_moduleTarget , $_dataInstances );	
	}	
	
	public function
	view()
	{
		if($this -> m_pView == null) return;
		$this -> m_pView -> view();
	}
}