<?php

class	shemeLanguages extends CSheme
{
	public function
	__construct()
	{
        parent::__construct('tb_languages');

		$this -> addColumn('data_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();
		$this -> addColumn('lang_key', DB_COLUMN_TYPE_STRING) -> setLength(4) -> setKey('UNIQUE');
		$this -> addColumn('lang_name', DB_COLUMN_TYPE_STRING) -> setLength(25);
		$this -> addColumn('lang_name_native', DB_COLUMN_TYPE_STRING) -> setLength(25);
		$this -> addColumn('lang_hidden', DB_COLUMN_TYPE_BOOL);
		$this -> addColumn('lang_locked', DB_COLUMN_TYPE_BOOL);
		$this -> addColumn('lang_default', DB_COLUMN_TYPE_BOOL);
		$this -> addColumn('lang_frontend', DB_COLUMN_TYPE_BOOL) -> setDefault('1');
		$this -> addColumn('lang_backend', DB_COLUMN_TYPE_BOOL) -> setDefault('0');

		$this -> addColumn('create_time', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('create_by', DB_COLUMN_TYPE_STRING) -> setLength(25);
		$this -> addColumn('update_time', DB_COLUMN_TYPE_BIGINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setDefault('0');
		$this -> addColumn('update_by', DB_COLUMN_TYPE_STRING) -> setLength(25) -> setDefault('NULL');
	}
}

?>