<?php

class CXMLSitemap
{
	public function
	generate(&$_sqlConnection)
	{
		$timestamp = time();
		$_targetFile	= 'sitemap.xml';

		$publicLocation	= CMS_SERVER_ROOT.DIR_PUBLIC;

		$_hFile 		 = fopen($publicLocation . $_targetFile, "w+");

		if (flock($_hFile, LOCK_EX))
		{
			##	Read pages from sql and write it to file

			fwrite($_hFile, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">');
			fwrite($_hFile, "\r\n");

			foreach(CLanguage::instance() -> getLanguages() as $_lang)
			{
				if($_lang -> lang_hidden)	continue;
				if($_lang -> lang_locked)	continue;

				$_langSuffix = $_lang -> lang_key .'/';
				$_langSuffix = (!CFG::GET() -> LANGUAGE -> DEFAULT_IN_URL && CLanguage::instance() -> getDefault() === $_lang -> lang_key ? '' : $_langSuffix);


				$modelCondition = new CModelCondition();
				$modelCondition -> where('page_language', $_lang -> lang_key);
				$modelCondition -> where('page_path', '/');	

				$_pSitemap 	 = new modelSitemap();
				$_pSitemap	-> load($_sqlConnection, $modelCondition);

				$sitemap	= $_pSitemap	-> getDataInstance();

				for($i = count($sitemap) - 1; $i >= 0; $i--)
				{		
					if(
							($sitemap[$i] -> hidden_state == 0)
						||	(!empty($sitemap[$i] -> page_auth))
						||	(	($sitemap[$i] -> hidden_state == 5 && $sitemap[$i] -> publish_from  < $timestamp)
							&&	($sitemap[$i] -> hidden_state == 5 && $sitemap[$i] -> publish_until > $timestamp && $sitemap[$i] -> publish_until != 0)
							)
					
					  ); else continue;


					fwrite($_hFile, "\t<url>\r\n");
					fwrite($_hFile, "\t\t<loc>". CMS_SERVER_URL . $_langSuffix . substr($sitemap[$i] -> page_path,1) ."</loc>\r\n");
					fwrite($_hFile, "\t\t<changefreq>monthly</changefreq>\r\n");
					fwrite($_hFile, "\t</url>\r\n");
				}
			}

			fwrite($_hFile, '</urlset>');
			fwrite($_hFile, "\r\n");

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
}

?>