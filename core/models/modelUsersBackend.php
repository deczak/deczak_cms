<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeUsersBackend.php';	

class 	modelUsersBackend extends CModel
{
	private	$m_shemeUsersBackend;

	public function
	__construct()
	{		
		parent::__construct();		

		$this -> m_shemeUsersBackend = new shemeUsersBackend();
	}	

	public function
	load(&$_sqlConnection, array $_where = [])
	{
		$_className	=	$this -> createClass($this -> m_shemeUsersBackend,'userBackend');

		$_tableUsers	=	$this -> m_shemeUsersBackend -> getTableName();

		$_sqlWhere = [];

		foreach($_where as $_whereKey => $_whereColumn)
		{
			$_sqlWhere[] = " `$_whereKey` = '". $_sqlConnection -> real_escape_string($_whereColumn) . "' ";
		}

		$_sqlSelect	=	"	SELECT		$_tableUsers.data_id,
										$_tableUsers.user_id,
										$_tableUsers.user_name_first,
										$_tableUsers.user_name_last,
										$_tableUsers.user_mail,
										$_tableUsers.login_count,
										$_tableUsers.time_login,
										$_tableUsers.time_create,
										$_tableUsers.is_locked
							FROM		$_tableUsers	
						";

		if(count($_sqlWhere) !== 0)
			$_sqlSelect	.=	" WHERE ". implode(' AND ', $_sqlWhere);

		$_sqlResult	=	 $_sqlConnection -> query($_sqlSelect);

		if($_sqlResult -> num_rows === 0) return false;

		while($_sqlResult !== false && $_sqlRow = $_sqlResult -> fetch_assoc())
		{
			$_modelIndex = count($this -> m_storage);
			
			$this -> decryptRawSQLDataset($_sqlRow, $_sqlRow['user_id'], ['user_name_first', 'user_name_last', 'user_mail']);

			$this -> m_storage[] = new $_className($_sqlRow, $this -> m_shemeUsersBackend -> getColumns());







		}	
		return true;
	}

	public function
	insert(&$_sqlConnection, $_dataset)
	{
		$_tableUsers	=	$this -> m_shemeUsersBackend -> getTableName();

		$this -> encryptRawSQLDataset($_dataset, $_dataset['user_id'], ['user_name_first', 'user_name_last', 'user_mail']);

		$_dataset['time_create'] = time();

		$_className		=	$this -> createClass($this -> m_shemeUsersBackend,'userBackend');
		$_model 		= 	new $_className($_dataset, $this -> m_shemeUsersBackend -> getColumns());

		$_sqlString		=	"INSERT INTO $_tableUsers SET ";

		$_loopCounter = 0;
		foreach($this -> m_shemeUsersBackend -> getColumns() as $_column)
		{
			if(!$this -> m_shemeUsersBackend -> columnExists(true, $_column -> name)) continue;
			if($_column -> name === 'data_id') continue;
			if(isset($_column -> ignore)) continue;
			$_tmp		 = $_column -> name;
			$_sqlString .= ($_loopCounter != 0 ? ', ':'');
			$_sqlString .= "`".$_column -> name ."` = '". $_model -> $_tmp ."'";
			$_loopCounter++;
		}
		
		if($_sqlConnection -> query($_sqlString) !== false) return true;
		return false;
	}

	public function
	update(&$_sqlConnection, $_dataset)
	{
		$_tableUsers	=	$this -> m_shemeUsersBackend -> getTableName();

		$this -> encryptRawSQLDataset($_dataset, $_dataset['user_id'], ['user_name_first', 'user_name_last', 'user_mail']);
	
		$_sqlString		 =	"UPDATE $_tableUsers SET ";
		$_loopCounter 	= 0;
		foreach($_dataset as $_column => $_value)
		{	
			if($_column === 'user_id') continue;
			if(!$this -> m_shemeUsersBackend -> columnExists(true, $_column)) continue;
			$_sqlString  .= ($_loopCounter != 0 ? ', ':'');
			$_sqlString  .= "`". $_sqlConnection -> real_escape_string($_column) ."` = '". $_sqlConnection -> real_escape_string($_value) ."'";
			$_loopCounter++;
		}
		$_sqlString 	 .= " WHERE user_id = '". $_dataset['user_id'] ."'";

		if($_sqlConnection -> query($_sqlString) !== false) return true;
		return false;
	}
	
	public function
	delete( &$_sqlConnection, array $_where)
	{
		$_tableUsers	=	$this -> m_shemeUsersBackend -> getTableName();
		
		if(empty($_where)) return false;

		$_sqlString		 =	"DELETE FROM $_tableUsers WHERE ";
		$_loopCounter 	 = 0;
		foreach($_where as $_column => $_value)
		{
			$_sqlString .= ($_loopCounter != 0 ? ', ':'');
			$_sqlString .= "`".$_column ."` = '". $_value ."'";
			$_loopCounter++;
		}
		
		if($_sqlConnection -> query($_sqlString) !== false) return true;
		return false;
	}

	public function
	searchValue($_needle, string $_searchColumn, string $_returnColumn)
	{
		foreach($this -> m_storage as $_user)
		{
			if($_user -> $_searchColumn === $_needle) return $_user -> $_returnColumn;
		}
		return NULL;
	}

	public function
	isDatasetExists(&$_sqlConnection, string $_tableName, array $_where)
	{
		$_tableName	=	$this -> m_shemeUsersBackend -> getTableName();
		return parent::isDatasetExists($_sqlConnection, $_tableName, $_where);
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