<?php

	$users = &$usersList[0];
 
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
			<h2><?= CLanguage::get() -> string('MENU'); ?></h2>
			<hr>
			<ul>
			<li><a class="darkblue" href="#user-data"><?= CLanguage::get() -> string('MOD_BEUSER_FM_USERINFO'); ?></a></li>
			<li><a class="darkblue" href="#user-auth"><?= CLanguage::get() -> string('MOD_BEUSER_FM_USERAUTH'); ?></a></li>
			<li><a class="darkblue" href="#user-rights"><?= CLanguage::get() -> string('MOD_BEUSER_FM_USERRIGHTS'); ?></a></li>
			</ul>
			<hr>
			<div class="delete-box">	
				<fieldset class="ui fieldset" data-xhr-target="user-delete" data-xhr-overwrite-target="delete/<?= $users -> user_id; ?>">	
					<div class="submit-container button-only">
						<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-trash-alt" data-icon="fa-trash-alt"></i></span><?= CLanguage::get() -> string('BUTTON_DELETE'); ?></button>
						<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-user-delete"><label for="protector-user-delete"></label></div>
					</div>
					<div class="result-box" data-error=""></div>
				</fieldset>
			</div>
		</div>
	</div>
	<div>

		
		<fieldset class="ui fieldset submit-able" id="user-data" data-xhr-target="user-data" data-xhr-overwrite-target="edit/<?= $users -> user_id; ?>">
			<legend><?= CLanguage::get() -> string('MOD_BEUSER_FM_USERINFO'); ?></legend>
			<div>
				<!-- group -->
				<div class="group width-100">
					<div class="group-head width-100"><?= CLanguage::get() -> string('MOD_BEUSER_FM_ACCOUNTINFO'); ?></div>

					<div class="input width-25">
						<label><?= CLanguage::get() -> string('MOD_BEUSER_FM_USERID'); ?></label>
						<input type="text" disabled value="<?= $users -> user_id; ?>">
						<i class="fas fa-lock"></i>
					</div>

					<div class="input width-25">
						<label><?= CLanguage::get() -> string('MOD_BEUSER_FM_CREATEDAT'); ?></label>
						<input type="text" disabled value="<?= $users -> time_create; ?>">
						<i class="fas fa-lock"></i>
					</div>

					<div class="input width-25">
						<label><?= CLanguage::get() -> string('MOD_BEUSER_FM_LASTLOGIN'); ?></label>
						<input type="text" disabled value="<?= $users -> time_login; ?>">
						<i class="fas fa-lock"></i>
					</div>

					<div class="input width-25">
						<label><?= CLanguage::get() -> string('MOD_BEUSER_FM_LOGINCOUNT'); ?></label>
						<input type="text" disabled value="<?= $users -> login_count; ?>">
						<i class="fas fa-lock"></i>
					</div>
				</div>

				<!-- group -->

				<div class="group width-100">
					<div class="group-head width-100"><?= CLanguage::get() -> string('MOD_BEUSER_FM_USERINFO'); ?></div>

					<div class="input width-25">
						<label><?= CLanguage::get() -> string('MOD_BEUSER_FM_FIRSTNAME'); ?></label>
						<input type="text" name="user_name_first" value="<?= $users -> user_name_first; ?>">
					</div>

					<div class="input width-25">
						<label><?= CLanguage::get() -> string('MOD_BEUSER_FM_LASTNAME'); ?></label>
						<input type="text" name="user_name_last" value="<?= $users -> user_name_last; ?>">
					</div>

					<div class="input width-50">
						<label><?= CLanguage::get() -> string('MOD_BEUSER_FM_MAILADDRESS'); ?></label>
						<input type="text" name="user_mail" value="<?= $users -> user_mail; ?>">
					</div>

				</div>

				<!-- group -->

				<div class="group width-100">
					<div class="group-head width-100"><?= CLanguage::get() -> string('MOD_BEUSER_FM_BACKENDSTTNGS'); ?></div>

					<div class="input width-25">
						<label><?= $language -> string('LANGUAGE'); ?></label>
						<div class="select-wrapper">
						<select name="language">
							<option value="en" <?= ($users -> language === 'en' ? 'selected' : ''); ?>>English</option>
							<option value="de" <?= ($users -> language === 'de' ? 'selected' : ''); ?>>Deutsch</option>
						</select>	
						</div>
					</div>

					<div class="input width-25">
						<label><?= $language -> string('MOD_BEUSER_FM_REMOTEAUTH'); ?></label>
						<div class="select-wrapper">
						<select name="allow_remote">
							<option value="0" <?= (!$users -> allow_remote ? 'selected' : ''); ?>><?= $language -> string('MOD_BEUSER_FM_REMOTEAUTH_0_NOTALLOWED'); ?></option>
							<option value="1" <?= ($users -> allow_remote ? 'selected' : ''); ?>><?= $language -> string('MOD_BEUSER_FM_REMOTEAUTH_1_ALLOWED'); ?></option>
						</select>	
						</div>
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
				<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-save" data-icon="fa-save"></i></span><?= CLanguage::get() -> string('BUTTON_SAVE'); ?></button>
				<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-user-data"><label for="protector-user-data"></label></div>
			</div>

		</fieldset>



		<fieldset class="ui fieldset submit-able" id="user-auth" data-xhr-target="user-auth" data-xhr-overwrite-target="edit/<?= $users -> user_id; ?>">
			<legend><?= CLanguage::get() -> string('MOD_BEUSER_FM_USERAUTH'); ?></legend>

			<div>

				<!-- group -->

				<div class="group width-100">
					<div class="group-head width-100"><?= CLanguage::get() -> string('MOD_BEUSER_FM_LOGININFO'); ?></div>

					<div class="input width-25">
						<label><?= CLanguage::get() -> string('MOD_BEUSER_FM_LOGINUSERNAME'); ?></label>
						<input type="text" name="login_name" value="">
					</div>

					<div class="input width-25">
						<label><?= CLanguage::get() -> string('MOD_BEUSER_FM_LOGINPASSWORD'); ?></label>
						<input type="password" name="login_pass_a" value="">
					</div>

					<div class="input width-25">
						<label><?= CLanguage::get() -> string('MOD_BEUSER_FM_LOGINPASSWORD2'); ?></label>
						<input type="password" name="login_pass_b" value="">
					</div>

				</div>

			</div>

			<div class="result-box" data-error=""></div>

			<!-- Submit button - beware of fieldset name -->

			<div class="submit-container">
				<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-save" data-icon="fa-save"></i></span><?= CLanguage::get() -> string('BUTTON_SAVE'); ?></button>
				<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-user-auth"><label for="protector-user-auth"></label></div>
			</div>

		</fieldset>


		<fieldset class="ui fieldset submit-able" id="user-rights" data-xhr-target="user-rights" data-xhr-overwrite-target="edit/<?= $users -> user_id; ?>">
			<legend><?= CLanguage::get() -> string('MOD_BEUSER_FM_USERRIGHTS'); ?></legend>
			<div>

				<!-- group -->
				<div class="group width-75">
					<div class="group-head width-100"><?= CLanguage::get() -> string('MOD_BEUSER_FM_RIGTHGROUPS'); ?></div>
					<div class="input width-100">
						<div style="display:flex;flex-wrap:wrap;">
							<input type="hidden" name="groups" value="">
							<?php 						
							foreach($right_groups as $_group)
							{
								$isActiveGroup  = isActiveGroup($_group -> group_id, $user_groups);
								?>
								<div class="ui pick-item">
									<input type="checkbox" id="group-<?= $_group -> group_id; ?>" name="groups[]" value="<?= $_group -> group_id; ?>"  <?= ($isActiveGroup ? 'checked' : ''); ?>>
									<label for="group-<?= $_group -> group_id; ?>">
										<?= ucfirst($_group -> group_name); ?>
									</label>
								</div>
								<?php
							}
							?>
						</div>
					</div>
				</div>	

				<!-- group -->
				<div class="group width-25">
					<div class="group-head width-100"><?= CLanguage::get() -> string('MOD_BEUSER_FM_LOCKEDSTATE'); ?></div>
					<div class="input width-100">
						<select name="is_locked">
							<option value="0" <?= ($users -> is_locked === 0 ? 'selected' : ''); ?>><?= CLanguage::get() -> string('MOD_BEUSER_FM_LOCKED_0_NOTLOCKED'); ?></option>
							<option value="1" <?= ($users -> is_locked === 1 ? 'selected' : ''); ?>><?= CLanguage::get() -> string('MOD_BEUSER_FM_LOCKED_1_NOTVERIFIED'); ?></option>
							<option value="2" <?= ($users -> is_locked === 2 ? 'selected' : ''); ?>><?= CLanguage::get() -> string('MOD_BEUSER_FM_LOCKED_2_FAILEDLOGIN'); ?></option>
						<select>
					</div>
				</div>			

			</div>

			<div class="result-box" data-error=""></div>

			<!-- Submit button - beware of fieldset name -->

			<div class="submit-container">
				<button class="ui button icon labeled trigger-submit-fieldset " type="button" disabled><span><i class="fas fa-save" data-icon="fa-save"></i></span><?= CLanguage::get() -> string('BUTTON_SAVE'); ?></button>
				<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-user-rights"><label for="protector-user-rights"></label></div>
			</div>

		</fieldset>



			<br><br>

	</div>
</div>
