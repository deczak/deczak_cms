<?php

	#tk::dbug($tagsList);
	
?>

<div class="be-module-container">

	<table class="table-overview">
		<thead>
			<tr>
				<td class="batch-selection-item"></td>
				<td><?= $language -> string('MOD_BETAGS_NAME'); ?></td>
				<td><?= $language -> string('MOD_BETAGS_URLAPPENDIX'); ?></td>
				<td style="text-align:center;"><?= $language -> string('MOD_BETAGS_HIDDEN'); ?></td>
				<td style="text-align:center;"><?= $language -> string('MOD_BETAGS_DISABLED'); ?></td>
				<td style="text-align:center;"><?= $language -> string('MOD_BETAGS_ALLOCATION'); ?></td>
				<td class="bach-item-menu"></td>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach($tagsList as $dataKey => $dataSet)
		{
			?>

			<tr class="trigger-batch-item">
				<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-checkbox" name="group-id[]" value="<?= $dataSet -> tag_id; ?>" id="item-<?= $dataKey; ?>"><label for="item-<?= $dataKey; ?>"></label></td>
				<td><?= $dataSet -> tag_name; ?></td>
				<td>/<?= $dataSet -> tag_url; ?>/</td>
				<td><div class="color-indicator negative" data-state="<?= $dataSet -> tag_hidden; ?>"></div></td>
				<td><div class="color-indicator negative" data-state="<?= $dataSet -> tag_disabled; ?>"></div></td>
				<td style="text-align:center;"><?= $dataSet -> allocation; ?></td>
				<td class="bach-item-menu"><span>&equiv;</span><div class="dropdown-content"><div></div><a href="<?= CMS_SERVER_URL_BACKEND . $pageRequest -> urlPath; ?>tag/<?= $dataSet -> tag_id; ?>"><?= $language -> string('BUTTON_EDIT'); ?></a></div></td>
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

	div.be-module-container table.table-overview td.assignments { text-align:center; }
</style>




