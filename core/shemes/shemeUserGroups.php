<?php


class shemeUserGroups extends CSheme
{
	public function
	__construct()
	{
// vor dem kopieren CSheme anpassen

		parent::__construct();		

		$this -> setTable('tb_users_groups');
		
		$this -> addColumn('data_id'		, 'int');



		$this -> addColumn('user_id'	, 'string') ;
		$this -> addColumn('group_id'	, 'int') ;
		

	#	$this -> addColumn('group_name'		, 'string') -> isVirtual();
	#	$this -> addColumn('group_rights'	, 'text') -> isVirtual();

		
		/*


CREATE TABLE `tb_rights_groups` (
  `data_id` int(11) NOT NULL,
  `group_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_rights` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `tb_users_groups` (
  `data_id` int(11) NOT NULL,
  `user_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

	*/



	}
}



		

?>