<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelMediathek.php';	

class cmsUploadMediathek
{
	public function
	__construct()
	{
		//CMS_SERVER_ROOT
		//CMS_SERVER_URL
		//DIR_MEDIATHEK
	}

	public function
	process(string $destPath, bool $isRegularUpload = true, ?object $additionalParams = null) : ?object
	{	
		$imageSizesList = CFG::GET()->MEDIATHEK->IMAGES_SIZES;

		if($_FILES["file"]["error"] !== UPLOAD_ERR_OK)
			return null;

		$srcFilepath = $_FILES["file"]["tmp_name"];
		
		if(!file_exists($srcFilepath))
			return null;

		$dstFilename = basename($_FILES["file"]["name"]);

		$dstFilelocation = CMS_SERVER_ROOT . DIR_MEDIATHEK . $destPath;

		$fnpart 		= explode('.', $dstFilename);
		$fileextension 	= array_pop($fnpart);
		$basename 		= implode('.', $fnpart);

		if(!file_exists($dstFilelocation.$basename))
		if(!mkdir($dstFilelocation.$basename, 0777, true))
		{
			// TODO ERR
			return null;
		}

		if($isRegularUpload)
		{
			move_uploaded_file($srcFilepath, $dstFilelocation.$basename.'/'.$dstFilename);
		} 
		else
		{
			copy($srcFilepath, $dstFilelocation.$basename.'/'.$dstFilename);
		}

		$mediathekFileInfo 		= new SplFileInfo($dstFilelocation.$basename.'/'.$dstFilename);

		$processedItem = new stdClass;
		$processedItem->filename 		= $dstFilename;
		$processedItem->filelocation 	= $dstFilelocation.$basename.'/';
		$processedItem->mimetype	 	= $_FILES["file"]["name"];
		$processedItem->filesize 		= $mediathekFileInfo -> getSize();

		switch($_FILES["file"]["type"])
		{
			case 'image/png': 
			case 'image/webp': 
			case 'image/jpeg':

				$processedItem->rects = MEDIATHEK::createResizedImages($dstFilelocation.$basename.'/', $dstFilename, $_FILES["file"]["type"], $imageSizesList);
				break;

			default: 

		}

		switch($_FILES["file"]["type"])
		{
			case 'image/jpeg':

				$itemExifInfo	= (object)exif_read_data($dstFilelocation.$basename.'/'.$dstFilename, 'EXIF');
				break;
		}

		$itemInfo = new stdClass;

		$itemInfo -> scheme		   = 1;
		$itemInfo -> filename	   = $dstFilename;
		$itemInfo -> sizes		   = [];
		$itemInfo -> license	   = $additionalParams->media_item_license ?? ($itemExifInfo -> Copyright ?? '');
		$itemInfo -> license_url   = $additionalParams->media_item_licenseurl ?? '';
		$itemInfo -> gear		   = [
			"by_meta"	=> false,
			"camera"	=> $additionalParams->media_item_camera ?? ($itemExifInfo -> Model ?? ''),
			"lens"		=> $additionalParams->media_item_lens ?? ($itemExifInfo -> LensModel ?? ($itemExifInfo -> {'UndefinedTag:0xA434'} ?? ''))
		];
		$itemInfo -> gear_settings = [];	// This values are not getting retrieved at the moment
		$itemInfo -> title		   = $additionalParams->media_item_title ?? '';
		$itemInfo -> caption	   = $additionalParams->media_item_caption ?? '';
		$itemInfo -> author 	   = $additionalParams->media_item_author ?? ($itemExifInfo -> Artist ?? '');
		$itemInfo -> notice		   = '';
		$itemInfo -> timeAdd	   = time();

		switch($_FILES["file"]["type"])
		{
			case 'image/jpeg':

				$itemInfo -> sizes = $processedItem->rects ?? [];
	
				break;

			default: // ...................	Add file for download

				// todo
		}

		file_put_contents($dstFilelocation.$basename.'/'.'info.json', json_encode($itemInfo, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));			
	
		$mediaItem = modelMediathek::new();

		$mediaItem->media_filename 	  	= $itemInfo -> filename;
		$mediaItem->media_title 		= $itemInfo -> title;
		$mediaItem->media_caption	  	= $itemInfo -> caption;
		$mediaItem->media_author		= $itemInfo -> author;
		$mediaItem->media_notice	  	= $itemInfo -> notice;
		$mediaItem->media_license	  	= $itemInfo -> license;
		$mediaItem->media_license_url   = $itemInfo -> license_url;
		$mediaItem->media_gear	  		= (object)$itemInfo -> gear;
		$mediaItem->media_gear_settings = (object)$itemInfo -> gear_settings ?? [];
		$mediaItem->media_size	  		= $processedItem->filesize;
		$mediaItem->media_extension	  	= $fileextension;
		$mediaItem->media_mime	  		= $_FILES["file"]["type"];
		$mediaItem->create_by	  		= CSession::instance() -> getValue('user_id');
		$mediaItem->create_time		  	= time();

		$mediaItem->save();

		file_put_contents($dstFilelocation.$basename.'/'. $mediaItem->media_id .'.media-id', $mediaItem->media_id);
		
		return $processedItem;	
	}

}
