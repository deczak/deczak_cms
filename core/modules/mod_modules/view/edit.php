<?php
if(isset($modulesList))
{
	$dataset = &$modulesList[0];
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
			<li><a class="darkblue" href="#module-data"><?php echo CLanguage::string('M_BEMOULE_MODULEINFO'); ?></a></li>
			</ul>
			<hr>

			<div class="result-box ping-result lower-font-size" id="ping-lock-result" data-error=""></div>
				
		</div>
	</div>
	<div>
		
		<fieldset class="ui fieldset submit-able" id="module-data" data-xhr-target="edit" data-xhr-overwrite-target="edit/<?php echo $dataset -> module_id; ?>">

			<input type="hidden" name="data_id" value="<?php echo $dataset -> data_id; ?>">

			<legend><?php echo CLanguage::string('M_BEMOULE_MODULEINFO'); ?></legend>
			<div>


				<div class="group width-100">

					<div class="group-head width-100"><?php echo CLanguage::string('M_BEMOULE_MODULEINFO'); ?> </div>

					<div class="input width-25">
						<label><?php echo CLanguage::string('M_BEMOULE_NAME'); ?></label>
						<input type="text" name="" disabled value="<?php echo $dataset -> module_name; ?>">
						<i class="fas fa-lock"></i>
					</div>
					
					<div class="input width-25">
						<label><?php echo CLanguage::string('M_BEMOULE_MODULETYPE'); ?></label>
						<input type="text" name="" disabled value="<?= ucfirst($dataset -> module_type); ?> / <?= ucfirst($dataset -> module_group); ?>" maxlength="250">
						<i class="fas fa-lock"></i>
					</div>

					<div class="input width-25">
						<label><?php echo CLanguage::string('M_BEMOULE_MODULELOC'); ?></label>
						<input type="text" name="" disabled value="<?php echo $dataset -> module_location; ?>" maxlength="250">
						<i class="fas fa-lock"></i>
					</div>

					<div class="input width-25">
						<label><?php echo CLanguage::string('M_BEMOULE_MODULEID'); ?></label>
						<input type="text" name="" disabled value="<?php echo $dataset -> module_id; ?>" maxlength="250">
						<i class="fas fa-lock"></i>
					</div>
				</div>



				<div class="group width-100">

					<?php
					switch($dataset -> module_id)
					{
						case 2: 
						case 3: 
						case 6: 
						case 8: 
						case 9: 
						case 10: 
						case 11: 
						case 12: 
						case 18: 
						case 1: $disableSelect = true;
					}
					?>

					<div class="input width-25">
						<label><?php echo CLanguage::string('M_BEMOULE_MODULEACTIVATED'); ?></label>
						<div class="select-wrapper">
						<select name="is_active" <?= (isset($disableSelect) && $disableSelect ? 'disabled' : ''); ?>>
							<option value="0" <?php echo ($dataset -> is_active == 0 ? 'selected' : ''); ?>><?php echo CLanguage::string('NO'); ?></option>
							<option value="1" <?php echo ($dataset -> is_active == 1 ? 'selected' : ''); ?>><?php echo CLanguage::string('YES'); ?></option>
						</select>	
						</div>
					</div>

				</div>

			
		



				
			</div>

			<?php if($enableEdit) { ?>

				<div class="result-box" data-error=""></div>

				<!-- Submit button - beware of fieldset name -->

				<div class="submit-container">
					<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-save" data-icon="fa-save"></i></span><?php echo CLanguage::string('BUTTON_SAVE'); ?></button>
					<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-module-data"><label for="protector-module-data"></label></div>
				</div>

			<?php } ?>

		</fieldset>



		<?php if(isset($enableDelete) && $enableDelete && $dataset -> module_type !== 'core') { ?>	

			<div class="delete-box" style="display:flex;background:rgba(255,0,0,0.6);padding: 15px 23px;; align-items:center; border-radius:3px;">
				<div style="width:213px;border:2px solid white; border-radius:3px; background:white; padding:4px;">
				<fieldset class="ui fieldset" data-xhr-target="delete" data-xhr-overwrite-target="delete/<?php echo $dataset -> module_id; ?>" style="margin:0px;">	
					<div class="submit-container button-only">
						<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-trash-alt" data-icon="fa-trash-alt"></i></span><?php echo CLanguage::string('BUTTON_DELETE'); ?></button>
						<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-module-delete"><label for="protector-module-delete"></label></div>
					</div>
				</fieldset>
				</div>
				<div style="color:white; font-weight:500; margin-left:20px;">
					<b><?php echo CLanguage::string('WARNING'); ?>:</b> <?php echo CLanguage::string('M_BEMOULE_MSG_WARNINGNOTICE'); ?>
				</div>
			</div>
		<?php } ?>


		<br><br>

	</div>
</div>


<?php if($agentsList !== false) { ?>
<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-index.js"></script>
<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-item.js"></script>
<script>

	let	requestURL	= CMS.SERVER_URL_BACKEND + CMS.PAGE_PATH +'ping/<?= $dataset -> module_id; ?>';
	let pingId		= cmsTabInstance.getId();
	
	cmstk.ping(requestURL, <?= CFG::GET() -> USER_SYSTEM -> MODULE_LOCKING -> PING_TIMEOUT; ?>, pingId);

	document.addEventListener("DOMContentLoaded", function(){

		document.dataInstance = new cmsRequestDataItem('', '<?= CFG::GET() -> BACKEND -> TIME_FORMAT; ?>', <?= $dataset -> module_id; ?>);
		document.dataInstance.requestData();

		let fieldsets = document.querySelectorAll('fieldset[data-xhr-target]');
		for(let i = 0; i < fieldsets.length; i++)
		{
			fieldsets[i].setAttribute('data-ping-id', pingId);
		}
	});	

</script>
<?php } ?>