<?php

class	CController
{
	protected	$m_aModule;
	protected	$m_aObject;

	protected	$m_pModel;
	protected 	$m_pView;

	protected 	$m_aCrumb;

	public function
	__construct(array $_module, &$_object)
	{
		$this -> m_aModule	= $_module;
		$this -> m_aObject	= $_object;		
		$this -> m_aCrumb	= [];
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
	getControllerAction(array $_rcaTarget, string $_defaultAction = 'view')
	{
		if(!isset($_rcaTarget[$this -> m_aObject -> object_id])) return $_defaultAction;
		return $_rcaTarget[$this -> m_aObject -> object_id];
	}
		
	public function
	getCrumb()
	{
		return $this -> m_aCrumb;
	}

	public function
	getSubSection()
	{
		$_aSections = [];

		if(empty($this -> m_aModule['sub']))
			return $_aSections;	

		usort($this -> m_aModule['sub'], function($a, $b) { return $a['menu_order'] <=> $b['menu_order']; });

		if(!isset($this -> m_aModule['sub'])) return $_aSections;
		foreach($this -> m_aModule['sub'] as $_sub)
		{
			if(empty($_sub['url_name']) || empty($_sub['menu_name']))
				continue;

			$_aSections[] = [ "page_path" => $_sub['url_name'] .'/', "menu_name" => CLanguage::instance() -> getString($_sub['menu_name']) ];
	
		}	
		return $_aSections;	
	}
}

?>