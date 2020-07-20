<div class="be-module-container">

	<table class="table-overview">
		<thead>
			<tr>
				<td class="batch-selection-item"></td>
				<td><?= $language -> string('USER'); ?></td>
				<td><?= $language -> string('MOD_BEREMOTEU_USERORIGIN'); ?></td>
				<td style="text-align:center;"><?= $language -> string('MOD_BEREMOTEU_ALLOCATED'); ?></td>
				<td><?= CLanguage::get() -> string('TIME_UPDATE_AT'); ?></td>
				<td><?= CLanguage::get() -> string('UPDATE_BY'); ?></td>
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

	<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-checkbox" name="group-id[]" value="%id%" id="item-%id%"><label for="item-%id%"></label></td>
	<td>%user_name_first% %user_name_last%</td>
	<td>%db_name% (%db_server%)</td>
	<td style="text-align:center;">%allocations%</td>
	<td>%update_time%</td>
	<td>%update_by%</td>
	<td class="bach-item-menu"><span>&equiv;</span><div class="dropdown-content"><div></div><a href="<?php echo CMS_SERVER_URL_BACKEND . $pageRequest -> urlPath; ?>user/%id%"><?php echo $language -> string('BUTTON_EDIT'); ?></a></div></td>
	
</template>

<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-index.js"></script>
<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-item.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function(){

	let	indexList = new cmsRequestDataIndex('template-table-row', '<?= CFG::GET() -> BACKEND -> TIME_FORMAT; ?>');
		indexList.init();
		
});	
</script>