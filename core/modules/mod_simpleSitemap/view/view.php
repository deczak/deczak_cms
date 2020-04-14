
<?php
if($currentTemplate !== NULL)
{
	$currentTemplate = current($currentTemplate);
	include $currentTemplate -> templateFilepath;
}
?>
