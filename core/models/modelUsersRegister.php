<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeUsersRegister.php';	

class 	modelUserRegister extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('UserRegister');		
		$this -> m_sheme = new shemeUserRegister();
	}	

	public function
	registerUserId(&$_sqlConnection, int $_userType)
	{
		$freeUserId = substr(rand(),0,10);

		while(true)
		{
			$freeUserId  = substr(rand(),0,10);
			if($this -> isUnique($_sqlConnection, ['user_id' => $freeUserId]))
				break;
		}

		$insertData = [];
		$insertData['user_id']	 = $freeUserId;
		$insertData['user_type'] = $_userType;

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