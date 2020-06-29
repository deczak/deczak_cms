<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSitemap.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelDeniedRemote.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelModules.php';	

class	CHTAccess
{
	private $m_dataLocation;
	private $m_rootLocation;
	private $m_publicFolder;
	private $m_backendFolder;
	private $m_backendFilepath;
	private $m_modulesFilepath;

	public function
	__construct()
	{
		$this -> m_dataLocation  	= CMS_SERVER_ROOT.DIR_DATA.'htaccess/';
		$this -> m_rootLocation 	= CMS_SERVER_ROOT;

		$this -> m_backendFilepath	= CMS_SERVER_ROOT.DIR_DATA.'backend/backend.json';

		$this -> m_publicFolder  	= 'public';
		$this -> m_backendFolder 	= CMS_BACKEND_PUBLIC;
	}

	public function
	generatePart4Backend(CDatabaseConnection &$_pDatabase)
	{
		$_targetFile = '2-backend';

		$_hFile 	 = fopen($this -> m_dataLocation . $_targetFile, "r+");

		if (flock($_hFile, LOCK_EX))
		{
			ftruncate($_hFile, 0);


			/*

			##	Get Page and object data

			$_backend	= file_get_contents($this -> m_backendFilepath);
			$_backend	= json_decode($_backend);

			$_activeModules	= CModules::instance() -> getModules();

			##	looping pages

			foreach($_backend as $_page)
			{
				if(empty($_page -> page_path))
					continue;

					$_createEndNullSub = true;

				##	looping objects of current page

				foreach($_page -> objects as $_object)
				{	
					$_moduleData = $this -> _findActiveModuleData($_activeModules, $_object -> module_id);

					if($_moduleData === false)
						continue;

					## check if this module is reloaded by another one, if yes, skip this
					
					if(!empty($_moduleData -> module_extends_by))
					{	
						$_createEndNullSub = false;
						continue;
					}

					

					##
						
					$_moduleJSON = CMS_SERVER_ROOT.$_moduleData -> module_type.'/'.DIR_MODULES.$_moduleData -> module_location.'/module.json';

					if(!file_exists($_moduleJSON))
						continue;
		
					$_moduleParams	= file_get_contents($_moduleJSON);
					$_moduleParams	= json_decode($_moduleParams);	
	
					##	looping sub section of module thats used by object		

					foreach($_moduleParams -> module_subs as $_moduleSub)
					{	

						if(empty($_moduleSub -> url_name))
						{
							$_createEndNullSub = true;
						}
						else
						{
							$_requestedURL   = [];
							$_requestedURL[] = $this -> m_backendFolder;
							$_requestedURL[] = $_page -> page_path;
							$_requestedURL[] = $_moduleSub -> url_name;

							$_redirectURL	 = [];
							$_redirectURL[]	 = 'cms-node='. $_page -> node_id;
							$_redirectURL[]	 = 'cms-ctrl-action['. $_object -> object_id .']='. $_moduleSub ->	ctl_target;

							if(property_exists($_moduleParams, 'htaccess') && property_exists($_moduleParams -> htaccess, 'variables'))
							{
								foreach($_moduleParams -> htaccess -> variables as $_addIndex => $_addVariable)
								{
									$_requestedURL[] = $_addVariable -> wildcard;
									$_redirectURL[]  = $_addVariable -> key .'=$'. ($_addIndex + 1);
								}
							}

							$_string =  "RewriteRule ^". implode('/', $_requestedURL) ."/?$ ". $this -> m_backendFolder ."/index.php?". implode('&', $_redirectURL) ."&%{QUERY_STRING} [NC,L]" . "\r\n";	
							fwrite($_hFile, $_string);
						}
					}
				}



				if($_createEndNullSub)
				{
					$_requestedURL   = [];
					$_requestedURL[] = $this -> m_backendFolder;
					$_requestedURL[] = $_page -> page_path;

					$_redirectURL	 = [];
					$_redirectURL[]	 = 'cms-node='. $_page -> node_id;

					$_string =  "RewriteRule ^". implode('/', $_requestedURL) ."/?$ ". $this -> m_backendFolder ."/index.php?". implode('&', $_redirectURL) ."&%{QUERY_STRING} [NC,L]" . "\r\n";	
					fwrite($_hFile, $_string);			
				}	
			}

			fwrite($_hFile, "\r\n");

			$_string = "RewriteRule ^". $this -> m_backendFolder ."/(.*)/?$ ". $this -> m_backendFolder ."/$1 [NC,L]" . "\r\n";	
			fwrite($_hFile, $_string);

			$_string = "RewriteRule ^((". $this -> m_backendFolder ."/).*)$ ". $this -> m_backendFolder ."/$1 [L,NC]" . "\r\n";
			fwrite($_hFile, $_string);				
			
			fwrite($_hFile, "\r\n");	

			*/




			fwrite($_hFile, "RewriteCond %{REQUEST_URI} =/". $this -> m_backendFolder ."/" . "\r\n");	
			fwrite($_hFile, "RewriteRule ^". $this -> m_backendFolder ."/index.php?%{QUERY_STRING} [NC,L]" . "\r\n");	
			fwrite($_hFile, "RewriteCond %{REQUEST_FILENAME} -f" . "\r\n");	
			fwrite($_hFile, "RewriteRule ^". $this -> m_backendFolder ."/(.*)/?$ ". $this -> m_backendFolder ."/$1 [NC,L]" . "\r\n");	
			fwrite($_hFile, "RewriteRule ^". $this -> m_backendFolder ."/(.*)/?$ ". $this -> m_backendFolder ."/index.php?$1 [L,QSA]" . "\r\n");	



			if(CFG::GET() -> CRONJOB -> CRON_DIRECTORY_PUBLIC)
			{	
				$_string = "RewriteRule ^cron/(.*)/?$ cron/$1 [NC,L]" . "\r\n";
				fwrite($_hFile, $_string);	
				fwrite($_hFile, "\r\n");	
			}

			##	Release lock

			fflush($_hFile); 
			flock($_hFile, LOCK_UN); 
		}
		else
		{
			# Flock returned false
		}

		fclose($_hFile);
		
	}

	private function
	_findActiveModuleData(&$_modulesData, $_moduleID)
	{
		foreach($_modulesData as $_module)
		{
			if($_module -> module_id == $_moduleID)
				return $_module;
		}
		return false;
	}

	public function
	generatePart4Frontend(CDatabaseConnection &$_pDatabase)
	{
		$_targetFile = '3-frontend';

		$_hFile 	 = fopen($this -> m_dataLocation . $_targetFile, "a");

		if(flock($_hFile, LOCK_EX))
		{
			ftruncate($_hFile, 0);

			
		
			if(CFG::GET() -> ERROR_PAGES -> ERROR403)
			{
				fwrite($_hFile, 'RewriteRule ^403/?$ index.php?cms-error=403 [NC,L]');
				fwrite($_hFile, "\r\n");
			}

			if(CFG::GET() -> ERROR_PAGES -> ERROR404)
			{
				fwrite($_hFile, 'RewriteRule ^404/?$ index.php?cms-error=404 [NC,L]');
				fwrite($_hFile, "\r\n");
			}

			

			/*

			##	Read pages from sql and write it to file

			CLanguage::instance() -> initialize($_pDatabase);

			$supportedLanguages = CLanguage::instance() -> getLanguages();
			
			$_numLanguages = count($supportedLanguages);
			$_procLanguage = 0;

			foreach($supportedLanguages as $_lang)
			{	
				$_procLanguage++;

				$_langSuffix = $_lang -> lang_key .'/';
				$_langSuffix = (!CFG::GET() -> LANGUAGE -> DEFAULT_IN_URL && CLanguage::instance() -> getDefault() === $_lang -> lang_key ? '' : $_langSuffix);

				$modelCondition = new CModelCondition();
				$modelCondition -> where('page_language', $_lang -> lang_key);	
				$modelCondition -> where('page_path', '/');		

				$_pSitemap 	 = new modelSitemap();
				$_pSitemap	-> load($_pDatabase, $modelCondition);

				$_sitemap	= $_pSitemap	-> getResult();

				for($i = count($_sitemap) - 1; $i >= 0; $i--)
				{
					$_sitemap[$i] -> page_path = substr($_sitemap[$i] -> page_path,1);
					
					##	Check if path is empty but not first element
					if(empty($_sitemap[$i] -> page_path) && $i != 0) continue;

					##	Add default redirect, but needs once at the end of all languages
					if(empty($_sitemap[$i] -> page_path) && $i == 0)
					{

						if(!empty($_langSuffix))
						{

							$_string =  "RewriteRule ^". $_langSuffix ."?$ ". $this -> m_publicFolder ."/index.php?cms-node=". $_sitemap[$i] -> node_id ."&cms-lang=". $_sitemap[$i] -> page_language ."&%{QUERY_STRING} [NC,L]" . "\r\n";	

							fwrite($_hFile, $_string);
						}

						if($_procLanguage === $_numLanguages)
						{
							$_string = "RewriteRule ^((?!". $this -> m_publicFolder ."/).*)$ ". $this -> m_publicFolder ."/$1 [L,NC]" . "\r\n";
							fwrite($_hFile, $_string);
						}
						break;
					}

					##	redirect for the site

					$_string =  "RewriteRule ^". $_langSuffix . $_sitemap[$i] -> page_path ."?$ ". $this -> m_publicFolder ."/index.php?cms-node=". $_sitemap[$i] -> node_id ."&cms-lang=". $_sitemap[$i] -> page_language ."&%{QUERY_STRING} [NC,L]" . "\r\n";	
					fwrite($_hFile, $_string);
				}

				fwrite($_hFile, "\r\n");
			}

			*/


			fwrite($_hFile, "RewriteCond %{THE_REQUEST} /public/([^\s?]*) [NC]" . "\r\n");	
			fwrite($_hFile, "RewriteRule ^ %1 [L,NE,R=302]" . "\r\n");	
			fwrite($_hFile, "RewriteRule ^(.*)$ public/index.php?$1 [L,QSA]" . "\r\n");	




			##	Release lock

			fflush($_hFile); 
			flock($_hFile, LOCK_UN); 
		}
		else
		{
			# Flock returned false
		}

		fclose($_hFile);
	}

	public function
	generatePart4DeniedAddress(CDatabaseConnection &$_pDatabase)
	{
		$_targetFile = '0-denied';

		if(!CFG::GET() -> SESSION -> DENIED_ACCESS_ON || !CFG::GET() -> SESSION -> DENIED_ACCESS_HTACCESS)
		{
			@unlink($this -> m_dataLocation . $_targetFile);
			return;
		}

		$_hFile 	 = fopen($this -> m_dataLocation . $_targetFile, "a");

		if(flock($_hFile, LOCK_EX))
		{
			ftruncate($_hFile, 0);

			fwrite($_hFile, "\r\n");
			fwrite($_hFile, 'Order Allow,Deny');
			fwrite($_hFile, "\r\n");

			$modelDeniedRemote 	 = new modelDeniedRemote();
			$modelDeniedRemote	-> load($_pDatabase);

			$deniedList			 = $modelDeniedRemote -> getResult();

			if(is_array($deniedList)) // workaround weil kein array wenn leer (?)
			{
				foreach($deniedList as $denied)
				{
					fwrite($_hFile, 'Deny from '. $denied -> denied_ip);
					fwrite($_hFile, "\r\n");
				}
			}

			fwrite($_hFile, "Allow from all");
			fwrite($_hFile, "\r\n");
	
			fflush($_hFile); 

			flock($_hFile, LOCK_UN); 
		}
		else
		{
			# Flock returned false
		}

		fclose($_hFile);	
	}

	public function
	writeHTAccess(CDatabaseConnection &$_pDatabase)
	{

// TODO .. router.json aktualisieren


		$pRouter  = CRouter::instance();
		$pRouter -> createRoutes($_pDatabase);





		##	get source files and order by name

		$_filenames 	= [];
		$_dirIterator 	= new DirectoryIterator( $this -> m_dataLocation );
		foreach($_dirIterator as $_dirItem)
		{
			if($_dirItem -> isDot() || $_dirItem -> getType() !== 'file')
				continue;

			$_filenames[] = $_dirItem -> getFilename();
		}
		sort($_filenames);

		##	open htaccess 

		$_targetFile = '.htaccess';
		$_hDstFile 	 = fopen($this -> m_rootLocation . $_targetFile, "a");

		if (flock($_hDstFile, LOCK_EX))
		{
			ftruncate($_hDstFile, 0);	

			if(CFG::GET() -> ERROR_PAGES -> ERROR403)
			{
				fwrite($_hDstFile, 'ErrorDocument 403 '. CMS_SERVER_URL .'403');
				fwrite($_hDstFile, "\r\n");
			}

			if(CFG::GET() -> ERROR_PAGES -> ERROR404)
			{
				fwrite($_hDstFile, 'ErrorDocument 404 '. CMS_SERVER_URL .'404 ');
				fwrite($_hDstFile, "\r\n");
			}

			foreach($_filenames as $_file)
			{	
						
				##	Read Source File

				$_hSrcFile 	 = fopen($this -> m_dataLocation . $_file, "r");

				if (flock($_hSrcFile, LOCK_EX))
				{
					##	Write data into destination file

					while (($buffer = fgets($_hSrcFile)) !== false)
					{					
						fwrite($_hDstFile, $buffer);
					}

					fflush($_hSrcFile); 
					flock($_hSrcFile, LOCK_UN); 
				}
				else
				{
					# Flock returned false
				}
				fclose($_hSrcFile);
			}

			##	Release lock

			fflush($_hDstFile); 
			flock($_hDstFile, LOCK_UN);
		}
		else
		{
			# Flock returned false
		}

		fclose($_hDstFile);
	}

}

?>