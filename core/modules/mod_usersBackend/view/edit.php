<?php

	$users = &$users[0];
 
	$users -> time_login 	= ($users -> time_login == 0 ? '-' : date(TIME_FORMAT_BACKENDVIEW, $users -> time_login) );
	$users -> time_create 	= ($users -> time_create == 0 ? '-' : date(TIME_FORMAT_BACKENDVIEW, $users -> time_create) );

	function
	isActiveGroup($group_id, &$groups)
	{
		foreach($groups as $group)
			if($group -> group_id === $group_id)
				return true;
		return false;
	}

?>

<div class="be-module-container forms-view">
	<div>
		<div class="inter-menu">
			<h2><?php echo CLanguage::instance() -> getString('MOD_BEUSER_MENU'); ?></h2>
			<hr>
			<ul>
			<li><a class="darkblue" href="#user-data"><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_USERINFO'); ?></a></li>
			<li><a class="darkblue" href="#user-auth"><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_USERAUTH'); ?></a></li>
			<li><a class="darkblue" href="#user-rights"><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_USERRIGHTS'); ?></a></li>
			</ul>
			<hr>
			<div class="delete-box">	
				<fieldset class="ui fieldset" data-xhr-target="user-delete" data-xhr-overwrite-target="delete/<?php echo $users -> user_id; ?>">	
					<div class="submit-container">
						<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-user-delete"><label for="protector-user-delete"></label></div>
						<button class="trigger-submit-fieldset" type="button" disabled><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_BTN_DELETE'); ?></button>
					</div>
					<div class="result-box" data-error=""></div>
				</fieldset>
			</div>
		</div>
	</div>
	<div>

		
		<fieldset class="ui fieldset submit-able" id="user-data" data-xhr-target="user-data">
			<legend><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_USERINFO'); ?></legend>
			<div>
				<!-- group -->
				<div class="group width-100">
					<div class="group-head width-100"><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_ACCOUNTINFO'); ?></div>

					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_USERID'); ?></label>
						<input type="text" disabled value="<?php echo $users -> user_id; ?>">
						<i class="fas fa-lock"></i>
					</div>

					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_CREATEDAT'); ?></label>
						<input type="text" disabled value="<?php echo $users -> time_create; ?>">
						<i class="fas fa-lock"></i>
					</div>

					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_LASTLOGIN'); ?></label>
						<input type="text" disabled value="<?php echo $users -> time_login; ?>">
						<i class="fas fa-lock"></i>
					</div>

					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_LOGINCOUNT'); ?></label>
						<input type="text" disabled value="<?php echo $users -> login_count; ?>">
						<i class="fas fa-lock"></i>
					</div>
				</div>

				<!-- group -->

				<div class="group width-100">
					<div class="group-head width-100"><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_USERINFO'); ?></div>

					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_FIRSTNAME'); ?></label>
						<input type="text" name="user_name_first" value="<?php echo $users -> user_name_first; ?>">
					</div>

					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_LASTNAME'); ?></label>
						<input type="text" name="user_name_last" value="<?php echo $users -> user_name_last; ?>">
					</div>

					<div class="input width-50">
						<label><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_MAILADDRESS'); ?></label>
						<input type="text" name="user_mail" value="<?php echo $users -> user_mail; ?>">
					</div>

				</div>

			</div>

			<div class="result-box" data-error=""></div>

			<!-- Submit button - beware of fieldset name -->

			<div class="submit-container">
				<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-user-data"><label for="protector-user-data"></label></div>
				<button class="trigger-submit-fieldset" type="button" disabled><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_BTN_SAVE'); ?></button>
			</div>

		</fieldset>



		<fieldset class="ui fieldset submit-able" id="user-auth" data-xhr-target="user-auth">
			<legend><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_USERAUTH'); ?></legend>

			<div>

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
				<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-user-auth"><label for="protector-user-auth"></label></div>
				<button class="trigger-submit-fieldset" type="button" disabled><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_BTN_SAVE'); ?></button>
			</div>

		</fieldset>


		<fieldset class="ui fieldset submit-able" id="user-rights" data-xhr-target="user-rights">
			<legend><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_USERRIGHTS'); ?></legend>
			<div>

				<!-- group -->
				<div class="group width-75">
					<div class="group-head width-100"><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_RIGTHGROUPS'); ?></div>
					<div class="input width-100">
						<div style="display:flex;flex-wrap:wrap;">
							<input type="hidden" name="groups" value="">
							<?php 						
							foreach($right_groups as $_group)
							{

								$isActiveGroup  = isActiveGroup($_group -> group_id, $user_groups);


								echo '<div style="  position:relative;border:1px solid grey; border-radius:4px; padding:4px 7px; margin:2px;background:rgb(233,223,37); font-size:0.80em; font-weight:500;"><input type="checkbox" name="groups[]" value="'. $_group -> group_id .'" id="group-'. $_group -> group_id .'" '. ($isActiveGroup ? 'checked' : '') .'><label for="group-'. $_group -> group_id .'" style="width:200px;display:block;">'. ucfirst($_group -> group_name) .'</label></div>';
							}
							?>
						</div>
					</div>
				</div>	

				<!-- group -->
				<div class="group width-25">
					<div class="group-head width-100"><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_LOCKEDSTATE'); ?></div>
					<div class="input width-100">
						<select name="is_locked">
							<option value="0" <?php echo ($users -> is_locked === 0 ? 'selected' : ''); ?>><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_LOCKED_0_NOTLOCKED'); ?></option>
							<option value="1" <?php echo ($users -> is_locked === 1 ? 'selected' : ''); ?>><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_LOCKED_1_NOTVERIFIED'); ?></option>
							<option value="2" <?php echo ($users -> is_locked === 2 ? 'selected' : ''); ?>><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_LOCKED_2_FAILEDLOGIN'); ?></option>
						<select>
					</div>
				</div>			

			</div>

			<div class="result-box" data-error=""></div>

			<!-- Submit button - beware of fieldset name -->

			<div class="submit-container">
				<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-user-rights"><label for="protector-user-rights"></label></div>
				<button class="trigger-submit-fieldset " type="button" disabled><?php echo CLanguage::instance() -> getString('MOD_BEUSER_FM_BTN_SAVE'); ?></button>
			</div>

		</fieldset>



			<br><br>

	</div>
</div>

