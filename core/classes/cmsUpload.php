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
	processUpload(int $destSection, string $destPath, bool $isRegularUpload = true) : ?object
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

		return $sectionProcessor->process($destPath, $isRegularUpload);
	}
}
