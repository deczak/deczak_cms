<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SCHEME.'schemeSimple.php';	
include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SCHEME.'schemeBackendSimple.php';	

class	modelSimple extends cmsModel
{
	public static string $schemeName = 'schemeSimple';
}

class	modelBackendSimple extends cmsModel
{
	public static string $schemeName = 'schemeBackendSimple';
}
