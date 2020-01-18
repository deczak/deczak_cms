<?php

$data = &$languagesList[0];


?>


<div class="be-module-container forms-view">
	<div>
		<div class="inter-menu">
			<h2><?php echo $language -> string('MENU'); ?></h2>
			<hr>
			<ul>
			<li><a class="darkblue" href="#lang-data"><?php echo $language -> string('LANGUAGE'); ?></a></li>
			</ul>
			<hr>
			<div class="delete-box">
				<?php if($enableDelete && !$data -> lang_default) { ?>
					<fieldset class="ui fieldset" data-xhr-target="delete" data-xhr-overwrite-target="delete/<?php echo $data -> lang_key; ?>">	
						<div class="submit-container button-only">
							<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><i class="fas fa-trash-alt"></i><?php echo $language -> string('BUTTON_DELETE'); ?></button>
							<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-language-delete"><label for="protector-language-delete"></label></div>
						</div>
						<div class="result-box" data-error=""></div>
					</fieldset>
				<?php } ?>
			</div>
		</div>
	</div>
	<div>
		
		<fieldset class="ui fieldset submit-able" id="lang-data" data-xhr-target="language" data-xhr-overwrite-target="edit/<?php echo $data -> lang_key; ?>">

			<input type="hidden" name="data_id" value="<?php echo $data -> data_id; ?>">

			<legend><?php echo $language -> string('LANGUAGE'); ?></legend>
			<div>

				<div class="group width-100">

					<div class="group-head width-100"><?php echo $language -> string('M_BELANG_SETTINGS'); ?> </div>

					<div class="input width-25">
						<label><?php echo $language -> string('LANGUAGE'); ?></label>
						<input type="text" name="" disabled value="<?php echo strtoupper($data -> lang_key); ?>">
						<i class="fas fa-lock"></i>
					</div>
					
					<div class="input width-25">
						<label><?php echo $language -> string('LANGUAGE'); ?></label>
						<input type="text" name="lang_name" value="<?= $data -> lang_name; ?>" maxlength="25">
					</div>

					<div class="input width-25">
						<label><?php echo $language -> string('M_BELANG_NAMENATIVE'); ?></label>
						<input type="text" name="lang_name_native" value="<?php echo $data -> lang_name_native; ?>" maxlength="25">
					</div>

					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('M_BELANG_DEFAULT'); ?></label>
						<div class="select-wrapper <?= ($data -> lang_default ? 'select-disabled' : ''); ?>">
						<select name="lang_default" <?= ($data -> lang_default ? 'disabled' : ''); ?>>
							<option value="0" <?php echo ($data -> lang_default ? 'selected' : ''); ?>><?php echo CLanguage::get() -> string('NO'); ?></option>
							<option value="1" <?php echo ($data -> lang_default ? 'selected' : ''); ?>><?php echo CLanguage::get() -> string('YES'); ?></option>
						</select>	
						</div>
						<?= ($data -> lang_default ? '<i class="fas fa-lock"></i>' : ''); ?>
					</div>
					
				</div>

				<div class="group width-100">

					<div class="group-head width-100"><?php echo $language -> string('M_BELANG_VISSETTINGS'); ?> </div>

					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('M_BELANG_HIDDEN'); ?></label>
						<div class="select-wrapper">
						<select name="lang_hidden">
							<option value="0" <?php echo ($data -> lang_hidden ? 'selected' : ''); ?>><?php echo CLanguage::get() -> string('NO'); ?></option>
							<option value="1" <?php echo ($data -> lang_hidden ? 'selected' : ''); ?>><?php echo CLanguage::get() -> string('YES'); ?></option>
						</select>	
						</div>
					</div>
					
					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('M_BELANG_LOCKED'); ?></label>
						<div class="select-wrapper">
						<select name="lang_locked">
							<option value="0" <?php echo ($data -> lang_locked ? 'selected' : ''); ?>><?php echo CLanguage::get() -> string('NO'); ?></option>
							<option value="1" <?php echo ($data -> lang_locked ? 'selected' : ''); ?>><?php echo CLanguage::get() -> string('YES'); ?></option>
						</select>	
						</div>
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
					<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><i class="fas fa-save"></i><?php echo $language -> string('BUTTON_SAVE'); ?></button>
					<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-lang-data"><label for="protector-lang-data"></label></div>
				</div>

			<?php } ?>

		</fieldset>

		<div class="ui"><div class="result-box big" data-error="1">
			<b><?= $language -> string('WARNING'); ?>:</b> &nbsp;<?= $language -> string('M_BELANG_DEFWARNING'); ?>		
		</div></div>

		<div class="ui"><div class="result-box big" data-error="2">
			<b><?= $language -> string('NOTE'); ?>:</b> &nbsp;<?= $language -> string('M_BELANG_DEFNOTE'); ?>		
		</div></div>

		<div class="ui"><div class="result-box big" data-error="2">
			<b><?= $language -> string('NOTE'); ?>:</b> &nbsp;<?= $language -> string('M_BELANG_DEFDELELTE'); ?>		
		</div></div>

		<br><br>

	</div>
</div>

