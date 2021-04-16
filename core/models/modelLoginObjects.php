<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeLoginObjects.php';	

class 	modelLoginObjects extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('shemeLoginObjects', 'loginObject');
	}	

	public function
	load(CDatabaseConnection &$_pDatabase, CModelCondition $_pCondition = NULL, $_execFlags = NULL)
	{
		parent::load($_pDatabase, $_pCondition, $_execFlags);

		foreach($this -> m_resultList as $objectIndex => $item)
		{
			foreach($this -> m_resultList[$objectIndex] -> object_fields as $fieldIndex => $item)
			{
				if(!empty($this -> m_resultList[$objectIndex] -> object_fields[$fieldIndex] -> formTable))
				{
					$this -> m_resultList[$objectIndex] -> object_fields[$fieldIndex] -> optionsList = [];

					$addResult 	= $_pDatabase		-> query(DB_SELECT) 
													-> table($this -> m_resultList[$objectIndex] -> object_fields[$fieldIndex] -> formTable)
													-> exec($_execFlags);

					foreach($addResult as $addIndex => $addItem)
					{
						$propValue 	 = $this -> m_resultList[$objectIndex] -> object_fields[$fieldIndex] -> formValue;
						$propText 	 = $this -> m_resultList[$objectIndex] -> object_fields[$fieldIndex] -> formText;

						$optionItem  = new stdClass;
						$optionItem	-> value 		= $addItem -> $propValue;
						$optionItem -> text 		= $addItem -> $propText;
						$optionItem -> isDefault 	= ($addItem -> is_default ?? ($addIndex == 0 ? true : false));

						$this -> m_resultList[$objectIndex] -> object_fields[$fieldIndex] -> optionsList[] = $optionItem;
					}
				}
			}
		}
	}
}

/**
 * 	Parent class for the data class with toolkit functions. It get the child instance to access the child properties.

class 	toolkitLoginObjects
{
	protected	$m_childInstance;

	public function
	__construct($_instance)
	{
		$this -> m_childInstance = $_instance;
	}
}
 */
?>