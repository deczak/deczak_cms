

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

<div class="ui" style="display:flex; justify-content:space-between; padding:10px;">

	<div class="input" style="width:23%;">
		<label>View</label>
		<div class="select-wrapper">
			<select name="sitemap-template">
				<?php
				foreach($avaiableTemplates as $template)
					echo '<option '. ($object -> params -> template === $template -> templateId ? 'selected' : '') .' value="'. $template -> templateId .'">'. $template -> templateName .'</option>';
				?>
			</select>
		</div>
	</div>


	<div class="input" style="width:23%;">
		<label>Show hidden</label>
		<div class="select-wrapper">
			<select name="sitemap-display-hidden">
				<option <?= ($object -> params -> display_hidden === '0' ? 'selected' : ''); ?> value="0">No</option>
				<option <?= ($object -> params -> display_hidden === '1' ? 'selected' : ''); ?> value="1">Yes</option>
			</select>
		</div>
	</div>

	<div class="input" style="width:23%;">
	</div>

	<div class="input" style="width:23%;">
	</div>

</div>

<?php
if($currentTemplate !== NULL)
{
	$currentTemplate = current($currentTemplate);
	include $currentTemplate -> templateFilepath;
}
?>
