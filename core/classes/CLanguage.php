<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelLanguages.php';	

require_once 'CSingleton.php';

class	CLanguage extends CSingleton
{
    private		$m_aStorage;   
	private		$isInitialized;
	private		$m_activeLanguage;
	private		$m_defaultLanguage;

	private		$languagesList;

	public static function
	initialize(?CDatabaseConnection &$_dbConnection, string $_activeLanguage = '') : bool
	{
		if($_dbConnection === null)
			return false;

		$instance  = static::instance();

		$instance -> languagesList = [];

		$modelLanguages = new modelLanguages();
		$modelLanguages -> load($_dbConnection);
		$srcLanguages = &$modelLanguages -> getResult();

		foreach($srcLanguages as $lang)
		{
			if($lang -> lang_default)
				$instance -> m_defaultLanguage = $lang -> lang_key;

			$instance -> languagesList[$lang -> lang_key] = $lang;
		}

		$_activeLanguage = (empty($_activeLanguage) ? $instance -> m_defaultLanguage : $_activeLanguage);

		$instance -> m_activeLanguage		= $_activeLanguage;

		if(!isset($instance -> languagesList[$_activeLanguage]))
			$instance -> m_activeLanguage = $instance -> m_defaultLanguage;

		$instance -> isInitialized			= true;

		return true;
	}

	public static function
	getLanguages() : array
	{
		$instance  = static::instance();
		if(empty($instance -> isInitialized)) return [];
		return $instance -> languagesList;
	}

	public static function
	getDefault() : string
	{
		$instance  = static::instance();
		return $instance -> m_defaultLanguage;
	}

	public static function
	loadLanguageFile(string $_Filelocation , $_LanguageKey = false, array $_compareData = [] ) : void
	{ 
		$instance  = static::instance();

		if( !file_exists( $_Filelocation . $_LanguageKey .'.lang' ) )
		{
			if( !file_exists( $_Filelocation . $instance -> m_defaultLanguage .'.lang' ) )
			{
				return;
			}
			$_LanguageKey = $instance -> m_defaultLanguage;
		}

		if($_LanguageKey === false)
			$_LanguageKey = $instance -> getDefault();
		
		$_aFilepaths[] = $_Filelocation . $_LanguageKey .'.lang';
		
		for( $i = 0; $i < count($_aFilepaths); $i++)
		{
			if( !file_exists($_aFilepaths[$i]) )
			{
				continue;
			}
			
			$_pFileHandler 	= fopen($_aFilepaths[$i],"r");

			$_bIgnoreString 	= false;

			while( !feof($_pFileHandler) )
			{
				$_FileLine = fgets($_pFileHandler);

				if ($_FileLine === false) 
				{
					break;
				}
				
				$_FileLine = trim( $_FileLine );
				
				if( !isset($_FileLine[0]) OR $_FileLine[0] === '#' OR strlen($_FileLine) === 0 )
				{
					continue;
				}

				if( $_FileLine[0] === '$')
				{
					if( substr($_FileLine,1,6) === 'import' )
					{
						$_aFilepaths[] = $_Filelocation . substr( $_FileLine , strpos($_FileLine,'(')+1 , strlen($_FileLine) - strpos($_FileLine,'(') - 2 ) .'.lang';
						continue;
					}

					if( substr($_FileLine,1,3) === 'if(' )
					{
						$_comparsionFile = substr( $_FileLine , strpos($_FileLine,'(')+1 , strlen($_FileLine) - strpos($_FileLine,'(') - 2 );
						$_comparsionFile = explode('==',$_comparsionFile);
						foreach($_compareData as $_cIndex => $_cValue)
						{
							if($_cIndex === trim($_comparsionFile[0]) && $_cValue === trim($_comparsionFile[1]))
							{
								$_bIgnoreString = false;
								break;
							}
							else
							{
								$_bIgnoreString = true;
							}
						}
						continue;
					}

					if( substr($_FileLine,1,5) === 'ifend' )
					{
						$_bIgnoreString = false;
						continue;
					}					
				}

				$fi_aStringData = explode( '=' , $_FileLine );

				if( count($fi_aStringData) !== 2)
				{
					continue;
				}

				$fi_aStringData[1] = explode('#', $fi_aStringData[1])[0];

				if(!$_bIgnoreString)
				$instance -> m_aStorage[$_LanguageKey][ trim($fi_aStringData[0]) ] = trim($fi_aStringData[1]);
			}		
			
			fclose($_pFileHandler);
		}
	}
	
	public static function
	string(string $_StringID, string $_format = 'regular', bool $_preventUnknownKMark = false) : string
	{
		$instance  = static::instance();

		if(empty($instance -> isInitialized)) return 'not_initialized';

		$_StringID 		= explode(' ', $_StringID);
		$returnValue 	= '';

		foreach($_StringID as $key => $stringId)
		{
			$stringId = trim($stringId);

			if($key !== 0)
				$returnValue .= ' ';

			if( isset( $instance -> m_aStorage[$instance -> m_activeLanguage][$stringId] ) )
			{


				$returnValue .= $instance -> m_aStorage[$instance -> m_activeLanguage][$stringId];
			}
			else
			{
				if(!$_preventUnknownKMark)
					$returnValue .= '(!) ';
				$returnValue .= $stringId;
			}
		}

		switch($_format)
		{
			case 'all_lower': $returnValue = strtolower($returnValue); break;
			case 'all_upper': $returnValue = strtoupper($returnValue); break;
		}

		return $returnValue;
	}

	public static function
	stringExt(string $_StringID , array $_Replacement = array(), bool $_preventUnknownKMark = false) : string
	{
		$instance  = static::instance();
		if(empty($instance -> isInitialized)) return 'not_initialized';
		if( isset( $instance -> m_aStorage[$instance -> m_activeLanguage][$_StringID] ) )
		{
			if(!empty($_Replacement))
			{
				$_arrayKeys 	= array_keys($_Replacement);
				$_arrayValues 	= array_values($_Replacement);
				$_tempString	= str_replace($_arrayKeys,$_arrayValues,$instance -> m_aStorage[$instance -> m_activeLanguage][$_StringID]);
				$_tempString	= str_replace('\r\n',"\r\n",$_tempString);
				return $_tempString;
			}	
		}
				
		return (!$_preventUnknownKMark ? '(!) ' : '') . $_StringID;
	}
	
	public static function
	getStringAlternates(string $_StringID) : array
	{
		$instance  = static::instance();
		$_aReturnData = [];
		if(empty($instance -> isInitialized)) return $_aReturnData;
		foreach($instance -> m_aStorage as $_langKey => $_stringsData)
		{
			if(isset($_stringsData[$_StringID]))
				$_aReturnData[$_langKey] = $_stringsData[$_StringID];
		}
		return $_aReturnData;
	}

	public static function
	getActiveLanguage()
	{
		$instance  = static::instance();
		if(empty($instance -> isInitialized)) return false;
		return $instance -> m_activeLanguage;
	}

	public static function
	getActive()
	{
		$instance  = static::instance();
		if(empty($instance -> isInitialized)) return false;
		return $instance -> m_activeLanguage;
	}

	public static function
	setActiveLanguage(string $_activeLanguage)
	{
		$instance  = static::instance();
		if(empty($instance -> isInitialized))
			return false;

		if(!CMS_BACKEND)
		{
			if(!isset($instance -> languagesList[$_activeLanguage]))

				$instance -> m_activeLanguage = $instance -> m_defaultLanguage;	

			else

				$instance -> m_activeLanguage = $_activeLanguage;
		}
		else
		{
			if(!isset(CFG::GET() -> LANGUAGE -> BACKEND[$_activeLanguage]))

				$instance -> m_activeLanguage = 'en';	

			else

				$instance -> m_activeLanguage = $_activeLanguage;
		}

		return $instance -> m_activeLanguage;
	}	
}
