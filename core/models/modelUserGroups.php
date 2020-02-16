<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeUserGroups.php';	

class 	modelUserGroups extends CModel
{

	public function
	__construct()
	{		
		parent::__construct();		

		$this -> m_sheme = new shemeUserGroups();
	}	

	public function
	load(&$_sqlConnection, CModelCondition $_condition = NULL)
	{
		$className		=	$this -> createClass($this -> m_sheme, 'userGroup', '');
		$tableName		=	$this -> m_sheme -> getTableName();

		$sqlString		=	"	SELECT		*
								FROM		$tableName
							".	($_condition != NULL ? $_condition -> getConditions($_sqlConnection, $_condition) : '');

		$sqlResult		=	 $_sqlConnection -> query($sqlString);

		while($sqlResult !== false && $sqlRow = $sqlResult -> fetch_assoc())
		{	
			$this -> m_storage[] = new $className($sqlRow, $this -> m_sheme -> getColumns());
		}
		
		return true;
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