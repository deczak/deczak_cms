<?php

class CNodesSearch
{
	protected	$m_validSearchTypeList;
	protected	$m_response;

	public function
	__construct()
	{
		$this -> m_validSearchTypeList 	= [];
		$this -> m_validSearchTypeList[] = 'tag';
		$this -> m_validSearchTypeList[] = 'category';
		$this -> m_validSearchTypeList[] = 'search';
	}

	public function
	detectSearch()
	{
		$searchType 	= $this -> getUriQueryVar('cms-search-nodes-type', false);
		$searchValue	= $this -> getUriQueryVar('cms-search-nodes-value', false);

		if(		$searchType	 === false
			||	$searchValue === false
		  )	return false;


		if(!in_array($searchType, $this -> m_validSearchTypeList))
			return false;

		$this -> m_response = new stdClass;
		$this -> m_response -> type  = $searchType;
		$this -> m_response -> value = $searchValue;
		
		return true;		
	}

	public function
	getType()
	{
		if($this -> m_response !== null)
			return $this -> m_response -> type;
		return false;
	}

	public function
	getValue()
	{
		if($this -> m_response !== null)
			return $this -> m_response -> value;
		return false;
	}

	protected function
	getUriQueryVar(string $_variableName, bool $_methodPost = false)
	{
		$_pURLVariables	 =	new CURLVariables();
		$_request		 =	[];
		$_request[] 	 = 	[	"input" => $_variableName,  	"validate" => "strip_tags|!empty" ,	"use_default" => true, "default_value" => false ]; 		
		$_pURLVariables -> retrieve($_request, !$_methodPost, $_methodPost);	

		if($_pURLVariables -> getValue($_variableName) === false)
			return false;

		return $_pURLVariables -> getValue($_variableName);
	}
}

?>