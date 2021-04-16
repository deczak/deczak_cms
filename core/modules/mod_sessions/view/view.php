<?php
$_dataSet = &$sessionList[0];
$_dataSet -> time_create 	= ($_dataSet -> time_create == 0 ? '-' : date(CFG::GET() -> BACKEND -> TIME_FORMAT, $_dataSet -> time_create) );


?>

<div class="be-module-container forms-view">
	<div>
		<div class="inter-menu">
			<h2><?php echo $language -> string('MENU'); ?></h2>
			<hr>
			<ul>
			<li><a class="darkblue" href="#session-information"><?php echo $language -> string('SESSION INFORMATION'); ?></a></li>
			<li><a class="darkblue" href="#session-access"><?php echo $language -> string('M_BESESSION_HISTORY'); ?></a></li>
			</ul>
			<hr>
			<div class="delete-box">	
				<?php if($enableDelete) { ?>
					<fieldset class="ui fieldset button-only" data-xhr-target="session-delete" data-xhr-overwrite-target="delete/<?php echo $_dataSet -> data_id; ?>">	
						<div class="submit-container button-only">
							<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-trash-alt" data-icon="fa-trash-alt"></i></span><?php echo $language -> string('BUTTON_DELETE'); ?></button>
							<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-session-delete"><label for="protector-session-delete"></label></div>
						</div>
						<div class="result-box" data-error=""></div>
					</fieldset>
				<?php } ?>
			</div>
		</div>
	</div>
	<div>
		
		<fieldset class="ui fieldset" id="session-information" data-xhr-target="session-information">

			<input type="hidden" name="data_id" value="<?php echo $_dataSet -> data_id; ?>">

			<legend><?php echo $language -> string('SESSION INFORMATION'); ?></legend>

			<div>
		
				<div class="group width-100">

					<div class="group-head width-100"><?php echo $language -> string('USER'); ?></div>

					<div class="input width-25">
						<label>IP <?php echo $language -> string('ADDRESS'); ?></label>
						<input type="text" name="denied_ip" value="<?php echo $_dataSet -> user_ip; ?>">
					</div>

					<div class="input width-50">
						<label><?php echo $language -> string('M_BESESSION_SESSIONID'); ?></label>
						<input type="text" name="denied_desc" value="<?php echo $_dataSet -> session_id; ?>" maxlength="250">
					</div>

					<div class="input width-25">
						<label><?php echo $language -> string('DATE'); ?></label>
						<input type="text" name="denied_ip" value="<?php echo $_dataSet -> time_create; ?>">
					</div>
				</div>

				<div class="group width-100">

					<div class="input width-100">
						<label><?php echo $language -> string('M_BESESSION_USERAGENT'); ?></label>
						<input type="text" name="denied_desc" value="<?php echo $_dataSet -> user_agent; ?>" maxlength="250">
					</div>
				</div>

				<div class="group width-100">

					<div class="input width-100">
						<label><?php echo $language -> string('M_BESESSION_REFERRER'); ?></label>
						<input type="text" name="denied_desc" value="<?php echo $_dataSet -> pages[0] -> referer; ?>" maxlength="250">
					</div>
				</div>
						
			</div>

		</fieldset>
		
		<fieldset class="ui fieldset" id="session-access" data-xhr-target="session-access">

			<input type="hidden" name="data_id" value="<?php echo $_dataSet -> data_id; ?>">

			<legend><?php echo $language -> string('M_BESESSION_HISTORY'); ?></legend>

			<div>
		
				<div class="group width-100">
					<div class="input width-100">
			
						<table id="table-access">
							<thead>
								<tr>
									<td><?php echo $language -> string('TIMESTAMP'); ?></td>
									<td><?php echo $language -> string('PAGE'); ?></td>
								</tr>
							</thead>
							<tbody>

								<?php

								foreach($_dataSet -> pages as $page)
								{
								

									?>
									<tr>
										<td><?= date("d / m / Y H:i:s", $page -> time_access); ?></td>
										<td><?= $page -> page_title; ?></td>
									</tr>									
									<?php
								}
								?>

							</tbody>
						</table>

						<style>
							#table-access	{ width:100%; border: 2px solid white; margin-top: 14px; }
							#table-access	> thead td { font-weight:700; }
							#table-access	> thead td:nth-of-type(1) { width:190px; }
							#table-access td { padding: 2px 6px; font-size:0.9em;}
							#table-access tbody > tr { background:white; }

							#table-access tbody .right-item { margin:2px 8px; display:flex; align-items:center; position:relative; font-size:0.9em; background: rgb(214,187,35); padding: 3px 12px 1px 10px; border:1px solid black; }
							#table-access tbody .right-item label { min-width:110px; }
						</style>


					</div>
				</div>	
						
			</div>

		</fieldset>
		<br><br>

	</div>
</div>

