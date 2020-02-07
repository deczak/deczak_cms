<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeUsersBackend.php';	

class 	modelUsersBackend extends CModel
{
	public function
	__construct()
	{			
		parent::__construct('userBackend');		
		$this -> m_sheme = new shemeUsersBackend();
	}	

	public function
	load(&$_sqlConnection, CModelCondition $_condition = NULL)
	{
		$className	=	$this -> createClass($this -> m_sheme, $this -> m_className, '', $this -> m_additionalProperties);
		$tableName	=	$this -> m_sheme -> getTableName();

		$_sqlSelect	=	"	SELECT		$tableName.data_id,
										$tableName.user_id,
										$tableName.user_name_first,
										$tableName.user_name_last,
										$tableName.user_mail,
										$tableName.login_count,
										$tableName.time_login,
										$tableName.create_time,
										$tableName.update_time,
										$tableName.create_by,
										$tableName.update_by,
										$tableName.language,
										$tableName.is_locked
							FROM		$tableName	
						".	($_condition != NULL ? $_condition -> getConditions($_sqlConnection, $_condition) : '');
			
		$_sqlResult	=	 $_sqlConnection -> query($_sqlSelect);
		
		if($_sqlResult -> num_rows === 0) return false;

		while($_sqlResult !== false && $_sqlRow = $_sqlResult -> fetch_assoc())
		{
			$this -> decryptRawSQLDataset($_sqlRow, $_sqlRow['user_id'], ['user_name_first', 'user_name_last', 'user_mail']);

			$this -> m_storage[] = new $className($_sqlRow, $this -> m_sheme -> getColumns());
		}	
		return true;
	}

	public function
	insert(&$_sqlConnection, &$_dataset, &$_insertID)
	{
		$className		=	$this -> createClass($this -> m_sheme,'userBackend');
		$tableName	=	$this -> m_sheme -> getTableName();

		$this -> encryptRawSQLDataset($_dataset, $_dataset['user_id'], ['user_name_first', 'user_name_last', 'user_mail']);

		$model 		= 	new $className($_dataset, $this -> m_sheme -> getColumns());

		$sqlString		=	"INSERT INTO $tableName SET ";

		$loopCounter = 0;
		foreach($this -> m_sheme -> getColumns() as $column)
		{
			if($column -> isVirtual) continue;
			$_tmp		 = $column -> name;
			$sqlString .= ($loopCounter != 0 ? ', ':'');
			$sqlString .= "`".$column -> name ."` = '". $model -> $_tmp ."'";
			$loopCounter++;
		}
		
		if($_sqlConnection -> query($sqlString) !== false) return true;
		return false;
	}

	public function
	update(&$_sqlConnection, &$_dataset, CModelCondition $_condition = NULL)
	{
		if($_condition === NULL || !$_condition -> isSet()) return false;

		$userId = $_condition -> getConditionListValue('user_id');

		$this -> encryptRawSQLDataset($_dataset, $userId, ['user_name_first', 'user_name_last', 'user_mail']);

		$tableName	=	$this -> m_sheme -> getTableName();
	
		$sqlString		 =	"UPDATE $tableName SET ";
		$loopCounter 	= 0;
		foreach($_dataset as $column => $_value)
		{	
			if(!$this -> m_sheme -> columnExists(true, $column)) continue;
			$sqlString  .= ($loopCounter != 0 ? ', ':'');
			$sqlString  .= "`". $_sqlConnection -> real_escape_string($column) ."` = '". $_sqlConnection -> real_escape_string($_value) ."'";
			$loopCounter++;
		}

		$sqlString	.=	$_condition -> getConditions($_sqlConnection, $_condition);

		if($_sqlConnection -> query($sqlString) !== false) return true;
		return false;
	}
}

/**
 * 	Parent class for the data class with toolkit functions. It get the child instance to access the child properties.

class 	toolkitUsersBackend
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