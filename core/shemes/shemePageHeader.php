<?php


class shemePageHeader extends CSheme
{
	public function
	__construct()
	{
// vor dem kopieren CSheme anpassen

		parent::__construct();		

		$this -> setTable('tb_page_header');
		
		$this -> addColumn('data_id'		, 'int');
		#$this -> addColumn('data_id'		, 'int') -> setAutoIncrement('data_id') -> setKey('data_id') -> setAttribute('data_id', 'UNSIGNED');
		$this -> addColumn('page_id'		, 'int');
		$this -> addColumn('page_language'	, 'string');
		$this -> addColumn('page_title'		, 'string');
		$this -> addColumn('page_name'		, 'string');
		$this -> addColumn('page_description', 'string');
		$this -> addColumn('page_version'	, 'string');
		$this -> addColumn('node_id'		, 'int');

/*
		$this -> m_dataSheme['tb_page_header']['table']		= 	"tb_page_header";
		$this -> m_dataSheme['tb_page_header']['collate']	= 	"utf8mb4_unicode_ci";
		$this -> m_dataSheme['tb_page_header']['columns'][]	=	[ "name" => "data_id"      		, "type" => "int"   		, "ai" => true			, "key" => "UNIQUE" ];		
		$this -> m_dataSheme['tb_page_header']['columns'][]	=	[ "name" => "page_id"      		, "type" => "int"   		];
		$this -> m_dataSheme['tb_page_header']['columns'][]	=	[ "name" => "page_language" 	, "type" => "string"		, "length" 	=> "5" 	 	];
		$this -> m_dataSheme['tb_page_header']['columns'][]	=	[ "name" => "page_title" 		, "type" => "string"		, "length" 	=> "100"	, "null" => "NULL"   	 	];
		$this -> m_dataSheme['tb_page_header']['columns'][]	=	[ "name" => "page_name" 		, "type" => "string"		, "length" 	=> "100"	, "null" => "NULL"   	 	];
		$this -> m_dataSheme['tb_page_header']['columns'][]	=	[ "name" => "page_description" 	, "type" => "string"		, "length" 	=> "160"	, "null" => "NULL"   	 	];
		$this -> m_dataSheme['tb_page_header']['columns'][]	=	[ "name" => "page_version" 	 	, "type" => "mediumint"   	, "default" => 1  		];
		

		$this -> addColumn('node_id'		, 'int');			unsigned

		*/


	}
}



		

?>