<div class="be-module-container">

	<table class="table-overview">
		<thead>
			<tr>
				<td class="batch-selection-item"></td>
				<td></td>
				<td>IP <?php echo $language -> string('ADDRESS'); ?></td>
				<td><?php echo $language -> string('DATE'); ?></td>
				<td class="assignments"><?php echo $language -> string('M_BESESSION_VISITEDPAGES'); ?></td>
				<td class=""><?php echo $language -> string('M_BESESSION_ACTIVEPAGE'); ?></td>
				<td class="bach-item-menu"></td>
			</tr>
		</thead>
		<tbody id="table-body-overview"><!-- javascript injection --></tbody>
		<tfoot>
			<tr>
				<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-all-checkbox" id="item-all"><label for="item-all"></label></td>
				<td><?php echo $language -> string('SELECT_ALL'); ?></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>			
		</tfoot>
	</table>

</div>

<style>

	div.be-module-container table.table-overview td.assignments { text-align:center; }
</style>

<template id="template-table-row">

	<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-checkbox" name="group-id[]" value="%data_id%" id="item-%data_id%"><label for="item-%data_id%"></label></td>
	<td>%agent_name%</td>
	<td>%user_ip%</td>
	<td>%time_create%</td>
	<td class="assignments">%num_pages%</td>
	<td>%latest_page%</td>
	<td class="bach-item-menu"><span>&equiv;</span><div class="dropdown-content"><div></div><a href="<?php echo CMS_SERVER_URL_BACKEND . $pageRequest -> urlPath; ?>session/%data_id%"><?php echo $language -> string('BUTTON_EDIT'); ?></a></div></td>
	
</template>

<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-index.js"></script>
<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-item.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function(){

	let	indexList = new cmsRequestDataIndex('template-table-row', '<?= CFG::GET() -> BACKEND -> TIME_FORMAT; ?>');
		indexList.init('index');
		
});	
</script>




