<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeUsersRegister.php';	

class 	modelUsersRegister extends CModel
{
	public function
	__construct()
	{		
        parent::__construct('shemeUsersRegister', 'userRegister');
	}	

	public function
	registerUserId(CDatabaseConnection &$_pDatabase, int $_userType, string $_userHash = NULL, string $_userName = NULL)
	{
		$freeUserId = date('yz').substr(rand(),0,5);

		while(true)
		{
			$freeUserId  = date('yz').substr(rand(),0,5);

			$modelCondition = new CModelCondition();
			$modelCondition -> where('user_id', $freeUserId);

			if($this -> unique($_pDatabase, $modelCondition))
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

		$this -> insert($_pDatabase, $insertData);

		return $freeUserId;
	}

	public function
	removeUserId(CDatabaseConnection &$_pDatabase, string $_userId)
	{
		$modelCondition = new CModelCondition();
		$modelCondition -> where('user_id', $_userId);
		$this -> delete($_pDatabase, $modelCondition);
	}

	public function
	insert(CDatabaseConnection &$_pDatabase, array $_dataset, $_execFlags = NULL)
	{
		$this -> encryptRawSQLDataset($_dataset, $_dataset['user_id'], ['user_name']);		
		return parent::insert($_pDatabase, $_dataset);
	}

	public function
	load(CDatabaseConnection &$_pDatabase, CModelCondition &$_pCondition = NULL, $_execFlags = NULL)
	{
		if(parent::load($_pDatabase, $_pCondition, $_execFlags) === false)
			return false;


		foreach($this -> m_resultList as $dataset)
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