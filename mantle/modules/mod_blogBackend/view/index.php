<?php

	#tk::dbug($deniedList);

	
?>

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
		<tbody>
		<?php
		foreach($deniedList as $_dataKey => $_dataSet)
		{
			$_dataSet -> create_time 	= ($_dataSet -> create_time == 0 ? '-' : date(TIME_FORMAT_BACKENDVIEW, $_dataSet -> create_time) );

			?>

			<tr class="trigger-batch-item">
				<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-checkbox" name="group-id[]" value="<?php echo $_dataSet -> data_id; ?>" id="item-<?php echo $_dataKey; ?>"><label for="item-<?php echo $_dataKey; ?>"></label></td>
				<td class=""><?php echo $_dataSet -> denied_ip; ?></td>
				<td class=""><?php echo $_dataSet -> denied_desc; ?></td>
				<td class=""><?php echo $_dataSet -> create_time; ?></td>
				<td class=""><?php echo tk::getBackendUserName($sqlConnection, $_dataSet -> create_by); ?></td>
				<td class="bach-item-menu"><span>&equiv;</span><div class="dropdown-content"><div></div><a href="<?php echo CMS_SERVER_URL_BACKEND . $pageRequest -> urlPath; ?>address/<?php echo $_dataSet -> data_id; ?>"><?php echo $language -> string('BUTTON_EDIT'); ?></a></div></td>
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
			</tr>
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

</div>

<style>

	div.be-module-container table.table-overview td.assignments { text-align:center; }
</style>




