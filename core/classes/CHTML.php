<?php

class	CHTML extends CBasic
{
	public function
	__construct()
	{
		parent::__construct();

		$this -> m_aStorage['alternate'] = [];
	}

	public function
	addMeta()
	{
	}

	public function
	addStyleSheet()
	{
	}

	public function
	addScript()
	{
	}

	public function
	openDocument(&$page, &$imperator, array &$_pageRequest, bool $_disableCaching = false)
	{
		if($_disableCaching) $this -> disableCaching();

		$pTemplate 		=	new CTemplates(CMS_SERVER_ROOT . DIR_TEMPLATES);
		$template 		= 	$pTemplate -> readTemplateData($page -> page_template);
		$templatePath	= 	CMS_SERVER_ROOT . DIR_TEMPLATES . $page -> page_template .'/';

		$messages		=	CMessages::instance();		
		$language		= 	CLanguage::instance();	
		$session		= 	CSession::instance();	

		$sitemap		=	$imperator -> m_sitemap;

		define('URL_LANG_PRREFIX', ((CFG::LANG_DEFAULT_SUFFIX || $page -> page_language !== CFG::LANG_DEFAULT) ? $page -> page_language .'/' : '') );

		echo "<!DOCTYPE html>\r\n";
		echo "<html lang=\"". $page -> page_language ."\">\r\n";
		echo "<head>\r\n";
		echo "\t<meta charset=\"UTF-8\">\r\n";
		echo "\t<meta name=\"format-detection\" content=\"telephone=no\">\r\n";
		echo "\t<title>". $page -> page_title ."</title>\r\n";
  		echo "\t<meta name=\"description\" content=\"". $page -> page_description ."\">\r\n";

		if(!empty($page -> alternate_path))
		foreach($page -> alternate_path as $_langKey => $_langPath) 	
			echo "\t<link rel=\"alternate\" hreflang=\"". $_langKey ."\" href=\"". CMS_SERVER_URL . ((CFG::LANG_DEFAULT_SUFFIX || $_langKey !== CFG::LANG_DEFAULT) ? $_langKey .'/' : '') . ($_langPath['path'] === '/' ? '' : substr($_langPath['path'],1)) ."\">\r\n";

		##	Page Panel for editing sites
		if(CMS_BACKEND && $page -> page_template !== CMS_BACKEND_TEMPLATE)
			@include CMS_SERVER_ROOT . DIR_TEMPLATES . CMS_BACKEND_TEMPLATE .'/page-edit-head.php';				

		foreach($template -> include_head as $_file)
			@include $templatePath . $_file;	

		echo "</head>\r\n";

		if(CMS_BACKEND && $page -> page_template !== CMS_BACKEND_TEMPLATE)			
			echo "<body data-node-id=\"". $page -> node_id."\">\r\n";
		else		
			echo "<body>\r\n";
		
		##	Page edit
		if(CMS_BACKEND && $page -> page_template !== CMS_BACKEND_TEMPLATE)									
			@include CMS_SERVER_ROOT . DIR_TEMPLATES . CMS_BACKEND_TEMPLATE .'/page-edit-panel.php';		

		foreach($template -> include_content as $_file)
			@include $templatePath . $_file;	
	
		##	Page edit
		if(CMS_BACKEND && $page -> page_template !== CMS_BACKEND_TEMPLATE)
			@include CMS_SERVER_ROOT . DIR_TEMPLATES . CMS_BACKEND_TEMPLATE .'/page-edit-footer.php';			

#CBenchmark::instance() -> stop();

		echo "</body>\r\n";
		echo "</html>";

	}

	private function
	disableCaching()
	{
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("Expires: 0");		
	}
}

?>
