<?php
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSitemap.php';	


/*


	->  backend seiten müssen auch in diese struktur

	->	module.json dateien berücksichtigen und die sections mit rein

			sections sind auch nodes aber dürfen dann bei der node_id nicht berücksichtigt werden

			segment der uri wird auf die section umgemappt und als GET bereit gestellt

*/


class CRouter extends CSingleton
{
	public	$nodesList;


	public	$requestedURI;

	public	$languageSettings;
	public	$languagesList;



	public function
	initialize($_languageSettings, $_languagesList)
	{

		$this -> languageSettings 	= $_languageSettings;
		$this -> languagesList 		= $_languagesList;

		$this -> nodesList 			= new CRouteNode(1, '', '');

	}


	public function
	createRoutes(CDatabaseConnection &$_dbConnection)
	{
		$modelSitemap = new modelSitemap();
		$modelSitemap -> load($_dbConnection);
		$sitemap = &$modelSitemap -> getResult();

		$this  -> _createRoute($sitemap, 1, 0, $this -> nodesList);
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
		$routeRequest  = new CRouteRequest;
		$routeRequest -> requestedURI = $_requestedURI;

		$this -> requestedURI = $_requestedURI; 

		$buffer = $_requestedURI;

		$questionMark = strpos($buffer, '?');

		if($questionMark !== false)
		{

			$buffer = substr($buffer, 0, $questionMark);
		}

		$buffer = trim($buffer, '/');
		$buffer = explode('/', $buffer);

		$nodeInstance = &$this-> nodesList;


		for($sIndex = 0; $sIndex < count($buffer); $sIndex++)
		{
			## determine language of request

			if($sIndex == 0)
			{
				$langFound = false;

				foreach($this -> languagesList as $lIndex => $language)
				{
					if(		$buffer[$sIndex] == $language -> lang_key 
						&& 	( 		!$language -> lang_default 
								|| 	($language -> lang_default && $this -> languageSettings -> DEFAULT_IN_URL)
						
							)
					  )
					{
						$routeRequest -> language = $language -> lang_key;
						$langFound = !$langFound;
						break;
					} elseif($language -> lang_default)
					{
						$routeRequest -> language = $language -> lang_key;
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

				if($sIndex == 0 && $childNode -> uriSegmentName == ''  && $nodeInstance -> nodeId == 1 && $childNode -> language == $routeRequest -> language)
				{

					$nodeInstance = &$childNode;
					$nodeFound = !$nodeFound;
					break;


				}

				if($childNode -> uriSegmentName == $buffer[$sIndex] && $childNode -> language == $routeRequest -> language)
				{

					$nodeInstance = &$childNode;
					$nodeFound = !$nodeFound;
					break;



				}




			}




			$routeRequest -> nodeId = $nodeInstance -> nodeId;







		}


		return $routeRequest;



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
	public	$queryName;
	public	$language;

	public function
	__construct($_nodeId, $_language, $_uriSegmentName, $_queryName = false)
	{
		$this -> nodeId 		= $_nodeId;
		$this -> uriSegmentName = $_uriSegmentName;
		$this -> queryName 		= $_queryName;
		$this -> language 		= $_language;
		$this -> childNodesList = [];
	}

	public function
	addChild(CRouteNode $_node) : CRouteNode
	{
		$index = count($this -> childNodesList);
		$this -> childNodesList[$index] = $_node;
		return $this -> childNodesList[$index];
	}
}






?>