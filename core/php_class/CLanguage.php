<?php

require_once 'CSingleton.php';

class	CLanguage extends CSingleton
{
    private		$m_aStorage;   
	private		$m_bInitialized;
	private		$m_activeLanguage;
	private		$m_defaultLanguage;
	private		$m_aSupportedLanguages;

	public function
	initialize(array $_parameters, string $_activeLanguage)
	{
		$this -> m_defaultLanguage		= $_parameters['default'];
		$this -> m_activeLanguage		= $_activeLanguage;
		$this -> m_aSupportedLanguages	= $_parameters['supported'];
		if(!isset($_parameters['supported'][$_activeLanguage]))
			$this -> m_activeLanguage = $_parameters['default'];
		$this -> m_bInitialized			= true;
	}

	public function
	loadLanguageFile(string $_Filelocation , string $_LanguageKey, array $_compareData = [] )
	{ 
		if( !file_exists( $_Filelocation . $_LanguageKey .'.lang' ) )
		{
			return;
		}
		
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

				if(!$_bIgnoreString)
				$this -> m_aStorage[$_LanguageKey][ trim($fi_aStringData[0]) ] = trim($fi_aStringData[1]);
			}		
			
			fclose($_pFileHandler);
		}
	}
	
	public function
	getString(string $_StringID, string $_DefaultString = '???')
	{
		if(empty($this -> m_bInitialized)) return 'not_initialized';
		if( isset( $this -> m_aStorage[$this -> m_activeLanguage][$_StringID] ) )
		{
			return $this -> m_aStorage[$this -> m_activeLanguage][$_StringID];
		}
		  
		return $_DefaultString;
	}	

	public function
	getStringExt(string $_StringID , array $_Replacement = array() )
	{
		if(empty($this -> m_bInitialized)) return 'not_initialized';
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
	getStringAlternates(string $_StringID)
	{
		$_aReturnData = [];
		if(empty($this -> m_bInitialized)) return $_aReturnData;
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
		if(empty($this -> m_bInitialized)) return false;
		return $this -> m_activeLanguage;
	}

	public function
	setActiveLanguage(string $_activeLanguage)
	{
		if(empty($this -> m_bInitialized)) return false;
		if(!isset($this -> m_aSupportedLanguages[$_activeLanguage]))
			$this -> m_activeLanguage = $this -> m_defaultLanguage;	
		else
			$this -> m_activeLanguage = $_activeLanguage;
		return $this -> m_activeLanguage;
	}	
}

?>