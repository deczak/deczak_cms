<?php

class	CURLVariables
{
	public	$m_aStorage;

	public function
	__construct()
	{
		$this -> m_aStorage		= [];
	}

	public function
	retrieve(array &$_requestStructure, bool $_fromGET , bool $_fromPOST, bool $_successfulTotalValidation = false)
	{

		$_bValidationSuccess =    true;

 		# Process the requested variables on foreach
        foreach($_requestStructure as $_requestedSet) 
        {
            # dataset check on required array elements
            if( !is_array($_requestedSet) OR !isset($_requestedSet['input']) OR empty($_requestedSet['validate']) )
            {
                if( $_successfulTotalValidation )
                {          
                	# If one dataset is not valid and we request a full validation, we break the foreach to stop the processs.
                    
                    $_bValidationSuccess = false; 
                    break;
                }
                continue;
            }

            #  Check if output exists, if not we use the input as  output
            if( !isset($_requestedSet['output']) )
            {
				$_requestedSet['output'] = $_requestedSet['input'];
            }

			#	Check if it exists already
			if(isset($this -> m_aStorage[ $_requestedSet['output'] ]) && !is_array($this -> m_aStorage[ $_requestedSet['output'] ])) continue;
		
            if( $_fromPOST && isset($_POST[ $_requestedSet["input"] ]) )  // check if POST and request exists in POST
            {
				$_requestedSet["validate"] = explode('|', $_requestedSet["validate"]);		
				$_requestedSet["validate"] = array_filter($_requestedSet["validate"],'strlen');

				$_bValidationSuccess = $this -> _retrieve($_POST[ $_requestedSet["input"] ], $_requestedSet, [], $_bValidationSuccess);
            }
            elseif( $_fromGET && isset($_GET[ $_requestedSet["input"] ])) // check if GET and request exists in GET
            {
				$_requestedSet["validate"] = explode('|', $_requestedSet["validate"]);		
				$_requestedSet["validate"] = array_filter($_requestedSet["validate"],'strlen');

				$_bValidationSuccess = $this -> _retrieve($_GET[ $_requestedSet["input"] ], $_requestedSet, [] , $_bValidationSuccess );
            }
            else
            {
                if(isset($_requestedSet["use_default"]) && isset($_requestedSet["default_value"]) AND $_requestedSet["use_default"] === true)
                {
                    $this -> m_aStorage[ $_requestedSet['output'] ] = $_requestedSet["default_value"];
                }
                else
                {
                    $_bValidationSuccess = false;
                }
            }
		}


        # Check on full validation request, return false if it failed
        if( $_successfulTotalValidation && !$_bValidationSuccess)
        {
            return false;
        }            
        return true;
	}

	private function
	_retrieve($_sourceData, array $_requestedSet, array $_subParentDimensions,  bool &$_bPreviousResult)
	{


		if(is_array($_sourceData))
		{
#echo "\r\n"." - is array";
#print_r($_sourceData);

			foreach($_sourceData as $_sourceSubKey => $_sourceSubArray)
			{


#echo "\r\n"." - - looping array:";
				$_subPDKey = count($_subParentDimensions);
				$_subParentDimensions[$_subPDKey] = $_sourceSubKey; 

#echo "\r\n"." - - make sub parent:";
#echo "\r\n";		
#print_r($_sourceSubKey);


#echo "\r\n"." - - subparent array 1 :";
#echo "\r\n";		
#print_r($_subParentDimensions);


				$_bPreviousResult = $this -> _retrieve($_sourceSubArray, $_requestedSet, $_subParentDimensions, $_bPreviousResult);



				unset($_subParentDimensions[ $_subPDKey ]);

	
#echo "\r\n"." - - subparent array 2:";
#echo "\r\n";		
#print_r($_subParentDimensions);

			
			}
		}
		else
		{


#echo "\r\n"." - handling for";	
#echo "\r\n";		
#print_r($_sourceData);
#print_r($_subParentDimensions);


#echo "\r\n"." - no array .. sub parent?";	
#echo "\r\n";		
#print_r($_subParentDimensions);
	
			if(!isset($this -> m_aStorage[ $_requestedSet['output'] ]))
				$this -> m_aStorage[ $_requestedSet['output'] ] = NULL;

			$_storageDest = &$this -> m_aStorage[ $_requestedSet['output'] ];


			foreach($_subParentDimensions as $_dimension)
			{

#echo "\r\n"." - - storage destination";	
#echo "\r\n";		
#print_r($_storageDest );	

#echo "\r\n"." - - secon level key";	
#echo "\r\n";		
#print_r($_dimension);			
	
				$_storageDest = &$_storageDest[$_dimension];

#echo "\r\n";		
#print_r($this -> m_aStorage);


			}

#echo "\r\n"." - validate check";		

			$_tempOutputValue = "";
			if($this -> _validate( $_sourceData , $_tempOutputValue , $_requestedSet["validate"] ))
			{ 
				$_storageDest = $_tempOutputValue;


#echo "\r\n"." - validate check ok ";	
#echo "\r\n";	
#print_r($_storageDest);	

#echo "\r\n";		
#print_r($this -> m_aStorage);
			}
			else
			{
				if(isset($_requestedSet["use_default"]) AND isset($_requestedSet["default_value"]) AND $_requestedSet["use_default"] === true)
				{
					$_storageDest = $_requestedSet["default_value"];
				}
				else
				{
					$_bPreviousResult = false;
				}
			}
		}
		return $_bPreviousResult;
	}

	private function
	_validate(string $_inputValue, &$_outputValue, array $_validateTypes )
	{
        $_bBreakForeach			=    false;
        $_bValidateResult		=    true;
        $_temporaryOutput		=    $_inputValue;        

        foreach( $_validateTypes as $_type )
        {
            switch($_type)
            {    
                ##  F O R M A T E   T Y P E S

                case    'strip_tags':

                        $_temporaryOutput = strip_tags($_temporaryOutput);
                        break;

                case    'strip_whitespaces':

                        $_temporaryOutput = trim($_temporaryOutput);
                        $_temporaryOutput = str_replace(" ","", $_temporaryOutput);
                        break;  

                case    'strip_quote':

                        $_temporaryOutput = str_replace('"','', $_temporaryOutput);
                        break;        

                case    'trim':
                        $_temporaryOutput = trim($_temporaryOutput);
                        break;

                case    'lowercase':
                        $_temporaryOutput = strtolower($_temporaryOutput);
                        break;

                case    'uppercase':
                        $_temporaryOutput = strtoupper($_temporaryOutput);
                        break;        

                case    'cast_bool':
                        if($_temporaryOutput === "1" OR $_temporaryOutput === "true" )
                        { $_temporaryOutput = true; } else { $_temporaryOutput = false; }
                        break;   

                case    'cast_int':
                        $_temporaryOutput = intval($_temporaryOutput);
                        break;        

                ##    V A L I D A T E   T Y P E S    

                case    'is_digit':

                        if( ctype_digit($_temporaryOutput) )
                        { $_bValidateResult = true; } else { $_bValidateResult = false; $_bBreakForeach = true; }
                        break;    
                    
                case    '!empty':    
                        if( !empty($_temporaryOutput) OR is_string($_temporaryOutput) AND strlen($_temporaryOutput) > 0 )
                        { $_bValidateResult = true; } else { $_bValidateResult = false; $_bBreakForeach = true; }
                        break;
            }

            if($_bBreakForeach)
            {
                break;
            }        
        }

        if($_bValidateResult)
        {
            $_outputValue = $_temporaryOutput;
        }
        return $_bValidateResult;   
	}

	public function
	getArray($_bReturnFalseIfEmpty = false)
	{
        if( count($this -> m_aStorage) === 0 )
            return false;
        return $this ->  m_aStorage;     
	}

	public function
	getValue(string $_key, $_defaultValue = false)
	{
        if( !isset($this ->  m_aStorage[$_key]) )
        {
            return $_defaultValue;        
        }
        return $this ->  m_aStorage[ $_key ];   
	}

	public function
	setValue(string $_key, $_value)
	{
        $this ->  m_aStorage[ $_key ] = $_value;
        return $this ->  m_aStorage[ $_key ];   
	}

	public function
	getURLAppendix(bool $_bStartWQuestionmark = true, array $_ignoreKeys = [])
	{
        $_returnValue		= "";
        $_bFirstValue		= true;
		$this -> _getURLAppendix($this -> m_aStorage, $_bFirstValue, $_bStartWQuestionmark, $_returnValue, $_ignoreKeys, [] );
        return $_returnValue;		
	}

	/**
	 * 	This function is an auxiliary function to generate the URL-Appendix
	 */
	private function
	_getURLAppendix(array $_arrayDimension, bool &$_bFirstValue, bool $_bStartWQuestionmark, string &$_returnValue, array &$_ignoreKeys, array $_parentKeys )
	{
        foreach($_arrayDimension as $_dataKey => $_dataValue)
        {   
            if(array_search($_dataKey,$_ignoreKeys,true) !== FALSE )
            {
                continue;
            }   

            if(!is_array($_dataValue) && (!$_bStartWQuestionmark OR !$_bFirstValue))
            {
                $_returnValue    .= '&';
            }
            else if(!is_array($_dataValue))
            {
                $_returnValue    .= '?';
                $_bFirstValue     = false;
            }

            if(is_array($_dataValue))
            {
				$_parentKeys[] = $_dataKey;                
				$this -> _getURLAppendix($_dataValue, $_bFirstValue, $_bStartWQuestionmark, $_returnValue, $_ignoreKeys, $_parentKeys);
				unset( $_parentKeys[count($_parentKeys) -1] );   
            }
			else
			{
				$_variableKey = '';
				if(count($_parentKeys) !== 0)
				{
					foreach($_parentKeys as $_parentKey)
					{
						if(empty($_variableKey))
						{
							$_variableKey .= $_parentKey;
						}
						else
						{
							$_variableKey .= '['. $_parentKey .']';
						}
					}
					$_variableKey .= '['. $_dataKey .']';
				}
				else
				{
					$_variableKey = $_dataKey;
				}
				$_returnValue        .= $_variableKey .'='. urlencode($_dataValue);
			}
        }
	}

	public function
	printHiddenInputs(array $_ignoreKeys = [])
	{
		$this -> _printHiddenInputs($this -> m_aStorage, $_ignoreKeys, [] );
	}

	/**
	 * 	This function is an auxiliary function to print the hidden inputs
	 */
	private function
	_printHiddenInputs(array $_arrayDimension, array &$_ignoreKeys, array $_parentKeys )
	{
        foreach($_arrayDimension as $_dataKey => $_dataValue)
        {           
            if(array_search($_dataKey,$_ignoreKeys,true) !== FALSE )
            {
                continue;
            }           

            if(is_array($_dataValue))
            { 
				$_parentKeys[] = $_dataKey;                  
				$this -> _printHiddenInputs($_dataValue, $_ignoreKeys, $_parentKeys  );
				unset( $_parentKeys[count($_parentKeys) -1] );  
            }
			else
			{
				$_variableKey = '';
				if(count($_parentKeys) !== 0)
				{
					foreach($_parentKeys as $_parentKey)
					{
						if(empty($_variableKey))
						{
							$_variableKey .= $_parentKey;
						}
						else
						{
							$_variableKey .= '['. $_parentKey .']';
						}
					}
					$_variableKey .= '['. $_dataKey .']';
				}
				else
				{
					$_variableKey = $_dataKey;
				}
				echo '<input type="hidden" name="'.$_variableKey .'" value="'. $_dataValue .'">';

			}
        }
	}
}
?>