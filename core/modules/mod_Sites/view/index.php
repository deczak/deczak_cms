<?php




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



#draw($pages);


?>
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
		<tbody>
		<?php
		foreach($pages as $_dataKey => $_dataSet)
		{
			if($_dataSet -> node_id == 1) continue;

	
			$_dataSet -> time_create 	= ($_dataSet -> time_create == 0 ? '-' : date(TIME_FORMAT_BACKENDVIEW, $_dataSet -> time_create) );
			$_dataSet -> time_update 	= ($_dataSet -> time_update == 0 ? '-' : date(TIME_FORMAT_BACKENDVIEW, $_dataSet -> time_update) );

			$_spacer = ($_dataSet -> page_path !== '/' ? $_dataSet -> level * 20 : 0);

			?>

			<tr class="trigger-batch-item">
				<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-checkbox" name="user-id[]" value="<?php echo $_dataSet -> user_id; ?>" id="item-<?php echo $_dataKey; ?>"><label for="item-<?php echo $_dataKey; ?>"></label></td>
				<td class="page-id"><?php echo $_dataSet -> node_id; ?></td>
				<td class=""><span style="display:inline-block; width:<?php echo $_spacer; ?>px;"></span><?php echo $_dataSet -> page_name; ?></td>
				<td class=""><?php echo $_dataSet -> page_path; ?></td>
				<td class="time-create"><?php echo $_dataSet -> time_create; ?></td>
				<td class="time-update"><?php echo $_dataSet -> time_update; ?></td>
				<td class="bach-item-menu"><span>&equiv;</span><div class="dropdown-content"><div></div>

					<a href="<?php echo CMS_SERVER_URL_BACKEND . REQUESTED_PAGE_PATH; ?>edit/<?php echo $_dataSet -> page_language .'/'; ?><?php echo $_dataSet -> node_id; ?>"><?php echo CLanguage::instance() -> getString('MOD_SITES_OV_TABLE_PAGEEDIT'); ?></a>
					<a href="<?php echo CMS_SERVER_URL_BACKEND . REQUESTED_PAGE_PATH; ?>create/<?php echo $_dataSet -> page_language .'/'; ?><?php echo $_dataSet -> node_id; ?>"><?php echo CLanguage::instance() -> getString('MOD_SITES_OV_TABLE_PAGECREATE'); ?></a>
					<a href="<?php echo CMS_SERVER_URL_BACKEND . REQUESTED_PAGE_PATH; ?>delete/<?php echo $_dataSet -> page_language .'/'; ?><?php echo $_dataSet -> node_id; ?>"><?php echo CLanguage::instance() -> getString('MOD_SITES_OV_TABLE_PAGEDELETE'); ?></a>
					<a href="<?php echo CMS_SERVER_URL_BACKEND . REQUESTED_PAGE_PATH; ?>deletetree/<?php echo $_dataSet -> page_language .'/'; ?><?php echo $_dataSet -> node_id; ?>"><?php echo CLanguage::instance() -> getString('MOD_SITES_OV_TABLE_PAGEDELETETREE'); ?></a>

					</div></td>
			</tr>

			<?php
		}
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

<?php

#tk::dbug($pages);
?>


