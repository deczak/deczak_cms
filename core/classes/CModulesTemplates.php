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
	load(string $_moduleRootDir, string $_moduleName, string $_templateName = '')
	{
		{	##	read module standard templates

			$templatesLocation = $_moduleRootDir.$_moduleName.'/template/';

			if(is_dir($templatesLocation)) 
			{
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
							$this -> templatesList[$templateJson -> templateId] = new moduleTemplate(
								$templateLocation . 'template.php',
								$templateJson -> templateId,
								$templateJson -> templateName,
								$templateJson -> templateDescription,
								$templateJson -> templateIcon
							);
						}
					}
				}	
			}	
		}

		{	##	read root template dir for module templates

			$templatesLocation = CMS_SERVER_ROOT.DIR_TEMPLATES_MODULE.$_moduleName.'/';

			if(is_dir($templatesLocation)) 
			{
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
							$this -> templatesList[$templateJson -> templateId] = new moduleTemplate(
								$templateLocation . 'template.php',
								$templateJson -> templateId,
								$templateJson -> templateName,
								$templateJson -> templateDescription,
								$templateJson -> templateIcon
							);
						}
					}
				}	
			}	
		}

		{	##	Find wanted module truncate other

			if(!empty($_templateName) && isset($this -> templatesList[$_templateName]))
				$this -> templatesList = [$this -> templatesList[$_templateName]];

		}
	}

}

class	moduleTemplate
{
	public	$templateFilepath;
	public	$templateId;
	public	$templateName;
	public	$templateDescription;
	public	$templateIcon;

	public function
	__construct(string $_templateFilepath, string $_templateId, string $_templateName, string $_templateDescription, string $_templateIcon)
	{
		$this -> templateFilepath		= $_templateFilepath;
		$this -> templateId				= $_templateId;
		$this -> templateName			= $_templateName;
		$this -> templateDescription	= $_templateDescription;
		$this -> templateIcon			= $_templateIcon;
	}
}

?>