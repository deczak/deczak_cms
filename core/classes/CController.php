<?php

class	CController
{
	protected	$m_aModule;
	protected	$m_aObject;

	protected	$m_pModel;
	protected 	$m_pView;

	public function
	__construct($_module, &$_object)
	{
		$this -> m_aModule	= $_module;
		$this -> m_aObject	= $_object;		
	}	
	
	public function
	view()
	{
		if($this -> m_pView == null) return;
		$this -> m_pView -> view();
	}
	
	protected function
	hasRights(array $_userRights, string $_requestedAction)
	{
		if(in_array($_requestedAction, $_userRights, true)) return true;
		return false;
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
		return in_array($_rightId, $this -> m_aModule -> user_rights, true);
	}

	private function
	_getModulePath(string $_controllerAction)
	{

		foreach($this -> m_aModule -> module_subs as $path)
		{
			if($path -> ctl_target === $_controllerAction)
				return $path;
		}
		return false;
	}

	private function
	_existsModuleRight(string $_rightId)
	{
		foreach($this -> m_aModule -> module_rights as $right)
		{
			if($right -> name === $_rightId)
				return true;
		}
		return false;
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

		if(empty($this -> m_aModule -> module_subs))
			return $_aSections;	

		usort($this -> m_aModule -> module_subs, function($a, $b) { return $a -> menu_order <=> $b -> menu_order; });

		if(!isset($this -> m_aModule -> module_subs)) return $_aSections;
		foreach($this -> m_aModule -> module_subs as $_sub)
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
		$_sectionIndex = array_search($_ctrlTarget, array_column($this -> m_aModule -> module_subs, 'ctl_target'));
		if($_sectionIndex !== false)
		{		

			CPageRequest::instance() -> addCrumb(
													(!empty($_customMenuName) ? $_customMenuName : CLanguage::get() -> string($this -> m_aModule -> module_subs[$_sectionIndex] -> menu_name)),
													(!$_noLink ? $this -> m_aModule -> module_subs[$_sectionIndex] -> url_name .'/' : false)
												);
		}
	}

	protected function
	setView(string $_view, string $_moduleTarget,  array $_dataInstances = [])
	{
		switch($this -> m_aModule -> module_type)
		{
			case 'mantle': $moduleTypeLocation = DIR_MANTLE; break;
			default		 : $moduleTypeLocation = DIR_CORE;
		}

		$this -> m_pView = new CView( CMS_SERVER_ROOT . $moduleTypeLocation . DIR_MODULES . $this -> m_aModule -> module_location .'/view/'. $_view, $_moduleTarget , $_dataInstances );	
	}	
}

?>