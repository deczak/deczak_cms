<?php
if($currentTemplate !== NULL)
{
	$activeTemplate = current($currentTemplate);
	include $activeTemplate -> templateFilepath;
}
?>
