<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelBackendSitemap.php';	// includes regular modelSitemap for Frontend

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

		##	We need to ensure that the nodes for default language are the first in the list

		$defaultLangKey = $this -> _getDefaultLanguageKey();

		$nodesListA = [];
		$nodesListB = [];
		foreach($sitemap as $node)
		{
			if($defaultLangKey == $node -> page_language)
				$nodesListA[] = $node;
			else
				$nodesListB[$node -> page_language][] = $node;
		}

		$sitemap = $nodesListA;
		foreach($nodesListB as $innerList)
			$sitemap = array_merge($sitemap, $innerList);
		
		##	frontend nodes

		$this -> nodesList 			= new CRouteNode(1, '', '');

		$this  -> _createRoute($sitemap, 1, 0, $this -> nodesList);

		##	special module nodes

#		$this -> _appendSpecialModuleRoutes($_dbConnection, $this -> nodesList, true); // there are no modules that use this feature

		##	backend nodes

		$modelBackendSitemap = new modelBackendSitemap();
		$modelBackendSitemap -> load($_dbConnection);
		$backendSitemap = &$modelBackendSitemap -> getResult();

		$defaultLangKey = $this -> _getDefaultLanguageKey();

		$nodesListA = [];
		$nodesListB = [];
		foreach($backendSitemap as $node)
		{
			if($defaultLangKey == $node -> page_language)
				$nodesListA[] = $node;
			else
				$nodesListB[$node -> page_language][] = $node;
		}

		$backendSitemap = $nodesListA;
		foreach($nodesListB as $innerList)
			$backendSitemap = array_merge($backendSitemap, $innerList);
		
		$backendStructure = $this -> nodesList -> addChild( new CRouteNode(-1, 'en', CMS_BACKEND_PUBLIC) );

		$this  -> _createRoute($backendSitemap, 0, 0, $backendStructure);

		$this -> _appendSpecialModuleRoutes($_dbConnection, $backendStructure, false);

		## 	Structure into file

		file_put_contents($this -> routesFilepah, json_encode($this -> nodesList));

	}

	private function
	_getDefaultLanguageKey()
	{
		foreach($this -> languagesList as $lIndex => $language)
		{
			if($language -> lang_default)
			{
				return $language -> lang_key;
			}
		}
		return 'en'; // should not happen
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

	private function
	_appendSpecialModuleRoutes(CDatabaseConnection &$_dbConnection, &$_nodesList, bool $_isFrontend)
	{

		if($_isFrontend)
		{
			$tbPageObject 		= 'tb_page_object';
			$tbPageObjectSimple = 'tb_page_object_simple';

			$condModules  	 = new CModelCondition();
			$condModules 	-> where('is_frontend', 1)
							-> where('is_active', 1);	
		}
		else
		{
			$tbPageObject 		= 'tb_backend_page_object';
			$tbPageObjectSimple = 'tb_backend_page_object_simple';

			$condModules  	 = new CModelCondition();
			$condModules 	-> where('is_frontend', 0)
							-> where('is_active', 1);	
		}


		$modulesList 	 = $_dbConnection	-> query(DB_SELECT) 
											-> table('tb_modules') 
											-> condition($condModules)
											-> exec();

		$sqlDB = $_dbConnection -> getConnection();

		foreach($modulesList as $module)
		{
			switch($module -> module_type) 
			{
				case 'core'   :	

								$moduleConfig 	= file_get_contents( CMS_SERVER_ROOT.DIR_CORE.DIR_MODULES. $module -> module_location .'/module.json');
								$moduleConfig 	= ($moduleConfig !== false ? json_decode($moduleConfig) : [] );	
								break;
															
				case 'mantle' : 

								$moduleConfig 	= file_get_contents( CMS_SERVER_ROOT.DIR_MANTLE.DIR_MODULES. $module -> module_location .'/module.json');
								$moduleConfig 	= ($moduleConfig !== false ? json_decode($moduleConfig) : [] );	
								break;
			}

			if( 	property_exists($moduleConfig, 'query_url_name')  && !empty($moduleConfig  -> query_url_name)
				&&	property_exists($moduleConfig, 'query_url_var')   && !empty($moduleConfig  -> query_url_var)
				&&	property_exists($moduleConfig, 'query_value_var') && !empty($moduleConfig  -> query_value_var)
			  )
			{
				$objectRes	=	$sqlDB -> query("	SELECT		$tbPageObject.node_id,
																$tbPageObjectSimple.params
													FROM		tb_modules
													JOIN		$tbPageObject
														ON		$tbPageObject.module_id 		= tb_modules.module_id
													JOIN		$tbPageObjectSimple
														ON		$tbPageObjectSimple.object_id	= $tbPageObject.object_id
													WHERE		module_controller = '". $module -> module_controller ."'
												",
												PDO::FETCH_CLASS,
												"stdClass");

				$objectList	=	$objectRes -> fetchAll();

				$node2ExpandList = [];

				foreach($objectList as $object)
				{
					$object -> params = json_decode($object -> params);

					if(!empty($object -> params -> parent_node_id))
						$node2ExpandList[] = $object -> params -> parent_node_id;
					else
						$node2ExpandList[] = $object -> node_id;
				}
				
				$node2ExpandList = array_unique($node2ExpandList);

				##	expand nodes

				foreach($node2ExpandList as $exNodeId)
				{
					$node = $this -> _getNodeByNodeId($_nodesList, $exNodeId);

					if($node == null)
						continue;

					$index = count($node -> childNodesList);

					$node -> childNodesList[$index] = new CRouteNode(	$node -> nodeId, 
																		$node -> language, 
																		$moduleConfig  -> query_url_name,
																		$moduleConfig  -> query_url_var
																		);

					$node -> childNodesList[$index] -> childNodesList[] = new CRouteNode(	$node -> nodeId, 
																							$node -> language, 
																							false,
																							$moduleConfig  -> query_value_var
																							);

				}
			}

			##	looping sub section of module thats used by object		
			if(property_exists($moduleConfig, 'module_subs')  && !empty($moduleConfig  -> module_subs) && is_array($moduleConfig  -> module_subs))
			{
				$objectRes	=	$sqlDB -> query("	SELECT		DISTINCT $tbPageObject.node_id,
																$tbPageObject.object_id,
																$tbPageObjectSimple.params
													FROM		tb_modules
													JOIN		$tbPageObject
														ON		$tbPageObject.module_id 		= tb_modules.module_id
													LEFT JOIN	$tbPageObjectSimple
														ON		$tbPageObjectSimple.object_id	= $tbPageObject.object_id
													WHERE		module_controller = '". $module -> module_controller ."'
												",
												PDO::FETCH_CLASS,
												"stdClass");

				$objectList	=	$objectRes -> fetchAll();

				$node2ExpandList = [];

				foreach($objectList as $object)
				{
					if(property_exists($object, 'params') && !empty($object -> params))
						$object -> params = json_decode($object -> params ?? '{}');
					else
						$object -> params = false;
					
					$item  = new stdClass;
					$item -> objectId = $object -> object_id;

					if($object -> params !== false && property_exists($object -> params, 'parent_node_id') && !empty($object -> params -> parent_node_id))
						$item -> nodeId   = $object -> params -> parent_node_id;
					else
						$item -> nodeId   = $object -> node_id;
					
					$node2ExpandList[] = $item;
				}
				
				foreach($node2ExpandList as $exNodeId)
				{
					$node = $this -> _getNodeByNodeId($_nodesList, $exNodeId -> nodeId);

					if($node == null)
						continue;
					
					foreach($moduleConfig -> module_subs as $_moduleSub)
					{	
						if(empty($_moduleSub -> url_name))
						{
							$_createEndNullSub = true;
						}
						else
						{		
							$index = count($node -> childNodesList);

							if(property_exists($_moduleSub, 'query_var'))
								$sub = $node -> addChild( new CRouteNode($node -> nodeId, 'en', $_moduleSub -> url_name, $_moduleSub -> query_var, [$exNodeId -> objectId => $_moduleSub -> ctl_target]) );
							else
								$sub = $node -> addChild( new CRouteNode($node -> nodeId, 'en', $_moduleSub -> url_name, 'cms-ctrl-action', [$exNodeId -> objectId => $_moduleSub -> ctl_target]) );

							if(property_exists($_moduleSub, 'subSection'))
							{
								$sub2 = $sub -> addChild( new CRouteNode($node -> nodeId, 'en', $_moduleSub -> subSection -> url_name, $_moduleSub -> subSection -> query_var ) );

								if(property_exists($_moduleSub -> subSection, 'subSection'))
								{
									$sub2 -> addChild( new CRouteNode($node -> nodeId, 'en', $_moduleSub -> subSection -> subSection -> url_name, $_moduleSub -> subSection -> subSection -> query_var ) );						
								}
							}
						}
					}
				}
			}
		}
	}

	private function
	_getNodeByNodeId(&$_nodesList, $_destNodeId)
	{
		if($_nodesList -> nodeId == $_destNodeId)
			return $_nodesList;

		foreach($_nodesList -> childNodesList as $childNode)
		{
			$node = $this -> _getNodeByNodeId($childNode, $_destNodeId);

			if($node != null)
				return $node;
		}

		return null;
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

			if(($sIndex == 0 && $buffer[$sIndex] === '404') || (CMS_BACKEND && $sIndex == 1 && $buffer[$sIndex] === '404'))
			{
				$routeRequest -> responseCode = 404;
				return $routeRequest;
			}

			if(($sIndex == 0 && $buffer[$sIndex] === '403') || (CMS_BACKEND && $sIndex == 1 && $buffer[$sIndex] === '403'))
			{
				$routeRequest -> responseCode = 403;
				return $routeRequest;
			}

			## determine language of request

			##	>>> Language

			if($sIndex == 0)
			{
				$langFound 		 = false;
				$defaultLanguage = null;

				foreach($this -> languagesList as $lIndex => $language)
				{
					if($buffer[$sIndex] == $language -> lang_key)
					{
						$routeRequest -> language = $language -> lang_key;
						$langFound = true;
					}

					if($language -> lang_default)
					{
						$defaultLanguage = $language;
					}
				}

				if(!$langFound )
				{
					$routeRequest -> language = $defaultLanguage -> lang_key;
				}

				if(!$langFound && $this -> languageSettings -> DEFAULT_IN_URL)
				{
					$buffer = array_merge([$defaultLanguage -> lang_key], $buffer);
				}
			}

			if($sIndex == 0 && count($buffer) == 1 && empty($buffer[$sIndex]))
			{
				$buffer[$sIndex] = $routeRequest -> language;
			}

			## <<< Language
		
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
				if(!CMS_BACKEND)
				{
					header("Location: ". CMS_SERVER_URL ."404"); 	
				}
				else
				{
					header("Location: ". CMS_SERVER_URL_BACKEND ."404"); 	
				}
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