<?php

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
	normalizeFilename(string $_filename, bool $_replaceSlashes = false)
	{
		$search 	= array(" ", "Ä", "Ö", "Ü", "ä", "ö", "ü", "ß");
		$replace 	= array("-", "Ae", "Oe", "Ue", "ae", "oe", "ue", "ss");

 		$_filename 	= str_replace($search, $replace, strtolower($_filename));
		$_filename = preg_replace( '/[^a-z0-9_\-]+/', '', $_filename);
	
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
	rrmdir($dir)
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
						unlink($dir. DIRECTORY_SEPARATOR .$object); 
				}
			}
			rmdir($dir); 
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

/**
 *	Round function for bcmath results 
 */
function bcround($n, $p = 0)
{
    $e = bcpow(10, $p + 1);
    return bcdiv(bcadd(bcmul($n, $e, 0), (strpos($n, '-') === 0 ? -5 : 5)), $e, $p);
}
		
?>