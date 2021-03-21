<?php

class	CController
{
	protected	$m_aModule;
	protected	$m_aObject;
	protected 	$m_bBackendCall;
	protected	$m_bInstallMode;

	protected	$m_pModel;
	protected 	$m_pView;

	public function
	__construct($_module, &$_object, bool $_backendCall = false)
	{
		$this -> m_aModule	= $_module;
		$this -> m_aObject	= $_object;	
		$this -> m_bBackendCall = $_backendCall;
		$this -> m_bInstallMode = false;
	}	

	public function isBackendCall()
	{
		return $this -> m_bBackendCall;
	}

	public function setInstallMode($_installMode = true)
	{
		$this -> m_bInstallMode = $_installMode;
	}

	public function getInstallMode()
	{
		return $this -> m_bInstallMode;
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
	existsUserRight(string $_rightId)
	{
		foreach($this -> m_aModule -> rights as $rightInfo)
			if($rightInfo -> name === $_rightId)
				return true;
		return false;
		//return in_array($_rightId, $this -> m_aModule -> rights, true);
	}

	private function
	_getModulePath(string $_controllerAction)
	{
		foreach($this -> m_aModule -> sections as $path)
		{
			if($path -> ctl_target === $_controllerAction)
				return $path;
		}
		return false;
	}

	private function
	_existsModuleRight(string $_rightId)
	{
		foreach($this -> m_aModule -> rights as $right)
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
	getControllerAction(array $_rcaTarget, string $_defaultAction = 'view')
	{
		if(!isset($_rcaTarget[$this -> m_aObject -> object_id])) return $_defaultAction;
		return $_rcaTarget[$this -> m_aObject -> object_id];
	}

	public function
	getSubSection()
	{
		$_aSections = [];

		if(empty($this -> m_aModule -> sections))
			return $_aSections;	

		usort($this -> m_aModule -> sections, function($a, $b) { return $a -> menu_order <=> $b -> menu_order; });

		if(!isset($this -> m_aModule -> sections)) return $_aSections;
		foreach($this -> m_aModule -> sections as $_sub)
		{
			if(empty($_sub -> url_name) || empty($_sub -> menu_name))
				continue;

			$_aSections[] = [ "page_path" => $_sub -> url_name .'/', "menu_name" => CLanguage::instance() -> getString($_sub -> menu_name) ];
	
		}	
		return $_aSections;	
	}

	protected function
	setCrumbData(string $_ctrlTarget, string $_customMenuName = '', bool $_noLink = false)
	{
		$_sectionIndex = array_search($_ctrlTarget, array_column($this -> m_aModule -> sections, 'ctl_target'));
		if($_sectionIndex !== false)
		{		

			CPageRequest::instance() -> addCrumb(
													(!empty($_customMenuName) ? $_customMenuName : CLanguage::get() -> string($this -> m_aModule -> sections[$_sectionIndex] -> menu_name)),
													(!$_noLink ? $this -> m_aModule -> sections[$_sectionIndex] -> url_name .'/' : false)
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
		$moduleType 	= $this -> m_aModule -> module -> module_type;
		$moduleLocation = $this -> m_aModule -> module -> module_location;

		switch($moduleType)
		{
			case 'mantle': $moduleTypeLocation = DIR_MANTLE; break;
			default		 : $moduleTypeLocation = DIR_CORE;
		}

		if(		!file_exists(CMS_SERVER_ROOT . $moduleTypeLocation . DIR_MODULES . $moduleLocation .'/view/'. $_view.'.php')
			&&	$this -> m_aModule -> parentModule !== NULL)
		{
			$moduleType 	= $this -> m_aModule -> parentModule -> module_type;
			$moduleLocation = $this -> m_aModule -> parentModule -> module_location;
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

?>