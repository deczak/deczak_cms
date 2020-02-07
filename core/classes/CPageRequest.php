<?php


include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelBackend.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPage.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelPageObject.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSitemap.php';	

class CPageRequest extends CSingleton
{
	public	$node_id;
	public	$page_language;
	public	$page_version;
	public	$xhRequest;
	public	$urlPath;
	public	$sitemap;

	public	$isEditMode;

	public	$objectsList;	
	public	$crumbsList;

	public	$responseCode;

	public function
	init(&$_sqlConnection, $_nodeId, $_language, $_version, $_xhRequest)
	{
		if($_sqlConnection === false)
			return false; 
			
		if($this -> isEditMode === NULL)
			$this -> isEditMode 	= false;

		$this -> objectsList 		= [];
		$this -> crumbsList 		= [];
										

		$sitemapCondition = new CModelCondition();
		$sitemapCondition -> where('page_language', $_language);	
		$sitemapCondition -> where('page_path', '/');			

		$modelSitemap = new modelSitemap();
		$modelSitemap -> load($_sqlConnection, $sitemapCondition);
		$this -> sitemap = &$modelSitemap -> getDataInstance();

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

		$this -> node_id 			= $_nodeId;
		$this -> page_language 		= $_language;
		$this -> page_version 		= $_version;
		$this -> xhRequest 			= $_xhRequest;



		$this -> page_title			= '';
		$this -> page_description	= '';

		$sqlWhere['node_id']		= $this -> node_id;
		$sqlWhere['page_version']	= $this -> page_version;


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

			if(!$modelPage -> loadOld($_sqlConnection, $pageCondition))
			{	##	Page not found
				$this -> node_id  		= false;
				$this -> responseCode 	= 404;
				return false;
			}

			$page = &$modelPage -> getDataInstance()[0];

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

			// fällt später weg beim umbau vom backend
			$sqlWhere['node_id']		= $this -> node_id;
			$sqlWhere['page_version']	= $this -> page_version;


			$modelPageObject = new modelPageObject();
			$modelPageObject -> loadOld($_sqlConnection, $sqlWhere);
			$this -> objectsList = &$modelPageObject -> getDataInstance();
		



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

					$this 	-> addCrumb($this -> sitemap[$i] -> page_name, $this -> sitemap[$i] -> page_path, $this -> sitemap[$i] -> node_id, $this -> sitemap[$i] -> page_language, $this -> sitemap[$i] -> level)
							-> setTitle($this -> sitemap[$i] -> page_title);

	#					$this -> m_page -> crumb_path[] = $this -> sitemap[$i];
						$_level--;
					}
				}
			
				##	Reverse array

				$this -> crumbsList = array_reverse($this -> crumbsList);
			}



	}
	else
	{


		$modelPage = new modelBackend();
		if(!$modelPage -> loadOld($this -> m_sqlConnection, $sqlWhere['node_id']))
		{
			$this -> setResponseCode(404);
			return false;
		}


		$page = &$modelPage -> getDataInstance();


		$page  -> page_language 	= $_language;

		foreach((array)$page as $property => $value)
		{
			$this -> $property = $value;
		}


		$sqlWhere['node_id']		= $this -> node_id;
		$sqlWhere['page_version']	= $this -> page_version;



			$this -> addCrumb($page -> page_name, $page -> page_path .'/');

			$shemePage		= new shemePage();

			$_className		=	$modelPage -> createClass($shemePage,'object');

			foreach($page -> objects as $_objectKey =>  $_objectData)
			{
				$this -> objectsList[] = new $_className($_objectData, $shemePage -> getColumns());
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
?>