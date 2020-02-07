<?php

$dataSet = &$tagsList[0];

 ?>

<div class="be-module-container forms-view">
	<div>
		<div class="inter-menu">
			<h2><?= $language -> string('MENU'); ?></h2>
			<hr>
			<ul>
			<li><a class="darkblue" href="#tag"><?= $language -> string('MOD_BETAGS_EDIT'); ?></a></li>
			</ul>
			<hr>
			<div class="delete-box">
				<?php if($enableDelete) { ?>	
					<fieldset class="ui fieldset" data-xhr-target="tag-delete" data-xhr-overwrite-target="delete/<?= $dataSet -> tag_id; ?>">	
						<div class="submit-container button-only">
							<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-trash-alt" data-icon="fa-trash-alt"></i></span><?= $language -> string('BUTTON_DELETE'); ?></button>
							<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-agent-delete"><label for="protector-agent-delete"></label></div>
						</div>
						<div class="result-box" data-error=""></div>
					</fieldset>
				<?php } ?>
			</div>
		</div>
	</div>
	<div>
		
		<fieldset class="ui fieldset submit-able" id="tag" data-xhr-target="edit-tag" data-xhr-overwrite-target="edit/<?= $dataSet -> tag_id; ?>">

			<input type="hidden" name="data_id" value="<?= $dataSet -> tag_id; ?>">

			<legend><?= $language -> string('MOD_BETAGS_EDIT'); ?></legend>
			<div>
				<!-- field group -->
				<div class="group width-100">

					<div class="group-head width-100"><?= $language -> string('MOD_BETAGS_INFO'); ?></div>

					<div class="input width-50">
						<label><?= $language -> string('MOD_BETAGS_NAME'); ?></label>
						<input type="text" name="tag_name" value="<?= $dataSet -> tag_name; ?>" maxlength="50">
					</div>
			
					<div class="input width-25">
						<label><?= $language -> string('MOD_BETAGS_HIDDEN'); ?></label>
						<div class="select-wrapper">
						<select name="tag_hidden">
							<option value="0" <?= ($dataSet -> tag_hidden == 0 ? 'selected' : ''); ?>><?= CLanguage::instance() -> getString('YES'); ?></option>
							<option value="1" <?= ($dataSet -> tag_hidden == 1 ? 'selected' : ''); ?>><?= CLanguage::instance() -> getString('NO'); ?></option>
						</select>	
						</div>
					</div>

					<div class="input width-25">
						<label><?= $language -> string('MOD_BETAGS_DISABLED'); ?></label>
						<div class="select-wrapper">
						<select name="tag_disabled">
							<option value="0" <?= ($dataSet -> tag_disabled == 0 ? 'selected' : ''); ?>><?= CLanguage::instance() -> getString('YES'); ?></option>
							<option value="1" <?= ($dataSet -> tag_disabled == 1 ? 'selected' : ''); ?>><?= CLanguage::instance() -> getString('NO'); ?></option>
						</select>	
						</div>
					</div>

				</div>
				
			</div>

			<?php if($enableEdit) { ?>

				<div class="result-box" data-error=""></div>

				<!-- Submit button - beware of fieldset name -->

				<div class="submit-container">
					<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-save" data-icon="fa-save"></i></span><?= $language -> string('BUTTON_SAVE'); ?></button>
					<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-tag"><label for="protector-tag"></label></div>
				</div>

			<?php } ?>

		</fieldset>


		<br><br>

	</div>
</div>

