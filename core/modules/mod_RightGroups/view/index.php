<?php

$_pPageRequest 	= CPageRequest::instance();

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
				<td><?= CLanguage::get() -> string('MOD_RGROUPS_GROUP_ID'); ?></td>
				<td><?= CLanguage::get() -> string('MOD_RGROUPS_GROUP_NAME'); ?></td>
				<td class="assignments"><?= CLanguage::get() -> string('MOD_RGROUPS_GROUP_ASSIGNMENT'); ?></td>
				<td><?= CLanguage::get() -> string('TIME_CREATE_AT'); ?></td>
				<td><?= CLanguage::get() -> string('CREATE_BY'); ?></td>
				<td class="bach-item-menu"></td>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach($right_groups as $_dataKey => $_dataSet)
		{
			$_dataSet -> create_time 	= ($_dataSet -> create_time == 0 ? '-' : date(CFG::GET() -> BACKEND -> TIME_FORMAT, $_dataSet -> create_time) );

			$_numOfAssignments = getAssignmentCount($_dataSet, $user_groups);

			?>

			<tr class="trigger-batch-item">
				<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-checkbox" name="group-id[]" value="<?= $_dataSet -> group_id; ?>" id="item-<?= $_dataKey; ?>"><label for="item-<?= $_dataKey; ?>"></label></td>
				<td class=""><?= $_dataSet -> group_id; ?></td>
				<td class=""><?= $_dataSet -> group_name; ?></td>
				<td class="assignments"><?= $_numOfAssignments; ?></td>
				<td class=""><?= $_dataSet -> create_time; ?></td>
				<td class=""><?= tk::getBackendUserName($sqlConnection, $_dataSet -> create_by); ?></td>
				<td class="bach-item-menu"><span>&equiv;</span><div class="dropdown-content"><div></div><a href="<?= CMS_SERVER_URL_BACKEND . $_pPageRequest -> urlPath; ?>group/<?= $_dataSet -> group_id; ?>"><?= CLanguage::get() -> string('BUTTON_EDIT'); ?></a></div></td>
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
				<td><?= CLanguage::get() -> string('SELECT_ALL'); ?></td>
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




