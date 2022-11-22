<?php

require_once 'cmsUploadMediathek.php';

class cmsUpload
{
	const DEST_MEDIATHEK = 0x01;

	public function
	__construct()
	{
	}

	public function
	processUpload(int $destSection, string $destPath, bool $isRegularUpload = true, ?object $additionalParams = null) : ?object
	{
		$sectionProcessor = null;

		switch($destSection)
		{
			case cmsUpload::DEST_MEDIATHEK:

				$sectionProcessor = new cmsUploadMediathek;
				break;

			default:

				return null;
		}

		return $sectionProcessor->process($destPath, $isRegularUpload, $additionalParams);
	}

	public static function 
	validateFilename(string $fileLocation, string $fileName) : string
	{
		$fileName = tk::normalizeFilename($fileName);

		if(!file_exists($fileLocation.$fileName))
			return $fileName;

		if(strpos($fileName, '.') !== false)
		{
			$fnpart 		= explode('.', $fileName);
			$fileExtension 	= array_pop($fnpart);
			$basename 		= implode('.', $fnpart);
		}
		else
		{
			$basename 		= $fileName;
			$fileExtension 	= '';
		}

		if(strpos($fileName, '-') !== false)
		{
			$basename		= explode('-', $basename);
			$fileCounter	= array_pop($basename);
			$basename		= implode('-', $basename);

			if(ctype_digit($fileCounter))
				$fileCounter++;
			else
				$fileCounter = 2;

			return cmsUpload::validateFilename($fileLocation, $basename .'-'. $fileCounter.'.'.$fileExtension);
		}

		return cmsUpload::validateFilename($fileLocation, $basename .'-2.'.$fileExtension);
	}
}
