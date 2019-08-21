<?php








class shemeSimple extends CSheme
{
	public function
	__construct()
	{
// vor dem kopieren CSheme anpassen

		parent::__construct();		

		$this -> setTable('tb_page_object_simple') ;
		
		#$this -> addColumn2('data_id'		, 'int') -> setAutoIncrement() -> setKey('data_id') -> setAttribute('data_id', 'UNSIGNED');



		$this -> addColumn('data_id'	, 'int') -> setAttribute('UNSIGNED') -> isAutoIncrement();
		$this -> addColumn('object_id'	, 'int') -> setAttribute('UNSIGNED');

		$this -> addColumn('body', 'text');

		$this -> addColumn('params', 'text');
	}
}



		

?>