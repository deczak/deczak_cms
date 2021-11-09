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

	public function
	initialize(?CDatabaseConnection &$_dbConnection, string $_activeLanguage = '') : bool
	{
		if($_dbConnection === null)
			return false;

		$this -> languagesList = [];

		$modelLanguages = new modelLanguages();
		$modelLanguages -> load($_dbConnection);
		$srcLanguages = &$modelLanguages -> getResult();

		foreach($srcLanguages as $lang)
		{
			if($lang -> lang_default)
				$this -> m_defaultLanguage = $lang -> lang_key;

			$this -> languagesList[$lang -> lang_key] = $lang;
		}

		$_activeLanguage = (empty($_activeLanguage) ? $this -> m_defaultLanguage : $_activeLanguage);

		$this -> m_activeLanguage		= $_activeLanguage;

		if(!isset($this -> languagesList[$_activeLanguage]))
			$this -> m_activeLanguage = $this -> m_defaultLanguage;

		$this -> isInitialized			= true;

		return true;
	}

	public function
	getLanguages() : array
	{
		if(empty($this -> isInitialized)) return [];
		return $this -> languagesList;
	}

	public function
	getDefault() : string
	{
		return $this -> m_defaultLanguage;
	}

	public function
	loadLanguageFile(string $_Filelocation , $_LanguageKey = false, array $_compareData = [] ) : void
	{ 
		if( !file_exists( $_Filelocation . $_LanguageKey .'.lang' ) )
		{
			if( !file_exists( $_Filelocation . $this -> m_defaultLanguage .'.lang' ) )
			{
				return;
			}
			$_LanguageKey = $this -> m_defaultLanguage;
		}

		if($_LanguageKey === false)
			$_LanguageKey = $this -> getDefault();
		
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
				$this -> m_aStorage[$_LanguageKey][ trim($fi_aStringData[0]) ] = trim($fi_aStringData[1]);
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

	public function
	getStringExt(string $_StringID , array $_Replacement = array()) : string
	{
		if(empty($this -> isInitialized)) return 'not_initialized';
		if( isset( $this -> m_aStorage[$this -> m_activeLanguage][$_StringID] ) )
		{
			if(!empty($_Replacement))
			{
				$_arrayKeys 	= array_keys($_Replacement);
				$_arrayValues 	= array_values($_Replacement);
				$_tempString	= str_replace($_arrayKeys,$_arrayValues,$this -> m_aStorage[$this -> m_activeLanguage][$_StringID]);
				$_tempString	= str_replace('\r\n',"\r\n",$_tempString);
				return $_tempString;
			}	
		}
		return '???';
	}
	
	public function
	getStringAlternates(string $_StringID) : array
	{
		$_aReturnData = [];
		if(empty($this -> isInitialized)) return $_aReturnData;
		foreach($this -> m_aStorage as $_langKey => $_stringsData)
		{
			if(isset($_stringsData[$_StringID]))
				$_aReturnData[$_langKey] = $_stringsData[$_StringID];
		}
		return $_aReturnData;
	}

	public function
	getActiveLanguage()
	{
		if(empty($this -> isInitialized)) return false;
		return $this -> m_activeLanguage;
	}

	public function
	getActive()
	{
		if(empty($this -> isInitialized)) return false;
		return $this -> m_activeLanguage;
	}

	public function
	setActiveLanguage(string $_activeLanguage)
	{
		if(empty($this -> isInitialized))
			return false;

		if(!CMS_BACKEND)
		{
			if(!isset($this -> languagesList[$_activeLanguage]))

				$this -> m_activeLanguage = $this -> m_defaultLanguage;	

			else

				$this -> m_activeLanguage = $_activeLanguage;
		}
		else
		{
			if(!isset(CFG::GET() -> LANGUAGE -> BACKEND[$_activeLanguage]))

				$this -> m_activeLanguage = 'en';	

			else

				$this -> m_activeLanguage = $_activeLanguage;
		}

		return $this -> m_activeLanguage;
	}	
}
