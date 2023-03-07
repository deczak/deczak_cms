<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelSimple.php';	

// daran denken das auch bei der intall einzutragen


class cmsController
{
	protected array  $rightOfPublicAccessList;
	protected object $moduleInfo;
	protected object $objectInfo;

	protected bool 	 $isBackendMode;
	protected bool 	 $isInstallMode;

	protected ?CView $m_pView;

	public function
	__construct(object $_moduleInfo, object &$_objectInfo, bool $_isBackendMode = false)
	{
		$this->rightOfPublicAccessList = [];

		$this->moduleInfo = $_moduleInfo;
		$this->objectInfo = $_objectInfo;
		$this->m_pView = null;

		$this->isBackendMode = $_isBackendMode;
		$this->isInstallMode = false;
	}

	/**
	 *	+++ jedermanns recht
	 */
	protected function
	setRightOfPublicAccess(string $_right)
	{
		if(!in_array($_right, $this->rightOfPublicAccessList, true))
			$this->rightOfPublicAccessList[] = $_right;

	}

	/**
	 * 	liefert die action, beim erstellen die create, bei xhr die xhr, wenn nicht vorhaden die default, sonst die übermittelte action
	 */
	protected function
	getAction(array $_rcaTarget, ?object &$_xhrInfo = null, bool $_pageEditMode, string $_defaultAction = 'view') : string
	{
		if($_pageEditMode && $_xhrInfo === null) 
			return 'edit';

		if($_xhrInfo !== null && $_xhrInfo -> action === 'cms-insert-module')
			return $_xhrInfo -> action = 'create';

		if($_xhrInfo !== null && !empty($_xhrInfo -> objectId) && $_xhrInfo -> objectId === $this -> objectInfo -> object_id)
			return $_xhrInfo -> action;
			
		if(!isset($_rcaTarget[$this -> objectInfo -> object_id]))
			return $_defaultAction;

		return $_rcaTarget[$this -> objectInfo -> object_id];
	}

	/**
	 * 
	 */
	private function
	detectRights(string $_action, array $allowedActions = []) : bool
	{
		if(in_array($_action, $allowedActions, true))
			return true;

		##	get requested inner module path by controller action

		$modulePath = $this -> getModulePath($_action);

		if($modulePath === null)
			return false;

		##	check if linked right exists in module rights

		if(!$this -> existsModuleRight($modulePath -> use_right))
			return false;

		##	check if linked right is in users his rights

		if(!$this -> existsUserRight($modulePath -> use_right))
			return false;


		return true;
	}
	
	/**
	 * 
	 */
	protected function
	validateRight(string $_action, ?object $_xhrInfo = null, array $allowedActions = []) : bool
	{
		if(!$this -> detectRights($_action, $allowedActions))
		{
			if($_xhrInfo !== null && $_xhrInfo -> isXHR)
			{
				tk::xhrResponse(
					200,
					[],
					1, 
					CLanguage::string('ERR_PERMISSON')
					);
			}

			CMessages::add(CLanguage::string('ERR_PERMISSON') , MSG_WARNING);
			return false;
		}

		return true;
	}
	
	protected function
	existsUserRight(string $_rightId) : bool
	{
		if(in_array($_rightId, $this -> moduleInfo -> user_rights) || in_array($_rightId, $this->rightOfPublicAccessList))
			return true;
		return false;
	}

	protected function
	getModulePath(string $_controllerAction) : ?object
	{

		if(property_exists($this -> moduleInfo, 'sections'))
		foreach($this -> moduleInfo -> sections as $path)
		{
			if($path -> ctl_target === $_controllerAction)
				return $path;
		}
		return null;
	}

	protected function
	existsModuleRight(string $_rightId) : bool
	{
		foreach($this -> moduleInfo -> rights as $right)
		{
			if($right -> name === $_rightId)
				return true;
		}
		return false;
	}


	protected function
	setView(string $_view, string $_moduleTarget,  array $_dataInstances = [])
	{
		$moduleType 	= $this -> moduleInfo -> module -> module_type;
		$moduleLocation = $this -> moduleInfo -> module -> module_location;

		switch($moduleType)
		{
			case 'mantle': $moduleTypeLocation = DIR_MANTLE; break;
			default		 : $moduleTypeLocation = DIR_CORE;
		}

		if(		!file_exists(CMS_SERVER_ROOT . $moduleTypeLocation . DIR_MODULES . $moduleLocation .'/view/'. $_view.'.php')
			&&	$this -> moduleInfo -> parentModule !== NULL)
		{
			$moduleType 	= $this -> moduleInfo -> parentModule -> module_type;
			$moduleLocation = $this -> moduleInfo -> parentModule -> module_location;
		}

		switch($moduleType)
		{
			case 'mantle': $moduleTypeLocation = DIR_MANTLE; break;
			default		 : $moduleTypeLocation = DIR_CORE;
		}

		$this -> m_pView = new CView( CMS_SERVER_ROOT . $moduleTypeLocation . DIR_MODULES . $moduleLocation .'/view/'. $_view, $_moduleTarget , $_dataInstances );	
	}	
	
	public function
	view()
	{
		if($this -> m_pView == null) return;
		$this -> m_pView -> view();
	}


	/**
	 * 	Returns the state of backend mode
	 */
	public function isBackendMode() : bool
	{
		return $this->isBackendMode;
	}

	/**
	 *	Set the install mode to tell the process thats called on install procedure 
	 */
	public function setInstallMode(bool $_isInstallMode = true)
	{
		$this->isInstallMode = $_isInstallMode;
	}

	/**
	 *	Returns the state of install mode 
	 */
	public function getInstallMode() : bool
	{
		return $this->isInstallMode;
	}

}


/**
 *	Exented Class for Simple Modules 
 */
class cmsControllerSimple extends cmsController
{

    const PREVENT_XHRRESPONSE 	= 0x01;

	protected ?object $modelSimple; 

	public function __construct(object $_moduleInfo, object &$_objectInfo)
	{
		parent::__construct($_moduleInfo, $_objectInfo);

		$this->modelSimple = new modelSimple();
	}

	/**
	 * 	Output function for regular view
	 */
	public function logicView(CDatabaseConnection &$_pDatabase) : bool
	{
		$simpleObject = modelSimple::db($_pDatabase)->where('object_id', '=', $this -> objectInfo -> object_id)->one();
		
		$this -> setView(	
						'view',	
						'',
						[
							'object' 	=> $simpleObject
						]
						);

		return true;
	}

	/**
	 * 	Output function for page edit mode
	 */
	public function logicEdit(CDatabaseConnection &$_pDatabase) : bool
	{
		$simpleObject = modelSimple::db($_pDatabase)->where('object_id', '=', $this -> objectInfo -> object_id)->one();
		
		$this -> setView(	
						'edit',	
						'',
						[
							'object' 	=> $simpleObject
						]
						);

		return true;
	}

	/**
	 * 	XHR process function to update object data
	 */
	public function logicUpdateExec(CDatabaseConnection &$_pDatabase, object $_xhrInfo, string $sOBody, object $sOParams, ?int $flags = null)
	{
		$simpleObject = modelSimple::db($_pDatabase)->where('object_id', '=', $_xhrInfo -> objectId)->one();

		$simpleObject->body   = $sOBody;
		$simpleObject->params = $sOParams;

		if($simpleObject->save())
		{
			$object = modelPageObject::
					db($_pDatabase)
				->where('object_id', '=', $_xhrInfo -> objectId)
				->one();

			$object->update_time 	= time();
			$object->update_by 		= 0;
			$object->update_reason	= '';
			$object->save();

			if($flags & cmsControllerSimple::PREVENT_XHRRESPONSE)
				return $simpleObject;
		
			tk::xhrResponse(
				200,
				[],
				);	
		}

		if($flags & cmsControllerSimple::PREVENT_XHRRESPONSE)
			return false;

		tk::xhrResponse(
			200,
			[],
			1,
			'Unknown error on sql query'
			);	
	}

	/**
	 * 	XHR process function to delete the object
	 */
	public function logicDeleteExec(CDatabaseConnection &$_pDatabase, object $_xhrInfo)
	{
		$responseErr =	false;
		$responseMsg =	'';
		$responseData = 	[];
		
		if(!$responseErr)
		{
			##	object get deleted by foreign key
		
			modelPageObject::
				  db($_pDatabase)
				->where('object_id', '=', $_xhrInfo -> objectId)
				->delete();

			$responseMsg = 'Object deleted';
											
		}
		else	// Validation Failed
		{
			$responseMsg .= 'Data validation failed - object was not updated';
			$responseErr = true;
		}

		tk::xhrResponse(
			200,
			$responseData,
			intval($responseErr), 
			$responseMsg
			);	
	
		return false;
	}

	/**
	 * 	XHR process function to insert the object
	 */
	public function logicInsertExec(CDatabaseConnection &$_pDatabase, object $_xhrInfo, string $sOBody, object $sOParams, ?int $flags = null)
	{
		$simpleObject = modelSimple::new([
			'object_id' => (int)$this -> objectInfo -> object_id,
			'body' 		=> $sOBody,
			'params' 	=> $sOParams,
		], $_pDatabase);

		if($simpleObject->save())
		{
			if($flags & cmsControllerSimple::PREVENT_XHRRESPONSE)
				return $simpleObject;

			$this -> setView(	
							'edit',	
							'',
							[
								'object' 	=> $simpleObject
							]
							);

			tk::xhrResponse(
				200,
				[ 'html' => $this -> m_pView -> getHTML() ],
				);	
		}

		if($flags & cmsControllerSimple::PREVENT_XHRRESPONSE)
			return false;

		tk::xhrResponse(
			200,
			[],
			1,
			'sql insert failed'
			);	
	}
}

