<?php

$object -> params = json_decode($object -> params);

$fieldsList = [];

foreach($login_objects as $objecKey => $objectSet )
	$fieldsList[] = [ "object_id" => $objectSet -> object_id, "object_fields" => json_decode( $objectSet -> object_fields, true ) ];
		
?>

<input type="hidden" name="cms-object-id" value="<?php echo $object -> object_id; ?>">

<div class="login-form-container" style="margin:0 auto; max-width:300px;">

	<form action="" method="post">

		<input type="hidden" name="cms-risa" value="login">
		<input type="hidden" name="cms-tlon" value="<?php echo $object -> params -> object_id; ?>">
		<input type="hidden" name="cms-oid" value="<?php echo $object -> object_id; ?>">

		<fieldset class="ui fieldset">
			<div>

				<div class="login-fields">
		
				</div>

				<div class="input width-100">
					<label>Login Object-ID</label>
					<div class="select-wrapper">
					<select name="login-object-id" id="login-object-select-<?php echo $object -> object_id; ?>">
						<?php
						foreach($login_objects as $objecKey => $objectSet )
						{
							echo '<option '. ($object -> params -> object_id == $objectSet -> object_id ? 'selected' :'') .'>'. $objectSet -> object_id .'</option>';
						}
						?>	
					</select>
					</div>
				</div>

				<div class="input width-100">
					<label>Auto redirect</label>
					<input type="text" name="login-object-redirect"  value="<?php echo $object -> body; ?>">
				</div>


				<div class="input width-100">
					<br>
					<button><?= CLanguage::GET() -> string('LOGIN'); ?></button>
				</div>

			</div>

		</fieldset>

	</form>

</div>

<?php
#tk::dbug($object -> params);
?>

<script>
(function() {
	
	function
	onObjectChange(element = null)
	{
		if(element == null)
			element = this;

		var	selectedObject 	= element.value;
		var	fieldList 		= <?php echo json_encode($fieldsList); ?>;
		var	fieldLabels 	= <?php echo json_encode($object -> params, JSON_HEX_QUOT | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE); ?>;
		var	formContainer 	= element.closest('fieldset');
		var	fieldsContainer = formContainer.querySelector('.login-fields');
			fieldsContainer.innerHTML = '';

		for(var i = 0; i < fieldList.length; i++)
		{
			if(fieldList[i].object_id === selectedObject)
			{

				for(var f = 0; f < fieldList[i].object_fields.length; f++)
				{
					var	oFieldBox = document.createElement('div');
						oFieldBox.classList.add('input', 'width-100');

					var oLabel = document.createElement('label');
						oLabel.innerHTML = 'Field label (for '+ fieldList[i].object_fields[f].name +')';

					var oInput = document.createElement('input');
						oInput.setAttribute('type', 'text');
						oInput.setAttribute('name', 'field_label[]');
						oInput.setAttribute('value', ((fieldLabels.labels != null && typeof fieldLabels.labels[f] !== 'undefined') ? fieldLabels.labels[f] : ''));
						oInput.setAttribute('placeholder', 'Field label (for '+ fieldList[i].object_fields[f].name +')');

					oFieldBox.appendChild(oLabel);
					oFieldBox.appendChild(oInput);

					fieldsContainer.appendChild(oFieldBox);



					var	oFieldBox = document.createElement('div');
						oFieldBox.classList.add('input', 'width-100');


					var oInput = document.createElement('input');
						oInput.setAttribute('type', fieldList[i].object_fields[f].type);
						oInput.setAttribute('name', fieldList[i].object_fields[f].name);
						oInput.setAttribute('value', '');

					oFieldBox.appendChild(oInput);

					fieldsContainer.appendChild(oFieldBox);
				}

				break;
			}
		}

	} onObjectChange(document.getElementById('login-object-select-<?php echo $object -> object_id; ?>'));

	document.getElementById('login-object-select-<?php echo $object -> object_id; ?>').onchange = onObjectChange;

}());
</script>
