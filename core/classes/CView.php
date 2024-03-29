<?php

class	CView
{
	private	$m_templatePath;
	private	$m_dataInstances;
	private	$m_object;

	public function
	__construct(string $_templatePath, string $object_target = '', array $_dataInstances = [])
	{
		$this -> m_templatePath 			= $_templatePath;
		$this -> m_dataInstances 			= $_dataInstances;
		$this -> m_object['object_target']	= $object_target;
	}

	public function
	view()
	{
		$pDatabase 		= CDatabase::instance() -> getConnection(CFG::GET() -> MYSQL -> PRIMARY_DATABASE);
		$pageRequest 	= CPageRequest::instance();

		##	Required for XHR Functions

		if(!empty($this -> m_object['object_target'])) echo ' <script> CMS.MODULE_TARGET = "'. $this -> m_object['object_target']  .'";</script>';

		##	
		
		foreach($this -> m_dataInstances as $_dataKey => $_dataInst)
		{
			$$_dataKey = $_dataInst;
		}

		include $this -> m_templatePath .'.php';
	}

	public function
	getHTML($pageRequest = null)
	{
		$pDatabase 		= CDatabase::instance() -> getConnection(CFG::GET() -> MYSQL -> PRIMARY_DATABASE);

		if(is_file($this -> m_templatePath .'.php'))
		{
			ob_start();

			foreach($this -> m_dataInstances as $_dataKey => $_dataInst)
			{
				$$_dataKey = $_dataInst;
			}

			include $this -> m_templatePath .'.php';

			return ob_get_clean();
		}
	}

}

?>