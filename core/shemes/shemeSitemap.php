<?php

##	Virtual Model for Sitemap

class shemeSitemap extends CSheme
{
	public function
	__construct()
	{
		parent::__construct();		

		$this -> setTable('dummy', true);
		
		$this -> addColumn('page_id'		, 'int'	);
		$this -> addColumn('page_version'	, 'string');
		$this -> addColumn('page_path'		, 'string');
		$this -> addColumn('page_title'		, 'string');
		$this -> addColumn('page_name'		, 'string');
		$this -> addColumn('page_language'	, 'string');
		$this -> addColumn('hidden_state'	, 'tinyint');
		$this -> addColumn('create_time'	, 'bigint');
		$this -> addColumn('update_time'	, 'bigint');
		$this -> addColumn('create_by'		, 'string');
		$this -> addColumn('update_by'		, 'string');
		$this -> addColumn('alternate_path'	, 'array');
		$this -> addColumn('node_id'		, 'int');
		$this -> addColumn('level'			, 'int');
		$this -> addColumn('offspring'		, 'int');
		$this -> addColumn('page_auth'		, 'string');
		$this -> addColumn('menu_follow'	, 'tinyint');
		$this -> addColumn('publish_from'	, 'bigint');
		$this -> addColumn('publish_until'	, 'bigint');
		$this -> addColumn('publish_expired', 'mediumint');
	}
}

?>