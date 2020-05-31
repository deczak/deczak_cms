<div class="be-module-container">

	<table class="table-overview">
		<thead>
			<tr>
				<td class="batch-selection-item"></td>
				<td class="user-id"><?php echo CLanguage::get() -> string('MOD_BEUSER_OV_TABLE_USERID'); ?></td>
				<td><?php echo CLanguage::get() -> string('USERNAME'); ?></td>
				<td class="time-create"><?php echo CLanguage::get() -> string('TIME_CREATE_AT'); ?></td>
				<td class="last-login"><?php echo CLanguage::get() -> string('MOD_BEUSER_OV_TABLE_LASTLOGIN'); ?></td>
				<td class="num-of-logins"><?php echo CLanguage::get() -> string('MOD_BEUSER_OV_TABLE_NUMLOGIN'); ?></td>
				<td class="bach-item-menu"></td>
			</tr>
		</thead>
		<tbody id="table-body-overview"><!-- javascript injection --></tbody>
		<tfoot>
			<tr>
				<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-all-checkbox" id="item-all"><label for="item-all"></label></td>
				<td colspan="2"><?php echo CLanguage::get() -> string('SELECT_ALL'); ?></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>			
		</tfoot>
	</table>

</div>

<style>

	div.be-module-container table.table-overview td.user-id { width:115px; }
	div.be-module-container table.table-overview td.time-create { width:205px; }
	div.be-module-container table.table-overview td.last-login { width:205px; }
	div.be-module-container table.table-overview td.num-of-logins { width:115px; text-align:center; }

</style>

<template id="template-table-row">

	<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-checkbox" name="group-id[]" value="%user_id%" id="item-%user_id%"><label for="item-%user_id%"></label></td>
	<td class="user-id">%user_id%</td>
	<td>%user_name_first% %user_name_last%</td>
	<td class="time-create">%create_time%</td>
	<td class="last-login">%time_login%</td>
	<td class="num-of-logins">%login_count%</td>
	<td class="bach-item-menu"><span>&equiv;</span><div class="dropdown-content"><div></div><a href="<?php echo CMS_SERVER_URL_BACKEND . $pageRequest -> urlPath; ?>user/%user_id%"><?php echo $language -> string('BUTTON_EDIT'); ?></a></div></td>
	
</template>

<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-index.js"></script>
<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-item.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function(){

	let	indexList = new cmsRequestDataIndex('template-table-row', '<?= CFG::GET() -> BACKEND -> TIME_FORMAT; ?>');
		indexList.init();
		
});	
</script>
