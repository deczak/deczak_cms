<?php

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeSessions.php';	

class 	modelSessions extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('Session');		
		$this -> m_sheme = new shemeSessions();
	}	

	public function
	load(&$_sqlConnection, CModelCondition $_condition = NULL, CModelComplementary $_complementary = NULL)
	{
		$className	=	$this -> createClass($this -> m_sheme, $this -> m_className, '', $this -> m_additionalProperties);
		$tableName	=	$this -> m_sheme -> getTableName();

		$sqlString	=	"	SELECT		*
							FROM		$tableName
						".	($_condition != NULL ? $_condition -> getConditions($_sqlConnection, $_condition) : '');

		$sqlResult =	$_sqlConnection -> query($sqlString);

		while($sqlResult !== false && $sqlRow = $sqlResult -> fetch_assoc())
		{	
			$this -> m_storage[] = new $className($sqlRow, $this -> m_sheme -> getColumns());

			if($_complementary !== NULL)
			{
				$index = count($this -> m_storage) - 1;

				foreach($_complementary -> complementaryList as $complementarySet)
				{
					$propertyName = $complementarySet -> propertyName;
					$propertyCompare = $complementarySet -> propertyCompare;

				#	switch($complementarySet -> type)
				#	{
				#		case 'array' : $this -> m_storage[$index] -> $propertyName 	= []; break;
				#		case 'string': $this -> m_storage[$index] -> $propertyName 	= ''; break;
				#	}

					$this -> m_storage[$index] -> $propertyName = [];

					foreach($complementarySet -> storageInstance as $subDataset)
					{
						if($this -> m_storage[$index] -> $propertyCompare === $subDataset -> $propertyCompare)
						{

							switch($complementarySet -> type)
							{
								case 'array' : $this -> m_storage[$index] -> $propertyName[] 	= $subDataset; break;
								case 'string': $this -> m_storage[$index] -> $propertyName 		= $subDataset -> $propertyName; break;
							}
						}
					}
				}
			}
		}
		
		return true;
	}
}



/**
 * 	Parent class for the data class with toolkit functions. It get the child instance to access the child properties.

class 	toolkitSessions
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