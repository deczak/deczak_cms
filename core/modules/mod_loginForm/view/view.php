
<?php


#$login_object -> object_fields = json_decode($login_object -> object_fields);


?>

<div class="login-form-container" style="margin:0 auto; max-width:300px;">
	<form action="" method="post">
		<input type="hidden" name="cms-risa" value="login">
		<input type="hidden" name="cms-tlon" value="<?php echo $object -> params -> object_id; ?>">
		<input type="hidden" name="cms-oid" value="<?php echo $object -> object_id; ?>">

		<fieldset class="ui fieldset">
			<div>

				<?php 
				foreach($login_object -> object_fields as $fieldIndex => $field)
				{

											
			switch($field -> query_type)
			{
				case 'compare'	:
									echo '<div class="input width-100">';
									echo '<label>'. (isset($object -> params -> labels[$fieldIndex]) ? $object -> params -> labels[$fieldIndex] : '') .'</label>';
									echo '<input type="'. $field -> type .'" name="'. $field -> name .'"  value="">';
									echo '</div>';
				
									break;

				case 'assign'	:

									?>
									<div class="input width-100">
										<label><?= (isset($object -> params -> labels[$fieldIndex]) ? $object -> params -> labels[$fieldIndex] : ''); ?></label>
										<div class="select-wrapper">
											<select name="<?= $field -> formValue; ?>" class="dropdown">
												<option></option>
												<?php
												foreach($field -> optionsList as $option)
												{
													echo '<option value="'. $option -> value .'" '. ($option -> isDefault ? 'selected' : '') .'>'. $option -> text .'</option>';
												}
												?>
											</select>	
										</div>
									</div>
									<?php

									break;

			}



				}
				?>

				<div class="input width-100">
					<br>
					<button><?= CLanguage::GET() -> string('LOGIN'); ?></button>
				</div>

			</div>

		</fieldset>

	</form>


</div>