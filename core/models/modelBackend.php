<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemePage.php';		
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemePageObject.php';	

include_once 'modelPageObject.php';		

class 	modelBackend extends CModel
{
	private	$m_shemePageObjects;

	public function
	__construct()
	{		
		parent::__construct('shemePage', 'page');	

		$this -> m_shemePageObjects 	= new shemePageObject();
	}	
	
	public function
	loadOld(CDatabaseConnection &$_pDatabase, string $_nodeID)
	{
		## Create Class

		$_className		=	$this -> createPrototype();

		##	Read backend file

		$_backendSites = file_get_contents( CMS_SERVER_ROOT.DIR_DATA .'backend/backend.json');
		$_backendSites = ($_backendSites !== false ? json_decode($_backendSites, true) : [] );	


		$_targetSite = array_search($_nodeID, array_column($_backendSites, 'node_id'));

		if($_targetSite !== false)
		{
			$_pageData = $_backendSites[$_targetSite];
			$_pageData['page_template'] 	= CMS_BACKEND_TEMPLATE;
			$_pageData['page_language'] 	= 'en';
			
			##	Check of user got authed



			if(!empty($_pageData['page_auth']) && $_targetSite != 0)
			{
				if(CSession::instance() -> isAuthed($_pageData['page_auth']) === false)
				{
					header("Location: ". CMS_SERVER_URL_BACKEND ); 			
					exit;		
				}
			}

			##	Create Page object

			$this -> m_resultList = new $_className($_pageData, $this -> m_pSheme -> getColumns());


			##	Create object object

			$modelPageObject = new modelPageObject();

			$_className		=	$modelPageObject -> createClass();

			foreach($this -> m_resultList -> objects as $_objectKey =>  $_objectData)
			{
				$this -> m_resultList -> objects[$_objectKey] = new $_className($_objectData, $this -> m_shemePageObjects -> getColumns());
			}
			
			return true;
		}
		return false;
	}
}

/**
 * 	Parent class for the data class with toolkit functions. It get the child instance to access the child properties.

class 	toolkitSite
{
	protected	$m_childInstance;

	public function
	__construct($_instance)
	{
		$this -> m_childInstance = $_instance;
	}

}
 */
?>