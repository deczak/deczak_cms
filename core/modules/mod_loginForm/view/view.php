
<?php

if(is_array($login_objects) && count($login_objects) != 0)
{
	$login_object = $login_objects[0];
}
else
{
	echo '-- Login Object failure';
	return;
}

$login_object -> object_fields = json_decode($login_object -> object_fields);


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
					echo '<div class="input width-100">';
					echo '<label>'. (isset($object -> params -> labels[$fieldIndex]) ? $object -> params -> labels[$fieldIndex] : '') .'</label>';
					echo '<input type="'. $field -> type .'" name="'. $field -> name .'"  value="">';
					echo '</div>';
				}
				?>

				<div class="input width-100">
					<br>
					<button>Login</button>
				</div>

			</div>

		</fieldset>

	</form>


</div>
