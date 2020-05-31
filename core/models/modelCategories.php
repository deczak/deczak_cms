<?php

define('MODEL_CATEGORIES_ALLOCATION_COUNT',0x101);

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeCategories.php';	

class 	modelCategories extends CModel
{
	public function
	__construct()
	{
		parent::__construct('shemeCategories', 'categories');
	}	

	public function
	load(CDatabaseConnection &$_pDatabase, CModelCondition &$_pCondition = NULL, $_execFlags = NULL)
	{
		if($_execFlags & MODEL_CATEGORIES_ALLOCATION_COUNT)
		{
			$conditionAllocation = new CModelCondition();
			$conditionAllocation -> where('tb_categories.category_id', 'tb_categories_allocation.category_id');

			$this -> addSelectColumns('tb_categories.*','COUNT(tb_categories_allocation.node_id) AS allocation');
			$this -> addRelation('left join', 'tb_categories_allocation', $conditionAllocation);

		}

		return parent::load($_pDatabase, $_pCondition, $_execFlags);
	}
}


/**
 * 	Parent class for the data class with toolkit functions. It get the child instance to access the child properties.

class 	toolkitCategories
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