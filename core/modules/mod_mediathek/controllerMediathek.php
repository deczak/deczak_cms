<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelMediathek.php';	

class	controllerMediathek extends CController
{

	public function
	__construct(object $_module, object &$_object)
	{		
		parent::__construct($_module, $_object);

		CPageRequest::instance() -> subs = $this -> getSubSection();
		
		/*
		$this -> imageSizesList = [
			'xlarge' => new pos(0, 0,1980, 1200),
			'large'  => new pos(0, 0,1600, 1024),
			'medium' => new pos(0, 0,1200, 1200),
			'small'  => new pos(0, 0, 800,  800),
			'thumb'  => new pos(0, 0, 500,  500),
		];
		*/
	}
	
	public function
	logic(CDatabaseConnection &$_pDatabase, array $_rcaTarget, ?object $_xhrInfo) : bool
	{
		##	Set default target if not exists

		$controllerAction = $this -> getControllerAction($_rcaTarget, 'index');
		
		##	Check user rights for this target
		
		if(!$this -> detectRights($controllerAction))
		{
			if($_xhrInfo !== null)
			{
				$validationErr =	true;
				$validationMsg =	CLanguage::string('ERR_PERMISSON');
				$responseData  = 	[];


				tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
			}

			CMessages::add(CLanguage::string('ERR_PERMISSON') , MSG_WARNING);
			return false;
		}

		$controllerAction = $this -> getControllerAction_v2($_rcaTarget, $_xhrInfo, 'index');

			
		if($_xhrInfo !== null && $_xhrInfo -> isXHR && $_xhrInfo -> objectId === $this -> objectInfo -> object_id)
			$controllerAction = 'xhr_'. $_xhrInfo -> action;

		##	Call sub-logic function by target, if there results are false, we make a fall back to default view

		$enableEdit 	= $this -> existsUserRight('edit');
		$enableDelete	= $this -> existsUserRight('delete');
		$enableUpload	= $this -> existsUserRight('upload');
	

		$logicDone = false;
		switch($controllerAction)
		{
			case 'xhr_index': 			$logicDone = $this -> logicXHRIndex($_pDatabase, $_xhrInfo, $enableEdit, $enableDelete, $enableUpload);
			#case 'xhr_import': 			$logicDone = $this -> logicXHRImport($_pDatabase, $_xhrInfo, $enableEdit, $enableDelete, $enableUpload);
			case 'xhr_directory_list': 	$logicDone = $this -> logicXHRDirectoryList();
			case 'xhr_directory_items': $logicDone = $this -> logicXHRDirectoryItems();
			case 'xhr_move_item':		$logicDone = $this -> logicXHRMoveItem();
			case 'xhr_remove_item':		$logicDone = $this -> logicXHRRemoveItem($_pDatabase);
			case 'xhr_edit_item':		$logicDone = $this -> logicXHREditItem($_pDatabase, $_xhrInfo, $enableEdit, $enableDelete, $enableUpload);
			case 'xhr_get_item':		$logicDone = $this -> logicXHRGetItem($_pDatabase, $_xhrInfo);
			case 'xhr_upload':		$logicDone = $this -> logicXHRUpload($_pDatabase, $_xhrInfo);
		}

		if(!$logicDone) // Default
			$logicDone = $this -> logicIndex($_pDatabase, $enableEdit, $enableDelete);	
	
		return $logicDone;
	}

	private function
	logicIndex(CDatabaseConnection &$_pDatabase, bool $_enableEdit = false, bool $_enableDelete = false, bool $_enableUpload = false) : bool
	{
		$this -> setView(	
						'index',	
						'',
						[
							'enableEdit'		=> $_enableEdit,
							'enableDelete'		=> $_enableDelete
						]
						);

		return true;
	}

	private function
	logicXHRIndex(CDatabaseConnection &$_pDatabase, ?object $_xhrInfo, bool $_enableEdit = false, bool $_enableDelete = false, bool $_enableUpload = false) : bool
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

					$mediaId = MEDIATHEK::getMediaIdFromItem($mediathekPath .$_dirItem -> getFilename());

					if($mediaId === null)
						continue;

					$itemDBInfo = modelMediathek::find($mediaId);

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
			
					$mediathekItem  = new stdClass;
					$mediathekItem -> type 	    = 'FILE';
					$mediathekItem -> name 	    = $itemDBInfo -> media_filename;
					$mediathekItem -> size 		= $itemDBInfo -> media_size;
					$mediathekItem -> extension = $itemDBInfo -> media_extension;
					$mediathekItem -> mime 		= $itemDBInfo -> media_mime;
					$mediathekItem -> path 		= $mediathekPath.$_dirItem -> getFilename();
					$mediathekItem -> media_id 	= $itemDBInfo -> media_id;
					$mediathekItem -> time 		= date("Y-m-d H:i:s", $_dirItem -> getMTime());

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
					$mediathekItem -> time 		= date("Y-m-d H:i:s", $_dirItem -> getMTime());

					$itemsListDirs[] = $mediathekItem;
				}
			}
		}

		$responseData = array_merge($itemsListDirs, $itemsListFiles);

		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call

		return false;
	}

	/**
	 * 	XHR Call to import all new (unprocessed) files in mediathek directory
	private function
	logicXHRImport(CDatabaseConnection &$_pDatabase, ?object $_xhrInfo, bool $_enableEdit = false, bool $_enableDelete = false, bool $_enableUpload = false) : bool
	{
		$validationErr   = false;
		$validationMsg   = 'OK';
		$responseData    = [];

		$itemsList = [];

		MEDIATHEK::getRawItemsList($itemsList);

		foreach($itemsList as $item)
		{
			$itemPath = CMS_SERVER_ROOT.DIR_MEDIATHEK.$item -> filelocation . $item -> filenameBase .'/';

			if(!file_exists($itemPath))
			if(!mkdir($itemPath, 0777, true))
			{
				// TODO ERR
				continue;
			}

			if(!rename(CMS_SERVER_ROOT.DIR_MEDIATHEK.$item -> filepath, $itemPath . $item -> filename))
			{
				// TODO ERR
				continue;
			}

			switch($item -> mime)
			{
				case 'image/jpeg':

					$itemExifInfo	= (object)exif_read_data($itemPath . $item -> filename, 'EXIF');
					break;
			}

			$itemInfo = new stdClass;

			$itemInfo -> scheme		   = 1;
			$itemInfo -> filename	   = $item -> filename;
			$itemInfo -> sizes		   = [];
			$itemInfo -> license	   = $itemExifInfo -> Copyright ?? '';
			$itemInfo -> license_url   = '';
			$itemInfo -> gear		   = [
				"by_meta"	=> false,
				"camera"	=> $itemExifInfo -> Model ?? '',
				"lens"		=> $itemExifInfo -> LensModel ?? $itemExifInfo -> {'UndefinedTag:0xA434'} ?? ''
			];
			$itemInfo -> gear_settings = [];	// This values are not getting retrieved at the moment
			$itemInfo -> title		   = '';
			$itemInfo -> caption	   = '';
			$itemInfo -> author 	   = $itemExifInfo -> Artist ?? '';
			$itemInfo -> notice		   = '';
			$itemInfo -> timeAdd	   = time();

			switch($item -> mime)
			{
				case 'image/png': 
				case 'image/webp': 
				case 'image/jpeg':

					$itemInfo -> sizes = MEDIATHEK::createResizedImages($itemPath, $item -> filename, $item -> mime, $this -> imageSizesList);
					$itemInfo -> sizes = $itemInfo -> sizes ?? [];
		
					break;

				default: // ...................	Add file for download

					// todo
			}

			file_put_contents($itemPath.'info.json', json_encode($itemInfo, JSON_UNESCAPED_UNICODE));

			$modelMediathek = new modelMediathek;
			$mediaId = $modelMediathek -> insert($_pDatabase, [
				'media_filename' 	  => $itemInfo -> filename,
				'media_title' 		  => $itemInfo -> title,
				'media_caption' 	  => $itemInfo -> caption,
				'media_author' 		  => $itemInfo -> author,
				'media_notice' 		  => $itemInfo -> notice,
				'media_license' 	  => $itemInfo -> license,
				'media_license_url'   => $itemInfo -> license_url,
				'media_gear' 		  => $itemInfo -> gear,
				'media_gear_settings' => $itemInfo -> gear_settings ?? [],
				'media_size' 		  => $item -> size,
				'media_extension' 	  => $item -> extension,
				'media_mime' 		  => $item -> mime,
				'create_by' 		  => CSession::instance() -> getValue('user_id'),
				'create_time' 		  => time()
			]);

			file_put_contents($itemPath. $mediaId .'.media-id', $mediaId);
		}

		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call

		return false;
	}

	 */
	/**
	 * 	XHR Call to retrieve a multidimensional array of mediathek directory
	 */
	private function
	logicXHRDirectoryList() : bool
	{
		$validationErr   = false;
		$validationMsg   = 'OK';
		$responseData    = [];

		MEDIATHEK::getMediathekStructure('', $responseData, 1);

		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call

		return false;
	}

	/**
	 * 	XHR Call to retrieve the items in the defined diretory only
	 */
	private function
	logicXHRDirectoryItems() : bool
	{
		$validationErr   = false;
		$validationMsg   = 'OK';
		$responseData    = [];

		$pURLVariables	 =	new CURLVariables();
		$requestList		 =	[];
		$requestList[] 	 = 	[ "input" => 'simple-gallery-path', "validate" => "strip_tags|!empty", "use_default" => true, "default_value" => '/' ]; 		
		$pURLVariables -> retrieve($requestList, false, true);	

		$urlVarList		 = $pURLVariables -> getArray();
	
		MEDIATHEK::getItemsList($urlVarList['simple-gallery-path'].'/', $responseData, true);

		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call

		return false;
	}

	/**
	 * 	XHR Call to move items
	 */
	private function
	logicXHRMoveItem() : bool
	{
		$validationErr   = false;
		$validationMsg   = 'OK';
		$responseData    = [];

		$pURLVariables	 =	new CURLVariables();
		$requestList		 =	[];
		$requestList[] 	 = 	[ "input" => 'mediathek-move-item-src', "validate" => "strip_tags|!empty", "use_default" => true, "default_value" => false ]; 		
		$requestList[] 	 = 	[ "input" => 'mediathek-move-item-dst', "validate" => "strip_tags|!empty", "use_default" => true, "default_value" => false ]; 		
		$pURLVariables -> retrieve($requestList, false, true);	

		$urlVarList		 = $pURLVariables -> getArray();


		if($urlVarList['mediathek-move-item-src'] === false || $urlVarList['mediathek-move-item-dst'] === false)
		{
			$validationErr =	true;
			$validationMsg =	'Mediathek, not valid parameters to move item';
			$responseData  = 	[];

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		}

		$destLocation 	  = CMS_SERVER_ROOT.DIR_MEDIATHEK.trim($urlVarList['mediathek-move-item-dst'],'/');

		if(!file_exists($destLocation))
		{
			$validationErr =	true;
			$validationMsg =	'Mediathek item destination does not exists';
			$responseData  = 	[];

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		}

		$itemLocation 	  = CMS_SERVER_ROOT.DIR_MEDIATHEK.trim($urlVarList['mediathek-move-item-src'],'/');
		$itemInfoLocation = $itemLocation.'/info.json';

		if(!file_exists($itemLocation))
		{
			$validationErr =	true;
			$validationMsg =	'Mediathek item does not exists';
			$responseData  = 	[];

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		}

		$itemFilename = trim($urlVarList['mediathek-move-item-src'],'/');
		$itemFilename = explode('/', $itemFilename);
		$itemFilename = end($itemFilename);

		if(file_exists($destLocation.'/'. $itemFilename))
		{
			$validationErr =	true;
			$validationMsg =	'Mediathek item on destination exists already';
			$responseData  = 	[];

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		}


		if(!file_exists($itemInfoLocation))
		{
			// possible dir 


			/*
			
				does not work with rename if new file already exists by duplicated files (php warning)

					this also means the other file has his own media-id.
					
					this also means there is could be content or a page with the media-id 

					remove / overwrite results in media-id conflict





				once solution is, move file by adding a suffix to his name if the file already exists

					loope recursive all DIRs and check the file on exists

				
				
			
			*/



		}
		else
		{
			// possible file

			$mediaId = MEDIATHEK::getMediaIdFromItem(trim($urlVarList['mediathek-move-item-src'],'/'));

			if($mediaId === null)
			{
				$validationErr =	true;
				$validationMsg =	'Mediathek item is not a valid item, Media-ID missing';
				$responseData  = 	[];

				tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
			}

			if(!rename(
				$itemLocation,
				$destLocation.'/'. $itemFilename))
			{
				$validationErr =	true;
				$validationMsg =	'Mediathek move file failed on exec';
				$responseData  = 	[];

				tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
			}
		}

		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call

		return false;
	}

	/**
	 * 	XHR Call to move items
	 */
	private function
	logicXHRRemoveItem(CDatabaseConnection &$_pDatabase) : bool
	{
		$validationErr   = false;
		$validationMsg   = 'OK';
		$responseData    = [];

		$pURLVariables	 =	new CURLVariables();
		$requestList		 =	[];
		$requestList[] 	 = 	[ "input" => 'mediathek-remove-item-src', "validate" => "strip_tags|!empty", "use_default" => true, "default_value" => false ]; 		
		$pURLVariables -> retrieve($requestList, false, true);	

		$urlVarList		 = $pURLVariables -> getArray();

		if($urlVarList['mediathek-remove-item-src'] === false)
		{
			$validationErr =	true;
			$validationMsg =	'Mediathek, not valid parameters to remove item';
			$responseData  = 	[];

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		}

		$itemLocation 	  = CMS_SERVER_ROOT.DIR_MEDIATHEK.trim($urlVarList['mediathek-remove-item-src'],'/');

		if(!file_exists($itemLocation))
		{
			$validationErr =	true;
			$validationMsg =	'Mediathek item does not exists';
			$responseData  = 	[];

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		}

		if($itemLocation === CMS_SERVER_ROOT.DIR_MEDIATHEK)
			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call

		$mediaId = MEDIATHEK::getMediaIdFromItem($urlVarList['mediathek-remove-item-src']);

		if($mediaId !== null)
			$mediaIdList[] = $mediaId;
		else
			$mediaIdList = MEDIATHEK::getMediaIdsFromPath(trim($urlVarList['mediathek-remove-item-src'],'/'));

		if(!empty($mediaIdList))
		{
			modelMediathek::whereIn('media_id', $mediaIdList)->delete();

			/*

			$modelCondition = new CModelCondition();
			$modelCondition -> whereIn('media_id', implode(',', $mediaIdList));
			
			$modelMediathek = new modelMediathek;
			$modelMediathek	-> delete($_pDatabase, $modelCondition);	
			
			*/
		}

		tk::rrmdir($itemLocation);

		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call

		return false;
	}

	/**
	 *
	 */
	private function
	logicXHREditItem(CDatabaseConnection &$_pDatabase, ?object $_xhrInfo, bool $_enableEdit = false, bool $_enableDelete = false, bool $_enableUpload = false) : bool
	{
		$validationErr   = false;
		$validationMsg   = 'OK';
		$responseData    = [];



		$pURLVariables	 =	new CURLVariables();
		$requestList		 =	[];
		$requestList[] 	 = 	[ "input" => 'media_id', "validate" => "strip_tags|trim|!empty", "use_default" => true, "default_value" => false ]; 		
		$requestList[] 	 = 	[ "input" => 'media_gear_camera', "validate" => "strip_tags|trim|!empty", "use_default" => true, "default_value" => '' ]; 		
		$requestList[] 	 = 	[ "input" => 'media_gear_lens', "validate" => "strip_tags|trim|!empty", "use_default" => true, "default_value" =>  '' ]; 	
		$requestList[] 	 = 	[ "input" => 'media_author', "validate" => "strip_tags|trim|!empty", "use_default" => true, "default_value" =>  '' ]; 		
		$requestList[] 	 = 	[ "input" => 'media_caption', "validate" => "strip_tags|trim|!empty", "use_default" => true, "default_value" =>  '' ]; 		
		$requestList[] 	 = 	[ "input" => 'media_notice', "validate" => "strip_tags|trim|!empty", "use_default" => true, "default_value" =>  '' ]; 			
		$requestList[] 	 = 	[ "input" => 'media_title', "validate" => "strip_tags|trim|!empty", "use_default" => true, "default_value" =>  '' ]; 			
		$requestList[] 	 = 	[ "input" => 'media_license_url', "validate" => "strip_tags|trim|!empty", "use_default" => true, "default_value" =>  '' ]; 			
		$requestList[] 	 = 	[ "input" => 'media_license', "validate" => "strip_tags|trim|!empty", "use_default" => true, "default_value" =>  '' ]; 			
		$pURLVariables -> retrieve($requestList, false, true);	

		$urlVarList		 = $pURLVariables -> getArray();


		if($urlVarList['media_id'] === false)
		{
			$validationErr =	true;
			$validationMsg =	'Mediathek, media-id missing';
			$responseData  = 	[];

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		}
				



		$urlVarList['media_gear'] = (object)[
			'camera' => $urlVarList['media_gear_camera'],
			'lens'   => $urlVarList['media_gear_lens']
		];




		$urlVarList['update_by'] = CSession::instance() -> getValue('user_id');
		$urlVarList['update_time'] = time();




		$modelMediathek = modelMediathek::find($urlVarList['media_id']);
		$modelMediathek->pull($urlVarList);

		/*
		$modelCondition = new CModelCondition();
		$modelCondition -> where('media_id', $urlVarList['media_id']);

		
		$modelMediathek  = new modelMediathek;
		if($modelMediathek -> update($_pDatabase, $urlVarList, $modelCondition) === false)
		*/
		if(!$modelMediathek->save())
		{


			$validationErr =	true;
			$validationMsg =	'Mediathek, update media item failed';
			$responseData  = 	[];

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		}


		$itemFSPath = MEDIATHEK::getItemFSPath($urlVarList['media_id']);



		$itemInfoPath = $itemFSPath.'info.json';

		if(!file_exists($itemInfoPath))
		{
			$validationErr =	true;
			$validationMsg =	'Mediathek, media info file missing';
			$responseData  = 	[];

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call

		}

					$itemInfo = file_get_contents($itemInfoPath);

					if($itemInfo === false)
					{
			$validationErr =	true;
			$validationMsg =	'Mediathek, media info file failure';
			$responseData  = 	[];

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
					}

					$itemInfo = json_decode($itemInfo);

					if($itemInfo === null)
					{
			$validationErr =	true;
			$validationMsg =	'Mediathek, media info file content failure';
			$responseData  = 	[];

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
					}



			$itemInfo -> title 		 = $urlVarList['media_title'];
			$itemInfo -> caption 	 = $urlVarList['media_caption'];
			$itemInfo -> license 	 = $urlVarList['media_license'];
			$itemInfo -> license_url = $urlVarList['media_license_url'];
			$itemInfo -> author 	 = $urlVarList['media_author'];
			$itemInfo -> notice 	 = $urlVarList['media_notice'];

			$itemInfo -> gear -> camera = $urlVarList['media_gear_camera'];
			$itemInfo -> gear -> lens 	= $urlVarList['media_gear_lens'];


					$itemInfo = file_put_contents($itemInfoPath, json_encode($itemInfo, JSON_UNESCAPED_UNICODE));



		$responseData = $urlVarList;


		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call

		return false;
	}

	/**
	 *
	 */
	private function
	logicXHRGetItem(CDatabaseConnection &$_pDatabase, ?object $_xhrInfo, bool $_enableEdit = false, bool $_enableDelete = false, bool $_enableUpload = false) : bool
	{
		$validationErr   = false;
		$validationMsg   = 'OK';
		$responseData    = [];

		$pURLVariables	 =	new CURLVariables();
		$requestList		 =	[];
		$requestList[] 	 = 	[ "input" => 'media_id', "validate" => "strip_tags|trim|!empty", "use_default" => true, "default_value" => false ]; 				
		$pURLVariables -> retrieve($requestList, false, true);	

		$urlVarList		 = $pURLVariables -> getArray();

		if($urlVarList['media_id'] === false)
		{
			$validationErr =	true;
			$validationMsg =	'Mediathek, media-id missing';
			$responseData  = 	[];

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		}


		/*
		$modelCondition = new CModelCondition();
		$modelCondition -> where('media_id', $urlVarList['media_id']);
		
		$modelMediathek  = new modelMediathek;
		$modelMediathek -> load($_pDatabase, $modelCondition);

		$itemDBInfo = $modelMediathek -> getResult();
		$itemDBInfo = (!empty($itemDBInfo) ? reset($itemDBInfo) : null);
		*/


		$itemDBInfo = modelMediathek::find($urlVarList['media_id']);

		$responseData = (array)$itemDBInfo;

		if($itemDBInfo === null)
		{
			$validationErr =	true;
			$validationMsg =	'Mediathek, could not find item';
			$responseData  = 	[];

			tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
		}

		$responseData['media_url'] = MEDIATHEK::getItemUrl($urlVarList['media_id']);
		
		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call

		return false;
	}

	/**
	 *
	 */
	private function
	logicXHRUpload(CDatabaseConnection &$_pDatabase, ?object $_xhrInfo) : bool
	{
		$queryValidationString = QueryValidation::STRIP_TAGS | QueryValidation::TRIM | QueryValidation::IS_NOTEMPTY;


		print_r($_POST); echo  PHP_EOL;


		$requestQuery = new cmsRequestQuery(true);
		$requestQuery->post('media-item-author')->validate($queryValidationString)->out('media_item_author')->exec();
		$requestQuery->post('media-item-camera')->validate($queryValidationString)->out('media_item_camera')->exec();
		$requestQuery->post('media-item-caption')->validate($queryValidationString)->out('media_item_caption')->exec();
		$requestQuery->post('media-item-lens')->validate($queryValidationString)->out('media_item_lens')->exec();
		$requestQuery->post('media-item-license')->validate($queryValidationString)->out('media_item_license')->exec();
		$requestQuery->post('media-item-licenseurl')->validate($queryValidationString)->out('media_item_licenseurl')->exec();
		$requestQuery->post('media-item-path')->validate($queryValidationString)->out('media_item_path')->exec();
		$requestQuery->post('media-item-title')->validate($queryValidationString)->out('media_item_title')->exec();
		$requestQuery->post('media-item-filename')->validate($queryValidationString)->out('media_item_filename')->exec();
		$mediaParams = $requestQuery->toObject();

		$cmsUpload = new cmsUpload;

		print_r($mediaParams); echo  PHP_EOL;

		$uploadResponse = $cmsUpload -> processUpload(
			cmsUpload::DEST_MEDIATHEK, 
			$mediaParams->media_item_path,
			true,
			$mediaParams
			);
			
		tk::xhrResponse(
			200, 
			$uploadResponse
			);

		return true;
	}
}
