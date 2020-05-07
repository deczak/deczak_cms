<?php

	$_pPageRequest 	= CPageRequest::instance();

?>

<div class="be-module-container">

	<table class="table-overview">
		<thead>
			<tr>
				<td class="batch-selection-item"></td>
				<td><?php echo CLanguage::instance() -> getString('MOD_LOGINO_OBJECT_NAME'); ?></td>
				<td><?php echo CLanguage::instance() -> getString('MOD_LOGINO_OBJECT_DESC'); ?></td>
				<td><?php echo CLanguage::instance() -> getString('TIME_CREATE_AT'); ?></td>
				<td><?php echo CLanguage::instance() -> getString('CREATE_BY'); ?></td>
				<td class="bach-item-menu"></td>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach($login_objects as $_dataKey => $_dataSet)
		{

			$_dataSet -> create_time 	= ($_dataSet -> create_time == 0 ? '-' : date(CFG::GET() -> BACKEND -> TIME_FORMAT, $_dataSet -> create_time) );


			?>

			<tr class="trigger-batch-item">
				<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-checkbox" name="object-id[]" value="<?= $_dataSet -> object_id; ?>" id="item-<?= $_dataKey; ?>"><label for="item-<?= $_dataKey; ?>"></label></td>
				<td class=""><?= $_dataSet -> object_id; ?></td>
				<td class=""><?= $_dataSet -> object_description; ?></td>
				<td class=""><?= $_dataSet -> create_time; ?></td>
				<td class=""><?php echo tk::getBackendUserName($pDatabase, $_dataSet -> create_by); ?></td>
				<td class="bach-item-menu"><span>&equiv;</span><div class="dropdown-content"><div></div><a href="<?= CMS_SERVER_URL_BACKEND . $_pPageRequest -> urlPath; ?>object/<?= $_dataSet -> object_id; ?>"><?= CLanguage::instance() -> getString('MOD_LOGINO_EDIT_OBJECT'); ?></a></div></td>
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
				<td><?= CLanguage::instance() -> getString('SELECT_ALL'); ?></td>
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



<pre>


</pre>


