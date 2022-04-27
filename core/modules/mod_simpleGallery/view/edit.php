<input type="hidden" name="cms-object-id" value="<?php echo $object -> object_id; ?>">



<div class="page-edit-module-controls-panel simple-gallery-control" data-target-list="simple-gallery-list-<?php echo $object -> object_id; ?>">

	<div class="left">

		<div class="module-header" style="white-space:nowrap; padding:0 8px; font-weight:700; font-size:1.1em;">
			<i class="fas fa-images"></i>&nbsp;&nbsp;&nbsp;Image Gallery
		</div> 


		<button class="ui button trigger-manage-gallery" type="button">
			Manage Images
		</button>

	</div>

	<div class="right">

		<!-- TEMPLATE --------------------------->

		<label></label>

		<?php foreach($avaiableTemplates as $template) { ?>

			<button class="ui button icon trigger-view-mode" data-template-id="<?= $template -> templateId; ?>" type="button" title="<?= $template -> templateName; ?>">
				<i class="<?= $template -> templateIcon; ?>"></i>
			</button>
		
		<?php } ?>

		<input type="hidden" name="simple-gallery-template" value="<?= $object -> params -> template; ?>">

		<div style="display:flex; align-items:center;">
			<label>Format</label>	
			<div class="input">
				<div class="select-wrapper">
					<select name="simple-gallery-format" style="width:120px; border-radius:0; padding: 5px;">
						<option <?= (($object -> params -> format ?? 'ratio') === 'ratio' ? 'selected' : ''); ?> value="ratio">Ratio based</option>
						<option <?= (($object -> params -> format ?? 'ratio') === 'squares' ? 'selected' : ''); ?> value="squares">Squares</option>
					</select>
				</div>	
			</div>
		</div>

		<div style="display:flex; align-items:center;">
			<label>Display divider</label>	
			<div class="input">
				<div class="select-wrapper">
					<select name="simple-gallery-display-divider" style="width:70px; border-radius:0;padding: 5px;">
						<option <?= (($object -> params -> display_divider ?? '8') === '2' ? 'selected' : ''); ?> value="2">2</option>
						<option <?= (($object -> params -> display_divider ?? '8') === '3' ? 'selected' : ''); ?> value="3">3</option>
						<option <?= (($object -> params -> display_divider ?? '8') === '4' ? 'selected' : ''); ?> value="4">4</option>
						<option <?= (($object -> params -> display_divider ?? '8') === '5' ? 'selected' : ''); ?> value="5">5</option>
						<option <?= (($object -> params -> display_divider ?? '8') === '6' ? 'selected' : ''); ?> value="6">6</option>
						<option <?= (($object -> params -> display_divider ?? '8') === '7' ? 'selected' : ''); ?> value="7">7</option>
						<option <?= (($object -> params -> display_divider ?? '8') === '8' ? 'selected' : ''); ?> value="8">8</option>
					</select>
				</div>	
			</div>
		</div>
	
	</div>

	<div style="width:100%; background:white; " class="simple-gallery-manage-list simple-gallery-list-<?php echo $object -> object_id; ?> ignore-flex" hidden>
	
		<button class="ui button trigger-manage-gallery-add-image" type="button" style="width:auto; font-size:0.85em; font-weight:400;">
			Add Image
		</button>

		<button class="ui button trigger-manage-gallery-add-folder" type="button" style="width:auto; font-size:0.85em; font-weight:400;">
			Add Mediathek folder
		</button>

		<br>

		<table class="ui table fluid ">
			<colgroup>
				<col class="fluid-90">
				<col class="fluid-10">
				<col style="width:29px;">
			</colgroup>
			<thead>
				<tr>
					<th>Item name</th>
					
					<th>Type</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>

	</div>

</div>


<div class="simple-gallery-items-list simple-gallery-list-<?php echo $object -> object_id; ?>"></div>



<script>

	document.addEventListener('DOMContentLoaded', function () {

		let snilNode = document.querySelector('.simple-gallery-items-list.simple-gallery-list-<?php echo $object -> object_id; ?>');
		let mecpNode = document.querySelector('.simple-gallery-manage-list.simple-gallery-list-<?php echo $object -> object_id; ?>');
		let rawGalleryItems_<?php echo $object -> object_id; ?> = <?= json_encode($object -> params -> itemList); ?>;
		for(let node of rawGalleryItems_<?php echo $object -> object_id; ?>)
		{
			document.MECP_SimpleGallery.addNavigationItem(
				snilNode,
				mecpNode,
				node['item-path'],
				node['item-path'],
				0,
				node['listing-type'],
				true
			);
		}

	}, false);

</script>


<div id="module-xhr-html-response-container-<?php echo $object -> object_id; ?>">

	<?php
	
		include 'view.php';
	
	?>

</div>

