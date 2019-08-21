<div class="be-module-container forms-view">
	<div>
		<div class="inter-menu">
			<h2><?php echo CLanguage::instance() -> getString('MOD_BEUSER_MENU'); ?></h2>
			<hr>
			<ul>
			<li><a class="darkblue" href="#user-create"><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_CREATEUSER'); ?></a></li>
			</ul>
			<hr>
		</div>
	</div>
	<div>


		<fieldset class="ui fieldset submit-able" id="user-create" data-xhr-target="user-create">
			<legend><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_CREATEUSER'); ?></legend>

			<div>

				<!-- group -->

				<div class="group width-100">
					<div class="group-head width-100"><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_USERINFO'); ?></div>

					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_FIRSTNAME'); ?></label>
						<input type="text" name="user_name_first" value="">
					</div>

					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_LASTNAME'); ?></label>
						<input type="text" name="user_name_last" value="">
					</div>

					<div class="input width-50">
						<label><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_MAILADDRESS'); ?></label>
						<input type="text" name="user_mail" value="">
					</div>

				</div>

				<!-- group -->

				<div class="group width-100">
					<div class="group-head width-100"><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_LOGININFO'); ?></div>

					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_LOGINUSERNAME'); ?></label>
						<input type="text" name="login_name" value="">
					</div>

					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_LOGINPASSWORD'); ?></label>
						<input type="password" name="login_pass_a" value="">
					</div>

					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_LOGINPASSWORD2'); ?></label>
						<input type="password" name="login_pass_b" value="">
					</div>

				</div>

			</div>

			<div class="result-box" data-error=""></div>

			<!-- Submit button - beware of fieldset name -->

			<div class="submit-container">
				<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-create-user"><label for="protector-create-user"></label></div>
				<button class="trigger-submit-fieldset" type="button" disabled><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_BTN_SAVE'); ?></button>
			</div>

		</fieldset>



			<br><br>

	</div>
</div>
