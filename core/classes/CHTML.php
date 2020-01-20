<?php

class	CHTML extends CBasic
{
	public function
	__construct()
	{
		parent::__construct();
	}

	public function
	openDocument(&$page, &$imperator, &$pageRequest)
	{
		if(isset($pageRequest -> cache_disabled) && $pageRequest -> cache_disabled == 1)
			$this -> disableCaching();
		$pTemplate 		=	new CTemplates(CMS_SERVER_ROOT . DIR_TEMPLATES);

		if(!isset($pageRequest -> page_template) || $pageRequest -> page_template == NULL)
			$pageRequest -> page_template = CONFIG::GET() -> TEMPLATE -> ERROR_TEMPLATE;
		
		$template 	= 	$pTemplate -> readTemplateData($pageRequest -> page_template);


		$templatePath	= 	CMS_SERVER_ROOT . DIR_TEMPLATES . $pageRequest -> page_template .'/';

		$messages		=	CMessages::instance();		
		$language		= 	CLanguage::instance();	
		$session		= 	CSession::instance();	
		$modules		= 	CModules::instance();

		$sitemap		=	$pageRequest -> sitemap;

		define('URL_LANG_PRREFIX', ((CONFIG::GET() -> LANGUAGE -> DEFAULT_IN_URL || $pageRequest -> page_language !== CLanguage::instance() -> getDefault()) ? $pageRequest -> page_language .'/' : '') );

		switch($pageRequest -> responseCode)
		{
			case 404:	header("HTTP/1.0 404 Not Found");
						$pageRequest -> crawler_index 	= 0;
						$pageRequest -> crawler_follow 	= 0;
						$pageRequest -> page_title		= '404 Not Found';
						$pageRequest -> page_description= '';
						break;
			case 403:	header('HTTP/1.0 403 Forbidden'); 
						$pageRequest -> crawler_index 	= 0;
						$pageRequest -> crawler_follow 	= 0;
						$pageRequest -> page_title		= '403 Forbidden';
						$pageRequest -> page_description= '';
						break;
			case 920:	header('HTTP/1.0 403 Forbidden'); 
						$pageRequest -> crawler_index 	= 0;
						$pageRequest -> crawler_follow 	= 0;
						$pageRequest -> page_title		= 'Database Error';
						$pageRequest -> page_description= '';
						break;
		}

		
		
		echo "<!DOCTYPE html>\r\n";
		echo "<html lang=\"". $pageRequest -> page_language ."\">\r\n";
		echo "<head>\r\n";
		echo "\t<meta charset=\"UTF-8\">\r\n";
		echo "\t<title>". $pageRequest -> page_title ."</title>\r\n";
  		echo "\t<meta name=\"description\" content=\"". tk::strip_breaks_n_tags($pageRequest -> page_description) ."\">\r\n";
		echo "\t<meta NAME=\"robots\" content=\"". ($pageRequest -> crawler_index == 1 ? 'INDEX' : 'NOINDEX') .','. ($pageRequest -> crawler_follow == 1 ? 'FOLLOW' : 'NOFOLLOW') ."\">\r\n";

		if(!empty($pageRequest -> alternate_path))
		foreach($pageRequest -> alternate_path as $_langKey => $_langPath) 	
			echo "\t<link rel=\"alternate\" hreflang=\"". $_langKey ."\" href=\"". CMS_SERVER_URL . ((CONFIG::GET() -> LANGUAGE -> DEFAULT_IN_URL || $_langKey !== CLanguage::instance() -> getDefault()) ? $_langKey .'/' : '') . ($_langPath['path'] === '/' ? '' : substr($_langPath['path'],1)) ."\">\r\n";

		##	Page Panel for editing sites
		if(CMS_BACKEND && $pageRequest -> page_template !== CMS_BACKEND_TEMPLATE)
			@include CMS_SERVER_ROOT . DIR_TEMPLATES . CMS_BACKEND_TEMPLATE .'/page-edit-head.php';				

		foreach($template -> include_head as $_file)
			@include $templatePath . $_file;	

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
/*

?>




<div style="display:flex;">

		<div class="ui pick-item">
			<input type="checkbox" id="bbb" name="group_rights[][]" value="" >
			<label for="bbb" title="fgfgfgfgf">
				Random Item
			</label>
			<span class="trigger-pick-item-rm">&times;</span>
		</div>


		<div class="ui pick-item">
			<input type="radio" id="bbb" name="group_rights[][]" value="" >
			<label for="bbb" title="fgfgfgfgf">
				Random Item
			</label>
			<span class="trigger-pick-item-rm">&times;</span>
		</div>


		<div class="ui pick-item">
			<label for="bbb" title="fgfgfgfgf">
				<i class="fas fa-hashtag"></i> &nbsp;&nbsp;Random Item
			</label>
			<span class="trigger-pick-item-rm">&times;</span>
		</div>


		<div class="ui pick-item">
			<label for="bbb" title="fgfgfgfgf">
				<i class="fas fa-hashtag"></i> &nbsp;&nbsp;Random Item
			</label>
		</div>


		</div>




<?php 
*/

		##	temporary solution for js only
		/*
			Später durch Klasse ersetzen die in CModules die Pfade zu den Dateien erfasst und eine sammel js/css erstellt 
		*/
		echo '<script>';
		foreach($modules -> loadedList as $loadedModule)
		{
			if(property_exists($loadedModule, 'include'))
			{
				foreach($loadedModule -> include as $includeFile)
				{
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
