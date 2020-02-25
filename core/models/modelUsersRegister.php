<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeUsersRegister.php';	

class 	modelUsersRegister extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('UserRegister');		
		$this -> m_sheme = new shemeUsersRegister();
	}	

	public function
	registerUserId(&$_sqlConnection, int $_userType, string $_userHash = NULL, string $_userName = NULL)
	{
		$freeUserId = date('yz').substr(rand(),0,5);

		while(true)
		{
			$freeUserId  = date('yz').substr(rand(),0,5);
			if($this -> isUnique($_sqlConnection, ['user_id' => $freeUserId]))
				break;
		}

		$insertData = [];
		$insertData['user_id']	 = $freeUserId;
		$insertData['user_type'] = $_userType;

		if($_userHash !== NULL && !empty($_userName))
		{
			$insertData['user_hash'] = $_userHash;
			$insertData['user_name'] = $_userName;
		}

		$registerId = 0;
		$this -> insert($_sqlConnection, $insertData, $registerId);

		return $freeUserId;
	}

	public function
	removeUserId(&$_sqlConnection, string $_userId)
	{
		$modelCondition = new CModelCondition();
		$modelCondition -> where('user_id', $_userId);
		$this -> delete($_sqlConnection, $modelCondition);
	}

	public function
	insert(&$_sqlConnection, &$_dataset, &$_insertID)
	{
		$this -> encryptRawSQLDataset($_dataset, $_dataset['user_id'], ['user_name']);		
		return parent::insert($_sqlConnection, $_dataset, $_insertID);
	}

	public function
	load(&$_sqlConnection, CModelCondition $_condition = NULL, CModelComplementary $_complementary = NULL)
	{
		if(!parent::load($_sqlConnection, $_condition))
			return false;

		foreach($this -> m_storage as $dataset)
			$this -> decryptRawSQLDataset($dataset, $dataset -> user_id, ['user_name']);

		return true;
	}

}

/**
 * 	Parent class for the data class with toolkit functions. It get the child instance to access the child properties.

class 	toolkitUserRegister
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