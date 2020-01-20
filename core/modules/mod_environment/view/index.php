<?php


?>

<div class="be-module-container forms-view">
	<div>
		<div class="inter-menu"><!--
			<h2><?php echo $language -> string('MENU'); ?></h2>
			<hr>
			<ul>
			<li><a class="darkblue" href="#module-data"><?php echo $language -> string('M_BEMOULE_MODULEINFO'); ?></a></li>
			</ul>-->
			<?php /*
			<hr>
			<div class="delete-box">
				<?php if($enableDelete && $data -> module_type !== 'core') { ?>
					<fieldset class="ui fieldset" data-xhr-target="uninstall" data-xhr-overwrite-target="delete/<?php echo $data -> module_id; ?>">	
						<div class="submit-container button-only">
							<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><i class="fas fa-trash-alt"></i><?php echo $language -> string('BUTTON_DELETE'); ?></button>
							<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-module-delete"><label for="protector-module-delete"></label></div>
						</div>
						<div class="result-box" data-error=""></div>
					</fieldset>
				<?php } ?>
			</div>
			*/ ?>
		</div>
	</div>
	<div>
		




		<?php if($enableEdit) { ?>



		<fieldset class="ui fieldset submit-able button-only" id="">


			<div>


				<div class="group width-100">

					<div class="group-head width-100"><?= $language -> string('M_BEENV_GENERATE'); ?></div>



		<div class="ui" style="width:100%;"><div class="result-box" data-error="2">
			<?= $language -> string('M_BEENV_GENERATE_NOTE'); ?>		
		</div></div>


			

			<div class="delete-box" style="padding: 15px 23px; border-radius:3px; display:flex; flex-direction:column;">

				<div style="display:flex; align-items:center; margin-bottom:15px;">
					<div style="width:213px; border-radius:3px; background:white;">
					<fieldset class="ui fieldset" data-xhr-target="update-htaccess" data-xhr-overwrite-target="edit/1" style="margin:0px;">	
						<div class="submit-container button-only">
							<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><i class="fas fa-sync-alt"></i><?php echo $language -> string('M_BEENV_UPDATE'); ?></button>
							<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-update-htaccess"><label for="protector-update-htaccess"></label></div>
						</div>
					</fieldset>
					</div>
					<div style="font-weight:500; margin-left:20px;">
						<?= $language -> string('M_BEENV_GEN_HTACCESS'); ?>
					</div>
				</div>

				<div style="display:flex; align-items:center; ">
					<div style="width:213px; border-radius:3px; background:white;">
					<fieldset class="ui fieldset" data-xhr-target="update-sitemap" data-xhr-overwrite-target="edit/1" style="margin:0px;">	
						<div class="submit-container button-only">
							<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><i class="fas fa-sync-alt"></i><?php echo $language -> string('M_BEENV_UPDATE'); ?></button>
							<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-update-sitemap"><label for="protector-update-sitemap"></label></div>
						</div>
					</fieldset>
					</div>
					<div style="font-weight:500; margin-left:20px;">
						<?= $language -> string('M_BEENV_GEN_SITEMAP'); ?>
					</div>
				</div>

			</div>

			</div>


</fieldset>


		<?php } ?>


		<br><br>

	</div>
</div>

