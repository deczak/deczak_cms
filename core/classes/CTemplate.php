<?php

class	CTemplates
{
	private	$m_templatesLocation;
	private $m_templates;

	public function
	__construct(string $_templatesLocation)
	{
		if(empty($_templatesLocation))
			trigger_error("CTemplates::__construct -- Templates location path is not set", E_USER_ERROR);

		$this -> m_templatesLocation	= $_templatesLocation;
		$this -> m_templates 			= [];
	}

	/**
	 * 	Read the templates directory
	 */
	public function
	searchTemplates(bool $_exludeHidden = false)
	{
		$_dirIterator = new DirectoryIterator( $this -> m_templatesLocation );

		foreach($_dirIterator as $_dirItem)
		{
			if($_dirItem -> isDot() || $_dirItem -> getType() !== 'dir')
				continue;


			if($_dirItem -> getFilename()[0] === '.') continue;

			$this -> readTemplateData( $_dirItem -> getFilename() , $_exludeHidden );
		}
	}

	/**
	 * 	Read template information from given template folder
	 */
	public function
	readTemplateData(string $_templateID, bool $_exludeHidden = false)
	{
		$_template = file_get_contents( $this -> m_templatesLocation . $_templateID .'/template.json');
		$_template = json_decode($_template);

		if($_template == NULL)
		{
			trigger_error("CTemplates::readTemplateData -- Template '. $_templateID .' json file is not valid");
			return;
		}

		if($_exludeHidden && $_template -> hide_on_selection === true) 
			return NULL;

		$this -> m_templates[$_templateID] = $_template ;
		return $this -> m_templates[$_templateID];
	}

	/**
	 * 	Returns all templates
	 */
	public function
	getTemplates()
	{
		return $this -> m_templates;
	}

	/**
	 * 	Returns defined template
	 */
	public function
	getTemplate(string $_templateID)
	{
		if(isset($this -> m_templates[$_templateID]))
			return $this -> m_templates[$_templateID];
		return NULL;
	}

}

?>