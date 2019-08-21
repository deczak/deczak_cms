<?php

/*
 
	This file contains different toolkit functions, as example using those with tk::<function> 

 */

class	TK
{
	public static function
	dbug($_data, bool $_returnAsString = false)
	{
		if($_returnAsString) return print_r($_data, true);
		echo '<pre style="border:1px dotted red; padding:10px; margin:10px;">';
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
						
		echo json_encode($_array, JSON_HEX_APOS | JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
		exit;
	}

	public static function
	normalizeFilename(string $_filename, bool $_replaceSlashes = false)
	{
		$search 	= array(" ", "Ä", "Ö", "Ü", "ä", "ö", "ü", "ß");
		$replace 	= array("-", "Ae", "Oe", "Ue", "ae", "oe", "ue", "ss");

 		$_filename 	= str_replace($search, $replace, strtolower($_filename));

		if($_replaceSlashes)
			$_filename = preg_replace( '/[^a-z0-9_\-]+/', '', $_filename);
		else
			$_filename = preg_replace( '/[^a-z0-9_-\/]+/', '', $_filename);
	
		return $_filename;
	}
	
	public static function
	getActiveServerProtocol()
	{
		if(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
			return 'https://';
		return 'http://';			 
	}
}

class	CRYPT
{
	private static function
	CRYPTKEY(string $_key, bool $_appendKey )
	{
		$_CryptKey	=	ENCRYPTION_BASEKEY;
		if( !empty($_key) AND !$_appendKey)
		{
			$_CryptKey	=	$_key;
		}
		else if(!empty($_key) AND $_appendKey)
		{
			$_CryptKey	= $_CryptKey . $_key;
		
		}
		return	hash( 'sha256', $_CryptKey );
	}

	private static function
	CRYPTVECTOR(string $_key, bool $_appendKey )
	{
		$_CryptKey		=	ENCRYPTION_BASEKEY;
		if( !empty($_key) AND !$_appendKey)
		{
			$_CryptKey	=	$_key;
		}
		else if(!empty($_key) AND $_appendKey)
		{
			$_CryptKey	= $_CryptKey . $_key;
		}
		return	substr( hash( 'sha256', $_CryptKey ), 0, 16 );
	}

	public static function
	ENCRYPT(string $_string, string $_key = '', bool $_appendKey = false )
	{	
		return	base64_encode( openssl_encrypt( $_string, ENCRYPTION_METHOD, CRYPT::CRYPTKEY($_key,$_appendKey), 0, CRYPT::CRYPTVECTOR($_key,$_appendKey) ) );	
	}

	public static function
	DECRYPT(string $_string, string $_key = '', bool $_appendKey = false )
	{
		return	openssl_decrypt( base64_decode( $_string ), ENCRYPTION_METHOD, CRYPT::CRYPTKEY($_key,$_appendKey), 0, CRYPT::CRYPTVECTOR($_key,$_appendKey) );		
	}	

	public static function
	CHECKSUM(string $_string)
	{
		$_checksum 		= 9999;
		$_stringSize	= strlen($_string);
		for($i = 0; $i < $_stringSize; $i++)
		{
			if(ctype_digit($_string[$i]))
			{
				$_checksum = $_checksum + $_string[$i];
			}
			else
			{
				$_checksum = $_checksum + ord($_string[$i]);
			}
		}
		return substr($_checksum,strlen($_checksum) - 4,4);
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

?>