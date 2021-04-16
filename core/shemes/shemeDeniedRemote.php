<?php

class shemeDeniedRemote extends CSheme
{
	public function
	__construct()
	{
		parent::__construct('tb_denied_remote');	
		
		$this -> addColumn('data_id', DB_COLUMN_TYPE_INT) -> setKey('PRIMARY') -> setAttribute(DB_COLUMN_ATTR_UNSIGNED) -> setAutoIncrement() -> setSystemId();
		
		$this -> addColumn('denied_ip', DB_COLUMN_TYPE_STRING) -> setLength(40);
		$this -> addColumn('denied_desc', DB_COLUMN_TYPE_STRING) -> setLength(250);

		$this -> addColumnGroup(DB_COLUMN_GROUP_CREATE);
		$this -> addColumnGroup(DB_COLUMN_GROUP_UPDATE);
		$this -> addColumnGroup(DB_COLUMN_GROUP_LOCK);

	}
}

?>