<?php


class shemePagePath extends CSheme
{
	public function
	__construct()
	{
// vor dem kopieren CSheme anpassen

		parent::__construct();		

		$this -> setTable('tb_page_path');
		
		$this -> addColumn('node_id'		, 'int');
	#	$this -> addColumn('node_id'		, 'int') -> setAutoIncrement('data_id') -> setKey('data_id') -> setAttribute('data_id', 'UNSIGNED');

		$this -> addColumn('page_id'		, 'int');
		$this -> addColumn('page_language'	, 'string');
		$this -> addColumn('page_path'		, 'string');
		$this -> addColumn('node_rgt'		, 'int');
		$this -> addColumn('node_lft'		, 'int');

/*

left right unsigned


		$this -> m_dataSheme['tb_page_path']['table']		= 	"tb_page_path";
		$this -> m_dataSheme['tb_page_path']['collate']		= 	"utf8mb4_unicode_ci";
		$this -> m_dataSheme['tb_page_path']['columns'][]	=	[ "name" => "node_id"     	 	, "type" => "int"   		, "ai" => true			, "key" => "UNIQUE" ];
		$this -> m_dataSheme['tb_page_path']['columns'][]	=	[ "name" => "page_id"      		, "type" => "int"   		];
		$this -> m_dataSheme['tb_page_path']['columns'][]	=	[ "name" => "page_language" 	, "type" => "string"		, "length" 	=> "5"		];
		$this -> m_dataSheme['tb_page_path']['columns'][]	=	[ "name" => "page_path" 		, "type" => "string"		, "length" 	=> "250"	];
		$this -> m_dataSheme['tb_page_path']['columns'][]	=	[ "name" => "node_rgt" 		, "type" => "int"		];
		$this -> m_dataSheme['tb_page_path']['columns'][]	=	[ "name" => "node_lft" 		, "type" => "int"		];

		*/


	}
}



		

?>