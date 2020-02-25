
<?php
#tk::dbug($sessionList);
?>

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
		<tbody>
		<?php
		foreach($sessionList as $_dataKey => $_dataSet)
		{
			$_dataSet -> time_create 	= ($_dataSet -> time_create == 0 ? '-' : date(TIME_FORMAT_BACKENDVIEW, $_dataSet -> time_create) );

			?>

			<tr class="trigger-batch-item">
				<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-checkbox" name="group-id[]" value="<?php echo $_dataSet -> data_id; ?>" id="item-<?php echo $_dataKey; ?>"><label for="item-<?php echo $_dataKey; ?>"></label></td>
				<td class=""><?php echo tk::getValueFromArrayByValueI($agentsList, 'agent_suffix', $_dataSet -> user_agent, 'agent_name', $language -> string('VISITOR')) ?></td>
				<td class=""><?php echo $_dataSet -> user_ip; ?></td>
				<td class=""><?php echo $_dataSet -> time_create; ?></td>
				<td class="assignments"><?php echo count($_dataSet -> pages); ?></td>
				<td class=""><?php echo $_dataSet -> pages[ count($_dataSet -> pages) - 1] -> page_title; ?></td>
				<td class="bach-item-menu"><span>&equiv;</span><div class="dropdown-content"><div></div><a href="<?php echo CMS_SERVER_URL_BACKEND . $pageRequest -> urlPath; ?>session/<?php echo $_dataSet -> data_id; ?>"><?php echo $language -> string('BUTTON_EDIT'); ?></a></div></td>
			</tr>

			<?php
		}
		?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="7"></td>
			</tr>
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




