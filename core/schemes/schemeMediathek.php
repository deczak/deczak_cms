<?php

class schemeMediathek extends CScheme
{
	public function
	__construct()
	{
        parent::__construct('tb_mediathek');
		
		$this -> addColumn('media_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement() -> setSystemId();

		$this -> addColumn('media_filename', DB_COLUMN_TYPE_STRING) -> setLength(200);
		$this -> addColumn('media_title', DB_COLUMN_TYPE_STRING) -> setLength(150);
		$this -> addColumn('media_caption', DB_COLUMN_TYPE_STRING) -> setLength(150);
		$this -> addColumn('media_author', DB_COLUMN_TYPE_STRING) -> setLength(150);
		$this -> addColumn('media_notice', DB_COLUMN_TYPE_STRING) -> setLength(150);
		$this -> addColumn('media_license', DB_COLUMN_TYPE_STRING) -> setLength(150);
		$this -> addColumn('media_license_url', DB_COLUMN_TYPE_STRING) -> setLength(150);
		$this -> addColumn('media_gear', DB_COLUMN_TYPE_JSON);
		$this -> addColumn('media_gear_settings', DB_COLUMN_TYPE_JSON);
		$this -> addColumn('media_size', DB_COLUMN_TYPE_STRING) -> setLength(50);
		$this -> addColumn('media_extension', DB_COLUMN_TYPE_STRING) -> setLength(50);
		$this -> addColumn('media_mime', DB_COLUMN_TYPE_STRING) -> setLength(50);

		$this -> addColumnGroup(DB_COLUMN_GROUP_CREATE);
		$this -> addColumnGroup(DB_COLUMN_GROUP_UPDATE);
		$this -> addColumnGroup(DB_COLUMN_GROUP_LOCK);
	}
}
