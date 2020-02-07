
<div class="be-module-container">

	<table class="table-overview">
		<thead>
			<tr>
				<td class="batch-selection-item"></td>
				<td><?php echo $language -> string('LANGUAGE'); ?></td>
				<td class="column-state"><?php echo $language -> string('M_BELANG_HIDDEN'); ?></td>
				<td class="column-state"><?php echo $language -> string('M_BELANG_LOCKED'); ?></td>
				<td class="column-button"></td>
				<td class="bach-item-menu"></td>
			</tr>
		</thead>
		<tbody id="table-body-overview"></tbody>
		<tfoot>
			<tr>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-all-checkbox" id="item-all"><label for="item-all"></label></td>
				<td colspan="4"><?php echo $language -> string('SELECT_ALL'); ?></td>
				<td></td>
			</tr>			
		</tfoot>
	</table>

	<div class="ui">
		<div id="result-box-install" class="ui result-box"></div>
	</div>

</div>

<style>

	div.be-module-container table.table-overview .column-state { width:150px; text-align:center; }
	div.be-module-container table.table-overview .column-button { text-align:center; }

</style>

<template id="template-table-row-languages">
	
	<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-checkbox" name="group-id[]" value="%MODULE_ID%" id="item-%MODULE_ID%"><label for="item-%MODULE_ID%"></label></td>
	<td class="">%LANG_NAME%</td>
	<td><div class="color-indicator negative" data-state="%LANG_HIDDEN%"></div></td>
	<td><div class="color-indicator negative" data-state="%LANG_LOCKED%"></div></td>
	<td class="column-button"></td>
	<td class="bach-item-menu"><span>&equiv;</span><div class="dropdown-content"><div></div><a href="<?php echo CMS_SERVER_URL_BACKEND . $pageRequest -> urlPath; ?>language/%LANG_KEY%"><?php echo $language -> string('BUTTON_EDIT'); ?></a></div></td>
	
</template>

<script>
document.addEventListener("DOMContentLoaded", function(){

	let	indexList = new cmsIndexList();
		indexList.init();
		
});	
</script>