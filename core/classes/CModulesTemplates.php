<?php

class CModulesTemplates
{

	public $dataLocation;
	public $templatesList;

	public function
	__construct()
	{
		$this -> dataLocation 	= CMS_SERVER_ROOT.DIR_DATA .'modules/';
		$this -> templatesList	= [];
	}




	public function
	load(string $_moduleName, string $_templateName = '')
	{
		$templatesLocation = $this -> dataLocation .$_moduleName.'/templates/';

		if(!empty($_templateName))
		{
			##	read defined template

			$targetTemplate = $templatesLocation.$_templateName.'/';

			if(file_exists($targetTemplate))
			{
				$templateJson 		= file_get_contents($targetTemplate . 'template.json');

				if($templateJson !== false)
				{
					$templateJson = json_decode($templateJson);

					if($templateJson !== NULL)
					{
						$this -> templatesList[] = new moduleTemplate(
																		$targetTemplate . 'template.php',
																		$templateJson -> templateId,
																		$templateJson -> templateName,
																		$templateJson -> templateDescription
																	 );
					}
				}
			}
			else
			{
				$this -> templatesList = NULL;
			}
		}
		else
		{
			##	read all templates withouth source

			$_dirIterator = new DirectoryIterator($templatesLocation);

			foreach($_dirIterator as $_dirItem)
			{
				if($_dirItem -> isDot() || $_dirItem -> getType() !== 'dir')
					continue;

				if($_dirItem -> getFilename()[0] === '.') continue;

				$templateLocation 	= $templatesLocation . $_dirItem -> getFilename() . '/';
				$templateJson 		= file_get_contents($templateLocation . 'template.json');

				if($templateJson !== false)
				{
					$templateJson = json_decode($templateJson);

					if($templateJson !== NULL)
					{
						$this -> templatesList[] = new moduleTemplate(
																		$templateLocation . 'template.php',
																		$templateJson -> templateId,
																		$templateJson -> templateName,
																		$templateJson -> templateDescription
																	 );
					}
				}
			}
		}
	}
}

class	moduleTemplate
{
	public	$templateFilepath;
	public	$templateId;
	public	$templateName;
	public	$templateDescription;

	public function
	__construct(string $_templateFilepath, string $_templateId, string $_templateName, string $_templateDescription)
	{
		$this -> templateFilepath		= $_templateFilepath;
		$this -> templateId				= $_templateId;
		$this -> templateName			= $_templateName;
		$this -> templateDescription	= $_templateDescription;
	}
}

?>