<?php


?>
<div class="be-module-container">

	<table class="table-overview">
		<thead>
			<tr>
				<td class="batch-selection-item"></td>
				<td class="user-id"><?php echo CLanguage::instance() -> getString('MOD_BEUSER_OV_TABLE_USERID'); ?></td>
				<td><?php echo CLanguage::instance() -> getString('MOD_BEUSER_OV_TABLE_USER'); ?></td>
				<td class="time-create"><?php echo CLanguage::instance() -> getString('MOD_BEUSER_OV_TABLE_CREATETIME'); ?></td>
				<td class="last-login"><?php echo CLanguage::instance() -> getString('MOD_BEUSER_OV_TABLE_LASTLOGIN'); ?></td>
				<td class="num-of-logins"><?php echo CLanguage::instance() -> getString('MOD_BEUSER_OV_TABLE_NUMLOGIN'); ?></td>
				<td class="bach-item-menu"></td>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach($users as $_dataKey => $_dataSet)
		{
			$_dataSet -> time_login 	= ($_dataSet -> time_login == 0 ? '-' : date(TIME_FORMAT_BACKENDVIEW, $_dataSet -> time_login) );
			$_dataSet -> time_create 	= ($_dataSet -> time_create == 0 ? '-' : date(TIME_FORMAT_BACKENDVIEW, $_dataSet -> time_create) );

			?>

			<tr class="trigger-batch-item">
				<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-checkbox" name="user-id[]" value="<?php echo $_dataSet -> user_id; ?>" id="item-<?php echo $_dataKey; ?>"><label for="item-<?php echo $_dataKey; ?>"></label></td>
				<td class="user-id"><?php echo $_dataSet -> user_id; ?></td>
				<td><?php echo $_dataSet -> user_name_first .' '. $_dataSet -> user_name_last; ?></td>
				<td class="time-create"><?php echo $_dataSet -> time_create; ?></td>
				<td class="last-login"><?php echo $_dataSet -> time_login; ?></td>
				<td class="num-of-logins"><?php echo $_dataSet -> login_count; ?></td>
				<td class="bach-item-menu"><span>&equiv;</span><div class="dropdown-content"><div></div><a href="<?php echo CMS_SERVER_URL_BACKEND . REQUESTED_PAGE_PATH; ?>user/<?php echo $_dataSet -> user_id; ?>"><?php echo CLanguage::instance() -> getString('MOD_BEUSER_OV_TABLE_EDITUSER'); ?></a></div></td>
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
				<td><?php echo CLanguage::instance() -> getString('MOD_BEUSER_OV_TABLE_SELECTALL'); ?></td>
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

	div.be-module-container table.table-overview td.user-id { width:115px; }
	div.be-module-container table.table-overview td.time-create { width:205px; }
	div.be-module-container table.table-overview td.last-login { width:205px; }
	div.be-module-container table.table-overview td.num-of-logins { width:115px; text-align:center; }

</style>




