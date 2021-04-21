<?php

class shemePage extends CSheme
{
	public function
	__construct()
	{
		parent::__construct('tb_page');
		
		$this -> addColumn('data_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();

		$this -> addColumn('node_id', DB_COLUMN_TYPE_INT) -> setIndex('INDEX') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('page_id', DB_COLUMN_TYPE_INT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('page_version', DB_COLUMN_TYPE_MEDIUMINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);

		$this -> addColumn('page_template', DB_COLUMN_TYPE_STRING) -> setLength(50);
		$this -> addColumn('update_reason', DB_COLUMN_TYPE_STRING) -> setLength(250);
		$this -> addColumn('hidden_state', DB_COLUMN_TYPE_TINYINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
		$this -> addColumn('cache_disabled', DB_COLUMN_TYPE_TINYINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
		$this -> addColumn('crawler_index', DB_COLUMN_TYPE_TINYINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('1');
		$this -> addColumn('crawler_follow', DB_COLUMN_TYPE_TINYINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('1');
		$this -> addColumn('menu_follow', DB_COLUMN_TYPE_TINYINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('1');

		$this -> addColumn('publish_from', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
		$this -> addColumn('publish_until', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
		$this -> addColumn('publish_expired', DB_COLUMN_TYPE_TINYINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');

		$this -> addColumn('page_auth', DB_COLUMN_TYPE_STRING) -> setLength(25)-> setDefault('NULL');


		$this -> addColumn('create_time', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('create_by', DB_COLUMN_TYPE_STRING) -> setLength(25);
		$this -> addColumn('update_time', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
		$this -> addColumn('update_by', DB_COLUMN_TYPE_STRING) -> setLength(25) -> setDefault('NULL');

		$this -> addColumn('page_path', DB_COLUMN_TYPE_STRING) -> setVirtual();
		$this -> addColumn('page_title', DB_COLUMN_TYPE_STRING) -> setVirtual();
		$this -> addColumn('page_name', DB_COLUMN_TYPE_STRING) -> setVirtual();
		$this -> addColumn('page_language', DB_COLUMN_TYPE_STRING) -> setVirtual();
		$this -> addColumn('page_description', DB_COLUMN_TYPE_STRING) -> setVirtual();
		$this -> addColumn('alternate_path', 0) -> setVirtual();
		$this -> addColumn('page_categories', 0) -> setVirtual();
		$this -> addColumn('page_tags', 0) -> setVirtual();
		$this -> addColumn('objects', 0) -> setVirtual();
	}
}
