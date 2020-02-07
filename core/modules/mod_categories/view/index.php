<?php

	#tk::dbug($categoriesList);
	
?>

<div class="be-module-container">

	<table class="table-overview">
		<thead>
			<tr>
				<td class="batch-selection-item"></td>
				<td><?= $language -> string('MOD_BECATEGORIES_NAME'); ?></td>
				<td><?= $language -> string('MOD_BECATEGORIES_URLAPPENDIX'); ?></td>
				<td style="text-align:center;"><?= $language -> string('MOD_BECATEGORIES_HIDDEN'); ?></td>
				<td style="text-align:center;"><?= $language -> string('MOD_BECATEGORIES_DISABLED'); ?></td>
				<td style="text-align:center;"><?= $language -> string('MOD_BECATEGORIES_ALLOCATION'); ?></td>
				<td class="bach-item-menu"></td>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach($categoriesList as $dataKey => $dataSet)
		{
			?>

			<tr class="trigger-batch-item">
				<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-checkbox" name="group-id[]" value="<?= $dataSet -> category_id; ?>" id="item-<?= $_dataKey; ?>"><label for="item-<?= $_dataKey; ?>"></label></td>
				<td><?= $dataSet -> category_name; ?></td>
				<td>/<?= $dataSet -> category_url; ?>/</td>
				<td><div class="color-indicator negative" data-state="<?= $dataSet -> category_hidden; ?>"></div></td>
				<td><div class="color-indicator negative" data-state="<?= $dataSet -> category_disabled; ?>"></div></td>
				<td style="text-align:center;"><?= $dataSet -> allocation; ?></td>
				<td class="bach-item-menu"><span>&equiv;</span><div class="dropdown-content"><div></div><a href="<?= CMS_SERVER_URL_BACKEND . $pageRequest -> urlPath; ?>category/<?= $dataSet -> category_id; ?>"><?= $language -> string('BUTTON_EDIT'); ?></a></div></td>
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




