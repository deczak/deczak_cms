<?php

$dataSet 	= &$usersList[0];
$user 		= $dataSet['user'];


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
			<h2><?= $language -> string('MENU'); ?></h2>
			<hr>
			<ul>
			<li><a class="darkblue" href="#user"><?= $language -> string('MOD_BEREMOTEU_USERRIGHTS'); ?></a></li>
			</ul>
		</div>
	</div>
	<div>


		<fieldset class="ui fieldset" id="group-data" data-xhr-target="group-data" data-xhr-overwrite-target="edit/<?php echo $right_group -> group_id; ?>">
			<legend><?php echo CLanguage::get() -> string('USER'); ?></legend>
			<div>
				<!-- group -->
				<div class="group width-100">
					<div class="input width-25">
						<label><?php echo CLanguage::get() -> string('USER'); ?></label>
						<input type="text" disabled value="<?= $user -> user_name_first; ?> <?= $user -> user_name_last; ?>">
						<i class="fas fa-lock"></i>
					</div>

					<div class="input width-25">
						<label><?php echo CLanguage::get() -> string('MOD_BEREMOTEU_USERORIGIN'); ?></label>
						<input type="text" disabled value="<?= $dataSet['db_name']; ?> (<?= $dataSet['db_server']; ?>)">
						<i class="fas fa-lock"></i>
					</div>

					<div class="input width-25">
					</div>

					<div class="input width-25">
					</div>
				</div>

			</div>

		</fieldset>



		
		<fieldset class="ui fieldset submit-able" id="user" data-xhr-target="edit-user" data-xhr-overwrite-target="edit/<?= $dataSet['id']; ?>">

			<legend><?= $language -> string('MOD_BEREMOTEU_USERRIGHTS'); ?></legend>
			<div>
				<!-- group -->
				<div class="group width-100">
					<div class="group-head width-100"><?php echo CLanguage::get() -> string('MOD_BEREMOTEU_RIGTHGROUPS'); ?></div>
					<div class="input width-100">
						<div style="display:flex;flex-wrap:wrap;">
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
					<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-save" data-icon="fa-save"></i></span><?= $language -> string('BUTTON_SAVE'); ?></button>
					<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-user"><label for="protector-user"></label></div>
				</div>

			<?php } ?>

		</fieldset>


		<br><br>

	</div>
</div>
