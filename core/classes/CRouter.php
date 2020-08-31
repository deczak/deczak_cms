<?php
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSitemap.php';	

class CRouter extends CSingleton
{
	public	$nodesList;
	public	$requestedURI;
	public	$languageSettings;
	public	$languagesList;
	public	$routesFilepah;

	public function
	initialize($_languageSettings, $_languagesList)
	{
		$this -> languageSettings 	= $_languageSettings;
		$this -> languagesList 		= $_languagesList;
		$this -> nodesList 			= new CRouteNode(1, '', '');

		$this -> routesFilepah		= CMS_SERVER_ROOT.DIR_DATA.'routes.json';
	}

	public function
	createRoutes(CDatabaseConnection &$_dbConnection)
	{
		$modelSitemap = new modelSitemap();
		$modelSitemap -> load($_dbConnection);
		$sitemap = &$modelSitemap -> getResult();

		##	frontend nodes

		$this -> nodesList 			= new CRouteNode(1, '', '');

		$this  -> _createRoute($sitemap, 1, 0, $this -> nodesList);

		##	backend nodes

		$backendFilepath	= CMS_SERVER_ROOT.DIR_DATA.'backend/backend.json';

		$backend	= file_get_contents($backendFilepath);
		$backend	= json_decode($backend);

		$backendStructure = $this -> nodesList -> addChild( new CRouteNode(-1, 'en', CMS_BACKEND_PUBLIC) );

		##	looping pages

		foreach($backend as $page)
		{
			$module = null;

			if(empty($page -> page_path))
				continue;

			$_createEndNullSub = true;

			$_activeModules	= CModules::instance() -> getModules();

			##	looping objects of current page

			foreach($page -> objects as $_object)
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

				if($module === null)
					$module = $backendStructure -> addChild( new CRouteNode($page -> node_id, 'en', $page -> page_path) );

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
						if(property_exists($_moduleSub, 'query_var'))
							$sub = $module -> addChild( new CRouteNode($page -> node_id, 'en', $_moduleSub -> url_name, $_moduleSub -> query_var, [$_object -> object_id => $_moduleSub -> ctl_target]) );
						else
							$sub = $module -> addChild( new CRouteNode($page -> node_id, 'en', $_moduleSub -> url_name, 'cms-ctrl-action', [$_object -> object_id => $_moduleSub -> ctl_target]) );

						if(property_exists($_moduleSub, 'subSection'))
						{
							$sub2 = $sub -> addChild( new CRouteNode($page -> node_id, 'en', $_moduleSub -> subSection -> url_name, $_moduleSub -> subSection -> query_var ) );

							if(property_exists($_moduleSub -> subSection, 'subSection'))
							{
								$sub2 -> addChild( new CRouteNode($page -> node_id, 'en', $_moduleSub -> subSection -> subSection -> url_name, $_moduleSub -> subSection -> subSection -> query_var ) );						
							}
						}
					}
				}
			}
		}

		## 	Structure into file

		file_put_contents($this -> routesFilepah, json_encode($this -> nodesList));
	}

	private function
	_createRoute($_sitemap, $_sitemapIndex, $_parentLevel, &$_destStructure)
	{
		for($i = $_sitemapIndex; $i < count($_sitemap); $i++)
		{
			if($_sitemap[$i] -> level > $_parentLevel)
			{
				$childNode = $_destStructure -> addChild( new CRouteNode($_sitemap[$i] -> node_id, $_sitemap[$i] -> page_language, $_sitemap[$i] -> page_path_segment) );
				$i = $this -> _createRoute($_sitemap, $i + 1 , $_sitemap[$i] -> level, $childNode);
			}
			else
			{
				break;
			}
		}

		return $i - 1;
	}

	public function
	route(string $_requestedURI) : CRouteRequest
	{
		$this -> nodesList = file_get_contents($this -> routesFilepah);
		$this -> nodesList = json_decode($this -> nodesList);

		$routeRequest  = new CRouteRequest;
		$routeRequest -> requestedURI = $_requestedURI;
		$routeRequest -> responseCode = 200;

		$this -> requestedURI = $_requestedURI; 

		$buffer = $_requestedURI;

		$questionMark = strpos($buffer, '?');

		if($questionMark !== false)
		{
			$buffer = substr($buffer, 0, $questionMark);
		}

		$buffer = trim($buffer, '/');
		$buffer = explode('/', $buffer);
		$buffer = array_filter($buffer, 'strlen');

		$nodeInstance = &$this-> nodesList;

		for($sIndex = 0; $sIndex < count($buffer); $sIndex++)
		{
			## if error documents request

			if($sIndex == 0 && $buffer[$sIndex] === '404')
			{
				$routeRequest -> responseCode = 404;
				return $routeRequest;
			}

			if($sIndex == 0 && $buffer[$sIndex] === '403')
			{
				$routeRequest -> responseCode = 403;
				return $routeRequest;
			}

			## determine language of request

			if($sIndex == 0)
			{
				#$langFound = false;

				foreach($this -> languagesList as $lIndex => $language)
				{
					if(		$buffer[$sIndex] == $language -> lang_key 
						&& 	( 		!$language -> lang_default 
								|| 	($language -> lang_default && $this -> languageSettings -> DEFAULT_IN_URL)
							)
					  )
					{
						$routeRequest -> language = $language -> lang_key;
						#$langFound = !$langFound;
						break;
					} elseif($language -> lang_default && !CMS_BACKEND)
					{
						$routeRequest -> language = $language -> lang_key;
						$buffer = array_merge([$language -> lang_key], $buffer);
					}
				}
			}

			if($sIndex == 0 && count($buffer) == 1 && empty($buffer[$sIndex]))
			{
				$buffer[$sIndex] = $routeRequest -> language;
			}

			$nodeFound = false;

			foreach($nodeInstance -> childNodesList as $childNode)
			{
				if(		$sIndex == 0 
					&& 	$childNode -> uriSegmentName == ''  
					&& 	$nodeInstance -> nodeId == 1 
					&& 	$childNode -> language == $routeRequest -> language 
					&& 	$buffer[$sIndex] != CMS_BACKEND_PUBLIC)
				{
					$nodeInstance = &$childNode;
					$nodeFound = true;

					break;
				}

				if($childNode -> uriSegmentName == $buffer[$sIndex] && ( (!CMS_BACKEND && $childNode -> language == $routeRequest -> language ) || CMS_BACKEND)  )
				{
					$nodeInstance = &$childNode;
					$nodeFound = true;

					if($childNode -> queryVar !== false)
					{
						if($childNode -> queryVar == 'cms-ctrl-action' && !empty($childNode -> uriSegmentNameAlias))
							$childNode -> uriSegmentNameAlias = json_decode($childNode -> uriSegmentNameAlias, true);
					
						$_GET[ $childNode -> queryVar ] = (!empty($childNode -> uriSegmentNameAlias) ? $childNode -> uriSegmentNameAlias: $childNode -> uriSegmentName);
					}

					break;
				}

				if($childNode -> uriSegmentName === false && $childNode -> queryVar !== false)
				{
					$nodeInstance 	= &$childNode;
					$nodeFound = true;

					$_GET[ $childNode -> queryVar ] = $buffer[$sIndex];

					break;
				}
			}

			if(!$nodeFound)
			{
				header("Location: ". CMS_SERVER_URL ."404"); 	
				exit;	
			}

			$routeRequest -> nodeId = $nodeInstance -> nodeId;
		}

		return $routeRequest;
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
}

class CRouteRequest
{
	public	$requestedURI;
	public	$nodeId;
	public	$language;
}

class CRouteNode
{
	public	$childNodesList;
	public	$nodeId;
	public	$uriSegmentName;
	public	$uriSegmentNameAlias;
	public	$queryVar;
	public	$language;

	public function
	__construct($_nodeId, $_language, $_uriSegmentName, $_queryVar = false, $_uriSegmentNameAlias = false)
	{
		$this -> nodeId 			 = $_nodeId;
		$this -> uriSegmentName 	 = $_uriSegmentName;
		$this -> uriSegmentNameAlias = $_uriSegmentNameAlias;
		$this -> queryVar 			 = $_queryVar;
		$this -> language 			 = $_language;
		$this -> childNodesList 	 = [];
	}

	public function
	addChild(CRouteNode $_node) : CRouteNode
	{

		if($_node -> queryVar != 'cms-ctrl-action' && is_array($_node -> uriSegmentNameAlias))
			$_node -> uriSegmentNameAlias = $_node -> uriSegmentNameAlias[1];
		elseif($_node -> queryVar == 'cms-ctrl-action')
			$_node -> uriSegmentNameAlias = json_encode($_node -> uriSegmentNameAlias);

		$index = count($this -> childNodesList);
		$this -> childNodesList[$index] = $_node;
		return $this -> childNodesList[$index];
	}
}

?>