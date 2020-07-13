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

		$_hFile 	 = fopen($this -> m_dataLocation . $_targetFile, "a");

		if (flock($_hFile, LOCK_EX))
		{
			ftruncate($_hFile, 0);

			fwrite($_hFile, "RewriteCond %{REQUEST_FILENAME} !-f " . "\r\n");	
			fwrite($_hFile, "RewriteRule ^". $this -> m_backendFolder ."/(.*)/?$ ". $this -> m_backendFolder ."/index.php?$1 [NC,L,QSA]" . "\r\n");	
			fwrite($_hFile, "RewriteCond %{REQUEST_FILENAME} -f " . "\r\n");	
			fwrite($_hFile, "RewriteRule ^". $this -> m_backendFolder ."/(.*)$ ". $this -> m_backendFolder ."/$1 [L,NC,QSA]" . "\r\n");	

			if(CFG::GET() -> CRONJOB -> CRON_DIRECTORY_PUBLIC)
			{	
				fwrite($_hFile, "\r\n");
				fwrite($_hFile, "RewriteRule ^cron/(.*)/?$ cron/$1 [NC,L]" . "\r\n");	
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
		
		#	if(CFG::GET() -> ERROR_PAGES -> ERROR403)
		#	{
		#		fwrite($_hFile, 'RewriteRule ^403/?$ index.php?cms-error=403 [NC,L]');
		#		fwrite($_hFile, "\r\n");
		#	}

		#	if(CFG::GET() -> ERROR_PAGES -> ERROR404)
		#	{
		#		fwrite($_hFile, 'RewriteRule ^404/?$ index.php?cms-error=404 [NC,L]');
		#		fwrite($_hFile, "\r\n");
		#	}
		
			fwrite($_hFile, "RewriteCond %{THE_REQUEST} /public/([^\s?]*) [NC]" . "\r\n");	
			fwrite($_hFile, "RewriteRule ^ %1 [L,NE,R=302]" . "\r\n");	
			
			fwrite($_hFile, "\r\n");

			fwrite($_hFile, "RewriteCond %{REQUEST_FILENAME} !-f " . "\r\n");
			fwrite($_hFile, "RewriteRule ^public/(.*)/?$ public/index.php?$1 [L,NC,QSA]" . "\r\n");
			fwrite($_hFile, "RewriteRule ^((?!public/).*)$ public/$1 [L,NC]" . "\r\n");

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

		#	if(CFG::GET() -> ERROR_PAGES -> ERROR403)
		#	{
		#		fwrite($_hDstFile, 'ErrorDocument 403 '. CMS_SERVER_URL .'403');
		#		fwrite($_hDstFile, "\r\n");
		#	}

		#	if(CFG::GET() -> ERROR_PAGES -> ERROR404)
		#	{
		#		fwrite($_hDstFile, 'ErrorDocument 404 '. CMS_SERVER_URL .'404 ');
		#		fwrite($_hDstFile, "\r\n");
		#	}

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