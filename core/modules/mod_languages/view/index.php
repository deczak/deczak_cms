
<div class="be-module-container">

	<table class="table-overview">
		<thead>
			<tr>
				<td class="batch-selection-item"></td>
				<td><?php echo CLanguage::string('LANGUAGE'); ?></td>
				<td class="column-state"><?php echo CLanguage::string('M_BELANG_HIDDEN'); ?></td>
				<td class="column-state"><?php echo CLanguage::string('M_BELANG_LOCKED'); ?></td>
				<td class="column-button"></td>
				<td class="bach-item-menu"></td>
			</tr>
		</thead>
		<tbody id="table-body-overview"></tbody>
		<tfoot>
			<tr>
				<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-all-checkbox" id="item-all"><label for="item-all"></label></td>
				<td colspan="4"><?php echo CLanguage::string('SELECT_ALL'); ?></td>
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

<template id="template-table-row">
	
	<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-checkbox" name="group-id[]" value="%lang_key%" id="item-%lang_key%"><label for="item-%lang_key%"></label></td>
	<td class="">%lang_name%</td>
	<td><div class="color-indicator negative" data-state="%lang_hidden%"></div></td>
	<td><div class="color-indicator negative" data-state="%lang_locked%"></div></td>
	<td class="column-button"></td>
	<td class="bach-item-menu"><span>&equiv;</span><div class="dropdown-content"><div></div><a href="<?php echo CMS_SERVER_URL_BACKEND . $pageRequest -> urlPath; ?>language/%lang_key%"><?php echo CLanguage::string('BUTTON_EDIT'); ?></a></div></td>
	
</template>

<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-index.js"></script>
<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-item.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function(){

	let	indexList = new cmsRequestDataIndex('template-table-row', '<?= CFG::GET() -> BACKEND -> TIME_FORMAT; ?>');
		indexList.init('index');
		
});	
</script>