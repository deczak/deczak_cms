<?php


	$right_group = &$right_groups[0];
 
	$_pModules		 =	CModules::instance();
	$_aActiveModules = $_pModules -> getModules();	

?>

<div class="be-module-container forms-view">
	<div>
		<div class="inter-menu">
			<h2><?php echo CLanguage::get() -> string('MENU'); ?></h2>
			<hr>
			<ul>
			<li><a class="darkblue" href="#group-data"><?php echo CLanguage::get() -> string('MOD_RGROUPS_GROUP_INFO'); ?></a></li>
			</ul>
			<hr>
			<div class="delete-box">	
			</div>
		</div>
	</div>
	<div>

		
		<fieldset class="ui fieldset submit-able" id="group-data" data-xhr-target="group-data">
			<legend><?php echo CLanguage::get() -> string('MOD_RGROUP_SUB_CREATE_NAME'); ?></legend>
			<div>
				<!-- group -->
				<div class="group width-100">

					<div class="group-head width-100"><?php echo CLanguage::get() -> string('MOD_RGROUPS_GROUP_INFO'); ?></div>

					<div class="input width-25">
						<label><?php echo CLanguage::get() -> string('MOD_RGROUPS_GROUP_NAME'); ?></label>
						<input type="text" name="group_name" value="">
					</div>

					<div class="input width-25">
					</div>

					<div class="input width-25">
					</div>

					<div class="input width-25">
					</div>
				</div>







				<!-- group -->
				<div class="group width-100">
					<div class="input width-100">
			
					<div class="group-head width-100"><?php echo CLanguage::get() -> string('MOD_RGROUPS_GROUP_RIGHTS'); ?></div>
						<table id="table-mod-group-rights">
							<thead>
								<tr>
									<td>Module name</td>
									<td>Module rights</td>
								</tr>
							</thead>
							<tbody>

								<?php
								foreach($_aActiveModules as $_module)
								{
									switch($_module -> module_type)
									{
										case 'core'  :	$_modLocation	= CMS_SERVER_ROOT . DIR_CORE . DIR_MODULES . $_module -> module_location .'/';									
														break;

										case 'mantle':	$_modLocation	= CMS_SERVER_ROOT . DIR_MANTLE . DIR_MODULES . $_module -> module_location .'/';
														break;
									}

									$_moduleData = file_get_contents($_modLocation .'/module.json');
									$_moduleData = json_decode($_moduleData);

									?>
									<tr>
										<td><?php echo $_module -> module_name; ?></td>
										<td>
											<div style="display:flex;">
											<?php
											foreach($_moduleData -> rights as $_right)
											{
												?>
												<div class="ui pick-item">
													<input type="checkbox" id="<?php echo $_module -> module_id .'-'. $_right -> name; ?>" name="group_rights[<?php echo $_module -> module_id; ?>][]" value="<?php echo $_right -> name; ?>">
													<label for="<?php echo $_module -> module_id .'-'. $_right -> name; ?>" title="<?php echo CLanguage::get() -> string($_right -> desc); ?>">
														<?php echo CLanguage::get() -> string($_right -> desc); ?>
													</label>
												</div>
												<?php
											}
											?>
											</div>
										</td>
									</tr>									
									<?php
								}
								?>

							</tbody>
						</table>

						<style>
							#table-mod-group-rights	{ width:100%; border: 1px solid black; margin-top: 14px; }
							#table-mod-group-rights	> thead td { font-size:0.9em; font-weight:700; }
							#table-mod-group-rights	> thead td:first-child { width:225px; }
							#table-mod-group-rights td { padding: 2px 6px; }
							#table-mod-group-rights tbody > tr { background:white; }						
						</style>


					</div>
				</div>	
		

			</div>

			<div class="result-box" data-error=""></div>

			<!-- Submit button - beware of fieldset name -->

			<div class="submit-container">
				<button class="ui button icon labeled trigger-submit-fieldset " type="button" disabled><i class="fas fa-save"></i><?php echo CLanguage::get() -> string('BUTTON_SAVE'); ?></button>
				<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-group-rights"><label for="protector-group-rights"></label></div>
			</div>

		</fieldset>






			<br><br>

	</div>
</div>

