<?php

define('MSG_ERROR',1);
define('MSG_WARNING',2);
define('MSG_OK',3);
define('MSG_NOTIFY',4);
define('MSG_LOG',9);
define('MSG_DEBUG',10);

require_once 'CSingleton.php';

class	CMessages extends CSingleton
{
	private	$m_bProtocolReporting;
	private	$m_bDebugReporting;
	private	$m_LogDestination;
	private	$m_aStorage;

	public function
	initialize(bool $_protocolReporting = false, bool $_debugReporting = false)
	{
		$this -> m_bProtocolReporting 	= $_protocolReporting;
		$this -> m_bDebugReporting 		= $_debugReporting;
		$this -> m_aStorage				= [];

		if($_protocolReporting) $this -> initMessagesLog();
		return true;
	}

	public function
	addMessage(string $_msgContent, int $_msgType = MSG_OK, string $_msgTarget = '', bool $_toLogFile = false)
	{
		if(strlen($_msgContent) == 0) return false;
		if(!$this -> m_bProtocolReporting && $_msgType === MSG_LOG && !$_toLogFile) return false;
		if(!$this -> m_bDebugReporting && $_msgType === MSG_DEBUG) return false;
		switch($_msgType)
		{
			case MSG_ERROR:
			case MSG_WARNING:
			case MSG_OK:
			case MSG_NOTIFY:	$this -> m_aStorage[$_msgType][] = new CMessagesItem($_msgType, $_msgTarget, $_msgContent);
								if(!$_toLogFile) break;

			case MSG_LOG:		if(!$this -> m_bProtocolReporting) break;
								$_hFile = fopen($this -> m_LogDestination, "a");
								while(true)
								{
									if (flock($_hFile, LOCK_EX))
									{
										fwrite($_hFile, "\t". date("H:i:s") ."  ::  ". $_msgContent ."\r\n");
										fflush($_hFile); 
										flock($_hFile, LOCK_UN);
										break;
									}
								}
								fclose($_hFile);
		}
		return true;
	}

	public function
	clearMessages()
	{
		$this -> m_aStorage = [];
	}

	public function
	printMessages(string $_msgTarget = '')
	{
		foreach($this -> m_aStorage as $_msgType => $_msgSet)
		{
			echo '<div class="ui result-box" data-error="'. $_msgType .'">';
			foreach($_msgSet as $_msg)
			{
				if($_msgTarget !== '' && !$_msg -> isMessageTarget($_msgTarget)) continue;
				echo '<p>'. $_msg -> m_MessageContent .'</p>';
			}
			echo '</div>';
		}
	}

	public function
	initMessagesLog()
	{
		$_timeStamp		= time();

		$_destDirectoryPath		  = CMS_SERVER_ROOT . DIR_MESSSAGELOG;
		$this -> m_LogDestination = $_destDirectoryPath . date("Ymd-H-i-s",$_timeStamp) .'-mesages-log';

		if(!is_dir($_destDirectoryPath))
		{
			if(mkdir($_destDirectoryPath) === false)
				trigger_error("CMessages::initMessagesLog -- Call of mkdir fails on initialization",E_USER_WARNING);
		}

		$_hFile = fopen($this -> m_LogDestination, "w");
		fwrite($_hFile, "\r\n\tLOG FILE SESSION @ ". date("Y-m-d H:i:s",$_timeStamp) ."\r\n");
		fwrite($_hFile, "\r\n");
		fwrite($_hFile, "\r\n\t\tuser_agent     :  " .$_SERVER['HTTP_USER_AGENT'] ."");
		fwrite($_hFile, "\r\n\t\trequested_uri  :  " .$_SERVER['REQUEST_URI'] ."\r\n");
		fwrite($_hFile, "\r\n\t----------------------------------------------------------------------------------------------\r\n");
		fwrite($_hFile, "\r\n");
		fclose($_hFile);
	}
}

class	CMessagesItem
{
	public	$m_iMessageType;
	public	$m_MessageTarget;
	public	$m_MessageContent;

	public function
	__construct(int $_msgType, string $_msgTarget, string $_msgContent)
	{
		$this -> m_iMessageType 	= $_msgType;
		$this -> m_MessageTarget 	= $_msgTarget;
		$this -> m_MessageContent 	= $_msgContent;
	}

	public function
	__toString()
	{
		return $this -> m_MessageContent;
	}

	public function
	isMessageType(int $_msgType)
	{
		return ($_msgType === $this -> m_iMessageType ? true : false);
	}

	public function
	isMessageTarget(string $_msgTarget)
	{
		return ($_msgTarget === $this -> m_MessageTarget ? true : false);
	}
}
?>