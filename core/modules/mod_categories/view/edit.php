<?php
if(isset($categoriesList))
	$dataset = &$categoriesList[0];
else
	$categoriesList = false;
?>

<div class="be-module-container forms-view">
	<div>
		<div class="ui inter-menu">
			<h2><?= $language -> string('MENU'); ?></h2>
			<hr>
			<ul>
			<li><a class="darkblue" href="#category"><?= $language -> string('MOD_BECATEGORIES_EDIT'); ?></a></li>
			</ul>
			<hr>
			<div class="delete-box">
				<?php if(isset($enableDelete) && $enableDelete && $categoriesList !== false) { ?>	
					<fieldset class="ui fieldset" data-xhr-target="category-delete" data-xhr-overwrite-target="delete/<?= $dataset -> category_id; ?>">	
						<div class="submit-container button-only">
							<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-trash-alt" data-icon="fa-trash-alt"></i></span><?= $language -> string('BUTTON_DELETE'); ?></button>
							<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-agent-delete"><label for="protector-agent-delete"></label></div>
						</div>
						<div class="result-box" data-error=""></div>
					</fieldset>
				<?php } ?>
			</div>

			<div class="result-box ping-result lower-font-size" id="ping-lock-result" data-error=""></div>
			
		</div>
	</div>
	<div>
		
		<fieldset class="ui fieldset submit-able" id="category" data-xhr-target="edit-category" <?= ($categoriesList !== false ? 'data-xhr-overwrite-target="edit/'. $dataset -> category_id .'"' : ''); ?>>

			<legend><?= $language -> string('MOD_BECATEGORIES_EDIT'); ?></legend>
			<div>
				<!-- field group -->
				<div class="group width-100">

					<div class="group-head width-100"><?= $language -> string('MOD_BECATEGORIES_INFO'); ?></div>

					<div class="input width-50">
						<label><?= $language -> string('MOD_BECATEGORIES_NAME'); ?></label>
						<input type="text" name="category_name" value="" maxlength="50">
					</div>
			
					<div class="input width-25">
						<label><?= $language -> string('MOD_BECATEGORIES_HIDDEN'); ?></label>
						<div class="select-wrapper">
						<select name="category_hidden">
							<option value="0"><?= CLanguage::instance() -> getString('YES'); ?></option>
							<option value="1"><?= CLanguage::instance() -> getString('NO'); ?></option>
						</select>	
						</div>
					</div>

					<div class="input width-25">
						<label><?= $language -> string('MOD_BECATEGORIES_DISABLED'); ?></label>
						<div class="select-wrapper">
						<select name="category_disabled">
							<option value="0"><?= CLanguage::instance() -> getString('YES'); ?></option>
							<option value="1"><?= CLanguage::instance() -> getString('NO'); ?></option>
						</select>	
						</div>
					</div>

				</div>
				
			</div>

			<?php if(isset($enableEdit) && $enableEdit || $categoriesList === false) { ?>

				<div class="result-box" data-error=""></div>

				<!-- Submit button - beware of fieldset name -->

				<div class="submit-container">
					<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-save" data-icon="fa-save"></i></span><?= $language -> string('BUTTON_SAVE'); ?></button>
					<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-category"><label for="protector-category"></label></div>
				</div>

			<?php } ?>

		</fieldset>


		<br><br>

	</div>
</div>

<?php if($deniedList !== false) { ?>
<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-index.js"></script>
<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-item.js"></script>
<script>

	let	requestURL	= CMS.SERVER_URL_BACKEND + CMS.PAGE_PATH +'ping/<?= $dataset -> category_id; ?>';
	let pingId		= cmstk.getRandomId();
	
	cmstk.ping(requestURL, <?= CFG::GET() -> USER_SYSTEM -> MODULE_LOCKING -> PING_TIMEOUT; ?>, pingId);

	document.addEventListener("DOMContentLoaded", function(){

		document.dataInstance = new cmsRequestDataItem('', '<?= CFG::GET() -> BACKEND -> TIME_FORMAT; ?>', <?= $dataset -> category_id; ?>);
		document.dataInstance.requestData();

		let fieldsets = document.querySelectorAll('fieldset[data-xhr-target]');
		for(let i = 0; i < fieldsets.length; i++)
		{
			fieldsets[i].setAttribute('data-ping-id', pingId);
		}
	});	

</script>
<?php } ?>

