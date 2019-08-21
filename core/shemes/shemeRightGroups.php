<?php


class shemeRightGroups extends CSheme
{
	public function
	__construct()
	{
// vor dem kopieren CSheme anpassen

		parent::__construct();		

		$this -> setTable('tb_right_groups');
				
		$this -> addColumn('group_id'		, 'int');
	#	$this -> addColumn('data_id'		, 'int') -> setAutoIncrement('data_id') -> setKey('data_id') -> setAttribute('data_id', 'UNSIGNED');

		$this -> addColumn('group_name'		, 'string');
		$this -> addColumn('group_rights'	, 'text');
		
		
/*
CREATE TABLE `tb_rights_groups` (
  `group_id` int(11) NOT NULL,
  `group_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_rights` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
*/





	}
}



		

?>