<?php

#include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelTags.php';	

class	controllerMediathek extends CController
{

	public function
	__construct(object $_module, object &$_object)
	{		
		parent::__construct($_module, $_object);

		CPageRequest::instance() -> subs = $this -> getSubSection();
	}
	
	public function
	logic(CDatabaseConnection &$_pDatabase, array $_rcaTarget, /*?object*/ $_xhrInfo)
	{

		if($_xhrInfo === false) // temporary because of type conflict
			$_xhrInfo = null;

		##	Set default target if not exists

		$controllerAction = $this -> getControllerAction($_rcaTarget, 'index');

		##	Check user rights for this target

		if(!$this -> detectRights($controllerAction))
		{
			if($_xhrInfo !== null)
			{
				$_bValidationErr =	true;
				$_bValidationMsg =	CLanguage::get() -> string('ERR_PERMISSON');
				$_bValidationDta = 	[];

				tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
			}

			CMessages::instance() -> addMessage(CLanguage::get() -> string('ERR_PERMISSON') , MSG_WARNING);
			return;
		}
		
		if($_xhrInfo !== null)
			$controllerAction = 'xhr_'. $_xhrInfo -> action;

		##	Call sub-logic function by target, if there results are false, we make a fall back to default view

		$enableEdit 	= $this -> existsUserRight('edit');
		$enableDelete	= $this -> existsUserRight('delete');
		$enableUpload	= $this -> existsUserRight('upload');

		$logicDone = false;
		switch($controllerAction)
		{
			case 'xhr_index': 	$logicDone = $this -> logicXHRIndex($_pDatabase, $_xhrInfo, $enableEdit, $enableDelete, $enableUpload);

		#	case 'view'		: $_logicResults = $this -> logicView(	$_pDatabase, $_isXHRequest, $enableEdit, $enableDelete);	break;
		#	case 'edit'		: $_logicResults = $this -> logicEdit(	$_pDatabase, $_isXHRequest);	break;	
		#	case 'create'	: $_logicResults = $this -> logicCreate($_pDatabase, $_isXHRequest);	break;

		#	case 'delete'	: $_logicResults = $this -> logicDelete($_pDatabase, $_isXHRequest);	break;	
		#	case 'ping'		: $_logicResults = $this -> logicPing($_pDatabase, $_isXHRequest, $enableEdit, $enableDelete);	break;	
		}

		if(!$logicDone)
		{
			##	Default View
			$this -> logicIndex($_pDatabase, $enableEdit, $enableDelete, $enableUpload);	
		}
	}

	private function
	logicIndex(CDatabaseConnection &$_pDatabase, $_enableEdit = false, $_enableDelete = false, $_enableUpload = false)
	{
		$this -> setView(	
						'index',	
						'',
						[
							'enableEdit'		=> $_enableEdit,
							'enableDelete'		=> $_enableDelete
						]
						);
	}

	private function
	logicXHRIndex(CDatabaseConnection &$_pDatabase, ?object $_xhrInfo, $_enableEdit = false, $_enableDelete = false, $_enableUpload = false)
	{




		$validationErr   = false;
		$validationMsg   = 'OK';
		$responseData    = [];

		$pURLVariables	 =	new CURLVariables();
		$requestList		 =	[];
		$requestList[] 	 = 	[ "input" => 'q', "validate" => "strip_tags|!empty", "use_default" => true, "default_value" => false ]; 		
		$pURLVariables -> retrieve($requestList, false, true);	





		$_dirIterator 	= new DirectoryIterator(CMS_SERVER_ROOT.DIR_MEDIATHEK);
		foreach($_dirIterator as $_dirItem)
		{
			if($_dirItem -> isDot())
				continue;

			if($_dirItem -> getFilename()[0] === '.')
				continue;

			if($_dirItem -> isDir())
			{
				$mediathekItem  = new stdClass;
				$mediathekItem -> type      = 'DIR';
				$mediathekItem -> name      = $_dirItem -> getFilename();
				$mediathekItem -> size      = 0;
				$mediathekItem -> extension = 'dir';
				$mediathekItem -> mime 		= 'dir';
				$mediathekItem -> exif 		= false;

				$responseData[] = $mediathekItem;
			}
			elseif($_dirItem -> isFile())
			{
				$mediathekItem  = new stdClass;
				$mediathekItem -> type 	    = 'FILE';
				$mediathekItem -> name 	    = $_dirItem -> getFilename();
				$mediathekItem -> size 		= $_dirItem -> getSize();
				$mediathekItem -> extension = $_dirItem -> getExtension();
				$mediathekItem -> mime 		= mime_content_type($_dirItem -> getPathname());

				switch($mediathekItem -> mime)
				{
					case 'image/jpeg':

						$mediathekItem -> exif	= exif_read_data($_dirItem -> getPathname(), 'EXIF');
						break;

					default:

						$mediathekItem -> exif	= false;
				}


				



				


				$responseData[] = $mediathekItem;		
			}


			





		}




/*

			$modelCondition  = 	new CModelCondition();

			if($_pURLVariables -> getValue("q") !== false)
			{	
				$conditionSource = 	explode(' ', $_pURLVariables -> getValue("q"));
				foreach($conditionSource as $conditionItem)
				{
					$itemParts = explode(':', $conditionItem);

					if(count($itemParts) == 1)
					{
						$modelCondition -> whereLike('tag_name', $itemParts[0]);
					}
					else
					{
						if( $itemParts[0] == 'cms-system-id' )
							$itemParts[0] = 'tb_tags.tag_id';
						
						$modelCondition -> where($itemParts[0], $itemParts[1]);
					}
				}										
			}

			$modelCondition -> groupBy('tag_id');

			if(!$this -> m_pModel -> load($_pDatabase, $modelCondition, MODEL_TAGS_ALLOCATION_COUNT))
			{
				$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
				$_bValidationErr = true;
			}											

			$data = $this -> m_pModel -> getResult();

*/



			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		



	}


/*

	private function
	logicCreate(CDatabaseConnection &$_pDatabase, $_isXHRequest)
	{
		if($_isXHRequest !== false)
		{
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			$_pURLVariables	 =	new CURLVariables();
			$_request		 =	[];
			$_request[] 	 = 	[	"input" => "tag_name",  	"validate" => "strip_tags|trim|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "tag_hidden",  	"validate" => "strip_tags|trim|!empty" ]; 	
			$_request[] 	 = 	[	"input" => "tag_disabled", "validate" => "strip_tags|trim|!empty" ]; 		
			$_pURLVariables -> retrieve($_request, false, true); // POST 
			$_aFormData		 = $_pURLVariables ->getArray();

			if(empty($_aFormData['tag_name'])) 	{ 	$_bValidationErr = true; 	$_bValidationDta[] = 'tag_name'; 	}
			if(!isset($_aFormData['tag_hidden'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'tag_hidden'; 	}
			if(!isset($_aFormData['tag_disabled'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'tag_disabled'; 	}

			if(!$_bValidationErr)	// Validation OK (by pre check)
			{		
			}
			else	// Validation Failed 
			{
				$_bValidationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
			}

			if(!$_bValidationErr)	// Validation OK
			{
				$_aFormData['tag_url'] 	= tk::normalizeFilename($_aFormData['tag_name'], true);

				$dataId = $this -> m_pModel -> insert($_pDatabase, $_aFormData);

				if($dataId !== false)
				{					
					$_bValidationMsg = CLanguage::get() -> string('MOD_BETAGS_TAG') .' '. CLanguage::get() -> string('WAS_CREATED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
											
					$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath .'tag/'.$dataId;
				}
				else
				{
					$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
				}
			}


			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
		}

		$this -> setCrumbData('create');
		$this -> setView(
						'create',
						'create/'
						);

		return true;
	}

	private function
	logicView(CDatabaseConnection &$_pDatabase, $_isXHRequest = false, $_enableEdit = false, $_enableDelete = false)
	{
		$systemId = $this -> querySystemId();

		if($systemId !== false )
		{	
			$modelCondition = new CModelCondition();
			$modelCondition -> where('tag_id', $systemId);

			if($this -> m_pModel -> load($_pDatabase, $modelCondition))
			{
				##	Gathering additional data

				$_crumbName	 = $this -> m_pModel -> getResultItem('tag_id', intval($systemId),'tag_name');

				$this -> setCrumbData('edit', $_crumbName, true);
				$this -> setView(
								'edit',
								'tag/'. $systemId,								
								[
									'tagsList' 	=> $this -> m_pModel -> getResult(),
									'enableEdit'		=> $_enableEdit,
									'enableDelete'		=> $_enableDelete
								]								
								);
				return true;
			}
		}
		
		CMessages::instance() -> addMessage(CLanguage::get() -> string('MOD_BETAGS_ERR_USERID_UK'), MSG_WARNING);
		return false;
	}

	private function
	logicEdit(CDatabaseConnection &$_pDatabase, $_isXHRequest = false)
	{
		$systemId = $this -> querySystemId();

		if($systemId !== false && $_isXHRequest !== false)
		{	
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			$pingId 	= $this -> querySystemId('cms-ping-id', true);

			## check if dataset is locked, call his own xhrResult() 
			$this -> detectLock($_pDatabase, $systemId, $pingId);

			switch($_isXHRequest)
			{
				case 'edit-tag'  :	

										$_pFormVariables =	new CURLVariables();
										$_request		 =	[];
										$_request[] 	 = 	[	"input" => "tag_name",  	"validate" => "strip_tags|trim|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "tag_hidden",  	"validate" => "strip_tags|trim|!empty" ]; 	
										$_request[] 	 = 	[	"input" => "tag_disabled", "validate" => "strip_tags|trim|!empty" ]; 		
										$_pFormVariables -> retrieve($_request, false, true); // POST 
										$_aFormData		 = $_pFormVariables ->getArray();

										if(empty($_aFormData['tag_name'])) 	{ 	$_bValidationErr = true; 	$_bValidationDta[] = 'tag_name'; 	}
										if(!isset($_aFormData['tag_hidden'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'tag_hidden'; 	}
										if(!isset($_aFormData['tag_disabled'])) { 	$_bValidationErr = true; 	$_bValidationDta[] = 'tag_disabled'; 	}

										if(!$_bValidationErr)
										{
											$_aFormData['tag_url'] 	= tk::normalizeFilename($_aFormData['tag_name'], true);

											$modelCondition = new CModelCondition();
											$modelCondition -> where('tag_id', $systemId);

											if($this -> m_pModel -> update($_pDatabase, $_aFormData, $modelCondition))
											{
												$_bValidationMsg = CLanguage::get() -> string('MOD_BETAGS_TAG') .' '. CLanguage::get() -> string('WAS_UPDATED');
											}
											else
											{
												$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
												$_bValidationErr = true;
											}											
										}
										else	// Validation Failed
										{
											$_bValidationMsg .= CLanguage::get() -> string('ERR_VALIDATIONFAIL');
											$_bValidationErr = true;
										}

										break;
			}

			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call		
		}

		return false;
	}

	private function
	logicDelete(CDatabaseConnection &$_pDatabase, $_isXHRequest = false)
	{
		$systemId = $this -> querySystemId();

		if($systemId !== false && $_isXHRequest !== false)
		{	
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			$pingId 	= $this -> querySystemId('cms-ping-id', true);

			## check if dataset is locked, call his own xhrResult() 
			$this -> detectLock($_pDatabase, $systemId, $pingId);

			switch($_isXHRequest)
			{
				case 'tag-delete':

									$modelCondition = new CModelCondition();
									$modelCondition -> where('tag_id', $systemId);

									if($this -> m_pModel -> delete($_pDatabase, $modelCondition))
									{
										$_bValidationMsg = CLanguage::get() -> string('MOD_BETAGS_TAG') .' '. CLanguage::get() -> string('WAS_DELETED') .' - '. CLanguage::get() -> string('WAIT_FOR_REDIRECT');
										$_bValidationDta['redirect'] = CMS_SERVER_URL_BACKEND . CPageRequest::instance() -> urlPath;
									}
									else
									{
										$_bValidationMsg .= CLanguage::get() -> string('ERR_SQL_ERROR');
									}

									break;
			}

			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);	// contains exit call
		}

		return false;
	}

	public function
	logicPing(CDatabaseConnection &$_pDatabase, $_isXHRequest = false, $_enableEdit = false, $_enableDelete = false)
	{
		$systemId 	= $this -> querySystemId();
		$pingId 	= $this -> querySystemId('cms-ping-id', true);

		if($systemId !== false && $_isXHRequest !== false)
		{	
			$_bValidationErr =	false;
			$_bValidationMsg =	'';
			$_bValidationDta = 	[];

			switch($_isXHRequest)
			{
				case 'lockState':	
				
					$locked	= $this -> m_pModel -> ping($_pDatabase, CSession::instance() -> getValue('user_id'), $systemId, $pingId, MODEL_LOCK_UPDATE);
					tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $locked);
					break;
			}

			tk::xhrResult(intval($_bValidationErr), $_bValidationMsg, $_bValidationDta);
		}
	}
	*/
}
