<div class="be-module-container">

	<table class="table-overview">
		<thead>
			<tr>
				<td class="batch-selection-item"></td>
				<td><?= CLanguage::get() -> string('MOD_RGROUPS_GROUP_ID'); ?></td>
				<td><?= CLanguage::get() -> string('MOD_RGROUPS_GROUP_NAME'); ?></td>
				<td style="text-align:center;"><?= CLanguage::get() -> string('MOD_RGROUPS_GROUP_ASSIGNMENT'); ?></td>
				<td><?= CLanguage::get() -> string('TIME_CREATE_AT'); ?></td>
				<td><?= CLanguage::get() -> string('CREATE_BY'); ?></td>
				<td class="bach-item-menu"></td>
			</tr>
		</thead>
		<tbody id="table-body-overview"><!-- javascript injection --></tbody>
		<tfoot>
			<tr>
				<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-all-checkbox" id="item-all"><label for="item-all"></label></td>
				<td><?= CLanguage::get() -> string('SELECT_ALL'); ?></td>
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

	<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-checkbox" name="group-id[]" value="%group_id%" id="item-%group_id%"><label for="item-%group_id%"></label></td>
	<td>%group_id%</td>
	<td>%group_name%</td>
	<td style="text-align:center;">%num_assignments%</td>
	<td>%create_time%</td>
	<td>%creaty_by_name%</td>
	<td class="bach-item-menu"><span>&equiv;</span><div class="dropdown-content"><div></div><a href="<?php echo CMS_SERVER_URL_BACKEND . $pageRequest -> urlPath; ?>group/%group_id%"><?php echo $language -> string('BUTTON_EDIT'); ?></a></div></td>
	
</template>

<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-index.js"></script>
<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-item.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function(){

	let	indexList = new cmsRequestDataIndex('template-table-row', '<?= CFG::GET() -> BACKEND -> TIME_FORMAT; ?>');
		indexList.init();
		
});	
</script>



