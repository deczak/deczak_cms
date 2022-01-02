

<input type="hidden" name="cms-object-id" value="<?php echo $object -> object_id; ?>">



<?php

if(empty($object -> params -> template))
	$object -> params -> template = 'list';


$timestamp = time();

?>

<div class="page-edit-module-controls-panel simple-navigation-control" data-target-list="simple-navigation-list-<?php echo $object -> object_id; ?>">

	<div class="left">

		<div class="module-header" style="white-space:nowrap; padding:0 8px; font-weight:700; font-size:1.1em;">
			<i class="fas fa-sitemap"></i>&nbsp;&nbsp;&nbsp;Navigation
		</div> 


		<button class="ui button trigger-manage-pages" type="button">
			Manage pages
		</button>



	</div>

	<div class="right">

		<!-- TEMPLATE --------------------------->

		<label><?= CLanguage::string('M_BESIMPLENAV_VIEWMODE'); ?></label>

		<?php foreach($avaiableTemplates as $template) { ?>

			<button class="ui button icon trigger-view-mode" data-template-id="<?= $template -> templateId; ?>" type="button" title="<?= $template -> templateName; ?>">
				<i class="<?= $template -> templateIcon; ?>"></i>
			</button>
		
		<?php } ?>

		<input type="hidden" name="simple-navigation-template" value="<?= $object -> params -> template; ?>">
		
	</div>


	<div style="width:100%; background:white;" class="simple-navigation-manage-list simple-navigation-list-<?php echo $object -> object_id; ?> ignore-flex" hidden>
	

		<button class="ui button trigger-manage-pages-add-page" type="button" style="width:auto; font-size:0.85em; font-weight:400;">
			Add page
		</button>

		<button class="ui button trigger-manage-pages-add-subpagesby" type="button" style="width:auto; font-size:0.85em; font-weight:400;">
			Add subpages by
		</button>

		<br>

		<table class="ui table fluid ">
			<colgroup>
				<col class="fluid-80">
				<col class="fluid-10">
				<col class="fluid-10">
				<col style="width:29px;">
			</colgroup>
			<thead>
				<tr>
					<th>Page name</th>
					<th>Display hidden</th>
					<th>Type</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>

	</div>

</div>




	<div class="simple-navigation-items-list simple-navigation-list-<?php echo $object -> object_id; ?>">

	</div>

<script>

	document.addEventListener('DOMContentLoaded', function () {

		let snilNode = document.querySelector('.simple-navigation-items-list.simple-navigation-list-<?php echo $object -> object_id; ?>');
		let mecpNode = document.querySelector('.simple-navigation-manage-list.simple-navigation-list-<?php echo $object -> object_id; ?>');
		let rawNavigationItems_<?php echo $object -> object_id; ?> = <?= json_encode($object -> params -> nodeList); ?>;
		for(let node of rawNavigationItems_<?php echo $object -> object_id; ?>)
		{
			document.MECP_SimpleNavigation.addNavigationItem(
				snilNode,
				mecpNode,
				node['node-id'],
				node['page_name'],
				node['listing-hidden'],
				node['listing-type']
			);
		}

	}, false);

</script>

<?php
if($currentTemplate !== NULL)
{
	$activeTemplate = current($currentTemplate);
	include $activeTemplate -> templateFilepath;
}
?>
