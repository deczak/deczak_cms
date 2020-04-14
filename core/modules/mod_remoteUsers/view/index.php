<?php

function
getAllocations($_groupsList, $_userHash)
{
	foreach($_groupsList as $group)
	{
		if($group -> user_hash === $_userHash)
			return $group -> allocation;
	}
	
	return 0;
}

function
getUpdateBy($_groupsList, $_userHash)
{
	foreach($_groupsList as $group)
	{
		if($group -> user_hash === $_userHash)
			return $group -> update_by;
	}
	
	return 0;
}

function
getUpdateTime($_groupsList, $_userHash)
{
	foreach($_groupsList as $group)
	{
		if($group -> user_hash === $_userHash)
			return $group -> update_time;
	}
	
	return 0;
}

?>
<div class="be-module-container">

	<table class="table-overview">
		<thead>
			<tr>
				<td class="batch-selection-item"></td>
				<td><?= $language -> string('USER'); ?></td>
				<td><?= $language -> string('MOD_BEREMOTEU_USERORIGIN'); ?></td>
				<td class="center"><?= $language -> string('MOD_BEREMOTEU_ALLOCATED'); ?></td>
				<td><?= CLanguage::get() -> string('TIME_UPDATE_AT'); ?></td>
				<td><?= CLanguage::get() -> string('UPDATE_BY'); ?></td>
				<td class="bach-item-menu"></td>
			</tr>
		</thead>
		<tbody>

		<?php
		
		foreach($usersList as $dataKey => $dataSet)
		{
			$user = $dataSet['user'];

			$updateTime = getUpdateTime($groupsList, $dataSet['id']);
			$updateBy 	= getUpdateBy($groupsList, $dataSet['id']);

			?>

			<tr class="trigger-batch-item">
				<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-checkbox" name="group-id[]" value="<?= $dataSet['id']; ?>" id="item-<?= $dataKey; ?>"><label for="item-<?= $dataKey; ?>"></label></td>
				<td><?= $user -> user_name_first; ?> <?= $user -> user_name_last; ?></td>
				<td><?= $dataSet['db_name']; ?> (<?= $dataSet['db_server']; ?>)</td>
				<td class="center"><?= getAllocations($groupsList, $dataSet['id']); ?></td>
				<td><?= ($updateTime != 0 ? date(CFG::GET() -> BACKEND -> TIME_FORMAT, $updateTime) : ''); ?></td>
				<td><?php echo tk::getBackendUserName($sqlConnection, $updateBy); ?></td>
				<td class="bach-item-menu"><span>&equiv;</span><div class="dropdown-content"><div></div><a href="<?= CMS_SERVER_URL_BACKEND . $pageRequest -> urlPath; ?>user/<?= $dataSet['id']; ?>"><?= $language -> string('BUTTON_EDIT'); ?></a></div></td>
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
				<td><?= $language -> string('SELECT_ALL'); ?></td>
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

	div.be-module-container table.table-overview td.center { text-align:center; }
</style>

<?php

#	tk::dbug($registersList);
#	tk::dbug($groupsList);
#	tk::dbug($usersList);
?>



