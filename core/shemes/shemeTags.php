<?php

class shemeTags extends CSheme
{
	public function
	__construct()
	{
		parent::__construct('tb_tags');	
		$this -> addColumn('tag_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();

		$this -> addColumn('tag_name', DB_COLUMN_TYPE_STRING) -> setLength(50);
		$this -> addColumn('tag_url', DB_COLUMN_TYPE_STRING) -> setLength(50);
		$this -> addColumn('tag_hidden', DB_COLUMN_TYPE_TINYINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('tag_disabled', DB_COLUMN_TYPE_TINYINT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
	}
}

?>