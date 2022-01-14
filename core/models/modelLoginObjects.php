<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SCHEME.'schemeLoginObjects.php';	

class 	modelLoginObjects extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('schemeLoginObjects', 'loginObject');
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

		return true;
	}
}
