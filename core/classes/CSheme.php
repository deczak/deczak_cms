<?php


class CShemeColumn
{
	public	$name;
	public	$type;
	public	$isVirtual;
	public	$isAutoIncrement;
	public	$attribute;
	public	$defaultValue;
	public	$isNull;
	public	$length;

	public function
	__construct(string $name, string $type)
	{
		$this -> name 		= $name;
		$this -> type 		= $type;
		$this -> isVirtual 	= false;

		$this -> isAutoIncrement = false;
		$this -> attribute 		 = false;
		$this -> defaultValue 	 = NULL;
		$this -> isNull		 	 = false;
		$this -> length			 = 1;
	}

	public function
	isVirtual(bool $isVirtual = true)
	{
		$this -> isVirtual = $isVirtual;
		return $this;
	}

	public function
	isAutoIncrement()
	{
		$this -> isAutoIncrement = true;
		return $this;
	}

	public function
	isNull()
	{
		$this -> isNull = 'NULL';
		return $this;
	}

	public function
	setDefault(string $_default)
	{
		if($_default === 'NULL')
		{
			$this -> isNull = true;
			return $this;
		}

		$this -> defaultValue = $_default;
		return $this;
	}

	public function
	setLength($_length)
	{
		$this -> length = $_length;
		return $this;
	}

	public function
	setAttribute(string $attribute)
	{
		if(empty($attribute))
			return $this;

		$this -> attribute = $attribute;
		return $this;
	}
}


class CSheme
{
	protected	$m_sheme;

	public function
	__construct()
	{
		$this -> m_tableName		= '';
		$this -> m_collate = '';
		$this -> m_charset = '';
		$this -> m_sheme['columns'] = [];
	}

	protected function
	setTable(string $_tableName, bool $_isVirtual = false)
	{
		$this -> m_tableName 		= $_tableName;
		$this -> m_collate 			= SQL::TABLE_COLLATE;
		$this -> m_charset 			= SQL::TABLE_CHARSET;
		$this -> m_sheme['virtual']		= $_isVirtual;
		return $this;
	}
	
	protected function
	&addColumn(string $_columnName, string $_dataType)
	{
		$this -> m_sheme['columns'][$_columnName] = new CShemeColumn($_columnName, $_dataType);
		return $this -> m_sheme['columns'][$_columnName];
	}

	public function
	&getColumns()
	{
		return $this -> m_sheme['columns'];
	}

	public function
	getTableName()
	{
		return $this -> m_tableName;
	}

	public function
	columnExists(bool $_excludeVirtual, string $_columnName)
	{
		foreach($this -> m_sheme['columns'] as $_column)
		{
			if($_excludeVirtual && $_column -> name === $_columnName && $_column -> isVirtual) return false;
			if($_column -> name === $_columnName) return true;
		}
		return false;
	}

	public function
	dropTable(&$_sqlConnection)
	{
		if(empty($_sqlConnection) || !property_exists($_sqlConnection, 'errno') || $_sqlConnection -> errno != 0)
			trigger_error("CImperator::__construct -- Invalid SQL connection", E_USER_ERROR);

		$_sqlConnection -> query("DROP TABLE IF EXISTS `". $this -> m_tableName ."`");	
	}

	public function
	createTable(&$_sqlConnection)
	{
		if(empty($_sqlConnection) || !property_exists($_sqlConnection, 'errno') || $_sqlConnection -> errno != 0)
			trigger_error("CImperator::__construct -- Invalid SQL connection", E_USER_ERROR);

/*



CREATE TABLE `tb_page_object` (
  `node_id` int(10) UNSIGNED NOT NULL,
  `page_version` mediumint(9) UNSIGNED NOT NULL DEFAULT '1',
  `module_id` mediumint(9) UNSIGNED NOT NULL,
  `object_id` int(11) UNSIGNED NOT NULL,
  `object_order_by` mediumint(9) UNSIGNED NOT NULL,
  `time_create` bigint(20) UNSIGNED DEFAULT NULL,
  `time_update` bigint(20) UNSIGNED DEFAULT NULL,
  `create_by` mediumint(8) UNSIGNED DEFAULT NULL,
  `update_by` mediumint(8) UNSIGNED DEFAULT NULL,
  `update_reason` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `tb_page_object`
  ADD PRIMARY KEY (`object_id`),
  ADD KEY `node_id` (`node_id`);



--
ALTER TABLE `tb_page_object`
  MODIFY `object_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

*/

	}

	public function
	truncateTable(&$_sqlConnection)
	{		
		if(empty($_sqlConnection) || !property_exists($_sqlConnection, 'errno') || $_sqlConnection -> errno != 0)
			trigger_error("CImperator::__construct -- Invalid SQL connection", E_USER_ERROR);
	}

}

?>