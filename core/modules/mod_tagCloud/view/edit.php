

<input type="hidden" name="cms-object-id" value="<?php echo $object -> object_id; ?>">

<?php

if(empty($object -> params -> template))
	$object -> params -> template = 'list';

if(empty($object -> params -> display_hidden))
	$object -> params -> display_hidden = 0;

$timestamp = time();

?>

<div class="ui" style="width:100%;"><div class="result-box" data-error="2">
Changes on those settings gets visible after reload		
</div></div>

<div class="ui options-container">

	<div class="input">
		<label>Root Page (Node-ID)</label>
		<input type="text" name="tagcloud-parent-node-id" value="<?= $object -> params -> parent_node_id ?? '' ?>">
	</div>


	<div class="input">
		<label>View</label>
		<div class="select-wrapper">
			<select name="tagcloud-template">
				<?php
				foreach($avaiableTemplates as $template)
					echo '<option '. ($object -> params -> template === $template -> templateId ? 'selected' : '') .' value="'. $template -> templateId .'">'. $template -> templateName .'</option>';
				?>
			</select>
		</div>
	</div>

</div>

<style>
	.options-container { display:flex;  padding:10px; flex-wrap:wrap; }
	.options-container > div { width:23%; min-width:250px; }
	.options-container > div:not(:last-child) { margin-right:15px; }
</style>

<?php
if($currentTemplate !== NULL)
{
	$activeTemplate = current($currentTemplate);
	include $activeTemplate -> templateFilepath;
}
?>
