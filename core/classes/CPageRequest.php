<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPage.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPageObject.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSitemap.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelRedirect.php';	

class CPageRequest extends CSingleton
{


	/**
	 * 	Detects if request comes by XHR, it requires a xhr-action to return a valid info
	 * 
	 * 	@return	object A stdClass-object with info about this xhrequest or null if this is not a valid xhrequest
	 */
	public function detectXHRequest() : ?object
	{
		$_SERVER['HTTP_X_REQUESTED_XHR_ACTION'] = (!empty($_SERVER['HTTP_X_REQUESTED_XHR_ACTION']) ? trim(strip_tags($_SERVER['HTTP_X_REQUESTED_XHR_ACTION'])) : null);
		$_SERVER['HTTP_X_REQUESTED_XHR_OBJECT'] = (!empty($_SERVER['HTTP_X_REQUESTED_XHR_OBJECT']) ? trim(strip_tags($_SERVER['HTTP_X_REQUESTED_XHR_OBJECT'])) : 0);
		$_SERVER['HTTP_X_REQUESTED_WITH'] 		= (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) ? trim(strip_tags($_SERVER['HTTP_X_REQUESTED_WITH'])) : null);

		if($_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' && $_SERVER['HTTP_X_REQUESTED_XHR_ACTION'] !== null)
		{
			$xhrInfo = new stdClass;
			$xhrInfo -> isXHR 	 = true;
			$xhrInfo -> action 	 = $_SERVER['HTTP_X_REQUESTED_XHR_ACTION'];
			$xhrInfo -> objectId = (int)$_SERVER['HTTP_X_REQUESTED_XHR_OBJECT'];
			return $xhrInfo;
		}
		return null;
	}


	public function getPageLanguage() : string
	{
		return $this->page_language;		
	}



	##	code below this point is for refactoring/revision



	public	$node_id;
	public	$page_language;
	public	$page_version;
	public	$urlPath;
	public	$sitemap;

	public	$isEditMode;

	public	$objectsList;	
	public	$crumbsList;

	public	$responseCode = 200;

	public function
	init(?CDatabaseConnection &$_pDatabase, $_nodeId, $_language, $_version)
	{

		// TODO refactor because of double source

		if($_pDatabase === null)
			return false; 
			
		if($this -> isEditMode === NULL)
			$this -> isEditMode 	= false;

		$this -> objectsList 		= [];
		$this -> crumbsList 		= [];
										
		if(!CMS_BACKEND || (CMS_BACKEND && $this -> isEditMode))
		{
			$sitemapCondition = new CModelCondition();
			$sitemapCondition -> where('page_language', $_language);	
			$sitemapCondition -> where('page_path', '/');	

			$modelSitemap = new modelSitemap();
			$modelSitemap -> load($_pDatabase, $sitemapCondition);
			$this -> sitemap = &$modelSitemap -> getResult();
		}
		else
		{
			$sitemapCondition = new CModelCondition();
			$sitemapCondition -> where('page_language', 'en');	
			$sitemapCondition -> where('page_path', '/');	

			$modelBackendSitemap = new modelBackendSitemap();
			$modelBackendSitemap -> load($_pDatabase, $sitemapCondition);
			$this -> sitemap = &$modelBackendSitemap -> getResult();

		}


		if($_nodeId === false && (!CMS_BACKEND || (CMS_BACKEND && $this -> isEditMode)))
		{	##	Node-ID not set, get start page node-id by language

			foreach($this -> sitemap as $_mapIndex =>  $_mapItem)
			{
				if( $_mapItem -> level == 1)
				{
					$_nodeId = $_mapItem -> node_id;
					break;
				}
			}			
		}
		elseif($_nodeId === false)
		{
			$_nodeId = 2;
		}


		## Checking internal redirect settings

		$redirectCondition = new CModelCondition();
		$redirectCondition -> where('node_id', $_nodeId);			

		$modelRedirect	= new modelRedirect();
		$modelRedirect -> load($_pDatabase, $redirectCondition);

		$redirectList	= &$modelRedirect -> getResult();

		$this -> canonical = false;
		$this -> page_redirect = '';

		if(!empty($redirectList) && !CMS_BACKEND)
		{
			$redirectTarget = $redirectList[0] -> redirect_target;
		
			if(ctype_digit($redirectTarget)) {
				$_nodeId = $redirectTarget;
				$this -> canonical = true;
			}
			elseif(strpos($redirectTarget, 'http') !== false)
			{
				header('Location: ' . $redirectTarget, true, 307);
				exit;
			}
		}
		elseif(!empty($redirectList) && CMS_BACKEND)
		{
			$condRedirectInfo = new CModelCondition();
			$condRedirectInfo -> where('tb_page_path.node_id', $redirectList[0] -> redirect_target);		
			$modelRedirectInfo = new modelPage();
			$modelRedirectInfo -> load($_pDatabase, $condRedirectInfo);
			$RedirectInfo = $modelRedirectInfo -> getResult()[0];

			$redirectTarget = $redirectList[0] -> redirect_target;
			$this -> page_redirect = $redirectTarget;
			$this -> page_redirect_name = $RedirectInfo -> page_name;
		}

		##	

		$this -> node_id 			= $_nodeId;
		$this -> page_language 		= $_language;
		$this -> page_version 		= $_version;

		$this -> page_title			= '';
		$this -> page_description	= '';

		$pageCondition = new CModelCondition();
		$pageCondition -> where('tb_page_path.node_id', $this -> node_id);				
		#$pageCondition -> where('page_version', $this -> page_version);				


		if($this -> responseCode === NULL)
			$this -> responseCode	= 200;

		if($this -> responseCode !== 200)
		{
			$this -> node_id  		= false;
			return false;
		}

		if(!CMS_BACKEND || (CMS_BACKEND && $this -> isEditMode))
		{
			##	Frontend handling
			##

			$modelPage = new modelPage();

			if(!$modelPage -> load($_pDatabase, $pageCondition))
			{	##	Page not found
				$this -> node_id  		= false;
				$this -> responseCode 	= 404;
				return false;
			}

			$page = &$modelPage -> getResult()[0];

			##	Check visibility settings


			if(!CMS_BACKEND && !empty($page -> page_auth))
			{
				if(CSession::instance() -> isAuthed($page -> page_auth) === false)
				{
					header("Location: ". CMS_SERVER_URL ); 			
					exit;		
				}
			}


			$timestamp = time();

			if(		 $page -> hidden_state === 0
				||	 $page -> hidden_state === 2
				||	($page -> hidden_state === 4)
				||	(	($page -> hidden_state == 5 &&  $page -> publish_from  < $timestamp && $page -> publish_expired == 0)
					&&	($page -> hidden_state == 5 && ($page -> publish_until > $timestamp || $page -> publish_expired == 0) && $page -> publish_until != 0)
					)	
				||	CMS_BACKEND			
			  ); else
			{		
				$this -> setResponseCode(403);
				return false;			
			}

			if(	
				($page -> hidden_state === 4 && !CMS_BACKEND)
			  )
			{
				$this -> setResponseCode(404);
				return false;			
			}


			foreach((array)$page as $property => $value)
			{
				$this -> $property = $value;
			}


			$modelCondition = new CModelCondition();
			$modelCondition -> where('node_id', $this -> node_id);
			$modelCondition -> where('page_version', $this -> page_version);
			$modelCondition -> orderBy('object_order_by');

			$modelPageObject = new modelPageObject();
			$modelPageObject -> load($_pDatabase, $modelCondition);
			$this -> objectsList = &$modelPageObject -> getResult();
		
			foreach($this -> sitemap as $_mapIndex =>  $_mapItem)
			{
				if($_mapItem -> node_id === $this -> node_id)
				{
					$_pageIndex = $_mapIndex;
					break;
				}
			}

			if(isset($_pageIndex))
			{
				##	Loop Array back to start page and grab the pages for crumb path

				$_level = $this -> sitemap[$_pageIndex] -> level;

				for($i = $_pageIndex; $i >= 0; $i--)
				{
					if($this -> sitemap[$i] -> level == $_level)
					{

					$this 	-> addCrumb((empty($this -> sitemap[$i] -> crumb_name) ? $this -> sitemap[$i] -> page_name : $this -> sitemap[$i] -> crumb_name), $this -> sitemap[$i] -> page_path, $this -> sitemap[$i] -> node_id, $this -> sitemap[$i] -> page_language, $this -> sitemap[$i] -> level)
							-> setTitle($this -> sitemap[$i] -> page_title);

					#$this -> m_page -> crumb_path[] = $this -> sitemap[$i];
						$_level--;
					}
				}
			
				##	Reverse array

				$this -> crumbsList = array_reverse($this -> crumbsList);
			}

			##	Add detailed Language information

			$languagesList = CLanguage::getLanguages();
			if(isset($languagesList[$_language]))
				$this -> languageInfo = $languagesList[$_language];
			else
				$this -> languageInfo = NULL;

		}
		else
		{
	
			$modelCondition = new CModelCondition();
			$modelCondition -> where('tb_backend_page.node_id', $this -> node_id);

			$modelBackendPage = new modelBackendPage;

			if(!$modelBackendPage -> load($_pDatabase, $modelCondition))
			{
				$this -> setResponseCode(404);
				return false;
			}

			$page  = reset($modelBackendPage -> getResult());
			$page -> page_language 	= $_language;


			if(!empty($page -> page_auth))
			{
				if(CSession::instance() -> isAuthed($page -> page_auth) === false)
				{
					header("Location: ". CMS_SERVER_URL_BACKEND ); 			
					exit;		
				}
			}

			$timestamp = time();

			if(		 $page -> hidden_state === 0
				||	 $page -> hidden_state === 2
				||	($page -> hidden_state === 4)
				||	(	($page -> hidden_state == 5 &&  $page -> publish_from  < $timestamp && $page -> publish_expired == 0)
					&&	($page -> hidden_state == 5 && ($page -> publish_until > $timestamp || $page -> publish_expired == 0) && $page -> publish_until != 0)
					)	
				||	CMS_BACKEND			
			  ); else
			{		
				$this -> setResponseCode(403);
				return false;			
			}

			if(	
				($page -> hidden_state === 4)
			  )
			{
				$this -> setResponseCode(404);
				return false;			
			}

			foreach((array)$page as $property => $value)
			{
				$this -> $property = $value;
			}

			$modelCondition = new CModelCondition();
			$modelCondition -> where('node_id', $this -> node_id);
			$modelCondition -> where('page_version', $this -> page_version);
			$modelCondition -> orderBy('object_order_by');

			$modelBackendPageObject  = new modelBackendPageObject;
			$modelBackendPageObject -> load($_pDatabase, $modelCondition);
			$page -> objects = $modelBackendPageObject -> getResult();
			
			$this -> addCrumb((empty($page -> crumb_name) ? $page -> page_name : $page -> crumb_name), $page -> page_path .'');

			$schemeBackendPageObject		= new schemeBackendPageObject();

			$modelPageObject = new modelPageObject();

			$_className = $modelPageObject -> createClass();

			foreach($page -> objects as $_objectKey =>  $_objectData)
			{
				$pageObject = new $_className($_objectData, $schemeBackendPageObject -> getColumns());

				#$modelCondition = new CModelCondition();
				#$modelCondition -> where('object_id', $pageObject -> object_id);


				$simpleBEObject = modelBackendSimple::where('object_id', '=', $pageObject -> object_id)->one();

				if($simpleBEObject)
				{

					$pageObject -> body 	= $simpleBEObject -> body;
					$pageObject -> params 	= $simpleBEObject -> params;

				}

				/*
				$modelBackendSimple  = new modelBackendSimple;
				if($modelBackendSimple -> load($_pDatabase, $modelCondition))
				{
					$simpleObject = reset($modelBackendSimple -> getResult());

				}
				*/
				$this -> objectsList[] = $pageObject;		
			}


		}
	}

	public	function
	addCrumb($_name, $_urlPart, $_nodeId = 0, $_language = '', $_level = false)
	{
		$crumbIndex = count($this -> crumbsList);
		$this -> crumbsList[$crumbIndex] = new crumb($_name, $_urlPart, $_nodeId, $_language, ($_level !== false ? $_level : $crumbIndex));


		return $this -> crumbsList[$crumbIndex];
	}

	/**
	 * 	Set the response code for error pages, 200 can not be set by this function
	 */
	public function
	setResponseCode(int $_responseCode)
	{
		switch($_responseCode)
		{
			case 403:	// forbidden

						$this -> node_id  		= false;
						$this -> responseCode 	= 403;
						break;

			case 404:	// page not found

						$this -> node_id  		= false;
						$this -> responseCode 	= 404;
						break;

			case 920:	// sql connection failed

						$this -> node_id  		= false;
						$this -> responseCode 	= 920;
						break;
		}
	}

	/**
	 * Get the response code
	 */
	public function
	getResponseCode()
	{
		return $this -> responseCode;
	}



		
}

class crumb
{
	public	$name;
	public	$urlPart;
	public	$level;
	public	$nodeId;
	public	$language;
	public	$title;

	public function
	__construct($_name, $_urlPart, $_nodeId, $_language, $_level)
	{
		$this -> name 		= $_name;
		$this -> urlPart 	= $_urlPart;
		$this -> level 		= $_level;
		$this -> nodeId 	= $_nodeId;
		$this -> language 	= $_language;

		$this -> title 		= '';
	}

	public function
	setTitle($_title)
	{
		$this -> title 		= $_title;
	}
}