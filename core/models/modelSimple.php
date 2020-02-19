<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeSimple.php';	

class 	modelSimple extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('simple');		
		$this -> m_sheme = new shemeSimple();
	}
}

?>