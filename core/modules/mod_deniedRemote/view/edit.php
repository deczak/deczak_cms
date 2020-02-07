<?php

$denied = &$deniedList[0];

 ?>

<div class="be-module-container forms-view">
	<div>
		<div class="inter-menu">
			<h2><?php echo $language -> string('MENU'); ?></h2>
			<hr>
			<ul>
			<li><a class="darkblue" href="#denied-address"><?php echo $language -> string('CREATE'); ?> <?php echo $language -> string('DENIED'); ?> <?php echo $language -> string('ADDRESS'); ?></a></li>
			</ul>
			<hr>
			<div class="delete-box">
				<?php if($enableDelete) { ?>
					<fieldset class="ui fieldset" data-xhr-target="address-delete" data-xhr-overwrite-target="delete/<?php echo $denied -> data_id; ?>">	
						<div class="submit-container button-only">
							<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-trash-alt" data-icon="fa-trash-alt"></i></span><?php echo $language -> string('BUTTON_DELETE'); ?></button>
							<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-address-delete"><label for="protector-address-delete"></label></div>
						</div>
						<div class="result-box" data-error=""></div>
					</fieldset>
				<?php } ?>
			</div>
		</div>
	</div>
	<div>
		
		<fieldset class="ui fieldset submit-able" id="denied-address" data-xhr-target="denied-address" data-xhr-overwrite-target="edit/<?php echo $denied -> data_id; ?>">

			<input type="hidden" name="data_id" value="<?php echo $denied -> data_id; ?>">

			<legend><?php echo $language -> string('CREATE'); ?> <?php echo $language -> string('DENIED'); ?> <?php echo $language -> string('ADDRESS'); ?></legend>
			<div>

				<!-- denied ip -->

				<div class="group width-100">

					<div class="group-head width-100"><?php echo $language -> string('DENIED'); ?> <?php echo $language -> string('ADDRESS'); ?></div>

					<div class="input width-25">
						<label>IP <?php echo $language -> string('ADDRESS'); ?></label>
						<input type="text" name="denied_ip" value="<?php echo $denied -> denied_ip; ?>">
					</div>

					<div class="input width-75">
						<label><?php echo $language -> string('DESCRIPTION'); ?></label>
						<input type="text" name="denied_desc" value="<?php echo $denied -> denied_desc; ?>" maxlength="250">
					</div>
				</div>
				
			</div>

			<?php if($enableEdit) { ?>

				<div class="result-box" data-error=""></div>

				<!-- Submit button - beware of fieldset name -->

				<div class="submit-container">
					<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-save" data-icon="fa-save"></i></span><?php echo $language -> string('BUTTON_SAVE'); ?></button>
					<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-denied-address"><label for="protector-denied-address"></label></div>
				</div>

			<?php } ?>

		</fieldset>


		<br><br>

	</div>
</div>

