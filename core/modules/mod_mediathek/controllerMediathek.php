<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelMediathek.php';	

class	controllerMediathek extends CController
{

	public function
	__construct(object $_module, object &$_object)
	{		
		parent::__construct($_module, $_object);

		CPageRequest::instance() -> subs = $this -> getSubSection();

		$this -> imageSizesList = [
			'xlarge' => new pos(0, 0,1980, 1200),
			'large'  => new pos(0, 0,1600, 1024),
			'medium' => new pos(0, 0,1200, 1200),
			'small'  => new pos(0, 0, 800,  800),
			'thumb'  => new pos(0, 0, 500,  500),
		];
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
				$validationMsg =	CLanguage::get() -> string('ERR_PERMISSON');
				$responseData  = 	[];


				tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call
			}

			CMessages::add(CLanguage::get() -> string('ERR_PERMISSON') , MSG_WARNING);
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
			case 'xhr_import': 			$logicDone = $this -> logicXHRImport($_pDatabase, $_xhrInfo, $enableEdit, $enableDelete, $enableUpload);
			case 'xhr_directory_list': 	$logicDone = $this -> logicXHRDirectoryList();
			case 'xhr_directory_items': $logicDone = $this -> logicXHRDirectoryItems();
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


			$modelCondition = new CModelCondition();
			$modelCondition -> where('media_id', $mediaId);
			

			$modelMediathek = new modelMediathek;
			$modelMediathek	-> load($_pDatabase, $modelCondition);	

			$itemDBInfo = $modelMediathek -> getResult();

			$itemDBInfo = (!empty($itemDBInfo) ? reset($itemDBInfo) : null);



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

		return false;
	}


	/**
	 * 	XHR Call to import all new (unprocessed) files in mediathek directory
	 */
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


			$itemInfo -> sheme		   = 1;
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
				'media_mime' 		  => $item -> mime
			]);


			file_put_contents($itemPath. $mediaId .'.media-id', $mediaId);







		}





		



		tk::xhrResult(intval($validationErr), $validationMsg, $responseData);	// contains exit call

		return false;
	}


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

	
}
