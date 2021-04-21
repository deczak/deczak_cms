<?php

class shemeSessionsAccessArchiv extends CSheme
{
	public function
	__construct()
	{
		parent::__construct('tb_sessions_access_archiv');		

		
		$this -> addColumn('data_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement();

		$this -> addColumn('session_id', DB_COLUMN_TYPE_STRING) -> setLength(65);
		$this -> addColumn('node_id', DB_COLUMN_TYPE_INT) -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('time_access', DB_COLUMN_TYPE_BIGINT)  -> setAttribute(DB_COLUMN_ATTR_UNSIGNED);
		$this -> addColumn('referer', DB_COLUMN_TYPE_STRING) -> setLength(250);

		#$this -> addColumn('page_title', DB_COLUMN_TYPE_STRING) -> setVirtual() -> setDefault('');
	}
}
