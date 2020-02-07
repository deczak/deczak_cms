<?php

$_pPageRequest 	= CPageRequest::instance();

/*
function
draw(&$_nodeMap, $_nodeKey = 1, $_level = 1 )
{
	echo "<ul>";
	for($i = $_nodeKey; $i < count($_nodeMap); $i++)
	{
		if($_level !== intval($_nodeMap[$i] -> level)) 
			break;

		echo "<li> -". $_nodeMap[$i] -> page_name;

		if($_nodeMap[$i] -> offspring != 0)
			$i = draw($_nodeMap, ($i + 1), ($_level + 1));

		echo "</li>";
	}
	echo "</ul>";
	return $i - 1;
}
*/


#draw($pages);

// replace later by languages in lang table

echo '<div class="language-select-container">';

foreach(CLanguage::instance() -> getLanguages() as $language)
{
	echo '<a class="trigger-language-select" data-language="'. $language -> lang_key .'"><span>'.  strtoupper($language -> lang_key) .'</span>'. $language -> lang_name .'</a>';
}
echo '</div>';

?>

<style>

	.language-select-container { display:flex; padding:10px 20px; }
	.language-select-container .trigger-language-select { border:1px solid grey; padding:3px 6px; padding-left:34px; position:relative; margin-right:10px; font-size:0.8em; }
	.language-select-container .trigger-language-select span { background:grey; color:white; position:absolute; display:block; left:0px; top:0px; height:100%; padding:3px 6px; font-weight:500; }


</style>


<div class="be-module-container">

	<table class="table-overview">
		<thead>
			<tr>
				<td class="batch-selection-item"></td>
				<td class="node-id"><?php echo CLanguage::instance() -> getString('MOD_SITES_OV_TABLE_NODEID'); ?></td>
				<td class=""><?php echo CLanguage::instance() -> getString('MOD_SITES_OV_TABLE_PAGETITLE'); ?></td>
				<td class=""><?php echo CLanguage::instance() -> getString('MOD_SITES_OV_TABLE_PAGEURL'); ?></td>
				<td class="time-create"><?php echo CLanguage::instance() -> getString('MOD_SITES_OV_TABLE_CREATEDAT'); ?></td>
				<td class="time-update"><?php echo CLanguage::instance() -> getString('MOD_SITES_OV_TABLE_UPDATEDAT'); ?></td>
				<td class="bach-item-menu"></td>
			</tr>
		</thead>
		<tbody id="table-body-overview">

		<?php
		/*
		foreach($pages as $_dataKey => $_dataSet)
		{
			if($_dataSet -> node_id == 1) continue;

			$_dataSet -> create_time 	= ($_dataSet -> create_time == 0 ? '-' : date(TIME_FORMAT_BACKENDVIEW, $_dataSet -> create_time) );
			$_dataSet -> update_time 	= ($_dataSet -> update_time == 0 ? '-' : date(TIME_FORMAT_BACKENDVIEW, $_dataSet -> update_time) );

			$_spacer = ($_dataSet -> page_path !== '/' ? $_dataSet -> level * 20 : 0);

			?>

			<tr class="trigger-batch-item">
				<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-checkbox" name="user-id[]" value="<?php echo $_dataSet -> user_id; ?>" id="item-<?php echo $_dataKey; ?>"><label for="item-<?php echo $_dataKey; ?>"></label></td>
				<td class="page-id"><?php echo $_dataSet -> node_id; ?></td>
				<td class=""><span style="display:inline-block; width:<?php echo $_spacer; ?>px;"></span><?php echo $_dataSet -> page_name; ?></td>
				<td class=""><?php echo $_dataSet -> page_path; ?></td>
				<td class="time-create"><?php echo $_dataSet -> create_time; ?></td>
				<td class="time-update"><?php echo $_dataSet -> update_time; ?></td>
				<td class="bach-item-menu"><span>&equiv;</span><div class="dropdown-content"><div></div>

					<a href="<?php echo CMS_SERVER_URL_BACKEND . $_pPageRequest -> urlPath; ?>view/<?php echo $_dataSet -> page_language .'/'; ?><?php echo $_dataSet -> node_id; ?>?language=<?= $language; ?>"><?php echo CLanguage::instance() -> getString('MOD_SITES_OV_TABLE_PAGEEDIT'); ?></a>
					<a href="<?php echo CMS_SERVER_URL_BACKEND . $_pPageRequest -> urlPath; ?>create/<?php echo $_dataSet -> page_language .'/'; ?><?php echo $_dataSet -> node_id; ?>?language=<?= $language; ?>"><?php echo CLanguage::instance() -> getString('MOD_SITES_OV_TABLE_PAGECREATE'); ?></a>
					<a href="<?php echo CMS_SERVER_URL_BACKEND . $_pPageRequest -> urlPath; ?>delete/<?php echo $_dataSet -> page_language .'/'; ?><?php echo $_dataSet -> node_id; ?>?language=<?= $language; ?>"><?php echo CLanguage::instance() -> getString('MOD_SITES_OV_TABLE_PAGEDELETE'); ?></a>
					<a href="<?php echo CMS_SERVER_URL_BACKEND . $_pPageRequest -> urlPath; ?>deletetree/<?php echo $_dataSet -> page_language .'/'; ?><?php echo $_dataSet -> node_id; ?>?language=<?= $language; ?>"><?php echo CLanguage::instance() -> getString('MOD_SITES_OV_TABLE_PAGEDELETETREE'); ?></a>

					</div></td>
			</tr>

			<?php
		}
		*/
		?>
		</tbody>
		<tfoot>
			<tr>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-all-checkbox" id="item-all"><label for="item-all"></label></td>
				<td><?php echo CLanguage::instance() -> getString('MOD_SITES_OV_TABLE_SELECTALL'); ?></td>
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

	div.be-module-container table.table-overview td.page-id { width:115px; }
	div.be-module-container table.table-overview td.time-create { width:205px; }
	div.be-module-container table.table-overview td.time-update { width:205px; }

</style>

<template id="template-table-row-page">
	
	<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-checkbox" name="group-id[]" value="%NODE_ID%" id="item-%NODE_ID%"><label for="item-%NODE_ID%"></label></td>
	<td class="page-id"><span style="font-family:icons-solid;">%NODE_ID%</span></td>
	<td class=""><span style="display:inline-block; width:%SPACER%px;"></span>%PAGE_NAME%</td>
	<td class="">%PAGE_PATH%</td>
	<td class="time-create">%CREATE_TIME%</td>
	<td class="time-update">%UPDATE_TIME%</td>
	<td class="bach-item-menu"><span>&equiv;</span><div class="dropdown-content"><div></div>
		<a href="<?php echo CMS_SERVER_URL_BACKEND . $_pPageRequest -> urlPath; ?>view/%PAGE_LANGUAGE%/%NODE_ID%?language=<?= CLanguage::get() -> getActive(); ?>"><?php echo CLanguage::instance() -> getString('MOD_SITES_OV_TABLE_PAGEEDIT'); ?></a>
		<a class="trigger-page-option" data-xhr-overwrite-target="create/%PAGE_LANGUAGE%/%NODE_ID%"><?php echo CLanguage::instance() -> getString('MOD_SITES_OV_TABLE_PAGECREATE'); ?></a>
		<a class="trigger-page-option" data-xhr-overwrite-target="delete/%PAGE_LANGUAGE%/%NODE_ID%"><?php echo CLanguage::instance() -> getString('MOD_SITES_OV_TABLE_PAGEDELETE'); ?></a>
		<a class="trigger-page-option" data-xhr-overwrite-target="deletetree/%PAGE_LANGUAGE%/%NODE_ID%"><?php echo CLanguage::instance() -> getString('MOD_SITES_OV_TABLE_PAGEDELETETREE'); ?></a>
		</div></td>
	
</template>

<script>

	var indexList 		= null;
	var	activeLanguage 	= '';

	document.addEventListener("DOMContentLoaded", function(){

		indexList = new cmsIndexList();
		indexList.init();

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

