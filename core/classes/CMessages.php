<?php

define('MSG_ERROR',1);
define('MSG_WARNING',2);
define('MSG_OK',3);
define('MSG_NOTIFY',4);
define('MSG_LOG',9);

define('MSG_DEBUG',10);		// deprecated

require_once 'CSingleton.php';

/**
 * 	This class handles the messages for the user. 
 * 
 * 	This is a singleton class.
 */
class	CMessages extends CSingleton
{
	private	$m_LogDestination;
	private	$m_aStorage;

	/**
	 * 	Initialize function to setup the message system
	 */
	public static function 
	initialize()
	{
		$instance  = static::instance();
		$instance -> m_aStorage				= [];
		return $instance;
	}

	/**
	 * 	Adds a message to the message system for the user.
	 * 
	 * 	@param string $_msgContent The message string
	 * 	@param int $_msgType The message type, a named constant that begins with MSG_
	 * 	@param string $_msgTarget Optional the name of that section that should only receive the info
	 * 	@param bool $_msgContent Boolean switch for print the message into the log file. Log System needs to be enabled.
	 */
	public static function
	add(string $_msgContent, int $_msgType = MSG_OK, string $_msgTarget = '', bool $_toLogFile = false)
	{
		$instance  = static::instance();

		if(strlen($_msgContent) == 0) return;
		switch($_msgType)
		{
			case MSG_ERROR:
			case MSG_WARNING:
			case MSG_OK:
			case MSG_NOTIFY:	$instance -> m_aStorage[$_msgType][] = new CMessagesItem($_msgType, $_msgTarget, $_msgContent);

								if(!$_toLogFile) break;

			case MSG_LOG:		cmsLog::add($_msgContent);
		}
	}

	/**
	 * 	Clears the message storage
	 */
	public function
	clearMessages()
	{
		$this -> m_aStorage = [];
	}

	/**
	 * 	Prints all Message to the HTML Output or just by names target
	 * 
	 * 	@param string $_msgTarget Name of the section for only his message
	 */
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
