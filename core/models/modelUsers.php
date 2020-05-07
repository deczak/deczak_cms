<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeUsers.php';	

class 	modelUsers extends CModel
{
	public function
	__construct()
	{
		parent::__construct('shemeUsers', 'users');	
	}	

	public function
	load(CDatabaseConnection &$_pDatabase, CModelCondition &$_pCondition = NULL, $_execFlags = NULL)
	{
		if(!parent::load($_pDatabase, $_pCondition, $_execFlags))
			return false;

		foreach($this -> m_resultList as $dataset)
			$this -> decryptRawSQLDataset($dataset, $dataset -> user_id, ['user_name_first', 'user_name_last', 'user_mail']);

		return true;
	}

	public function
	insert(CDatabaseConnection &$_pDatabase, array $_dataset, $_execFlags = NULL)
	{
		$this -> encryptRawSQLDataset($_dataset, $_dataset['user_id'], ['user_name_first', 'user_name_last', 'user_mail']);		
		return parent::insert($_pDatabase, $_dataset, $_execFlags);
	}

	public function
	update(CDatabaseConnection &$_pDatabase, array $_insertData, CModelCondition &$_pCondition, $_execFlags = NULL)
	{
		if($_pCondition === NULL || !$_pCondition -> isSet()) return false;
		$userId = $_pCondition -> getConditionListValue('user_id');
		$this -> encryptRawSQLDataset($_insertData, $userId, ['user_name_first', 'user_name_last', 'user_mail']);
		return 	parent::update($_pDatabase, $_insertData, $_pCondition, $_execFlags);
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