<div class="be-module-container forms-view">
	<div>
		<div class="inter-menu">
			<h2><?= CLanguage::string('MENU'); ?></h2>
			<hr>
			<ul>
			<li><a class="darkblue" href="#user-create"><?= CLanguage::string('MOD_BEUSER_FM_CREATEUSER'); ?></a></li>
			</ul>
			<hr>
		</div>
	</div>
	<div>


		<fieldset class="ui fieldset submit-able" id="user-create" data-xhr-target="create">
			<legend><?= CLanguage::string('MOD_BEUSER_FM_CREATEUSER'); ?></legend>

			<div>

				<!-- group -->

				<div class="group width-100">
					<div class="group-head width-100"><?= CLanguage::string('MOD_BEUSER_FM_USERINFO'); ?></div>

					<div class="input width-25">
						<label><?= CLanguage::string('MOD_BEUSER_FM_FIRSTNAME'); ?></label>
						<input type="text" name="user_name_first" value="">
					</div>

					<div class="input width-25">
						<label><?= CLanguage::string('MOD_BEUSER_FM_LASTNAME'); ?></label>
						<input type="text" name="user_name_last" value="">
					</div>

					<div class="input width-50">
						<label><?= CLanguage::string('MOD_BEUSER_FM_MAILADDRESS'); ?></label>
						<input type="text" name="user_mail" value="">
					</div>

				</div>

				<!-- group -->

				<div class="group width-100">
					<div class="group-head width-100"><?= CLanguage::string('MOD_BEUSER_FM_LOGININFO'); ?></div>

					<div class="input width-25">
						<label><?= CLanguage::string('MOD_BEUSER_FM_LOGINUSERNAME'); ?></label>
						<input type="text" name="login_name" value="">
					</div>

					<div class="input width-25">
						<label><?= CLanguage::string('MOD_BEUSER_FM_LOGINPASSWORD'); ?></label>
						<input type="password" name="login_pass_a" value="">
					</div>

					<div class="input width-25">
						<label><?= CLanguage::string('MOD_BEUSER_FM_LOGINPASSWORD2'); ?></label>
						<input type="password" name="login_pass_b" value="">
					</div>

				</div>

				<!-- group -->

				<div class="group width-100">
					<div class="group-head width-100"><?= CLanguage::string('MOD_BEUSER_FM_BACKENDSTTNGS'); ?></div>

					<div class="input width-25">
						<label><?= CLanguage::string('LANGUAGE'); ?></label>
						<div class="select-wrapper">
						<select name="language">
							<option value="en">English</option>
							<option value="de">Deutsch</option>
						</select>	
						</div>
					</div>

					<div class="input width-25">
						<?php if(CFG::GET() -> USER_SYSTEM -> REMOTE_USER -> ENABLED) { ?>
						<label><?= CLanguage::string('MOD_BEUSER_FM_REMOTEAUTH'); ?></label>
						<div class="select-wrapper">
						<select name="allow_remote">
							<option value="0"><?= CLanguage::string('MOD_BEUSER_FM_REMOTEAUTH_0_NOTALLOWED'); ?></option>
							<option value="1"><?= CLanguage::string('MOD_BEUSER_FM_REMOTEAUTH_1_ALLOWED'); ?></option>
						</select>	
						</div>
						<?php } ?>
					</div>

					<div class="input width-25">
					</div>
					<div class="input width-25">
					</div>

				</div>

			</div>

			<div class="result-box" data-error=""></div>

			<!-- Submit button - beware of fieldset name -->

			<div class="submit-container">
				<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-save" data-icon="fa-save"></i></span><?= CLanguage::string('BUTTON_SAVE'); ?></button>
				<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-create-user"><label for="protector-create-user"></label></div>
			</div>

		</fieldset>



			<br><br>

	</div>
</div>
