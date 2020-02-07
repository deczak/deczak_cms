<?php

// possible bugged at some point on load functions
// string behaviour maybe deprecated since models can handle joins

class CModelComplementaryArray
{
	public	$type;
	public	$propertyName;
	public	$propertyCompare;
	public	$storageInstance;

	public function
	__construct(string $_propertyName, string $_propertyCompare, string $_type, &$_storageInstance)
	{
		$this -> type				= $_type;
		$this -> propertyName 		= $_propertyName;
		$this -> propertyCompare	= $_propertyCompare;
		$this -> storageInstance	= $_storageInstance;
	}
}

class CModelComplementary
{
	public $complementaryList;

	public function
	__construct()
	{
		$this -> complementaryList 		= [];
	}

	public function
	addArray($_propertyName, $_propertyCompare, &$_storageInstance)
	{
		$this -> complementaryList[] 		 = new CModelComplementaryArray($_propertyName, $_propertyCompare, 'array', $_storageInstance);
		return $this;
	}

	public function
	addValue($_propertyName, $_propertyCompare, &$_storageInstance)
	{
		$this -> complementaryList[] = new CModelComplementaryArray($_propertyName, $_propertyCompare, 'string', $_storageInstance);
		return $this;
	}

}

?>