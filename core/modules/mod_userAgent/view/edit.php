<?php
if(isset($agentsList))
{
	$dataset = &$agentsList[0];
}
else
{
	$agentsList = false;
}
?>

<div class="be-module-container forms-view">
	<div>
		<div class="ui inter-menu">
			<h2><?php echo $language -> string('MENU'); ?></h2>
			<hr>
			<ul>
			<li><a class="darkblue" href="#user-agent"><?php echo $language -> string('M_BEUSERAG_USERAGENT'); ?></a></li>
			</ul>
			<hr>
			<div class="delete-box">
				<?php if(isset($enableDelete) && $enableDelete && $agentsList !== false) { ?>	
					<fieldset class="ui fieldset" data-xhr-target="agent-delete" data-xhr-overwrite-target="delete/<?php echo $dataset -> data_id; ?>">	
						<div class="submit-container button-only">
							<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-trash-alt" data-icon="fa-trash-alt"></i></span><?php echo $language -> string('BUTTON_DELETE'); ?></button>
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
		
		<fieldset class="ui fieldset submit-able" id="user-agent" data-xhr-target="user-agent" <?= ($agentsList !== false ? 'data-xhr-overwrite-target="edit/'. $dataset -> data_id .'"' : ''); ?>>

			<legend><?php echo $language -> string('M_BEUSERAG_USERAGENT'); ?></legend>
			<div>

				<!-- user agent -->
				<div class="group width-100">

					<div class="group-head width-100"><?php echo $language -> string('M_BEUSERAG_USERAGENT'); ?></div>

					<div class="input width-25">
						<label><?php echo $language -> string('M_BEUSERAG_AGENTNAME'); ?></label>
						<input type="text" name="agent_name" value="" maxlength="35">
					</div>

					<div class="input width-50">
						<label><?php echo $language -> string('M_BEUSERAG_AGENTSUFFIX'); ?></label>
						<input type="text" name="agent_suffix" value="" maxlength="75">
					</div>
			
					<div class="input width-25">
						<label><?php echo $language -> string('ALLOWED'); ?></label>
						<div class="select-wrapper">
						<select name="agent_allowed">
							<option value="1"><?php echo CLanguage::instance() -> getString('YES'); ?></option>
							<option value="0"><?php echo CLanguage::instance() -> getString('NO'); ?></option>
						</select>	
						</div>
					</div>

				</div>
				
				<div class="group width-100">

					<div class="input width-100">
						<label><?php echo $language -> string('DESCRIPTION'); ?></label>
						<input type="text" name="agent_desc" value="" maxlength="200">
					</div>		

				</div>	
				
			</div>

			<?php if(isset($enableEdit) && $enableEdit || $agentsList === false) { ?>

				<div class="result-box" data-error=""></div>

				<!-- Submit button - beware of fieldset name -->

				<div class="submit-container">
					<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-save" data-icon="fa-save"></i></span><?php echo $language -> string('BUTTON_SAVE'); ?></button>
					<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-user-agent"><label for="protector-user-agent"></label></div>
				</div>

			<?php } ?>

		</fieldset>


		<br><br>

	</div>
	
</div>

<?php if($agentsList !== false) { ?>
<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-index.js"></script>
<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-item.js"></script>
<script>

	let	requestURL	= CMS.SERVER_URL_BACKEND + CMS.PAGE_PATH +'ping/<?= $dataset -> data_id; ?>';
	let pingId		= cmsTabInstance.getId();
	
	cmstk.ping(requestURL, <?= CFG::GET() -> USER_SYSTEM -> MODULE_LOCKING -> PING_TIMEOUT; ?>, pingId);

	document.addEventListener("DOMContentLoaded", function(){

		document.dataInstance = new cmsRequestDataItem('', '<?= CFG::GET() -> BACKEND -> TIME_FORMAT; ?>', <?= $dataset -> data_id; ?>);
		document.dataInstance.requestData();

		let fieldsets = document.querySelectorAll('fieldset[data-xhr-target]');
		for(let i = 0; i < fieldsets.length; i++)
		{
			fieldsets[i].setAttribute('data-ping-id', pingId);
		}
	});	

</script>
<?php } ?>