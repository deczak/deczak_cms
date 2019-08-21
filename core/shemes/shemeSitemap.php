<?php


class shemeSitemap extends CSheme
{
	public function
	__construct()
	{
// vor dem kopieren CSheme anpassen

		parent::__construct();		

		$this -> setTable('dummy', true);
		
		$this -> addColumn('page_id'		, 'int'	);
		$this -> addColumn('page_version'	, 'string');
		$this -> addColumn('page_path'		, 'string');
		$this -> addColumn('page_title'		, 'string');
		$this -> addColumn('page_name'		, 'string');
		$this -> addColumn('page_language'	, 'string');
		$this -> addColumn('time_create'	, 'bigint');
		$this -> addColumn('time_update'	, 'bigint');
		$this -> addColumn('create_by'		, 'mediumint');
		$this -> addColumn('update_by'		, 'mediumint');
		$this -> addColumn('alternate_path'	, 'array');
		$this -> addColumn('node_id'		, 'int');
		$this -> addColumn('level'			, 'int');
		$this -> addColumn('offspring'		, 'int');
	}
}



		

?>

