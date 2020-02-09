
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
			</div>
		</div>
	</div>
	<div>
		
		<fieldset class="ui fieldset submit-able" id="lang-data" data-xhr-target="lang-data">
			<legend><?php echo $language -> string('CREATE'); ?> <?php echo $language -> string('DENIED'); ?> <?php echo $language -> string('ADDRESS'); ?></legend>
			<div>

				<!-- language info -->
				<div class="group width-100">

					<div class="group-head width-100"><?php echo $language -> string('M_BELANG_SETTINGS'); ?> </div>

					<div class="input width-25">
						<label><?php echo $language -> string('M_BELANG_NAMENKEY'); ?></label>
						<input type="text" name="lang_key" value="">
					</div>
					
					<div class="input width-25">
						<label><?php echo $language -> string('LANGUAGE'); ?></label>
						<input type="text" name="lang_name" value="" maxlength="25">
					</div>

					<div class="input width-25">
						<label><?php echo $language -> string('M_BELANG_NAMENATIVE'); ?></label>
						<input type="text" name="lang_name_native" value="" maxlength="25">
					</div>

					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('M_BELANG_DEFAULT'); ?></label>
						<div class="select-wrapper">
						<select name="lang_default">
							<option value="0" selected><?php echo CLanguage::get() -> string('NO'); ?></option>
							<option value="1"><?php echo CLanguage::get() -> string('YES'); ?></option>
						</select>	
						</div>
					</div>
					
				</div>

				<!-- visual settings  -->
				<div class="group width-100">

					<div class="group-head width-100"><?php echo $language -> string('M_BELANG_VISSETTINGS'); ?> </div>

					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('M_BELANG_HIDDEN'); ?></label>
						<div class="select-wrapper">
						<select name="lang_hidden">
							<option value="0" ><?php echo CLanguage::get() -> string('YES'); ?></option>
							<option value="1" selected><?php echo CLanguage::get() -> string('NO'); ?></option>
						</select>	
						</div>
					</div>
					
					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('M_BELANG_LOCKED'); ?></label>
						<div class="select-wrapper">
						<select name="lang_locked">
							<option value="0" selected><?php echo CLanguage::get() -> string('YES'); ?></option>
							<option value="1"><?php echo CLanguage::get() -> string('NO'); ?></option>
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
				<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-save" data-icon="fa-save"></i></span><?php echo $language -> string('BUTTON_SAVE'); ?></button>
				<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-lang-data"><label for="protector-lang-data"></label></div>
			</div>

		</fieldset>

		<div class="ui"><div class="result-box big" data-error="1">
			<b><?= $language -> string('WARNING'); ?>:</b> &nbsp;<?= $language -> string('M_BELANG_DEFWARNING'); ?>		
		</div></div>

		<div class="ui"><div class="result-box big" data-error="2">
			<b><?= $language -> string('NOTE'); ?>:</b> &nbsp;<?= $language -> string('M_BELANG_DEFNOTE'); ?>		
		</div></div>

		<br><br>

	</div>
</div>

