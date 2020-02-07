
<div class="be-module-container forms-view">
	<div>
		<div class="inter-menu">
			<h2><?php echo $language -> string('MENU'); ?></h2>
			<hr>
			<ul>
			<li><a class="darkblue" href="#category-info"><?php echo $language -> string('MOD_BECATEGORIES_CREATE'); ?></a></li>
			</ul>
			<hr>
			<div class="delete-box">	
			</div>
		</div>
	</div>
	<div>
		
		<fieldset class="ui fieldset submit-able" id="category-info" data-xhr-target="create-category">
			<legend><?php echo $language -> string('MOD_BECATEGORIES_CREATE'); ?></legend>
			<div>
				<!-- field group -->
				<div class="group width-100">

					<div class="group-head width-100"><?php echo $language -> string('MOD_BECATEGORIES_INFO'); ?></div>

					<div class="input width-50">
						<label><?php echo $language -> string('MOD_BECATEGORIES_NAME'); ?></label>
						<input type="text" name="category_name" value="" maxlength="50">
					</div>
			
					<div class="input width-25">
						<label><?php echo $language -> string('MOD_BECATEGORIES_HIDDEN'); ?></label>
						<div class="select-wrapper">
						<select name="category_hidden">
							<option value="0"><?php echo CLanguage::instance() -> getString('YES'); ?></option>
							<option value="1"><?php echo CLanguage::instance() -> getString('NO'); ?></option>
						</select>	
						</div>
					</div>

					<div class="input width-25">
						<label><?php echo $language -> string('MOD_BECATEGORIES_DISABLED'); ?></label>
						<div class="select-wrapper">
						<select name="category_disabled">
							<option value="0"><?php echo CLanguage::instance() -> getString('YES'); ?></option>
							<option value="1"><?php echo CLanguage::instance() -> getString('NO'); ?></option>
						</select>	
						</div>
					</div>

				</div>
							

			</div>

			<div class="ui result-box" data-error=""></div>

			<!-- Submit button - beware of fieldset name -->

			<div class="submit-container">
				<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-save" data-icon="fa-save"></i></span><?php echo $language -> string('BUTTON_SAVE'); ?></button>
				<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-category"><label for="protector-category"></label></div>
			</div>

		</fieldset>

		<br><br>

	</div>
</div>

