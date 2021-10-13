<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelMediathek.php';	

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
			case 'xhr_import': 	$logicDone = $this -> logicXHRImport($_pDatabase, $_xhrInfo, $enableEdit, $enableDelete, $enableUpload);

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




/*

		$example = [];




			$dirItem  = new stdClass;
			$dirItem -> level = 0; 
			$dirItem -> path  = '';
			$dirItem -> childs = [];

			$example[] = $dirItem;

			$dirItem -> ts     = $this -> getMediathekStructure('', $dirItem -> childs, 1);
			

		
		


		tk::dbug($example);

		$example2 = [];

		$left = 1;
		$right = 2;

		$this -> getNestedSetStructure($example, $example2, $left, $right);

		tk::dbug($example2);

	*/



/*

das für import

							$mediathekItemExif	= (object)exif_read_data($mediathekFilelocation, 'EXIF');

							$mediathekItem -> exif = new stdClass;

							$mediathekItem -> exif -> Model			= $mediathekItemExif -> Model ?? '';
							$mediathekItem -> exif -> LensModel		= $mediathekItemExif -> LensModel ?? $mediathekItemExif -> {'UndefinedTag:0xA434'} ?? '';
							$mediathekItem -> exif -> Artist		= $mediathekItemExif -> Artist ?? '';
							$mediathekItem -> exif -> Copyright		= $mediathekItemExif -> Copyright ?? '';
*/
	
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
		$requestList[] 	 = 	[ "input" => 'path', "validate" => "strip_tags|!empty", "use_default" => true, "default_value" => '/' ]; 		
		$pURLVariables -> retrieve($requestList, false, true);	

		$urlVarList		 = $pURLVariables -> getArray();

		$itemsListFiles  = [];
		$itemsListDirs   = [];

		$mediathekPath	 = $urlVarList['path'];

		$mediathekPath = trim(str_replace('..', '',$mediathekPath), '/');
		$mediathekPath = explode('/', $mediathekPath);
		$mediathekPath = array_filter($mediathekPath, 'strlen');
		$mediathekPath = implode('/', $mediathekPath).'/';


		// TODO .. try catch


		$_dirIterator 	= new DirectoryIterator(CMS_SERVER_ROOT.DIR_MEDIATHEK.$mediathekPath);



		foreach($_dirIterator as $_dirItem)
		{
			if($_dirItem -> isDot())
				continue;

			if($_dirItem -> getFilename()[0] === '.')
				continue;

			if($_dirItem -> isDir())
			{







				$itemInfoLocation = CMS_SERVER_ROOT.DIR_MEDIATHEK.$mediathekPath .$_dirItem -> getFilename().'/info.json';

				if(file_exists($itemInfoLocation))
				{




			$mediaId = $this -> getMediaIdFromItem($mediathekPath .$_dirItem -> getFilename());


			if($mediaId === null)
				continue;


			$modelCondition = new CModelCondition();
			$modelCondition -> where('media_id', $mediaId);
			

		$modelMediathek = new modelMediathek;
		$modelMediathek	-> load($_pDatabase, $modelCondition);	

			$itemDBInfo = $modelMediathek -> getResult();

			$itemDBInfo = (!empty($itemDBInfo) ? reset($itemDBInfo) : null);



#$itemDBInfo -> media_gear = ($itemDBInfo -> media_gear !== null ? json_decode($itemDBInfo -> media_gear) : null);
#$itemDBInfo -> media_gear_settings = ($itemDBInfo -> media_gear_settings !== null ? json_decode($itemDBInfo -> media_gear_settings) : null);


			if($itemDBInfo === null)
				continue;


					## is mediathek item

					$itemInfo = file_get_contents($itemInfoLocation);

					if($itemInfo === false)
					{
						// TODO ERR
						continue;
					}

					$itemInfo = json_decode($itemInfo);

					if($itemInfo === null)
					{
						// TODO ERR
						continue;
					}

					if(!empty($itemInfo -> redirect))
					{
						continue;
					}
						
					$mediathekFilelocation 	= CMS_SERVER_ROOT.DIR_MEDIATHEK.$mediathekPath.$_dirItem -> getFilename().'/'.$itemInfo -> filename;
					$mediathekFileInfo 		= new SplFileInfo($mediathekFilelocation);






					$mediathekItem  = new stdClass;
					$mediathekItem -> type 	    = 'FILE';
					$mediathekItem -> name 	    = $itemDBInfo -> media_filename;
					$mediathekItem -> size 		= $itemDBInfo -> media_size;
					$mediathekItem -> extension = $itemDBInfo -> media_extension;
					$mediathekItem -> mime 		= $itemDBInfo -> media_mime;
					$mediathekItem -> path 		= $mediathekPath.$_dirItem -> getFilename();
					$mediathekItem -> media_id 		= $itemDBInfo -> media_id;

					switch($mediathekItem -> mime)
					{
						case 'image/jpeg':

							$mediathekItem -> exif = new stdClass;

							$mediathekItem -> exif -> Model			= ($itemDBInfo -> media_gear !== null ? $itemDBInfo -> media_gear -> camera : '');
							$mediathekItem -> exif -> LensModel		= ($itemDBInfo -> media_gear !== null ? $itemDBInfo -> media_gear -> lens : '');
							$mediathekItem -> exif -> Artist		= $itemDBInfo -> media_author ?? '';
							$mediathekItem -> exif -> Copyright		= $itemDBInfo -> media_license ?? '';
						break;

						default:

							$mediathekItem -> exif	= false;
					}


			








					$itemsListFiles[] = $mediathekItem;	
				}
				else
				{
					## is regular directory

					$mediathekItem  = new stdClass;
					$mediathekItem -> type      = 'DIR';
					$mediathekItem -> name      = $_dirItem -> getFilename();
					$mediathekItem -> size      = 0;
					$mediathekItem -> extension = 'dir';
					$mediathekItem -> mime 		= 'dir';
					$mediathekItem -> path 		= $mediathekPath.$_dirItem -> getFilename();
					$mediathekItem -> exif 		= false;

					$itemsListDirs[] = $mediathekItem;
				}
			}
		}

		$responseData = array_merge($itemsListDirs, $itemsListFiles);

		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
	}

	private function
	logicXHRImport(CDatabaseConnection &$_pDatabase, ?object $_xhrInfo, $_enableEdit = false, $_enableDelete = false, $_enableUpload = false)
	{
		$validationErr   = false;
		$validationMsg   = 'OK';
		$responseData    = [];

		$modelMediathek = new modelMediathek;

		/*
			import several states

				- items withouth media-id

				- single images
		*/





		/*
			verzeichnisse recursiv loopen und items per array abrufen

			items in db eintragen und media id in die json eintragen

		*/



		$itemsList = [];

		$this -> getMediathekItemsList('', $itemsList);



		foreach($itemsList as $item)
		{

			$mediaId = $this -> getMediaIdFromItem($item -> path);




			if($mediaId !== null)
				continue;







			$mediaId = $modelMediathek -> insert($_pDatabase, [
				'media_filename' => $item -> filename,
				'media_title' => $item -> title,
				'media_caption' => $item -> caption,
				'media_author' => $item -> author,
				'media_notice' => $item -> notice,
				'media_license' => $item -> license,
				'media_license_url' => $item -> license_url,
				'media_gear' => json_encode($item -> gear),
				'media_gear_settings' => json_encode($item -> gear_settings),
				'media_size' => $item -> size,
				'media_extension' => $item -> extension,
				'media_mime' => $item -> mime
			]);


			file_put_contents(CMS_SERVER_ROOT.DIR_MEDIATHEK.$item -> path.'/'. $mediaId .'.media-id', $mediaId);



			// TODO mediaid als datei erstellen
		
		}




		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
	}

	/**
	 * 	Create multi-level array from the mediethek directory
	 */
	public function
	getMediathekStructure(string $path, array &$destList, int $level)
	{
		$found = 0;
		$directoryList = new DirectoryIterator(CMS_SERVER_ROOT.DIR_MEDIATHEK.$path);
		foreach($directoryList as $directory)
		{
			if($directory -> isDot())
				continue;

			if(!$directory -> isDir())
				continue;

			if(file_exists(CMS_SERVER_ROOT.DIR_MEDIATHEK.$path.$directory -> getFilename().'/info.json'))
				continue;

			$dirItem  = new stdClass;
			$dirItem -> level = $level; 
			$dirItem -> path  = $path.$directory -> getFilename();
			$dirItem -> childs = [];
			$destList[] = $dirItem;
			$found++;
			$dirItem -> ts = $this -> getMediathekStructure($path.$directory -> getFilename().'/', $dirItem -> childs, $level + 1);
			$found = $found + $dirItem -> ts;
		}
		return $found;
	}

	/**
	 * 	Create one-level array from getMediathekStructure-array and append left/right informationen
	 */
	public function
	getNestedSetStructure(array $structureList, array &$destList, int &$left, int &$right)
	{
		foreach($structureList as $index => $item)
		{
			$item -> left = $left;
			$destList[] = $item;
			$left++;
			if($item -> ts != 0)
			{
				$right++;
				$this -> getNestedSetStructure($item -> childs, $destList, $left, $right);
			}
			$item -> right = $right;
			if($item -> ts == 0)
			{
				$right++;		
			}
			if($item -> ts != 0)
			{
				$left = $right + 1;
				if(count($structureList) !== 1 && $item -> ts !== 0)
				$right = $right + 2;
					else
				$right = $right + 1;
			}
			unset($item -> childs);
		}
	}

	public function
	getMediathekItemsList(string $path, array &$destList)
	{
		$directoryList = new DirectoryIterator(CMS_SERVER_ROOT.DIR_MEDIATHEK.$path);
		foreach($directoryList as $directory)
		{
			if($directory -> isDot())
				continue;

			if(!$directory -> isDir())
				continue;

			if(file_exists(CMS_SERVER_ROOT.DIR_MEDIATHEK.$path.$directory -> getFilename().'/info.json'))
			{




					$itemInfo = file_get_contents(CMS_SERVER_ROOT.DIR_MEDIATHEK.$path.$directory -> getFilename().'/info.json');

					if($itemInfo === false)
					{
						// TODO ERR
						continue;
					}

					$itemInfo = json_decode($itemInfo);

					if($itemInfo === null)
					{
						// TODO ERR
						continue;
					}

					if(!empty($itemInfo -> redirect))
					{
						continue;
					}
						
					$mediathekFilelocation 	= CMS_SERVER_ROOT.DIR_MEDIATHEK.$path.$directory -> getFilename().'/'.$itemInfo -> filename;
					$mediathekFileInfo 		= new SplFileInfo($mediathekFilelocation);

					$mediathekItem  = new stdClass;
					$mediathekItem -> path  	= $path.$directory -> getFilename();
					$mediathekItem -> filename  	= $directory -> getFilename();
					$mediathekItem -> name 	    	= $mediathekFileInfo -> getFilename();
					$mediathekItem -> size 			= $mediathekFileInfo -> getSize();
					$mediathekItem -> extension 	= $mediathekFileInfo -> getExtension();
					$mediathekItem -> title 		= $itemInfo -> title ?? '';
					$mediathekItem -> caption 		= $itemInfo -> caption ?? '';
					$mediathekItem -> author 		= $itemInfo -> author ?? '';
					$mediathekItem -> notice 		= $itemInfo -> notice ?? '';
					$mediathekItem -> gear 			= $itemInfo -> gear ?? [];
					$mediathekItem -> gear_settings = $itemInfo -> gear_settings ?? [];
					$mediathekItem -> license 		= $itemInfo -> license ?? '';
					$mediathekItem -> license_url 	= $itemInfo -> license_url ?? '';
					$mediathekItem -> mime 			= mime_content_type($mediathekFilelocation);



				$destList[] = $mediathekItem;

			}
			else
			{

				$this -> getMediathekItemsList($path.$directory -> getFilename().'/', $destList);

			}
			

		}
	}

	private function
	getMediaIdFromItem(string $itemPath)
	{

		$directoryList = new DirectoryIterator(CMS_SERVER_ROOT.DIR_MEDIATHEK.$itemPath);
		foreach($directoryList as $directory)
		{
			if($directory -> isDot())
				continue;

			if($directory -> isDir())
				continue;

			if($directory -> getExtension() === 'media-id')
				return $directory -> getBasename('.media-id');
		}

		return null;

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
