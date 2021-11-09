<?php
if(isset($languagesList))
{
	$dataset = &$languagesList[0];
}
else
{
	$dataset = false;
}
?>

<div class="be-module-container forms-view">
	<div>
		<div class="ui inter-menu">
			<h2><?php echo CLanguage::string('MENU'); ?></h2>
			<hr>
			<ul>
			<li><a class="darkblue" href="#lang-data"><?php echo CLanguage::string('LANGUAGE'); ?></a></li>
			</ul>
			<hr>
			<div class="delete-box">
				<?php if(isset($enableDelete) && $enableDelete && $dataset !== false && !$dataset -> lang_default) { ?>	
					<fieldset class="ui fieldset" data-xhr-target="delete" data-xhr-overwrite-target="delete/<?php echo $dataset -> lang_key; ?>">	
						<div class="submit-container button-only">
							<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-trash-alt" data-icon="fa-trash-alt"></i></span><?php echo CLanguage::string('BUTTON_DELETE'); ?></button>
							<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-language-delete"><label for="protector-language-delete"></label></div>
						</div>
						<div class="result-box" data-error=""></div>
					</fieldset>
				<?php } ?>
			</div>

			<div class="result-box ping-result lower-font-size" id="ping-lock-result" data-error=""></div>
		</div>
	</div>
	<div>
		
		<fieldset class="ui fieldset submit-able" id="lang-data" data-xhr-target="<?= (!$dataset ? 'create' : 'edit'); ?>" <?= ($dataset ? 'data-xhr-overwrite-target="edit/'. $dataset -> lang_key .'"' : ''); ?>>


			<legend><?php echo CLanguage::string('LANGUAGE'); ?></legend>
			<div>

				<div class="group width-100">

					<div class="group-head width-100"><?php echo CLanguage::string('M_BELANG_SETTINGS'); ?> </div>

					<div class="input width-25">
						<label><?php echo CLanguage::string('M_BELANG_NAMENKEY'); ?></label>
						<input type="text" name="lang_key" <?= ($dataset ? 'disabled' : ''); ?> value="">
						<?= ($dataset ? '<i class="fas fa-lock"></i>' : ''); ?>
					</div>
					
					<div class="input width-25">
						<label><?php echo CLanguage::string('LANGUAGE'); ?></label>
						<input type="text" name="lang_name" value="" maxlength="25">
					</div>

					<div class="input width-25">
						<label><?php echo CLanguage::string('M_BELANG_NAMENATIVE'); ?></label>
						<input type="text" name="lang_name_native" value="" maxlength="25">
					</div>

					<div class="input width-25">
						<label><?php echo CLanguage::string('M_BELANG_DEFAULT'); ?></label>
						<div class="select-wrapper <?= ($dataset -> lang_default ? 'select-disabled' : ''); ?>">
						<select name="lang_default" <?= ($dataset -> lang_default ? 'disabled' : ''); ?>>
							<option value="0"><?php echo CLanguage::string('NO'); ?></option>
							<option value="1"><?php echo CLanguage::string('YES'); ?></option>
						</select>	
						</div>
						<?= ($dataset -> lang_default ? '<i class="fas fa-lock"></i>' : ''); ?>
					</div>
					
				</div>

				<div class="group width-100">

					<div class="group-head width-100"><?php echo CLanguage::string('M_BELANG_VISSETTINGS'); ?> </div>

					<div class="input width-25">
						<label><?php echo CLanguage::string('M_BELANG_HIDDEN'); ?></label>
						<div class="select-wrapper">
						<select name="lang_hidden">
							<option value="0"><?php echo CLanguage::string('YES'); ?></option>
							<option value="1"><?php echo CLanguage::string('NO'); ?></option>
						</select>	
						</div>
					</div>
					
					<div class="input width-25">
						<label><?php echo CLanguage::string('M_BELANG_LOCKED'); ?></label>
						<div class="select-wrapper">
						<select name="lang_locked">
							<option value="0"><?php echo CLanguage::string('YES'); ?></option>
							<option value="1"><?php echo CLanguage::string('NO'); ?></option>
						</select>	
						</div>
					</div>

					<div class="input width-25">
					</div>

					<div class="input width-25">
					</div>
					
				</div>
			
			</div>

			<?php if(isset($enableEdit) && $enableEdit || $dataset === false) { ?>

				<div class="result-box" data-error=""></div>

				<!-- Submit button - beware of fieldset name -->

				<div class="submit-container">
					<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-save" data-icon="fa-save"></i></span><?php echo CLanguage::string('BUTTON_SAVE'); ?></button>
					<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-lang-data"><label for="protector-lang-data"></label></div>
				</div>

			<?php } ?>

		</fieldset>

		<div class="ui"><div class="result-box big" data-error="1">
			<b><?= CLanguage::string('WARNING'); ?>:</b> &nbsp;<?= CLanguage::string('M_BELANG_DEFWARNING'); ?>		
		</div></div>

		<div class="ui"><div class="result-box big" data-error="2">
			<b><?= CLanguage::string('NOTE'); ?>:</b> &nbsp;<?= CLanguage::string('M_BELANG_DEFNOTE'); ?>		
		</div></div>

		<div class="ui"><div class="result-box big" data-error="2">
			<b><?= CLanguage::string('NOTE'); ?>:</b> &nbsp;<?= CLanguage::string('M_BELANG_DEFDELELTE'); ?>		
		</div></div>

		<br><br>

	</div>
</div>


<?php if($dataset !== false) { ?>
<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-index.js"></script>
<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-item.js"></script>
<script>

	let	requestURL	= CMS.SERVER_URL_BACKEND + CMS.PAGE_PATH +'ping/<?= $dataset -> lang_key; ?>';
	let pingId		= cmsTabInstance.getId();
	
	cmstk.ping(requestURL, <?= CFG::GET() -> USER_SYSTEM -> MODULE_LOCKING -> PING_TIMEOUT; ?>, pingId);

	document.addEventListener("DOMContentLoaded", function(){

		document.dataInstance = new cmsRequestDataItem('', '<?= CFG::GET() -> BACKEND -> TIME_FORMAT; ?>', '<?= $dataset -> lang_key; ?>');
		document.dataInstance.requestData();

		let fieldsets = document.querySelectorAll('fieldset[data-xhr-target]');
		for(let i = 0; i < fieldsets.length; i++)
		{
			fieldsets[i].setAttribute('data-ping-id', pingId);
		}
	});	

</script>
<?php } ?>