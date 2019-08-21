<?php

function
getAssignmentCount($rightGroup, $userGroups)
{
	$_found = 0;
	foreach($userGroups as $userGroup)
	{
		if($userGroup -> group_id !== $rightGroup -> group_id)
			continue;
		$_found++;
	}
	return $_found;
}

?>

<div class="be-module-container">

	<table class="table-overview">
		<thead>
			<tr>
				<td class="batch-selection-item"></td>
				<td><?php echo CLanguage::instance() -> getString('MOD_RGROUPS_GROUP_ID'); ?></td>
				<td><?php echo CLanguage::instance() -> getString('MOD_RGROUPS_GROUP_NAME'); ?></td>
				<td class="assignments"><?php echo CLanguage::instance() -> getString('MOD_RGROUPS_GROUP_ASSIGNMENT'); ?></td>
				<td><?php echo CLanguage::instance() -> getString('TIME_CREATE_AT'); ?></td>
				<td><?php echo CLanguage::instance() -> getString('TIME_UPDATE_AT'); ?></td>
				<td class="bach-item-menu"></td>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach($right_groups as $_dataKey => $_dataSet)
		{
		#	$_dataSet -> time_login 	= ($_dataSet -> time_login == 0 ? '-' : date(TIME_FORMAT_BACKENDVIEW, $_dataSet -> time_login) );
		#	$_dataSet -> time_create 	= ($_dataSet -> time_create == 0 ? '-' : date(TIME_FORMAT_BACKENDVIEW, $_dataSet -> time_create) );

			$_numOfAssignments = getAssignmentCount($_dataSet, $user_groups);


			?>

			<tr class="trigger-batch-item">
				<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-checkbox" name="group-id[]" value="<?php echo $_dataSet -> group_id; ?>" id="item-<?php echo $_dataKey; ?>"><label for="item-<?php echo $_dataKey; ?>"></label></td>
				<td class=""><?php echo $_dataSet -> group_id; ?></td>
				<td class=""><?php echo $_dataSet -> group_name; ?></td>
				<td class="assignments"><?php echo $_numOfAssignments; ?></td>
				<td class=""></td>
				<td class=""></td>
				<td class="bach-item-menu"><span>&equiv;</span><div class="dropdown-content"><div></div><a href="<?php echo CMS_SERVER_URL_BACKEND . REQUESTED_PAGE_PATH; ?>group/<?php echo $_dataSet -> group_id; ?>"><?php echo CLanguage::instance() -> getString('MOD_RGROUPS_EDIT_GROUP'); ?></a></div></td>
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
				<td><?php echo CLanguage::instance() -> getString('SELECT_ALL'); ?></td>
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




