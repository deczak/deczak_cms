
<div class="be-module-container">

	<table class="table-overview">
		<thead>
			<tr>
				<td class="batch-selection-item"></td>
				<td><?php echo $language -> string('DENIED2 ADDRESS'); ?></td>
				<td><?php echo $language -> string('DESCRIPTION'); ?></td>
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
			</tr>			
		</tfoot>
	</table>


	<?php if(!CFG::GET() -> SESSION -> DENIED_ACCESS_ON) { ?>

		<br>

		<div class="ui"><div class="result-box big" data-error="1">
			<b><?= $language -> string('NOTE'); ?>:</b> &nbsp;<?= $language -> string('MOD_BE_RMADDR_FUNC_DISABLED'); ?>		
		</div></div>

	<?php } ?>


</div>

<template id="template-table-row">

	<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-checkbox" name="group-id[]" value="%data_id%" id="item-%data_id%"><label for="item-%data_id%"></label></td>
	<td>%denied_ip%</td>
	<td>%denied_desc%</td>
	<td>%create_time%</td>
	<td>%creaty_by_name%</td>
	<td class="bach-item-menu"><span>&equiv;</span><div class="dropdown-content"><div></div><a href="<?php echo CMS_SERVER_URL_BACKEND . $pageRequest -> urlPath; ?>address/%data_id%"><?php echo $language -> string('BUTTON_EDIT'); ?></a></div></td>
	
</template>

<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-index.js"></script>
<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-item.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function(){

	let	indexList = new cmsRequestDataIndex('template-table-row', '<?= CFG::GET() -> BACKEND -> TIME_FORMAT; ?>');
		indexList.init();
		
});	
</script>
