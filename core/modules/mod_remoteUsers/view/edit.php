<?php

$dataset 	= &$usersList[0];
$user 		= $dataset['user'];

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
			<h2><?= CLanguage::string('MENU'); ?></h2>
			<hr>
			<ul>
			<li><a class="darkblue" href="#user"><?= CLanguage::string('MOD_BEREMOTEU_USERRIGHTS'); ?></a></li>
			</ul>
			<hr>

			<div class="ui result-box ping-result lower-font-size" id="ping-lock-result" data-error=""></div>
		</div>
	</div>
	<div>


		<fieldset class="ui fieldset" id="group-data" data-xhr-target="group-data" data-xhr-overwrite-target="edit/<?php echo $right_group -> group_id; ?>">
			<legend><?php echo CLanguage::string('USER'); ?></legend>
			<div>
				<!-- group -->
				<div class="group width-100">
					<div class="input width-25">
						<label><?php echo CLanguage::string('USER'); ?></label>
						<input type="text" disabled value="<?= $user -> user_name_first; ?> <?= $user -> user_name_last; ?>">
						<i class="fas fa-lock"></i>
					</div>

					<div class="input width-25">
						<label><?php echo CLanguage::string('MOD_BEREMOTEU_USERORIGIN'); ?></label>
						<input type="text" disabled value="<?= $dataset['db_name']; ?> (<?= $dataset['db_server']; ?>)">
						<i class="fas fa-lock"></i>
					</div>

					<div class="input width-25">
					</div>

					<div class="input width-25">
					</div>
				</div>

			</div>

		</fieldset>



		
		<fieldset class="ui fieldset submit-able" id="user" data-xhr-target="edit-user" data-xhr-overwrite-target="edit/<?= $dataset['id']; ?>">

			<legend><?= CLanguage::string('MOD_BEREMOTEU_USERRIGHTS'); ?></legend>
			<div>
				<!-- group -->
				<div class="group width-100">
					<div class="group-head width-100"><?php echo CLanguage::string('MOD_BEREMOTEU_RIGTHGROUPS'); ?></div>
					<div class="input width-100">
						<div style="display:flex;flex-wrap:wrap;" id="container-group-rights">
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
			</div>

			<?php if($enableEdit) { ?>

				<div class="result-box" data-error=""></div>

				<!-- Submit button - beware of fieldset name -->

				<div class="submit-container">
					<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-save" data-icon="fa-save"></i></span><?= CLanguage::string('BUTTON_SAVE'); ?></button>
					<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-user"><label for="protector-user"></label></div>
				</div>

			<?php } ?>

		</fieldset>


		<br><br>

	</div>
</div>

<?php if($dataset !== false) { ?>
<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-index.js"></script>
<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-item.js"></script>
<script>

	let	requestURL	= CMS.SERVER_URL_BACKEND + CMS.PAGE_PATH +'ping/<?= $dataset['id']; ?>';
	let pingId		= cmsTabInstance.getId();
	
	cmstk.ping(requestURL, <?= CFG::GET() -> USER_SYSTEM -> MODULE_LOCKING -> PING_TIMEOUT; ?>, pingId);

	document.addEventListener("DOMContentLoaded", function(){

		document.dataInstance = new cmsRequestDataItem('', '<?= CFG::GET() -> BACKEND -> TIME_FORMAT; ?>', '<?= $dataset['id']; ?>', extendedReplace);
		document.dataInstance.requestData();

		let fieldsets = document.querySelectorAll('fieldset[data-xhr-target]');
		for(let i = 0; i < fieldsets.length; i++)
		{
			fieldsets[i].setAttribute('data-ping-id', pingId);
		}
	});	

	function extendedReplace(prop, propContent)
	{
		switch(prop)
		{
			case   'userGroups':

					let rightsTable = document.getElementById('container-group-rights');

					let groupItemAll = rightsTable.querySelectorAll('input[name="groups[]"]');

					for(let item in groupItemAll)
						groupItemAll[item].checked = false;

					for(var group in propContent)
					{	
						let groupItem = rightsTable.querySelector('input[id="group-'+ propContent[group].group_id +'"]');
						if(groupItem !== null)
							groupItem.checked = true;
					}

					break;
		}
		
	}

</script>
<?php } ?>