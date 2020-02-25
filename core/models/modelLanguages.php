<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeLanguages.php';	

class 	modelLanguages extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('languages');		
		$this -> m_sheme = new shemeLanguages();
	}
}

?>