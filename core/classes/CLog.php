<?php

require_once 'CSingleton.php';

/**
 * 	This class handles the log of failures and processes. 
 * 
 * 	This is a singleton class.
 */
class CLog extends CSingleton
{
	/**
	 * 	Initialize function to setup the log system
	 */
	public static function 
	initialize()
	{
		##	Set Logfile

		$logfileLocation = CMS_SERVER_ROOT."log/";
		$logfileName 	 = 'error-'. date("Y-m-d");

		if(!file_exists($logfileLocation))
			mkdir($logfileLocation);

		if(CFG::GET() -> ERROR_SYSTEM -> ERROR_FILE -> ENABLED)
		{
			ini_set("log_errors", 1);
			ini_set("error_log", $logfileLocation.$logfileName);
		}

		##	Log System

		$instance  = static::instance();
		$instance -> logFilename = $instance->getLogFilename();
		$instance -> firstCall 	 = true;

		//set_error_handler([$instance, 'errorHandler']);
		//set_exception_handler(...);
	}

	/**
	 * 	Adds an entry to the log file. If the value is a array or object, print_r will be used for output.
	 * 	@param mixed $value A string or object/array for the log file.
	 */
	public static function
	add($value)
	{
		if(!CFG::GET() -> ERROR_SYSTEM -> LOG_FILE -> ENABLED)
			return;

		$instance = static::instance();

		$hFile = fopen(CMS_SERVER_ROOT.DIR_LOG.($instance->logFilename ?? 'log'), "a");

		if(flock($hFile, LOCK_EX))
		{
			if(($instance->firstCall ?? true))
			{
				ftruncate($hFile, 0);

				fwrite($hFile, "\r\n\tLOG FILE SESSION @ ". date("Y-m-d H:i:s") ."\r\n");
				fwrite($hFile, "\r\n\t\tuser_agent     :  " .$_SERVER['HTTP_USER_AGENT'] ."");
				fwrite($hFile, "\r\n\t\trequested_uri  :  " .$_SERVER['REQUEST_URI'] ."\r\n");
				fwrite($hFile, "\r\n\t------------------------------------------------------------------------------------------------------------------------------\r\n");
				fwrite($hFile, "\r\n");
				
				$instance->firstCall = false;
			}

			fwrite($hFile, date("Y-m-d H:i:s") . "  ::  ");	

			if(is_object($value) || is_array($value))
				fwrite($hFile, print_r($value, true) . "\r\n");	
			else
				fwrite($hFile, $value . "\r\n");	

			fflush($hFile); 
			flock($hFile, LOCK_UN); 
		}

		fclose($hFile);		
	}

	/**
	 * 	Determine log filename by log mode setting
	 * 	@return string The filename for the log file
	 */
	private function
	getLogFilename() : string
	{
		switch(CFG::GET() -> ERROR_SYSTEM -> LOG_FILE -> LOG_MODE)
		{
			case 2:	// Session File
				return 'log-'. date("Y-m-d-H-i-s");
		}
		// Default is Single File (Mode 1)
		return 'log';
	}

	/*
	function 
	errorHandler(int $errno, string $errstr, ?string $errfile, ?int $errline)
	{
		if (!(error_reporting() & $errno)) 
			return false;
	
		$errstr = htmlspecialchars($errstr);

		switch ($errno) {

			case E_USER_ERROR:
			case E_USER_WARNING:
			case E_USER_NOTICE:
			case E_ERROR:                           
			case E_WARNING:                        
			case E_PARSE:                       
			case E_NOTICE:                            
			case E_CORE_ERROR:          
			case E_CORE_WARNING:        
			case E_COMPILE_ERROR:     
			case E_COMPILE_WARNING:    
			case E_STRICT:             
			case E_RECOVERABLE_ERROR:  

			default:

				break;

		}

		return true;
	}
	*/
}
