<div class="be-module-container">

	<table class="table-overview">
		<thead>
			<tr>
				<td class="batch-selection-item"></td>
				<td><?= $language -> string('MOD_BECATEGORIES_NAME'); ?></td>
				<td><?= $language -> string('MOD_BECATEGORIES_URLAPPENDIX'); ?></td>
				<td style="text-align:center;"><?= $language -> string('MOD_BECATEGORIES_HIDDEN'); ?></td>
				<td style="text-align:center;"><?= $language -> string('MOD_BECATEGORIES_DISABLED'); ?></td>
				<td style="text-align:center;"><?= $language -> string('MOD_BECATEGORIES_ALLOCATION'); ?></td>
				<td class="bach-item-menu"></td>
			</tr>
		</thead>
		<tbody id="table-body-overview"><!-- javascript injection --></tbody>
		<tfoot>
			<tr>
				<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-all-checkbox" id="item-all"><label for="item-all"></label></td>
				<td><?= $language -> string('SELECT_ALL'); ?></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>			
		</tfoot>
	</table>

</div>

<template id="template-table-row">

	<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-checkbox" name="group-id[]" value="%category_id%" id="item-%category_id%"><label for="item-%category_id%"></label></td>
	<td>%category_name%</td>
	<td>%category_url%</td>
	<td><div class="color-indicator negative" data-state="%category_hidden%"></div></td>
	<td><div class="color-indicator negative" data-state="%category_disabled%"></div></td>
	<td style="text-align:center;">%allocation%</td>
	<td class="bach-item-menu"><span>&equiv;</span><div class="dropdown-content"><div></div><a href="<?php echo CMS_SERVER_URL_BACKEND . $pageRequest -> urlPath; ?>category/%category_id%"><?php echo $language -> string('BUTTON_EDIT'); ?></a></div></td>
	
</template>

<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-index.js"></script>
<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-item.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function(){

	let	indexList = new cmsRequestDataIndex('template-table-row', '<?= CFG::GET() -> BACKEND -> TIME_FORMAT; ?>');
		indexList.init('index');
		
});	
</script>

