<?php

##	Virtual Model for Sitemap

class shemeSitemap extends CSheme
{
	public function
	__construct()
	{
        parent::__construct('sitemap', true);
		
		$this -> addColumn('page_id'		, DB_COLUMN_TYPE_INT);
		$this -> addColumn('page_version'	, DB_COLUMN_TYPE_STRING);
		$this -> addColumn('page_path'		, DB_COLUMN_TYPE_STRING);
		$this -> addColumn('page_title'		, DB_COLUMN_TYPE_STRING);
		$this -> addColumn('page_name'		, DB_COLUMN_TYPE_STRING);
		$this -> addColumn('page_language'	, DB_COLUMN_TYPE_STRING);
		$this -> addColumn('hidden_state'	, DB_COLUMN_TYPE_INT);
		$this -> addColumn('create_time'	, DB_COLUMN_TYPE_INT);
		$this -> addColumn('update_time'	, DB_COLUMN_TYPE_INT);
		$this -> addColumn('create_by'		, DB_COLUMN_TYPE_STRING);
		$this -> addColumn('update_by'		, DB_COLUMN_TYPE_STRING);
		$this -> addColumn('alternate_path'	, 0);
		$this -> addColumn('node_id'		, DB_COLUMN_TYPE_INT);
		$this -> addColumn('level'			, DB_COLUMN_TYPE_INT);
		$this -> addColumn('offspring'		, DB_COLUMN_TYPE_INT);
		$this -> addColumn('page_auth'		, DB_COLUMN_TYPE_STRING);
		$this -> addColumn('menu_follow'	, DB_COLUMN_TYPE_INT);
		$this -> addColumn('publish_from'	, DB_COLUMN_TYPE_INT);
		$this -> addColumn('publish_until'	, DB_COLUMN_TYPE_INT);
		$this -> addColumn('publish_expired', DB_COLUMN_TYPE_INT);
	}
}
