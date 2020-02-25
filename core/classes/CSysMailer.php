<?php

require_once 'CSingleton.php';

class	CSysMailer extends CSingleton
{
	private		$initialized;

	private		$receiverAddress;
	private		$receiverName;
	private		$mailSubjectPrefix;
	private		$timeoutSpamProtection;


	private		$encryptCertFilename;
	private		$encryptEnabled;

	private		$signingCertFilename;
	private		$signingKeyFilename;
	private		$signingCertPass;
	private		$signingEnabled;

	public function
	__construct()
	{}

	public function
	initialize()
	{		
		if(	   
				strlen(CFG::GET() -> SYSTEM_MAILER -> RECEIVER_ADDRESS) == 0
			||	strlen(CFG::GET() -> SYSTEM_MAILER -> RECEIVER_NAME) == 0
		  )	
		{
			$this -> initialized			= false;
			return false;
		}

		$this -> receiverAddress			= CFG::GET() -> SYSTEM_MAILER -> RECEIVER_ADDRESS;
		$this -> receiverName				= CFG::GET() -> SYSTEM_MAILER -> RECEIVER_NAME;
		$this -> mailSubjectPrefix			= CFG::GET() -> SYSTEM_MAILER -> SUBJECT_PREFIX;
		$this -> timeoutSpamProtection		= CFG::GET() -> SYSTEM_MAILER -> LOCK_TIMEOUT;
		$this -> initialized				= true;

		##	Mail signation

		$this -> signingEnabled = false;

		if(	   
				strlen(CFG::GET() -> SYSTEM_MAILER -> SMIME_CERT_SIGN_FILE) != 0
			&&	strlen(CFG::GET() -> SYSTEM_MAILER -> SMIME_PRIVKEY_FILE)   != 0
			&&	strlen(CFG::GET() -> SYSTEM_MAILER -> SMIME_PRIVKEY_PASS)   != 0
			&&	file_exists(CMS_SERVER_ROOT.DIR_CERT.CFG::GET() -> SYSTEM_MAILER -> SMIME_CERT_SIGN_FILE)
			&&	file_exists(CMS_SERVER_ROOT.DIR_CERT.CFG::GET() -> SYSTEM_MAILER -> SMIME_PRIVKEY_FILE)
		  )	
		{
			$this -> signingCertFilename 	= CFG::GET() -> SYSTEM_MAILER -> SMIME_CERT_SIGN_FILE;
			$this -> signingKeyFilename 	= CFG::GET() -> SYSTEM_MAILER -> SMIME_PRIVKEY_FILE;
			$this -> signingCertPass 		= CFG::GET() -> SYSTEM_MAILER -> SMIME_PRIVKEY_PASS;
			$this -> signingEnabled 		= true;
		}	

		##	Mail encryption

		$this -> encryptEnabled = false;
		
		if(	   
				strlen(CFG::GET() -> SYSTEM_MAILER -> SMIME_CERT_CRYPT_FILE) != 0
			&&	file_exists(CMS_SERVER_ROOT.DIR_CERT.CFG::GET() -> SYSTEM_MAILER -> SMIME_CERT_CRYPT_FILE)
		  )	
		{
			$this -> encryptCertFilename 	= CFG::GET() -> SYSTEM_MAILER -> SMIME_CERT_CRYPT_FILE;
			$this -> encryptEnabled 		= true;
		}

		return true;
	}

	public function
	sendMail(string $_subject, string $_message, bool $_spamProtection = false, $_SpamProtectionKey = '') : bool
	{

		if(!$this -> initialized || empty($_message)) return false;

		## Spam protection

		if($_spamProtection)
		{
			$_destDirectoryPath		= CMS_SERVER_ROOT . DIR_TEMP;
			$_destLockFilepath		= $_destDirectoryPath .'sys-mailer-lock-'. $_SpamProtectionKey;

			if(!is_dir($_destDirectoryPath))
			{
				if(mkdir($_destDirectoryPath) === false)
				{
					trigger_error("CSysMailer::sendMail -- Call of mkdir fails on initialization",E_USER_WARNING);
					exit;
				}
			}

			$_timeStamp		= time();

			if(file_exists($_destLockFilepath))
			{
				$_fileContent 	= file_get_contents($_destLockFilepath);		

				if(isset($_fileContent) && ($_fileContent + $this -> timeoutSpamProtection) > $_timeStamp)
				{
					// Timeout not reached, exit function
					return false;
				}
			}

			$_hFile = fopen($_destLockFilepath, "w");	

			fwrite($_hFile, $_timeStamp);
			fclose($_hFile);
		}

		## Mail headers

		$baseHeader 			= [];
		$baseHeader['To'] 		= $this -> receiverName ." <". $this -> receiverAddress . ">";
		$baseHeader['From'] 	= $this -> receiverName ." <". $this -> receiverAddress . ">";
		$baseHeader['Subject'] 	= $this -> mailSubjectPrefix . $_subject;

		$additionalHeader 		= [];
		$additionalHeader[] 	= "From: ". $this -> receiverName ." <". $this -> receiverAddress . ">";
		$additionalHeader[] 	= "To: ". $this -> receiverAddress;
		$additionalHeader[] 	= "Subject: ". $_subject;
		$additionalHeader[] 	= "Content-Type: text/plain; format=flowed; charset=\"utf-8\"; reply-type=original";
		$additionalHeader[] 	= "Content-Transfer-Encoding: 8bit";
		$additionalHeader 		= implode("\r\n", $additionalHeader)."\n\n";

		## Mail signing

		if($this -> signingEnabled)
		{
			$tempFilepath 	= tempnam(CMS_SERVER_ROOT.DIR_TEMP, $_SpamProtectionKey);

			file_put_contents($tempFilepath, $additionalHeader.$_message);

			if(!openssl_pkcs7_sign(	$tempFilepath, 
									$tempFilepath .'signed', 
									'file://'.realpath(CMS_SERVER_ROOT.DIR_CERT.$this -> signingCertFilename),
									array('file://'.realpath(CMS_SERVER_ROOT.DIR_CERT.$this -> signingKeyFilename), $this -> signingCertPass),
									$baseHeader)
			  )
			  	return false;

			$_message = file_get_contents($tempFilepath .'signed');

			$additionalHeader = explode("\n\n", $_message, 2)[0];
		}

		## Mail encryption

		if($this -> encryptEnabled)
		{
			if(!isset($tempFilepath))
			{
				$tempFilepath = tempnam(CMS_SERVER_ROOT.DIR_TEMP, $_SpamProtectionKey);
			}

			file_put_contents($tempFilepath, $additionalHeader.$_message);

			$encryptCert = file_get_contents(CMS_SERVER_ROOT.DIR_CERT.$this -> encryptCertFilename);

			$messageHeader = $baseHeader;
			unset($messageHeader['To'], $messageHeader['Subject']);

			if(!openssl_pkcs7_encrypt(	$tempFilepath, 
										$tempFilepath .'crypted',
										$encryptCert,
										$messageHeader,
										0,
										1)
			)
				return false;

			$_message 	= file_get_contents($tempFilepath .'crypted');
			$_message 	= explode("\n\n", $_message, 2);

			$additionalHeader = $_message[0];
			$_message = $_message[1];
		}

		mail($baseHeader["To"], $baseHeader["Subject"], $_message, $additionalHeader);

		if(isset($tempFilepath))
		{
			@unlink($tempFilepath);
			@unlink($tempFilepath .'crypted');
			@unlink($tempFilepath .'signed');
		}		

		return true;
	}
}
?>