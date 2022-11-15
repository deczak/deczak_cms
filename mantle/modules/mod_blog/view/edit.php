<input type="hidden" name="cms-object-id" value="<?php echo $object -> object_id; ?>">

<div class="page-edit-module-controls-panel blog-control" data-target-list="blog-list-<?php echo $object -> object_id; ?>">

	<div class="left">

		<div class="module-header" style="white-space:nowrap; padding:0 8px; font-weight:700; font-size:1.1em;">
			<i class="fas fa-blog"></i>&nbsp;&nbsp;&nbsp;Blog
		</div> 

	</div>

	<div class="right">

		<!-- TEMPLATE --------------------------->

		<label><?= CLanguage::string('M_BLOG_BEPE_BLOG_VIEWMODE'); ?></label>

		<?php foreach($avaiableTemplates as $template) { ?>

			<button class="ui button icon trigger-view-mode" data-template-id="<?= $template -> templateId; ?>" type="button" title="<?= $template -> templateName; ?>" style="height:29px;">
				<i class="<?= $template -> templateIcon; ?>"></i>
			</button>
		
		<?php } ?>

		<input type="hidden" name="blog-template" value="<?= $object -> params -> template; ?>">
		
	</div>

</div>

<div id="module-xhr-html-response-container-<?php echo $object -> object_id; ?>">

	<?php
	if($currentTemplate !== NULL)
	{
		$activeTemplate = current($currentTemplate);
		include $activeTemplate -> templateFilepath;
	}
	?>

</div>