<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeUsers.php';	

class 	modelUsers extends CModel
{
	public function
	__construct()
	{
		parent::__construct('Users');		
		$this -> m_sheme = new shemeUsers();
	}	

	public function
	load(&$_sqlConnection, CModelCondition $_condition = NULL, CModelComplementary $_complementary = NULL)
	{
		if(!parent::load($_sqlConnection, $_condition))
			return false;

		foreach($this -> m_storage as $dataset)
			$this -> decryptRawSQLDataset($dataset, $dataset -> user_id, ['user_name_first', 'user_name_last', 'user_mail']);

		return true;
	}

	public function
	insert(&$_sqlConnection, &$_dataset, &$_insertID)
	{
		$this -> encryptRawSQLDataset($_dataset, $_dataset['user_id'], ['user_name_first', 'user_name_last', 'user_mail']);		
		return parent::insert($_sqlConnection, $_dataset, $_insertID);
	}

	public function
	update(&$_sqlConnection, &$_dataset, CModelCondition $_condition = NULL)
	{
		if($_condition === NULL || !$_condition -> isSet()) return false;
		$userId = $_condition -> getConditionListValue('user_id');
		$this -> encryptRawSQLDataset($_dataset, $userId, ['user_name_first', 'user_name_last', 'user_mail']);
		return 	parent::update($_sqlConnection, $_dataset, $_condition);
	}	
}

/**
 * 	Parent class for the data class with toolkit functions. It get the child instance to access the child properties.

class 	toolkitUsers
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