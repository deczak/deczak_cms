<?php

$agent = &$agentsList[0];

 ?>

<div class="be-module-container forms-view">
	<div>
		<div class="inter-menu">
			<h2><?php echo $language -> string('MENU'); ?></h2>
			<hr>
			<ul>
			<li><a class="darkblue" href="#user-agent"><?php echo $language -> string('M_BEUSERAG_USERAGENT'); ?></a></li>
			</ul>
			<hr>
			<div class="delete-box">
				<?php if($enableDelete) { ?>	
					<fieldset class="ui fieldset" data-xhr-target="agent-delete" data-xhr-overwrite-target="delete/<?php echo $agent -> data_id; ?>">	
						<div class="submit-container button-only">
							<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-trash-alt" data-icon="fa-trash-alt"></i></span><?php echo $language -> string('BUTTON_DELETE'); ?></button>
							<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-agent-delete"><label for="protector-agent-delete"></label></div>
						</div>
						<div class="result-box" data-error=""></div>
					</fieldset>
				<?php } ?>
			</div>
		</div>
	</div>
	<div>
		
		<fieldset class="ui fieldset submit-able" id="user-agent" data-xhr-target="user-agent" data-xhr-overwrite-target="edit/<?php echo $agent -> data_id; ?>">

			<input type="hidden" name="data_id" value="<?php echo $agent -> data_id; ?>">

			<legend><?php echo $language -> string('M_BEUSERAG_USERAGENT'); ?></legend>
			<div>

				<!-- user agent -->
				<div class="group width-100">

					<div class="group-head width-100"><?php echo $language -> string('M_BEUSERAG_USERAGENT'); ?></div>

					<div class="input width-25">
						<label>IP <?php echo $language -> string('M_BEUSERAG_AGENTNAME'); ?></label>
						<input type="text" name="agent_name" value="<?php echo $agent -> agent_name; ?>" maxlength="35">
					</div>

					<div class="input width-50">
						<label>IP <?php echo $language -> string('M_BEUSERAG_AGENTSUFFIX'); ?></label>
						<input type="text" name="agent_suffix" value="<?php echo $agent -> agent_suffix; ?>" maxlength="75">
					</div>
			
					<div class="input width-25">
						<label><?php echo $language -> string('ALLOWED'); ?></label>
						<div class="select-wrapper">
						<select name="agent_allowed">
							<option value="1" <?php echo ($agent -> agent_allowed == 1 ? 'selected' : ''); ?>><?php echo CLanguage::instance() -> getString('YES'); ?></option>
							<option value="0" <?php echo ($agent -> agent_allowed == 0 ? 'selected' : ''); ?>><?php echo CLanguage::instance() -> getString('NO'); ?></option>
						</select>	
						</div>
					</div>

				</div>
				
				<div class="group width-100">

					<div class="input width-100">
						<label><?php echo $language -> string('DESCRIPTION'); ?></label>
						<input type="text" name="agent_desc" value="<?php echo $agent -> agent_desc; ?>" maxlength="200">
					</div>		

				</div>	
				
			</div>

			<?php if($enableEdit) { ?>

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

