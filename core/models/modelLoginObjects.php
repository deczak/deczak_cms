<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeLoginObjects.php';	

class 	modelLoginObjects extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('loginObject');		
		$this -> m_sheme = new shemeLoginObjects();
	}	


	public function
	load(&$_sqlConnection, CModelCondition $_condition = NULL, CModelComplementary $_complementary = NULL)
	{
		if(parent::load($_sqlConnection, $_condition, $_complementary))
		{
			foreach($this -> m_storage as $storageIndex => $storageItm)
			{
				$this -> m_storage[$storageIndex] -> object_fields = json_decode($this -> m_storage[$storageIndex] -> object_fields);

				foreach($this -> m_storage[$storageIndex] -> object_fields as $fieldIndex => $field)
				{
					switch($field -> query_type)
					{
						case 'assign'	:	

 											$this -> m_storage[$storageIndex] -> object_fields[$fieldIndex] -> optionsList = [];
											$optionsList = &$this -> m_storage[$storageIndex] -> object_fields[$fieldIndex] -> optionsList;

											$sqlString 		= 	"	SELECT		". $field -> formTable .".*
																	FROM		". $field -> formTable ."
																";

											$sqlAssignRes	=	$_sqlConnection -> query($sqlString);

											while($sqlAssignRes!= false && $sqlAssignItm = $sqlAssignRes -> fetch_assoc())
											{
												if(		isset($sqlAssignItm['is_active'])
													&& 	$sqlAssignItm['is_active'] === '1'
												  )  
												{
													$index = count($optionsList);

													$optionsList[$index]['text'] 		= 	$sqlAssignItm[$field -> formText];
													$optionsList[$index]['value'] 		= 	$sqlAssignItm[$field -> formValue];
													$optionsList[$index]['isDefault'] 	= 	(isset($sqlAssignItm['is_default']) && $sqlAssignItm['is_default'] === '1' ? true : false);
												}
											}

											break;
					}
			
				}

				// Back to json, some part still do a decode


				$this -> m_storage[$storageIndex] -> object_fields = json_encode($this -> m_storage[$storageIndex] -> object_fields);

			}

			return true;

		}
		
		return false;
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