<?php

class	CBasic
{
	public	$m_aStorage;

	public function
	__construct()
	{
		$this -> m_aStorage		= [];
	}

	public function
	getJSON(array $_jsonData)
	{
		return json_encode( $_jsonData , JSON_FORCE_OBJECT|JSON_HEX_APOS|JSON_HEX_QUOT );
	}

	public function
	printJSON(array $_jsonData )
	{
		header('Content-Type: application/json');
		echo json_encode( $_jsonData , JSON_FORCE_OBJECT|JSON_HEX_APOS|JSON_HEX_QUOT );
		exit();
	}	

	public function
	dbug(bool $_returnAsString = false)
	{
		tk::dbug($this -> m_aStorage, $_returnAsString);
	}

}
?>