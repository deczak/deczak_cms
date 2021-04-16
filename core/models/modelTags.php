<?php

define('MODEL_TAGS_ALLOCATION_COUNT',0x101);

include_once CMS_SERVER_ROOT.DIR_CORE.DIR_SHEME.'shemeTags.php';	

class 	modelTags extends CModel
{
	public function
	__construct()
	{		
		parent::__construct('shemeTags', 'tags');
	}	

	public function
	load(CDatabaseConnection &$_pDatabase, CModelCondition $_pCondition = NULL, $_execFlags = NULL)
	{
		if($_execFlags & MODEL_TAGS_ALLOCATION_COUNT)
		{
			$conditionAllocation = new CModelCondition();
			$conditionAllocation -> where('tb_tags.tag_id', 'tb_tags_allocation.tag_id');

			$this -> addSelectColumns('tb_tags.*','COUNT(tb_tags_allocation.node_id) AS allocation');
			$this -> addRelation('left join', 'tb_tags_allocation', $conditionAllocation);
		}

		return parent::load($_pDatabase, $_pCondition, $_execFlags);
	}
}


/**
 * 	Parent class for the data class with toolkit functions. It get the child instance to access the child properties.

class 	toolkitTags
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