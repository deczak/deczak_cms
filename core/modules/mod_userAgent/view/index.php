<div class="be-module-container">

	<table class="table-overview">
		<thead>
			<tr>
				<td class="batch-selection-item"></td>
				<td><?php echo $language -> string('M_BEUSERAG_AGENTNAME'); ?></td>
				<td><?php echo $language -> string('M_BEUSERAG_AGENTSUFFIX'); ?></td>
				<td><?php echo $language -> string('DESCRIPTION'); ?></td>
				<td style="text-align:center;"><?php echo $language -> string('ALLOWED'); ?></td>
				<td><?php echo $language -> string('TIME_CREATE_AT'); ?></td>
				<td><?php echo $language -> string('CREATE_BY'); ?></td>
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
				<td></td>
			</tr>			
		</tfoot>
	</table>

</div>


<template id="template-table-row">

	<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-checkbox" name="group-id[]" value="%data_id%" id="item-%data_id%"><label for="item-%data_id%"></label></td>
	<td>%agent_name%</td>
	<td>%agent_suffix%</td>
	<td>%agent_desc%</td>
	<td><div class="color-indicator positive" data-state="%agent_allowed%"></div></td>
	<td>%create_time%</td>
	<td>%creaty_by_name%</td>
	<td class="bach-item-menu"><span>&equiv;</span><div class="dropdown-content"><div></div><a href="<?php echo CMS_SERVER_URL_BACKEND . $pageRequest -> urlPath; ?>agent/%data_id%"><?php echo $language -> string('BUTTON_EDIT'); ?></a></div></td>
	
</template>

<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-index.js"></script>
<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-item.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function(){

	let	indexList = new cmsRequestDataIndex('template-table-row', '<?= CFG::GET() -> BACKEND -> TIME_FORMAT; ?>');
		indexList.init();
		
});	
</script>