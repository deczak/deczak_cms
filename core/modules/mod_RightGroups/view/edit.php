<?php

#tk::dbug($right_groups);
#tk::dbug($user_groups);

	$right_group = &$right_groups[0];
 
#	$right_group -> time_login 	= ($right_group -> time_login == 0 ? '-' : date(TIME_FORMAT_BACKENDVIEW, $right_group -> time_login) );
#	$right_group -> time_create 	= ($right_group -> time_create == 0 ? '-' : date(TIME_FORMAT_BACKENDVIEW, $right_group -> time_create) );

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
			<li><a class="darkblue" href="#group-rights"><?php echo CLanguage::get() -> string('MOD_RGROUPS_GROUP_RIGHTS'); ?></a></li>
			</ul>
			<hr>
			<div class="delete-box">	
				<?php if($enableDelete) { ?>
					<fieldset class="ui fieldset" data-xhr-target="group-delete" data-xhr-overwrite-target="delete/<?php echo $right_group -> group_id; ?>">	
						<div class="submit-container button-only">
							<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-trash-alt" data-icon="fa-trash-alt"></i></span><?php echo CLanguage::get() -> string('BUTTON_DELETE'); ?></button>
							<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-user-delete"><label for="protector-user-delete"></label></div>
						</div>
						<div class="result-box" data-error=""></div>
					</fieldset>
				<?php } ?>
			</div>
		</div>
	</div>
	<div>

		
		<fieldset class="ui fieldset submit-able" id="group-data" data-xhr-target="group-data" data-xhr-overwrite-target="edit/<?php echo $right_group -> group_id; ?>">
			<legend><?php echo CLanguage::get() -> string('MOD_RGROUPS_GROUP_INFO'); ?></legend>
			<div>
				<!-- group -->
				<div class="group width-100">
					<div class="input width-25">
						<label><?php echo CLanguage::get() -> string('MOD_RGROUPS_GROUP_ID'); ?></label>
						<input type="text" disabled value="<?php echo $right_group -> group_id; ?>">
						<i class="fas fa-lock"></i>
					</div>

					<div class="input width-25">
						<label><?php echo CLanguage::get() -> string('MOD_RGROUPS_GROUP_NAME'); ?></label>
						<input type="text" name="group_name" value="<?php echo $right_group -> group_name; ?>">
					</div>

					<div class="input width-25">
					</div>

					<div class="input width-25">
					</div>
				</div>

			</div>

			<?php if($enableEdit) { ?>

				<div class="result-box" data-error=""></div>

				<!-- Submit button - beware of fieldset name -->

				<div class="submit-container">
					<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-save" data-icon="fa-save"></i></span><?php echo CLanguage::get() -> string('BUTTON_SAVE'); ?></button>
					<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-group-data"><label for="protector-group-data"></label></div>
				</div>
				
			<?php } ?>

		</fieldset>



		<fieldset class="ui fieldset submit-able" id="group-rights" data-xhr-target="group-rights" data-xhr-overwrite-target="edit/<?php echo $right_group -> group_id; ?>">
			<legend><?php echo CLanguage::get() -> string('MOD_RGROUPS_GROUP_RIGHTS'); ?></legend>
			<div>

				<!-- group -->
				<div class="group width-100">
					<div class="input width-100">
			
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
									<tr style="">
										<td><?php echo $_module -> module_name; ?></td>
										<td>
											<div style="display:flex;">
											<?php
											foreach($_moduleData -> module_rights as $_right)
											{
												$_isActiveRight = '';
												$_tempBullShit = $_module -> module_id;

												if(property_exists($right_group -> group_rights, $_module -> module_id) && in_array($_right -> name, $right_group -> group_rights -> $_tempBullShit))
												{
													$_isActiveRight = 'checked';
												}

												?>
												<div class="ui pick-item">
													<input type="checkbox" id="<?php echo $_module -> module_id .'-'. $_right -> name; ?>" name="group_rights[<?php echo $_module -> module_id; ?>][]" value="<?php echo $_right -> name; ?>" <?php echo $_isActiveRight; ?>>
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
							#table-mod-group-rights	{ width:100%; border: 2px solid white; margin-top: 14px; }
							#table-mod-group-rights	> thead td { font-size:0.9em; font-weight:700; }
							#table-mod-group-rights	> thead td:first-child { width:225px; }
							#table-mod-group-rights td { padding: 2px 6px; }
							#table-mod-group-rights tbody > tr { background:white; }

						</style>


					</div>
				</div>	
		
			</div>

			<?php if($enableEdit) { ?>

				<div class="result-box" data-error=""></div>

				<!-- Submit button - beware of fieldset name -->

				<div class="submit-container">
					<button class="ui button icon labeled trigger-submit-fieldset " type="button" disabled><span><i class="fas fa-save" data-icon="fa-save"></i></span><?php echo CLanguage::get() -> string('BUTTON_SAVE'); ?></button>
					<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-group-rights"><label for="protector-group-rights"></label></div>
				</div>

			<?php } ?>

		</fieldset>






			<br><br>

	</div>
</div>
<?php tk::dbug($_aActiveModules); ?>

