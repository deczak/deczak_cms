<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeUserGroups.php';	

class 	modelUserGroups extends CModel
{
	public	$m_sheme;

	public function
	__construct()
	{		
		parent::__construct();		

		$this -> m_sheme = new shemeUserGroups();
	}	

	public function
	load(&$_sqlConnection, array $_where = [])
	{


		$_className	=	$this -> createClass($this -> m_sheme, 'userGroup', '', $this -> m_additionalProperties);

		$_tableName	=	$this -> m_sheme -> getTableName();

		$_sqlWhere = [];

		foreach($_where as $_whereKey => $_whereColumn)
		{
			$_sqlWhere[] = " `$_whereKey` = '". $_sqlConnection -> real_escape_string($_whereColumn) . "' ";
		}



		$_sqlString	=	"	SELECT		*
							FROM		$_tableName								
						";


		foreach($this -> m_tableRelations as $_relation)
		{

			$_sqlString	.=	" ". $_relation['join'] ." ". $_relation['shemeInstance'] -> getTableName();

			if(count($_relation['on']) === 2)
				$_sqlString	.=	" ON ". $_relation['shemeInstance'] -> getTableName() .".". $_relation['on'][0] ." = ". $_tableName .".". $_relation['on'][1];
			else							
				$_sqlString	.=	" ON ". $_relation['shemeInstance'] -> getTableName() .".". $_relation['on'][0] ." = ". $_tableName .".". $_relation['on'][0];
		

		}







		if(count($_sqlWhere) !== 0)
			$_sqlString	.=	" WHERE ". implode(' AND ', $_sqlWhere);



			$_sqlUserGroupRes	=	 $_sqlConnection -> query($_sqlString);

			while($_sqlUserGroupRes !== false && $_sqlUserGroup = $_sqlUserGroupRes -> fetch_assoc())
			{	
				$_modelIndex = count($this -> m_storage);
				
				$this -> m_storage[] = new $_className($_sqlUserGroup, $this -> m_sheme -> getColumns());


			#	$this -> includeAdditionalData($this -> m_storage[$_modelIndex], $_includeDataInstance, $_includeOn, $_includeValues);




			}

		
		return true;
	}

	public function
	insert(&$_sqlConnection, $_dataset)
	{

	}

	public function
	update(&$_sqlConnection, $_dataset)
	{

	}
	
	public function
	delete( &$_sqlConnection, array $_where)
	{
	
	}
/*
	public function
	includeDataByComparsion($_includeDataInstance = NULL, string $_includeOn = '', array $_includeValues = [])
	{
		foreach($this -> m_storage as &$_dataInstance)
			$this -> _includeDataByComparsion($_dataInstance, $_includeDataInstance, $_includeOn, $_includeValues);
	}
*/

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