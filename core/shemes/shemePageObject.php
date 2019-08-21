<?php


class shemePageObject extends CSheme
{
	public function
	__construct()
	{
// vor dem kopieren CSheme anpassen

		parent::__construct();		

		$this -> setTable('tb_page_object');
		
		$this -> addColumn('object_id'		, 'int') -> setAttribute('UNSIGNED') -> isAutoIncrement();

		$this -> addColumn('node_id'		, 'int') -> setAttribute('UNSIGNED');
		$this -> addColumn('page_version'	, 'mediumint') -> setAttribute('UNSIGNED') -> setDefault('1');
		$this -> addColumn('module_id'		, 'string') -> setAttribute('UNSIGNED');
		$this -> addColumn('object_order_by', 'mediumint') -> setAttribute('UNSIGNED');
		$this -> addColumn('time_create'	, 'bigint') -> setAttribute('UNSIGNED') -> setDefault('NULL');	
		$this -> addColumn('time_update'	, 'bigint') -> setAttribute('UNSIGNED') -> setDefault('NULL');	
		$this -> addColumn('create_by'		, 'mediumint') -> setAttribute('UNSIGNED') -> setDefault('NULL');
		$this -> addColumn('update_by'		, 'mediumint') -> setAttribute('UNSIGNED') -> setDefault('NULL');
		$this -> addColumn('update_reason'	, 'string') -> setAttribute('UNSIGNED') -> setDefault('NULL') -> setLength('250');

		$this -> addColumn('instance'		, 'int') -> isVirtual();
		$this -> addColumn('body'	, 'string') -> isVirtual();
		$this -> addColumn('params'	, 'string') -> isVirtual();




		/*


	-	engine			vorgegeben und per funktion
	-	charset			vorgegeben
	-	collate			vorgegeben
	
	-	key verschiedene typen und namen		
				


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

}



		

?>