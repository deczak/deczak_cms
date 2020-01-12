<?php

##	delete all files in temp directory

	$dirIterator 	= new DirectoryIterator( CMS_SERVER_ROOT.DIR_TEMP );
	foreach($dirIterator as $dirItem)
	{
		if($dirItem -> isDot() || $dirItem -> getType() !== 'file')
			continue;

		unlink(CMS_SERVER_ROOT.DIR_TEMP.$dirItem -> getFilename());
	}


?>