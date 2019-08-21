<?php

require_once 'CSingleton.php';

class	CSysMailer extends CSingleton
{
	private		$m_DestReceiverAddress;
	private		$m_DestReceiverName;
	private		$m_SubjectPrefix;
	private		$m_bInitialized;
	private		$m_iSpamProtectionTimeout;

	public function
	initialize(array $_receiverData, string $_subjectPrefix = '')
	{		
		if(	   !is_array($_receiverData) 
			||	empty($_receiverData['name'])
			||	empty($_receiverData['mail'])
		  )	
		{
			$this -> m_bInitialized		= false;
			trigger_error("CSysMailer::__construct -- Initialization failed",E_USER_WARNING);
		}

		$this -> m_DestReceiverAddress		= $_receiverData['mail'];
		$this -> m_DestReceiverName			= $_receiverData['name'];
		$this -> m_SubjectPrefix			= $_subjectPrefix;
		$this -> m_iSpamProtectionTimeout	= 60 * 60;
		$this -> m_bInitialized				= true;
		return true;
	}

	public function
	sendMail(string $_subject, string $_message, bool $_bSpamProtection = false, $_SpamProtectionKey = '') : bool
	{
		if(!$this -> m_bInitialized || empty($_message)) return false;

		if($_bSpamProtection)
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

				if(isset($_fileContent) && ($_fileContent + $this -> m_iSpamProtectionTimeout) > $_timeStamp)
				{
					// Timeout not reached, exit function
					return false;
				}
			}

			$_hFile = fopen($_destLockFilepath, "w");	
			fwrite($_hFile, $_timeStamp);
			fclose($_hFile);
		}

		$_MailReceivers 	= $this -> m_DestReceiverName ." <". $this -> m_DestReceiverAddress .">";
		
		$_aMailHeaders		= [];
		$_aMailHeaders[]	= "Content-type: text/plain; charset=UTF-8";
		$_aMailHeaders[]	= "Return-Path: ". $this -> m_DestReceiverName ." <". $this -> m_DestReceiverAddress . ">";
		$_aMailHeaders[]	= "From: ". $this -> m_DestReceiverName ." <". $this -> m_DestReceiverAddress . ">";
		$_aMailHeaders[]	= "Reply-To: ". $this -> m_DestReceiverName ." <". $this -> m_DestReceiverAddress . ">";			
				
		mail($_MailReceivers, $this -> m_SubjectPrefix . $_subject, $_message, implode("\r\n", $_aMailHeaders));

		return true;
	}
}
?>