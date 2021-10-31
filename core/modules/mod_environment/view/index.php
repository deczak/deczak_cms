<div class="be-module-container forms-view">

	<div>
		<div class="inter-menu">
			<h2><?php echo $language -> string('MENU'); ?></h2>
			<hr>
			<ul>
				<li><a class="darkblue" href="#backend"><?php echo $language -> string('M_BEENV_BACKENDGENERAL'); ?></a></li>
				<li><a class="darkblue" href="#error"><?php echo $language -> string('M_BEENV_ERROR'); ?></a></li>
				<li><a class="darkblue" href="#remote-system"><?php echo $language -> string('M_BEENV_REMOTEUSER'); ?></a></li>
				<li><a class="darkblue" href="#update-sitemap"><?php echo $language -> string('M_BEENV_GENERATE'); ?></a></li>
			</ul>
	
		</div>
	</div>

	<div>

		<fieldset class="ui fieldset submit-able" id="backend" data-xhr-target="edit_backend" data-xhr-overwrite-target="edit/1">
		
			<div>

				<div class="group width-100">

					<div class="group-head width-100" style="margin-bottom:15px;"><?= $language -> string('M_BEENV_BACKENDGENERAL'); ?></div>

					<div style="display:flex; align-items:center; margin-bottom:15px; width:100%;">
						<div  class="input" style="width:221px; flex-shrink:0;">
							<input type="text" name="backend_timeformat" <?= (!$enableEdit ? 'disabled' : ''); ?> value="<?= CFG::GET() -> BACKEND -> TIME_FORMAT; ?>" style="width:100%;">
								<?= (!$enableEdit ? '<i class="fas fa-lock"></i>' : ''); ?>
						</div>
						<div style="font-weight:500; margin-left:20px; width:100%; padding-bottom:8px;">
							<?= $language -> string('M_BEENV_BACKGEN_TIMEFORMAT'); ?>
						</div>
					</div>

				</div>

			</div>

			<?php if($enableEdit) { ?>

			<div class="result-box" data-error=""></div>

			<!-- Submit button - beware of fieldset name -->

			<div class="submit-container">
				<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-save" data-icon="fa-save"></i></span><?php echo CLanguage::instance() -> getString('BUTTON_SAVE'); ?></button>
				<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-backend-settings"><label for="protector-backend-settings"></label></div>
			</div>


			<?php } ?>

		</fieldset>



	
		<fieldset class="ui fieldset submit-able" id="error" data-xhr-target="edit_error" data-xhr-overwrite-target="edit/1">
		
			<div>
				
				<div class="group width-100">

					<div class="group-head width-100" style="margin-bottom:15px;"><?= $language -> string('M_BEENV_ERROR'); ?></div>

					<div style="display:flex; align-items:center; margin-bottom:15px; width:100%;">
						<div  class="input" style="width:221px; flex-shrink:0;">
							<div class="select-wrapper">
							<select name="enable_error_file" style="width:100%;" <?= (!$enableEdit ? 'disabled' : ''); ?>>
								<option value="0" <?= (!CFG::GET() -> ERROR_SYSTEM -> ERROR_FILE -> ENABLED ? 'selected' : ''); ?>><?php echo CLanguage::get() -> string('NO'); ?></option>
								<option value="1" <?= (CFG::GET() -> ERROR_SYSTEM -> ERROR_FILE -> ENABLED ? 'selected' : ''); ?>><?php echo CLanguage::get() -> string('YES'); ?></option>
							</select>	
							</div>
						</div>
						<div style="font-weight:500; margin-left:20px; width:100%; padding-bottom:8px;">
							<?= $language -> string('M_BEENV_ERROR_ENABLE_EFILE'); ?>
						</div>
					</div>

					<div style="display:flex; align-items:center; margin-bottom:15px; width:100%;">
						<div  class="input" style="width:221px; flex-shrink:0;">
							<div class="select-wrapper">
							<select name="enable_log_file" style="width:100%;" <?= (!$enableEdit ? 'disabled' : ''); ?>>
								<option value="0" <?= (!CFG::GET() -> ERROR_SYSTEM -> LOG_FILE -> ENABLED ? 'selected' : ''); ?>><?php echo CLanguage::get() -> string('NO'); ?></option>
								<option value="1" <?= (CFG::GET() -> ERROR_SYSTEM -> LOG_FILE -> ENABLED ? 'selected' : ''); ?>><?php echo CLanguage::get() -> string('YES'); ?></option>
							</select>	
							</div>
						</div>
						<div style="font-weight:500; margin-left:20px; width:100%; padding-bottom:8px;">
							<?= $language -> string('M_BEENV_ERROR_ENABLE_LFILE'); ?>
						</div>
					</div>

					<div style="display:flex; align-items:center; margin-bottom:15px; width:100%;">
						<div  class="input" style="width:221px; flex-shrink:0;">
							<div class="select-wrapper">
							<select name="error_log_file_mode" style="width:100%;" <?= (!$enableEdit ? 'disabled' : ''); ?>>
								<option value="1" <?= (CFG::GET() -> ERROR_SYSTEM -> LOG_FILE -> LOG_MODE === 1 ? 'selected' : ''); ?>><?php echo CLanguage::get() -> string('M_BEENV_ERROR_LOG_MODE_1'); ?></option>
								<option value="2" <?= (CFG::GET() -> ERROR_SYSTEM -> LOG_FILE -> LOG_MODE === 2 ? 'selected' : ''); ?>><?php echo CLanguage::get() -> string('M_BEENV_ERROR_LOG_MODE_2'); ?></option>
							</select>	
							</div>
						</div>
						<div style="font-weight:500; margin-left:20px; width:100%; padding-bottom:8px;">
							<?= $language -> string('M_BEENV_ERROR_LOG_MODE'); ?>
						</div>
					</div>


					<div class=" delete-box" style="padding-top: 0px; border-radius:3px; display:flex; flex-direction:column; padding-left:4px;">

						<div style="display:flex; align-items:center; margin-bottom:15px;">
							<div style="width:213px; border-radius:3px; background:white;">
							<fieldset class="ui fieldset" data-xhr-target="error_clear" data-xhr-overwrite-target="edit/1" style="margin:0px;">	
								<div class="submit-container button-only">
									<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-sync-alt" data-icon="fa-sync-alt"></i></span><?php echo $language -> string('BUTTON_DELETE'); ?></button>
									<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-error-clear"><label for="protector-error-clear"></label></div>
								</div>
							</fieldset>
							</div>
							<div style="font-weight:500; margin-left:20px;">
								<?= $language -> string('M_BEENV_ERROR_CLEAR'); ?>
							</div>
						</div>



				</div>

			</div>

			<?php if($enableEdit) { ?>

			<div class="result-box" data-error=""></div>

			<!-- Submit button - beware of fieldset name -->

			<div class="submit-container">
				<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-save" data-icon="fa-save"></i></span><?php echo CLanguage::instance() -> getString('BUTTON_SAVE'); ?></button>
				<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-error-settings"><label for="protector-error-settings"></label></div>
			</div>


			<?php } ?>

		</fieldset>
	



		<fieldset class="ui fieldset submit-able" id="remote-system" data-xhr-target="edit_remoteuser" data-xhr-overwrite-target="edit/1">

			<div>

				<div class="group width-100">

					<div class="group-head width-100" style="margin-bottom:15px;"><?= $language -> string('M_BEENV_REMOTEUSER'); ?></div>

					<div style="display:flex; align-items:center; margin-bottom:15px;  width:100%;">
						<div class="input" style="width:221px; flex-shrink:0;">
							<div class="select-wrapper">
							<select name="remote_enable" style="width:100%;" <?= (!$enableEdit ? 'disabled' : ''); ?>>
								<option value="0" <?= (!CFG::GET() -> USER_SYSTEM -> REMOTE_USER -> ENABLED ? 'selected' : ''); ?>><?php echo CLanguage::get() -> string('NO'); ?></option>
								<option value="1" <?= (CFG::GET() -> USER_SYSTEM -> REMOTE_USER -> ENABLED ? 'selected' : ''); ?>><?php echo CLanguage::get() -> string('YES'); ?></option>
							</select>	
							</div>
						</div>
						<div style="font-weight:500; margin-left:20px; width:100%; padding-bottom:8px;">
							<?= $language -> string('M_BEENV_REMOTEUSER_ENABLE'); ?>
						</div>
					</div>

					<div style="display:flex; align-items:center; margin-bottom:15px; width:100%;">
						<div  class="input" style="width:221px; flex-shrink:0;">
							<input type="text" name="remote_timeout" <?= (!$enableEdit ? 'disabled' : ''); ?> value="<?= CFG::GET() -> USER_SYSTEM -> REMOTE_USER -> REVOKE_RIGHTS; ?>" style="width:100%;">
								<?= (!$enableEdit ? '<i class="fas fa-lock"></i>' : ''); ?>
						</div>
						<div style="font-weight:500; margin-left:20px; width:100%; padding-bottom:8px;">
							<?= $language -> string('M_BEENV_REMOTEUSER_TIMEOUT'); ?>
						</div>
					</div>

					<div style="display:flex; align-items:center; margin-bottom:15px; width:100%;">
						<div class="input" style="width:221px; flex-shrink:0;">
							<div class="select-wrapper">
							<select name="remote_report" style="width:100%;" <?= (!$enableEdit ? 'disabled' : ''); ?>>
								<option value="0" <?= (!CFG::GET() -> USER_SYSTEM -> REMOTE_USER -> REPORT_REVOKE ? 'selected' : ''); ?>><?php echo CLanguage::get() -> string('NO'); ?></option>
								<option value="1" <?= (CFG::GET() -> USER_SYSTEM -> REMOTE_USER -> REPORT_REVOKE ? 'selected' : ''); ?>><?php echo CLanguage::get() -> string('YES'); ?></option>
							</select>	
							</div>
						</div>
						<div style="font-weight:500; margin-left:20px; width:100%; padding-bottom:8px;">
							<?= $language -> string('M_BEENV_REMOTEUSER_REPORT'); ?>
						</div>
					</div>

				</div>

			</div>

			<?php if($enableEdit) { ?>

			<div class="result-box" data-error=""></div>

			<!-- Submit button - beware of fieldset name -->

			<div class="submit-container">
				<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-save" data-icon="fa-save"></i></span><?php echo CLanguage::instance() -> getString('BUTTON_SAVE'); ?></button>
				<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-login-objects"><label for="protector-login-objects"></label></div>
			</div>


			<?php } ?>

		</fieldset>
		


		<fieldset class="ui fieldset submit-able" id="update-sitemap">

			<div>

				<div class="group width-100">

					<div class="group-head width-100"><?= $language -> string('M_BEENV_GENERATE'); ?></div>

					<div class="ui" style="width:100%;"><div class="result-box" data-error="2">
						<?= $language -> string('M_BEENV_GENERATE_NOTE'); ?>		
					</div></div>
			

					<div class="delete-box" style="padding: 15px 23px; border-radius:3px; display:flex; flex-direction:column;">

						<div style="display:flex; align-items:center; margin-bottom:15px;">
							<div style="width:213px; border-radius:3px; background:white;">
							<fieldset class="ui fieldset" data-xhr-target="update_htaccess" data-xhr-overwrite-target="edit/1" style="margin:0px;">	
								<div class="submit-container button-only">
									<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-sync-alt" data-icon="fa-sync-alt"></i></span><?php echo $language -> string('M_BEENV_UPDATE'); ?></button>
									<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-update-htaccess"><label for="protector-update-htaccess"></label></div>
								</div>
							</fieldset>
							</div>
							<div style="font-weight:500; margin-left:20px;">
								<?= $language -> string('M_BEENV_GEN_HTACCESS'); ?>
							</div>
						</div>

						<div style="display:flex; align-items:center; ">
							<div style="width:213px; border-radius:3px; background:white;">
							<fieldset class="ui fieldset" data-xhr-target="update_sitemap" data-xhr-overwrite-target="edit/1" style="margin:0px;">	
								<div class="submit-container button-only">
									<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-sync-alt" data-icon="fa-sync-alt"></i></span><?php echo $language -> string('M_BEENV_UPDATE'); ?></button>
									<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-update-sitemap"><label for="protector-update-sitemap"></label></div>
								</div>
							</fieldset>
							</div>
							<div style="font-weight:500; margin-left:20px;">
								<?= $language -> string('M_BEENV_GEN_SITEMAP'); ?>
							</div>
						</div>

					</div>



					<div class="ui" style="width:100%;"><div class="result-box" data-error="2">
						<?= $language -> string('M_BEENV_GENERATE_RES_NOTE'); ?>		
					</div></div>



			
					<div class="delete-box" style="padding: 15px 23px; border-radius:3px; display:flex; flex-direction:column;">

						<div style="display:flex; align-items:center; margin-bottom:15px;">
							<div style="width:213px; border-radius:3px; background:white;">
							<fieldset class="ui fieldset" data-xhr-target="update_resources" data-xhr-overwrite-target="edit/1" style="margin:0px;">	
								<div class="submit-container button-only">
									<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-sync-alt" data-icon="fa-sync-alt"></i></span><?php echo $language -> string('M_BEENV_UPDATE'); ?></button>
									<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-update-resources"><label for="protector-update-resources"></label></div>
								</div>
							</fieldset>
							</div>
							<div style="font-weight:500; margin-left:20px;">
								<?= $language -> string('M_BEENV_GENERATE_RES'); ?>
							</div>
						</div>

					</div>



				</div>

			</div>

		</fieldset>

	</div>
</div>

<br><br>