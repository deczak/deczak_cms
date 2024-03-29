<?php

require_once 'CSingleton.php';

class	CCookie extends CSingleton
{
	private		$m_bRequestHTTPSCookie;

	public function
	initialize() : void
	{
		$this -> m_bRequestHTTPSCookie = CFG::GET() -> LOGIN -> COOKIE_HTTPS;
	}

	public function
	getCookie(string $_cookieName) : ?string
	{
		if(!isset($_COOKIE[$_cookieName])) return null;
		return strip_tags($_COOKIE[$_cookieName]);
	}

	public function
	getCookieID(string $_cookieName) : ?string
	{
		if(!isset($_COOKIE[$_cookieName])) return null;
		if(strlen($_COOKIE[$_cookieName]) != 324) return null;
		return substr($_COOKIE[$_cookieName], 128,96);
	}

	public function
	createCookie(string $_cookieName, string $_cookieContent, int $_cookieExpire = 0)
	{
		setcookie($_cookieName, $_cookieContent, $_cookieExpire, "/", "", ($this -> m_bRequestHTTPSCookie === NULL ? false : $this -> m_bRequestHTTPSCookie) , true);	
	}

	public function
	createCookieIdinator(string $_cookieName, int $_timestamp, string $_sessionID) : string
	{
		$cookieID			= $this -> createCookieID($_timestamp);
		$cookieContent		= $this -> createCookieContent($_timestamp, $_sessionID, $cookieID);	// 320
		$cookieChecksum	= CRYPT::CHECKSUM($cookieContent); 									//   4 
		setcookie($_cookieName, $cookieContent . $cookieChecksum, 0, "/", "", ($this -> m_bRequestHTTPSCookie === NULL ? false : $this -> m_bRequestHTTPSCookie) , true);	
		return $cookieID;
	}

	private function
	createCookieID(int $_timestamp) : string
	{
		return md5($_timestamp . $_SERVER['REMOTE_ADDR']) . md5($_timestamp . $_SERVER['HTTP_USER_AGENT']) . md5($_timestamp);
	}

	private function
	createCookieContent(int $_timestamp, string $_sessionID, string $_cookieID) : string
	{
		##	If this get modifed, getCookieID() must be also edited // MD5 = 32 // SHA256 = 64

		//     0                                          64                                         128          224               256                            288                                         320
		//     64 ->                                      64 ->                                       96 ->        32 ->             32 ->                          32 ->
		return hash('sha256',substr($_sessionID, 0,32)) . hash('sha256',substr($_sessionID, 32,32)) . $_cookieID . md5($_timestamp) . md5($_SERVER['REMOTE_ADDR']) . md5($_timestamp . $_SERVER['HTTP_USER_AGENT']);
	}

	public function
	isCookieIdinatorValid(string $_cookieName, int $_timestamp, string $_sessionID) : bool
	{
		if(!isset($_COOKIE[$_cookieName])) return false;
		if(strlen($_COOKIE[$_cookieName]) != 324) return false;
		
		$cookieID			= $this -> createCookieID($_timestamp);
		$cookieContent		= $this -> createCookieContent($_timestamp, $_sessionID, $cookieID);	// 320
		$cookieChecksum	= CRYPT::CHECKSUM($cookieContent); 									//   4 

		if($_COOKIE[$_cookieName] === ($cookieContent . $cookieChecksum)) return true;
		return false;
	}

	public function
	deleteCookie(string $_cookieName) : void
	{
		setcookie($_cookieName, NULL, 1, "/", "", ($this -> m_bRequestHTTPSCookie === NULL ? false : $this -> m_bRequestHTTPSCookie) , true);	
	}
}
