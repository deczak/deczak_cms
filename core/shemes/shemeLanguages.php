<?php

class	shemeLanguages extends CSheme
{
	public function
	__construct()
	{
        parent::__construct('tb_languages');

		$this -> addColumn('data_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();
		$this -> addColumn('lang_key', DB_COLUMN_TYPE_STRING) -> setLength(4) -> setKey('UNIQUE') -> setSystemId();
		$this -> addColumn('lang_name', DB_COLUMN_TYPE_STRING) -> setLength(25);
		$this -> addColumn('lang_name_native', DB_COLUMN_TYPE_STRING) -> setLength(25);
		$this -> addColumn('lang_hidden', DB_COLUMN_TYPE_BOOL);
		$this -> addColumn('lang_locked', DB_COLUMN_TYPE_BOOL);
		$this -> addColumn('lang_default', DB_COLUMN_TYPE_BOOL);
		$this -> addColumn('lang_frontend', DB_COLUMN_TYPE_BOOL) -> setDefault('1');
		$this -> addColumn('lang_backend', DB_COLUMN_TYPE_BOOL) -> setDefault('0');

		$this -> addColumnGroup(DB_COLUMN_GROUP_CREATE);
		$this -> addColumnGroup(DB_COLUMN_GROUP_UPDATE);
		$this -> addColumnGroup(DB_COLUMN_GROUP_LOCK);
	}
}
