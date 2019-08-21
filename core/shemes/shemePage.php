<?php


class shemePage extends CSheme
{
	public function
	__construct()
	{
// vor dem kopieren CSheme anpassen

		parent::__construct();		

		$this -> setTable('tb_page');
		
		$this -> addColumn('data_id'		, 'int');
	#	$this -> addColumn('data_id'		, 'int') -> setAutoIncrement('data_id') -> setKey('data_id') -> setAttribute('data_id', 'UNSIGNED');

		$this -> addColumn('page_id'		, 'int');
		$this -> addColumn('page_version'	, 'mediumint');
		$this -> addColumn('page_template'	, 'string');
		$this -> addColumn('time_create'	, 'bigint');
		$this -> addColumn('time_update'	, 'bigint');
		$this -> addColumn('create_by'		, 'mediumint');
		$this -> addColumn('update_by'		, 'mediumint');
		$this -> addColumn('update_reason'	, 'string');
		$this -> addColumn('node_id'		, 'int');

		$this -> addColumn('page_path'		, 'string') -> isVirtual();
		$this -> addColumn('page_title'		, 'string') -> isVirtual();
		$this -> addColumn('page_name'		, 'string') -> isVirtual();
		$this -> addColumn('page_language'	, 'string') -> isVirtual();
		$this -> addColumn('page_description', 'string') -> isVirtual();
		$this -> addColumn('alternate_path'	, 'array') -> isVirtual();
		$this -> addColumn('objects'		, 'array') -> isVirtual();
		$this -> addColumn('crumb_path'		, 'array') -> isVirtual();



/*


		$this -> m_dataSheme['tb_page']['table']			= 	"tb_page";
		$this -> m_dataSheme['tb_page']['collate']			= 	"utf8mb4_unicode_ci";
		$this -> m_dataSheme['tb_page']['columns'][]		=	[ "name" => "data_id"      		, "type" => "int"			, "ai" => true			, "key" => "UNIQUE" ];
		$this -> m_dataSheme['tb_page']['columns'][]		=	[ "name" => "page_id"      		, "type" => "int"   		];
		$this -> m_dataSheme['tb_page']['columns'][]		=	[ "name" => "page_version" 	 	, "type" => "mediumint"   	, "default" => 1  		];
		$this -> m_dataSheme['tb_page']['columns'][]		=	[ "name" => "page_template" 	, "type" => "string"   		, "length" 	=> "50" 	];
		$this -> m_dataSheme['tb_page']['columns'][]		=	[ "name" => "time_create"		, "type" => "bigint"  		];
		$this -> m_dataSheme['tb_page']['columns'][]		=	[ "name" => "time_update"		, "type" => "bigint"  		];
		$this -> m_dataSheme['tb_page']['columns'][]		=	[ "name" => "create_by"			, "type" => "mediumint"  	];
		$this -> m_dataSheme['tb_page']['columns'][]		=	[ "name" => "update_by"			, "type" => "mediumint"  	];
		$this -> m_dataSheme['tb_page']['columns'][]		=	[ "name" => "update_reason"		, "type" => "string"  		, "length" 	=> "250" 	];


		$this -> m_dataSheme['page']['columns'][]	=	[ "name" => "page_path" 		, "type" => "string"		];
		$this -> m_dataSheme['page']['columns'][]	=	[ "name" => "page_title" 		, "type" => "string"		];
		$this -> m_dataSheme['page']['columns'][]	=	[ "name" => "page_name" 		, "type" => "string"		];
		$this -> m_dataSheme['page']['columns'][]	=	[ "name" => "page_language" 	, "type" => "string"		];
		$this -> m_dataSheme['page']['columns'][]	=	[ "name" => "create_by"			, "type" => "mediumint"  	];
		$this -> m_dataSheme['page']['columns'][]	=	[ "name" => "update_by"			, "type" => "mediumint"  	];
		$this -> m_dataSheme['page']['columns'][]	=	[ "name" => "alternate_path" 	, "type" => "array"			];					
		$this -> m_dataSheme['page']['columns'][]	=	[ "name" => "objects" 			, "type" => "array"			];		




		*/


	}
}



		

?>