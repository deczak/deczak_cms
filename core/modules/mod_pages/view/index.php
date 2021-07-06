<div class="be-module-container">

	<div class="language-select-container">
		<?php
		foreach(CLanguage::instance() -> getLanguages() as $language)
		{
			if(!$language -> lang_frontend)
				continue;

			echo '<a class="trigger-language-select" data-language="'. $language -> lang_key .'"><span>'.  strtoupper($language -> lang_key) .'</span>'. $language -> lang_name .'</a>';
		}
		?>
	</div>

	<table class="table-overview">
		<thead>
			<tr>
				<td class="batch-selection-item"></td>
				<td class="center node-id"><?php echo CLanguage::instance() -> getString('MOD_SITES_OV_TABLE_NODEID'); ?></td>
				<td class=""><?php echo CLanguage::instance() -> getString('MOD_SITES_OV_TABLE_PAGETITLE'); ?></td>
				<td class=""><?php echo CLanguage::instance() -> getString('MOD_SITES_OV_TABLE_PAGEURL'); ?></td>
				<td class="time-create"><?php echo CLanguage::instance() -> getString('TIME_CREATE_AT'); ?></td>
				<td class="time-update"><?php echo CLanguage::instance() -> getString('TIME_UPDATE_AT'); ?></td>
				<td class="bach-item-menu"></td>
			</tr>
		</thead>
		<tbody id="table-body-overview"><!-- javascript injection --></tbody>
		<tfoot>
			<tr>
				<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-all-checkbox" id="item-all"><label for="item-all"></label></td>
				<td colspan="2"><?php echo CLanguage::instance() -> getString('SELECT_ALL'); ?></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>			
		</tfoot>
	</table>

</div>

<style>

	div.be-module-container table.table-overview td.node-id { width:85px; }
	div.be-module-container table.table-overview td.time-create { width:205px; }
	div.be-module-container table.table-overview td.time-update { width:205px; }
	div.be-module-container table.table-overview td.center { text-align:center; }

	.language-select-container { display:flex; padding:10px 20px; margin-bottom:25px; }
	.language-select-container .trigger-language-select { border:1px solid grey; padding:3px 6px; padding-left:34px; position:relative; margin-right:10px; font-size:0.8em; }
	.language-select-container .trigger-language-select span { background:grey; color:white; position:absolute; display:block; left:0px; top:0px; height:100%; padding:3px 6px; font-weight:500; }

</style>

<template id="template-table-row-page">
	
	<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-checkbox" name="group-id[]" value="%NODE_ID%" id="item-%NODE_ID%"><label for="item-%NODE_ID%"></label></td>
	<td class="center node-id">%NODE_ID%</td>
	<td class=""><span style="display:inline-block; width:%SPACER%px;"></span>%PAGE_NAME%</td>
	<td class="">%PAGE_PATH%</td>
	<td class="time-create">%CREATE_TIME%</td>
	<td class="time-update">%UPDATE_TIME%</td>
	<td class="bach-item-menu"><span>&equiv;</span><div class="dropdown-content"><div></div>
		<a data-right="view" href="<?php echo CMS_SERVER_URL_BACKEND . $pageRequest -> urlPath; ?>view/%PAGE_LANGUAGE%/%NODE_ID%?language=<?= CLanguage::get() -> getActive(); ?>"><?php echo CLanguage::instance() -> getString('VIEW'); ?></a>
		<a data-right="edit" href="<?php echo CMS_SERVER_URL_BACKEND . $pageRequest -> urlPath; ?>view/%PAGE_LANGUAGE%/%NODE_ID%?language=<?= CLanguage::get() -> getActive(); ?>"><?php echo CLanguage::instance() -> getString('EDIT'); ?></a>
		<a data-right="edit" class="trigger-page-option" data-xhr-overwrite-target="create/%PAGE_LANGUAGE%/%NODE_ID%"><?php echo CLanguage::instance() -> getString('MOD_SITES_OV_TABLE_PAGECREATE'); ?></a>
		<a data-right="edit" class="trigger-page-option" data-xhr-overwrite-target="delete/%NODE_ID%"><?php echo CLanguage::instance() -> getString('DELETE'); ?></a>
		<a data-right="edit" class="trigger-page-option" data-xhr-overwrite-target="deletetree/%NODE_ID%"><?php echo CLanguage::instance() -> getString('MOD_SITES_OV_TABLE_PAGEDELETETREE'); ?></a>
		</div></td>
	
</template>

<script>

	var indexList 		= null;
	var	activeLanguage 	= '';
	var	languagesList   = <?= json_encode(CLanguage::instance() -> getLanguages()); ?>;

	document.addEventListener("DOMContentLoaded", function(){

		indexList = new cmsIndexList();
		indexList.init(languagesList);

	});	

	document.addEventListener('click', function(event) {
		var element = event.target; 

		if(	element !== null && element.classList.contains('trigger-language-select'))	onLanguageSelect(element, event);

		if(	element !== null && element.classList.contains('trigger-page-option'))	onPageOption(element, event);
		
	}, false);

	function
	onLanguageSelect(element,event)
	{
		event.stopPropagation();
		event.preventDefault();

		activeLanguage = element.getAttribute('data-language');
		indexList.requestData(activeLanguage);

		return false;
	}

	function
	onPageOption(element,event)
	{
		event.stopPropagation();
		event.preventDefault();

		var formData 		= new FormData();
		var	requestTarget	= CMS.SERVER_URL_BACKEND + CMS.PAGE_PATH + CMS.MODULE_TARGET + element.getAttribute('data-xhr-overwrite-target');

		cmstk.callXHR(requestTarget, formData, onSuccessPageOption, cmstk.onXHRError, this);

		return false;
	}

	function
	onSuccessPageOption(response, instance)
	{
		indexList.requestData(activeLanguage);
	}
	
</script>

