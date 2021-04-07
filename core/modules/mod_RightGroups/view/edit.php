
<?php
if(isset($rightGroupsList))
{
	$dataset = &$rightGroupsList[0];
}
else
{
	$rightGroupsList = false;
}

$activeModulesList = CModules::instance() -> getModules();	

?>

<div class="be-module-container forms-view">
	<div>
		<div class="inter-menu">
			<h2><?php echo CLanguage::get() -> string('MENU'); ?></h2>
			<hr>
			<ul>
			<li><a class="darkblue" href="#group-data"><?php echo CLanguage::get() -> string('MOD_RGROUPS_GROUP_INFO'); ?></a></li>
			<li><a class="darkblue" href="#group-rights"><?php echo CLanguage::get() -> string('MOD_RGROUPS_GROUP_RIGHTS'); ?></a></li>
			</ul>
			<hr>
			<div class="delete-box">	
				<?php if(isset($enableDelete) && $enableDelete && $rightGroupsList !== false) { ?>	
					<fieldset class="ui fieldset" data-xhr-target="group-delete" data-xhr-overwrite-target="delete/<?php echo $dataset -> group_id; ?>">	
						<div class="submit-container button-only">
							<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-trash-alt" data-icon="fa-trash-alt"></i></span><?php echo CLanguage::get() -> string('BUTTON_DELETE'); ?></button>
							<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-user-delete"><label for="protector-user-delete"></label></div>
						</div>
						<div class="ui result-box" data-error=""></div>
					</fieldset>
				<?php } ?>
			</div>

			<div class="ui result-box ping-result lower-font-size" id="ping-lock-result" data-error=""></div>
			
		</div>
	</div>

	<div>
		
		<fieldset class="ui fieldset submit-able" id="group-data" data-xhr-target="group-rights" <?= ($rightGroupsList !== false ? 'data-xhr-overwrite-target="edit/'. $dataset -> group_id .'"' : ''); ?>>
			<legend><?php echo CLanguage::get() -> string('MOD_RGROUPS_GROUP_INFO'); ?></legend>
			<div>
				<!-- group -->
				<div class="group width-100">
					<div class="input width-25">
						<label><?php echo CLanguage::get() -> string('MOD_RGROUPS_GROUP_ID'); ?></label>
						<input name="group_id" type="text" <?= ($rightGroupsList !== false ? 'disabled' : ''); ?> value="<?php echo $dataset -> group_id; ?>">
						<?= ($rightGroupsList !== false ? '<i class="fas fa-lock"></i>' : ''); ?>
					</div>

					<div class="input width-25">
						<label><?php echo CLanguage::get() -> string('MOD_RGROUPS_GROUP_NAME'); ?></label>
						<input type="text" name="group_name" value="<?php echo $dataset -> group_name; ?>">
					</div>

					<div class="input width-25">
					</div>

					<div class="input width-25">
					</div>
				</div>


				<!-- group -->
				<div class="group width-100">
					<div class="input width-100">
			
						<table id="table-mod-group-rights">
							<thead>
								<tr>
									<td>Module name</td>
									<td>Module rights</td>
								</tr>
							</thead>
							<tbody>

								<?php
								foreach($activeModulesList as $_module)
								{


									switch($_module -> module_type)
									{
										case 'core'  :	$_modLocation	= CMS_SERVER_ROOT . DIR_CORE . DIR_MODULES . $_module -> module_location .'/';									
														break;

										case 'mantle':	$_modLocation	= CMS_SERVER_ROOT . DIR_MANTLE . DIR_MODULES . $_module -> module_location .'/';
														break;
									}

									$_moduleData = file_get_contents($_modLocation .'/module.json');
									$_moduleData = json_decode($_moduleData);





		$pModulesInstall = new CModulesInstall;

		$moduleData = $pModulesInstall -> getMmoduleData($_moduleData, $_module -> module_location, $_module -> module_type);

		if($moduleData === false)
		{
			continue;
		}

		$moduleData = json_decode(json_encode($moduleData));

		if(empty($moduleData -> rights))
			continue;


									?>
									<tr>
										<td><?php echo $_module -> module_name; ?></td>
										<td>
											<div style="display:flex;">
											<?php
											foreach($moduleData -> rights as $_right)
											{
												?>
												<div class="ui pick-item">
													<input type="checkbox" id="<?php echo $_module -> module_id .'-'. $_right -> name; ?>" name="group_rights[<?php echo $_module -> module_id; ?>][]" value="<?php echo $_right -> name; ?>">
													<label for="<?php echo $_module -> module_id .'-'. $_right -> name; ?>" title="<?php echo CLanguage::get() -> string($_right -> desc); ?>">
														<?php echo CLanguage::get() -> string($_right -> desc); ?>
													</label>
												</div>
												<?php
											}
											?>
											</div>
										</td>
									</tr>									
									<?php
								}
								?>

							</tbody>
						</table>

						<style>
							#table-mod-group-rights	{ width:100%; border: 2px solid white; margin-top: 14px; }
							#table-mod-group-rights	> thead td { font-size:0.9em; font-weight:700; }
							#table-mod-group-rights	> thead td:first-child { width:225px; }
							#table-mod-group-rights td { padding: 2px 6px; }
							#table-mod-group-rights tbody > tr { background:white; }

						</style>


					</div>
				</div>	
		
			</div>

			<?php if(isset($enableEdit) && $enableEdit || $rightGroupsList === false) { ?>

				<div class="ui result-box" data-error=""></div>

				<!-- Submit button - beware of fieldset name -->

				<div class="submit-container">
					<button class="ui button icon labeled trigger-submit-fieldset " type="button" disabled><span><i class="fas fa-save" data-icon="fa-save"></i></span><?php echo CLanguage::get() -> string('BUTTON_SAVE'); ?></button>
					<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-group-rights"><label for="protector-group-rights"></label></div>
				</div>

			<?php } ?>

		</fieldset>

	</div>

</div>

<?php if($rightGroupsList !== false) { ?>
<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-index.js"></script>
<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-item.js"></script>
<script>

	let	requestURL	= CMS.SERVER_URL_BACKEND + CMS.PAGE_PATH +'ping/<?= $dataset -> group_id; ?>';
	let pingId		= cmstk.getRandomId();
	
	cmstk.ping(requestURL, <?= CFG::GET() -> USER_SYSTEM -> MODULE_LOCKING -> PING_TIMEOUT; ?>, pingId);

	document.addEventListener("DOMContentLoaded", function(){

		document.dataInstance = new cmsRequestDataItem('', '<?= CFG::GET() -> BACKEND -> TIME_FORMAT; ?>', <?= $dataset -> group_id; ?>, extendedReplace);
		document.dataInstance.requestData();

		let fieldsets = document.querySelectorAll('fieldset[data-xhr-target]');
		for(let i = 0; i < fieldsets.length; i++)
		{
			fieldsets[i].setAttribute('data-ping-id', pingId);
		}
	});	

	function extendedReplace(prop, propContent)
	{

		let rightsTable = document.getElementById('table-mod-group-rights');

		switch(prop)
		{
			case   'group_rights':

					for(var group in propContent)
					{	
						
						for(var right in propContent[group])
						{	

							let rightItem = rightsTable.querySelector('[name="group_rights['+ group +'][]"][value="'+ propContent[group][right] +'"]');
							if(rightItem !== null)
								rightItem.checked = true;

						}				
					}

					break;
		}
	}

</script>
<?php } ?>