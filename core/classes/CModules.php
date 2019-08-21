<?php

require_once	'CSingleton.php';

class	CModules extends CSingleton
{
	public	$m_aStorage;

	public function
	loadModules()
	{
		$this -> m_aStorage = file_get_contents( CMS_SERVER_ROOT.DIR_DATA .'active-modules.json');
		$this -> m_aStorage = ($this -> m_aStorage !== false ? json_decode($this -> m_aStorage, true) : [] );		

		if($this -> m_aStorage == NULL)
			trigger_error("CModules::loadModules -- Active modules json file is not valid",E_USER_ERROR);


		foreach($this -> m_aStorage as $_moduleKey => $_module)
		{
			switch($_module['module_type']) 
			{
				case 'core'   :	include CMS_SERVER_ROOT.DIR_CORE.DIR_MODULES.$_module['module_location'].'/'.$_module['module_controller'].'.php';
								if(empty($_module['module_model'])) break;
								include CMS_SERVER_ROOT.DIR_CORE.DIR_MODULES.$_module['module_location'].'/'.$_module['module_model'].'.php';
								break;

				case 'mantle' : include CMS_SERVER_ROOT.DIR_MANTLE.DIR_MODULES.$_module['module_location'].'/'.$_module['module_controller'].'.php';
								if(empty($_module['module_model'])) break;
								include CMS_SERVER_ROOT.DIR_MANTLE.DIR_MODULES.$_module['module_location'].'/'.$_module['module_model'].'.php';	
								break;

				default: unset($this -> m_aStorage[$_moduleKey]);
			}	
		}
	}

	public function
	getModules()
	{
		return $this -> m_aStorage;	
	}

	public function
	isLoaded(string $_moduleID)
	{
		return array_search($_moduleID, array_column($this -> m_aStorage, 'module_id'));
	}

}

?>