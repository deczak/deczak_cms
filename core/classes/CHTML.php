<?php

class	CHTML
{
	public function
	__construct()
	{
	}

	public function
	openDocument(CImperator &$imperator, CPageRequest &$pageRequest) : void
	{
		if(isset($pageRequest -> cache_disabled) && $pageRequest -> cache_disabled == 1)
			$this -> disableCaching();
		
		header("Permissions-Policy: interest-cohort=()");	

		if(CFG::GET() -> FRONTEND -> HEADER -> X_FRAME_OPTIONS !== '0')
			header("X-Frame-Options: ". CFG::GET() -> FRONTEND -> HEADER -> X_FRAME_OPTIONS);

		if(CFG::GET() -> FRONTEND -> HEADER -> X_CONTENT_TYPE_OPTIONS !== '0')
			header("X-Content-Type-Options: ". CFG::GET() -> FRONTEND -> HEADER -> X_CONTENT_TYPE_OPTIONS);
		
		switch($pageRequest -> responseCode)
		{
			case 404:	header(($_SERVER["SERVER_PROTOCOL"] ?? 'HTTP/1.1') . ' 404 Not Found');
						$pageRequest -> crawler_index 	= 0;
						$pageRequest -> crawler_follow 	= 0;
						$pageRequest -> page_title		= '404 Page not Found';
						$pageRequest -> page_name		= 'Page not Found';
						$pageRequest -> page_description= '';
						$pageRequest -> canonical		= false;
						$pageRequest -> page_template = CFG::GET() -> TEMPLATE -> ERROR_TEMPLATE;
						break;
			case 403:	header(($_SERVER["SERVER_PROTOCOL"] ?? 'HTTP/1.1') . ' 403 Forbidden'); 
						$pageRequest -> crawler_index 	= 0;
						$pageRequest -> crawler_follow 	= 0;
						$pageRequest -> page_title		= '403 Forbidden';
						$pageRequest -> page_name		= 'Forbidden';
						$pageRequest -> page_description= '';
						$pageRequest -> canonical		= false;
						$pageRequest -> page_template = CFG::GET() -> TEMPLATE -> ERROR_TEMPLATE;
						break;
			case 920:	header(($_SERVER["SERVER_PROTOCOL"] ?? 'HTTP/1.1') . ' 503 Service Unavailable'); 
						$pageRequest -> crawler_index 	= 0;
						$pageRequest -> crawler_follow 	= 0;
						$pageRequest -> page_title		= 'Database Error';
						$pageRequest -> page_name		= 'Database Error';
						$pageRequest -> page_description= '';
						$pageRequest -> canonical		= false;
						$pageRequest -> page_template = CFG::GET() -> TEMPLATE -> ERROR_TEMPLATE;
						break;
		}

		if(CMS_BACKEND && $pageRequest -> page_template === CMS_BACKEND_TEMPLATE)
			$pTemplate 		=	new CTemplates(CMS_SERVER_ROOT . DIR_TEMPLATES);
		else
			$pTemplate 		=	new CTemplates(CMS_SERVER_ROOT . DIR_TEMPLATES_PAGE);

		if(!isset($pageRequest -> page_template) || $pageRequest -> page_template == NULL)
			$pageRequest -> page_template = CFG::GET() -> TEMPLATE -> ERROR_TEMPLATE;
		
		$template 		= 	$pTemplate -> readTemplateData($pageRequest -> page_template);

		if(CMS_BACKEND && $pageRequest -> page_template === CMS_BACKEND_TEMPLATE)
			$templatePath	= 	CMS_SERVER_ROOT . DIR_TEMPLATES . $pageRequest -> page_template .'/';
		else
			$templatePath	= 	CMS_SERVER_ROOT . DIR_TEMPLATES_PAGE . $pageRequest -> page_template .'/';

		$messages		=	CMessages::instance();		
		$language		= 	CLanguage::instance();	
		$session		= 	CSession::instance();	
		$modules		= 	CModules::instance();

		$sitemap		=	$pageRequest -> sitemap;

		$pageRequest -> page_image_url = MEDIATHEK::getItemUrl($pageRequest -> page_image ?? 0);
		$pageRequest -> page_image_url = ($pageRequest -> page_image_url !== null ? $pageRequest -> page_image_url .'?binary&size=small' : $pageRequest -> page_image_url);
					
		echo "<!DOCTYPE html>\r\n";
		echo "<html lang=\"". $pageRequest -> page_language ."\">\r\n";
		echo "<head>\r\n";
		echo "\t<meta charset=\"UTF-8\">\r\n";
		echo "\t<title>". $pageRequest -> page_title ."</title>\r\n";
  		echo "\t<meta name=\"description\" content=\"". tk::strip_breaks_n_tags($pageRequest -> page_description) ."\">\r\n";
  		echo "\t<meta name=\"viewport\" content=\"width=device-width\">\r\n";
		echo "\t<meta NAME=\"robots\" content=\"". ($pageRequest -> crawler_index == 1 ? 'INDEX' : 'NOINDEX') .','. ($pageRequest -> crawler_follow == 1 ? 'FOLLOW' : 'NOFOLLOW') ."\">\r\n";

		if(!empty($pageRequest -> alternate_path))
		foreach($pageRequest -> alternate_path as $_langKey => $_langPath) 	
			echo "\t<link rel=\"alternate\" hreflang=\"". $_langKey ."\" href=\"". CMS_SERVER_URL . ((CFG::GET() -> LANGUAGE -> DEFAULT_IN_URL || $_langKey !== CLanguage::getDefault()) ? $_langKey .'/' : '') . ($_langPath['path'] === '/' ? '' : substr($_langPath['path'],1)) ."\">\r\n";

		if($pageRequest -> canonical)
			echo "\t<link rel=\"canonical\" href=\"". CMS_SERVER_URL . ((CFG::GET() -> LANGUAGE -> DEFAULT_IN_URL || $pageRequest -> page_language !== CLanguage::getDefault()) ? $pageRequest -> page_language .'/' : '') . $pageRequest -> page_path ."\">";

		##	Page Panel for editing sites
		if(CMS_BACKEND && $pageRequest -> page_template !== CMS_BACKEND_TEMPLATE)
			@include CMS_SERVER_ROOT . DIR_TEMPLATES . CMS_BACKEND_TEMPLATE .'/page-edit-head.php';				

		foreach($template -> include_head as $_file)
			@include $templatePath . $_file;	

		if(!CMS_BACKEND && file_exists(CMS_SERVER_ROOT.DIR_PUBLIC .'css/cms.css'))
			echo "\t<link href=\"".CMS_SERVER_URL."css/cms.css\" rel=\"stylesheet\" title=\"default\" media=\"screen\">";

		if(CMS_BACKEND && file_exists(CMS_SERVER_ROOT.DIR_BACKEND .'css/cms.css'))
			echo "\t<link href=\"".CMS_SERVER_URL.DIR_BACKEND ."css/cms.css\" rel=\"stylesheet\" title=\"default\" media=\"screen\">";

		echo "</head>\r\n";

		if(CMS_BACKEND)			
			echo "<body data-node-id=\"". $pageRequest -> node_id."\">\r\n";
		else		
			echo "<body>\r\n";

		##	Page edit
		if(CMS_BACKEND && $pageRequest -> page_template !== CMS_BACKEND_TEMPLATE)									
			@include CMS_SERVER_ROOT . DIR_TEMPLATES . CMS_BACKEND_TEMPLATE .'/page-edit-panel.php';		

		switch($pageRequest -> responseCode)
		{
			case 200:

				foreach($template -> include_content as $_file)
					@include $templatePath . $_file;	

				break;

			default:

				if(property_exists($template, 'include_error_'. $pageRequest -> responseCode))
				{
					$propertyName = 'include_error_'. $pageRequest -> responseCode;

					foreach($template -> $propertyName as $_file)
						@include $templatePath . $_file;	
				}
				else
				{
					foreach($template -> include_error as $_file)
						@include $templatePath . $_file;	
				}

				break;						
		} 

		if(!CMS_BACKEND && file_exists(CMS_SERVER_ROOT.DIR_PUBLIC .'js/cms.js'))
			echo "\t<script src=\"". CMS_SERVER_URL."js/cms.js\"></script>";

		if(CMS_BACKEND && file_exists(CMS_SERVER_ROOT.DIR_BACKEND .'js/cms.js'))
			echo "\t<script src=\"". CMS_SERVER_URL.DIR_BACKEND ."js/cms.js\"></script>";
					
		echo '<script>';
		foreach($modules -> loadedList as $loadedModule)
		{
			if(property_exists($loadedModule, 'includes'))
			{
				foreach($loadedModule -> includes as $includeFile)
				{
					if(($includeFile -> collect ?? false))
						continue;

					if(CMS_BACKEND && !($includeFile -> backend ?? false))
						continue;

					if(!CMS_BACKEND && !($includeFile -> frontend ?? false))
						continue;

					switch($loadedModule -> module_type)
					{
						case 'core'   : include_once CMS_SERVER_ROOT . DIR_CORE . DIR_MODULES . $loadedModule -> module_location .'/'. $includeFile -> file; break;
						case 'mantle' : include_once CMS_SERVER_ROOT . DIR_MANTLE . DIR_MODULES . $loadedModule -> module_location .'/'. $includeFile -> file; break;
					}
				}
			}
		}
		echo '</script>';
	
		##	Page edit
		if(CMS_BACKEND && $pageRequest -> page_template !== CMS_BACKEND_TEMPLATE)
			@include CMS_SERVER_ROOT . DIR_TEMPLATES . CMS_BACKEND_TEMPLATE .'/page-edit-footer.php';			

		echo "</body>\r\n";
		echo "</html>";
	}

	private function
	disableCaching() : void
	{
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Pragma: no-cache");
		header("Expires: 0");			
	}
}
