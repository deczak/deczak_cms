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

			CMessages::instance() -> addMessage(CLanguage::get() -> string('ERR_PERMISSON') , MSG_WARNING);
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
			case 'xhr_index': 	$logicDone = $this -> logicXHRIndex($_pDatabase, $_xhrInfo, $enableEdit, $enableDelete, $enableUpload);
			case 'xhr_import': 	$logicDone = $this -> logicXHRImport($_pDatabase, $_xhrInfo, $enableEdit, $enableDelete, $enableUpload);
		}

		if(!$logicDone) // Default
			$logicDone = $this -> logicIndex($_pDatabase, $enableEdit, $enableDelete);	
	
		return $logicDone;
	}







	private function
	logicIndex(CDatabaseConnection &$_pDatabase, bool $_enableEdit = false, bool $_enableDelete = false, bool $_enableUpload = false) : bool
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

		return false;
	}

	private function
	logicXHRImport(CDatabaseConnection &$_pDatabase, ?object $_xhrInfo, bool $_enableEdit = false, bool $_enableDelete = false, bool $_enableUpload = false) : bool
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

		return false;
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
}
