<?php

include_once 'modelSitemap.php';

class 	modelBackendSitemap extends modelSitemap
{
	public function
	__construct()
	{		
        parent::__construct();

		$this -> tbPage		 	= 'tb_backend_page';
		$this -> tbPagePath 	= 'tb_backend_page_path';
		$this -> tbPageHeader 	= 'tb_backend_page_header';
	}	
}

?>