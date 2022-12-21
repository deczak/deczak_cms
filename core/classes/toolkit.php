<?php

require_once 'type_pos.php';

class	TK
{
	public static function
	dbug($_data, bool $_returnAsString = false)
	{
		if($_returnAsString) return print_r($_data, true);
		echo '<pre style="border:1px dotted red; padding:10px; margin:10px; tab-size:4; -moz-tab-size:4;">';
		print_r($_data);
		echo '</pre>';
	}

	public static function
	xhrResult(int $_state, string $_msg, array $_data = [])
	{
		$_array		=	[
						"state"	=>	$_state,
						"msg"	=>	$_msg,
						"data"	=>	$_data
						];
		
		header("Permissions-Policy: interest-cohort=()");	
		header('Content-type:application/json');
						
		echo json_encode($_array, JSON_HEX_APOS | JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
		exit;
	}

	public static function
	xhrResponse(int $responseCode, array|object $data, int $error = 0, string $msg = 'OK')
	{
		header("Permissions-Policy: interest-cohort=()");	
		header('Content-type:application/json; charset=utf-8');
						
		$jsonResponse = json_encode([
			'error' => $error,
			'msg'   => $msg,
			'data' 	=> $data,
		], JSON_HEX_APOS | JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

		if($jsonResponse === false)
			http_response_code(500);

		echo $jsonResponse;

		http_response_code($responseCode);
		exit;
	}

	public static function
	normalizeFilename(string $_filename, bool $_replaceSlashes = false)
	{
		$search 	= array(" ", "Ä", "Ö", "Ü", "ä", "ö", "ü", "ß");
		$replace 	= array("-", "Ae", "Oe", "Ue", "ae", "oe", "ue", "ss");

 		$_filename 	= str_replace($search, $replace, strtolower($_filename));
		$_filename = preg_replace( '/[^a-z0-9_.-]+/', '', $_filename);
	
		return $_filename;
	}
	
	public static function
	getActiveServerProtocol()
	{
		if(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
			return 'https://';
		return 'http://';			 
	}

	public static function
	strip_breaks_n_tags(string $string)
	{
		$string = str_replace(array("\r", "\n"), '', $string);
		return strip_tags($string);
	}

	public static function
	getBackendUserName(CDatabaseConnection &$_pDatabase, string $_userId)
	{

		require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUsersBackend.php';
		require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelUsersRegister.php';	

		$registerCondition	 = new CModelCondition();
		$registerCondition 	-> where('user_id', $_userId);
		$registerCondition 	-> groupBy('user_id');
		$modelUsersRegister	 = new modelUsersRegister();
		$modelUsersRegister -> load($_pDatabase, $registerCondition);

		if(empty($modelUsersRegister -> getResult()))
			return '';

		$registerData = $modelUsersRegister -> getResult()[0];

		switch($registerData -> user_type)
		{
			case 0:	// Backend user

					$backendCondition	 = new CModelCondition();
					$backendCondition 	-> where('user_id', strval($_userId));
					$modelUsersBackend 	 = new modelUsersBackend();
					$modelUsersBackend 	-> load($_pDatabase, $backendCondition);

					if(empty($modelUsersBackend -> getResult()))
						return '';

					$user = $modelUsersBackend -> getResult()[0];
					return $user -> user_name_first .' '. $user -> user_name_last;

			case 3: // Remote user

					return $registerData -> user_name;
		}

		return '';
	}

	public static function
	getValueFromArrayByValueI(array $_srcArray, $_searchKey, $_searchValue, $_returnKey, $_returnDefault)
	{
		foreach($_srcArray as $dataset)
			if(isset($dataset -> $_searchKey) && isset($dataset -> $_returnKey) && stripos($_searchValue, $dataset -> $_searchKey) !== false)
				return $dataset -> $_returnKey;
		return $_returnDefault;
	}

	public static function
	isSSL()
	{
		if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' )
			return true;
		return false;
	}

	public static function
	matchRemoteAddr(string $_ip, string $_range) : bool
	{
		if(strpos($_range, '/') !== false)
		{
			## IPv4 & IPv6 with CIDR
			list($net, $maskBits) = explode('/', $_range);

			$size = (strpos($_ip, ':') === false) ? 4 : 16;

			$ip = inet_pton($_ip);
			$net = inet_pton($net);
			if (!$ip || !$net)
				return false;

			$solid = floor($maskBits / 8);
			$solidBits = $solid * 8;
			$mask = str_repeat(chr(255), $solid);
			for ($i = $solidBits; $i < $maskBits; $i += 8) {
			$bits = max(0, min(8, $maskBits - $i));
			$mask .= chr((pow(2, $bits) - 1) << (8 - $bits));
			}
			$mask = str_pad($mask, $size, chr(0));

			return ($ip & $mask) === ($net & $mask);
		}
		else
		{
			## IPv4 only 

			if(strpos($_range, '*') !==false)
			{	
				$lower 	= str_replace('*', '0', $_range);
				$upper 	= str_replace('*', '255', $_range);
				$_range = "$lower-$upper";
			}
			
			if(strpos($_range, '-')!==false)
			{ 
				list($lower, $upper) = explode('-', $_range, 2);

				$lower_dec 	= floatval(sprintf("%u",ip2long($lower)));
				$upper_dec 	= floatval(sprintf("%u",ip2long($upper)));
				$ip_dec 	= floatval(sprintf("%u",ip2long($_ip)));

				return ( ($ip_dec >= $lower_dec) && ($ip_dec <= $upper_dec) );
			}
			else
			{
				return ($_ip == $_range);
			}
		}

		return false;
	}

	public static function
	put($string, $append = false)
	{
		static $buffer;

		if(!$append)
			echo '<br>';

		$lineLength = 125;

		if($append)
		{
			echo str_pad(' '. $string, $lineLength - strlen($buffer), '.', STR_PAD_LEFT);
		}
		else
		{
			$buffer = $string;
			echo $string .' ';
		}
	}

	public static function
	getNodeFromSitemap(array &$_sitemap, int $_requestedNodeId)
	{
		foreach($_sitemap as $node)
		{
			if($node -> node_id == $_requestedNodeId)
				return $node;
		}
		return null;
	}

	public static function
	getRandomId($length = 40)
	{
    	return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ.:_-$&/?=!@+*#', ceil($length/strlen($x)) )), 1, $length);
	}

	public static function
	rrmdir($dir, bool $deleteInitDir = true)
	{ 
		if(is_dir($dir))
		{
			$objectList = scandir($dir);
			foreach($objectList as $object)
			{
				if($object != "." && $object != "..")
				{ 

					if(is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
						tk::rrmdir($dir. DIRECTORY_SEPARATOR .$object);
					else
						@unlink($dir. DIRECTORY_SEPARATOR .$object); 
				}
			}
			if($deleteInitDir)
				@rmdir($dir); 
		} 
	}

	public static function
	rmkdir(string $prefixPath, string $relativePath) : ?string
	{
		$prefixPath = '/'.trim($prefixPath,' /\\').'/';

		$relativePath = trim($relativePath,' /\\');
		$relativePath = str_replace('\\', '/', $relativePath);
		$relativePathSgmts = explode('/', $relativePath);

		$relativePath = '';

		foreach($relativePathSgmts as $sgmt)
		{
			$relativePath .= $sgmt.'/';

			if(!file_exists($prefixPath.$relativePath))
			{
				if(!mkdir($prefixPath.$relativePath, 0777, true))
					return null;
				chmod($prefixPath.$relativePath, 0777);
			}
		}

		return $relativePath;
	}

	public static function
	object_merge(object &$dstObject, object $srcObject)
	{
		foreach($srcObject as $prop => $value)
		{
			if(!is_object($value))
			{
				$dstObject -> $prop = $value;
				continue;
			}
			else
			{
				tk::object_merge($dstObject -> $prop, $value);
			}
		}
	}
}

class	CRYPT
{
	private static function
	CRYPTKEY(string $_key, bool $_appendKey )
	{
		$cryptKey	=	CFG::GET() -> ENCRYPTION -> BASEKEY;
		if(!empty($_key) AND !$_appendKey)
		{
			$cryptKey	=	$_key;
		}
		elseif(!empty($_key) AND $_appendKey)
		{
			$cryptKey	= $cryptKey . $_key;
		}
		return $cryptKey;
	}

	private static function
	CRYPTVECTOR(string $_key, bool $_appendKey )
	{
		$cryptKey		=	CFG::GET() -> ENCRYPTION -> BASEKEY;
		if( !empty($_key) AND !$_appendKey)
		{
			$cryptKey	=	$_key;
		}
		else if(!empty($_key) AND $_appendKey)
		{
			$cryptKey	= $cryptKey . $_key;
		}
		return	substr( hash( 'sha256', $cryptKey ), 0, 16 );
	}

	public static function
	ENCRYPT(string $_string, string $_key = '', bool $_appendKey = false )
	{	
		return	base64_encode( openssl_encrypt( $_string, CFG::GET() -> ENCRYPTION -> METHOD, CRYPT::CRYPTKEY($_key,$_appendKey), 0, CRYPT::CRYPTVECTOR($_key,$_appendKey) ) );	
	}

	public static function
	DECRYPT(string $_string, string $_key = '', bool $_appendKey = false )
	{
		return	openssl_decrypt( base64_decode( $_string ), CFG::GET() -> ENCRYPTION -> METHOD, CRYPT::CRYPTKEY($_key,$_appendKey), 0, CRYPT::CRYPTVECTOR($_key,$_appendKey) );		
	}	

	public static function
	CHECKSUM(string $_string)
	{
		$checksum 		= 9999;
		$stringSize	= strlen($_string);
		for($i = 0; $i < $stringSize; $i++)
		{
			if(ctype_digit($_string[$i]))
			{
				$checksum = $checksum + $_string[$i];
			}
			else
			{
				$checksum = $checksum + ord($_string[$i]);
			}
		}
		return substr($checksum,strlen($checksum) - 4,4);
	}

	public static function
	HASH256(string $_string, $_salt = NULL, $_pepper = NULL)
	{
		return CRYPT::HASH('sha256', $_string, $_salt, $_pepper);
	}

	public static function
	HASH512(string $_string, $_salt = NULL, $_pepper = NULL)
	{
		return CRYPT::HASH('sha512', $_string, $_salt, $_pepper);
	}

	public static function
	HASH(string $_algo, string $_string, $_salt, $_pepper)
	{
		return hash($_algo, $_string . (!empty($_salt) ? $_salt : '') . (!empty($_pepper) ? $_pepper : ''));
	}
	
	public static function
	LOGIN_HASH(string $_string)
	{
		return CRYPT::ENCRYPT($_string, CRYPT::CHECKSUM($_string), true);
	}
	
	public static function
	LOGIN_CRYPT(string $_string, string $_key)
	{
		return CRYPT::HASH512($_string, CRYPT::CHECKSUM(CRYPT::HASH256($_string)), $_key ) . CRYPT::CHECKSUM(CRYPT::HASH256($_string));
	}
}

class 	MEDIATHEK
{
	public static function 
	getItemUrl(int $mediaId, string $itemPath = '') : ?string
	{
		if(empty($mediaId))
			return null;

		$directoryList = new DirectoryIterator(CMS_SERVER_ROOT.DIR_MEDIATHEK.$itemPath);
		foreach($directoryList as $directory)
		{
			if($directory -> isDot())
				continue;

			if(file_exists(CMS_SERVER_ROOT.DIR_MEDIATHEK.$itemPath.$directory -> getFilename().'/'.$mediaId.'.media-id'))
				return CMS_SERVER_URL.DIR_MEDIATHEK.$itemPath.$directory -> getFilename().'/';

			if($directory -> isDir() && !file_exists(CMS_SERVER_ROOT.DIR_MEDIATHEK.$itemPath.$directory -> getFilename().'/info.json'))
			{
				$response = MEDIATHEK::getItemUrl($mediaId, $itemPath .$directory -> getFilename().'/');
				if($response !== null)
					return $response;
			}
		}

		return null;
	}

	public static function 
	getItemFSPath(int $mediaId, string $itemPath = '') : ?string
	{
		if(empty($mediaId))
			return null;

		$directoryList = new DirectoryIterator(CMS_SERVER_ROOT.DIR_MEDIATHEK.$itemPath);
		foreach($directoryList as $directory)
		{
			if($directory -> isDot())
				continue;

			if(file_exists(CMS_SERVER_ROOT.DIR_MEDIATHEK.$itemPath.$directory -> getFilename().'/'.$mediaId.'.media-id'))
				return CMS_SERVER_ROOT.DIR_MEDIATHEK.$itemPath.$directory -> getFilename().'/';

			if($directory -> isDir() && !file_exists(CMS_SERVER_ROOT.DIR_MEDIATHEK.$itemPath.$directory -> getFilename().'/info.json'))
			{
				$response = MEDIATHEK::getItemFSPath($mediaId, $itemPath .$directory -> getFilename().'/');
				if($response !== null)
					return $response;
			}
		}

		return null;
	}
  
	public static function 
	getItem(string $path, array &$destList)
	{


			if(file_exists(CMS_SERVER_ROOT.DIR_MEDIATHEK.$path.'/info.json'))
			{
				$itemInfo = file_get_contents(CMS_SERVER_ROOT.DIR_MEDIATHEK.$path.'/info.json');

				if($itemInfo === false)
				{
					// TODO ERR
					return;
				}

				$itemInfo = json_decode($itemInfo);

				if($itemInfo === null)
				{
					// TODO ERR
					return;
				}

				if(!empty($itemInfo -> redirect))
				{
					return;
				}

				$pathSegments = explode('/', $path);
					
				$mediathekFilelocation 	= CMS_SERVER_ROOT.DIR_MEDIATHEK.$path.'/'.$itemInfo -> filename;
				$mediathekFileInfo 		= new SplFileInfo($mediathekFilelocation);

				$mediathekItem  = new stdClass;
				$mediathekItem -> path  	= $path;
				$mediathekItem -> filename  	= end($pathSegments);
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


				switch($mediathekItem -> mime )
				{
					case 'image/jpeg':
					case 'image/png':
					case 'image/webp':

						$mediathekItem -> props  = getimagesize(CMS_SERVER_ROOT.DIR_MEDIATHEK.$path.'/'.$itemInfo -> filename);
						$mediathekItem -> orient = (($mediathekItem -> props[0] / $mediathekItem -> props[1]) > 1 ? 0 : 1);
				}


				$destList[] = $mediathekItem;
			}



	}

	public static function 
	getItemsList(string $path, array &$destList, bool $ignoreSubDirectory = false)
	{
		$path = ($path[ strlen($path) - 1 ] !== '/' ? $path.'/' : $path);

		if(!file_exists(CMS_SERVER_ROOT.DIR_MEDIATHEK.$path))
			return;

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


				switch($mediathekItem -> mime )
				{
					case 'image/jpeg':
					case 'image/png':
					case 'image/webp':

						$mediathekItem -> props  = getimagesize(CMS_SERVER_ROOT.DIR_MEDIATHEK.$path.$directory -> getFilename().'/'.$itemInfo -> filename);
						$mediathekItem -> orient = (($mediathekItem -> props[0] / $mediathekItem -> props[1]) > 1 ? 0 : 1);
				}


				$destList[] = $mediathekItem;
			}
			else
			{
				if(!$ignoreSubDirectory)
					MEDIATHEK::getItemsList($path.$directory -> getFilename().'/', $destList);
			}
		}
	}

	/**
	 *	Searches for unprocessed Mediethak Files
	 *	@param array $destList Reference of an Array where the found will be written as object
	 *	@param string $path Relative mediathek start path of the directory being searched in. An Empty String indicates a search in the whole mediathek.
	 */	
	public static function 
	getRawItemsList(array &$destList, string $path = '') : void
	{
		$directoryList = new DirectoryIterator(CMS_SERVER_ROOT.DIR_MEDIATHEK.$path);
		foreach($directoryList as $directory)
		{
			if($directory -> isDot())
				continue;

			if($directory -> isDir())
			{
				if(file_exists(CMS_SERVER_ROOT.DIR_MEDIATHEK.$path.$directory -> getFilename().'/info.json'))
				{
					continue;
				}
				else
				{
					MEDIATHEK::getRawItemsList($destList, $path.$directory -> getFilename().'/');
				}
			}
			elseif($directory -> isFile())
			{
				$mediathekFilelocation 	= CMS_SERVER_ROOT.DIR_MEDIATHEK.$path.$directory -> getFilename();
				$mediathekFileInfo 		= new SplFileInfo($mediathekFilelocation);

				$mediathekItem  = new stdClass;
				$mediathekItem -> filelocation	= $path;
				$mediathekItem -> filepath  	= $path.$directory -> getFilename();
				$mediathekItem -> filename  	= $directory -> getFilename();
				$mediathekItem -> filenameBase  = $mediathekFileInfo -> getBasename('.'. $mediathekFileInfo -> getExtension());
				$mediathekItem -> extension 	= strtolower($mediathekFileInfo -> getExtension());
				$mediathekItem -> mime 			= mime_content_type($mediathekFilelocation);
				$mediathekItem -> size 			= $mediathekFileInfo -> getSize();

				if($path === '')
				switch($mediathekItem -> filenameBase)
				{
					case 'index':
					case 'readme':	continue 2;
				}

				$destList[] = $mediathekItem;
			}
		}
	}
	
	public static function 
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

	/**
	 * 	This Function collects all media-id from defined path and returns them in an array
	 * 
	 *	@param string $path Defined relative mediathek path
	 * 	@return array List of found media-id in that path
	 */
	public static function
	getMediaIdsFromPath(string $path) : array
	{

		$mediaIdList = [];
		$itemsList = [];


		MEDIATHEK::getItemsList($path, $itemsList);

		foreach($itemsList as $item)
		{

			$itemLocation = new DirectoryIterator(CMS_SERVER_ROOT.DIR_MEDIATHEK.$item -> path);
			foreach($itemLocation as $itemFiles)
			{
				if(!$itemFiles -> isFile())
					continue;

				if($itemFiles -> getExtension() === 'media-id')
				{
					$mediaIdList[] =  $itemFiles -> getBasename('.media-id');
					break;
				}
			}
		}

		return $mediaIdList;
	}

	public static function
	createResizedImages(string $fileLocation, string $fileName, string $mime, array $supportedSizes) : ?array
	{
		$resizedImageList = [];

		switch($mime)
		{
			case 'image/jpeg':

    			$imageResource = imagecreatefromjpeg($fileLocation . $fileName);
				break;

			case 'image/png':

    			$imageResource = imagecreatefrompng($fileLocation . $fileName);
				break;

			case 'image/webp':

    			$imageResource = imagecreatefromwebp($fileLocation . $fileName);
				break;

			default:

				return null;
		}

		$srcSize = new pos(0, 0, imagesx($imageResource), imagesy($imageResource));

		foreach($supportedSizes as $sizeInfo)
		{
			if($sizeInfo -> w > $srcSize -> w || $sizeInfo -> h > $srcSize -> h)
				continue;

			$ratio = $srcSize -> w / $srcSize -> h;
			$pch   = $sizeInfo -> w / $ratio;  // 832

			if($pch > $sizeInfo -> h)
			{
				$pcw      = $sizeInfo -> h * $ratio;
				$destSize = new pos(0, 0, $pcw, $sizeInfo -> h);
			}
			else
			{
				$destSize = new pos(0, 0, $sizeInfo -> w, $pch);
			}

			$resizedResource = imagecreatetruecolor($destSize -> w, $destSize -> h);
			imagecopyresampled($resizedResource, $imageResource, 0, 0, 0, 0, $destSize -> w, $destSize -> h, $srcSize -> w, $srcSize -> h);

			$destFilename = $sizeInfo->name .'_'. $fileName;

			switch($mime)
			{
				case 'image/jpeg':

					imagejpeg($resizedResource, $fileLocation. $destFilename, 85); 
					break;

				case 'image/png':

					imagepng($resizedResource, $fileLocation. $destFilename, 5); 
					break;

				case 'image/webp':

					imagewebp($resizedResource, $fileLocation. $destFilename, 95); 
					break;

				default:

					continue 2;
			}

			$resizedImageList[$sizeInfo->name] = $destFilename;
		}

		return $resizedImageList;
	}

	/**
	 * 	Create multidimensional array with directory names from the mediethek directory
	 * 	@param string $path Initial path to start, empty string means the whole mediathek
	 * 	@param array $destList A reference to an Array where the data will be written
	 * 	@param int $level Integer that indicates the Level of the current element. It is independent from the real level, choice your own start value for your usage.
 	 */
	public static function
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
			$dirItem -> level  = $level; 
			$dirItem -> name   = $directory -> getFilename();
			$dirItem -> path   = $path.$directory -> getFilename();
			$dirItem -> childs = [];
			$destList[] = $dirItem;
			$found++;
			$dirItem -> ts = MEDIATHEK::getMediathekStructure($path.$directory -> getFilename().'/', $dirItem -> childs, $level + 1);
			$found = $found + $dirItem -> ts;
		}
		return $found;
	}
	
	/**
	 * 	Create one-level array from getMediathekStructure-array and append left/right informationen
	 * 	@param array $structureList The Array from MEDIATHEK::getMediathekStructure call
	 * 	@param array $destList A reference to an Array where the data will be written
	 * 	@param int $left A reference to an Integer of left nested item, intial value should be 1 
	 * 	@param int $right A reference to an Integer of right nested item, intial value should be 2 
	 */
	public static function
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
				MEDIATHEK::getNestedSetStructure($item -> childs, $destList, $left, $right);
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

	/**
	 */
	public static function
	createPath(string $path) : ?string
	{
		return tk::rmkdir(CMS_SERVER_ROOT . DIR_MEDIATHEK, $path);
	}

	/**
	 *	Deletes Mediathek content
	 */
	public static function
	deleteAll()
	{
		$directoryList = new DirectoryIterator(CMS_SERVER_ROOT . DIR_MEDIATHEK);
		foreach($directoryList as $directory)
		{
			if($directory -> isDot())
				continue;
			if($directory -> isDir())
			{
				tk::rrmdir($directory->getPathname());
			}
			/*
			elseif($directory -> isFile())
			{
				switch($directory->getFilename())
				{
					case 'readme':
					case 'index.php':
						continue 2;
				}
			}
			*/
		}
	}

}

/**
 *	Round function for bcmath results 
 */
function bcround($n, $p = 0)
{
    $e = bcpow(10, $p + 1);
    return bcdiv(bcadd(bcmul($n, $e, 0), (strpos($n, '-') === 0 ? -5 : 5)), $e, $p);
}
