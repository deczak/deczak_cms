<?php

	$login_object = &$login_objects[0];

	$login_object -> object_fields 		= json_decode($login_object -> object_fields, true);
	$login_object -> object_session_ext	= json_decode($login_object -> object_session_ext, true);
	$login_object -> object_databases	= json_decode($login_object -> object_databases, true);

	#tk::dbug($login_object);
 
	$login_object -> time_create 	= ($login_object -> time_create == 0 ? '-' : date(TIME_FORMAT_BACKENDVIEW, $login_object -> time_create) );
	$login_object -> time_update 	= ($login_object -> time_update == 0 ? '-' : date(TIME_FORMAT_BACKENDVIEW, $login_object -> time_update) );

	$login_object -> create_by 	= ($login_object -> create_by == 0 ? '-' : $login_object -> create_by );
	$login_object -> update_by 	= ($login_object -> update_by == 0 ? '-' : $login_object -> update_by );

	// this will be replaced later by another source, possible $authenticationTables gets removed and replaced by new one with direct use
	$authenticationTables[] = 'tb_users';
	$authenticationTables[] = 'tb_users_backend';

	// get primary sql connection
	$_sqlInstance = CSQLConnect::instance() -> getConnection( CFG::GET() -> MYSQL -> PRIMARY_DATABASE );

	// get columns from authentication tables
	$authenticationColumns = [];
	foreach($authenticationTables as $table)
	{
		$_sqlTableRes 	= $_sqlInstance -> query("SELECT DISTINCT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table'");
		while($_sqlTable = $_sqlTableRes -> fetch_assoc())
		{
			$authenticationColumns[$table][] = $_sqlTable['COLUMN_NAME'];
		}
	}
?>

<div class="be-module-container forms-view">
	<div>
		<div class="inter-menu">
			<h2><?php echo CLanguage::instance() -> getString('MENU'); ?></h2>
			<hr>
			<ul>
			<li><a class="darkblue" href="#group-data"><?php echo CLanguage::instance() -> getString('MOD_LOGINO_OBJECT_INFO'); ?></a></li>
			</ul>
			<hr>
			<div class="delete-box">	

				
			<?php if($enableDelete && $login_object -> is_protected != 1) { ?>

				<fieldset class="ui fieldset" data-xhr-target="object-delete" data-xhr-overwrite-target="delete/<?php echo $login_object -> object_id; ?>">	
					<div class="submit-container button-only">
						<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-trash-alt" data-icon="fa-trash-alt"></i></span><?php echo CLanguage::instance() -> getString('BUTTON_DELETE'); ?></button>
						<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-login-objects-delete"><label for="protector-login-objects-delete"></label></div>
					</div>
					<div class="result-box" data-error=""></div>
				</fieldset>

			<?php } ?>
			
			</div>
		</div>
	</div>
	<div>








	
		<fieldset class="ui fieldset submit-able" id="group-data" data-xhr-target="login-data" data-xhr-overwrite-target="edit/<?php echo $login_object -> object_id; ?>">
			<legend style=""><?php echo CLanguage::instance() -> getString('MOD_LOGINO_OBJECT'); ?></legend>
			<div>


				<!-- group 
				<div class="group width-100">

					<div class="group-head width-100"><?php echo CLanguage::instance() -> getString('MOD_LOGINO_OBJECT_INFO'); ?></div>


					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('TIME_CREATE_AT'); ?></label>
						<input type="text" disabled value="<?php echo $login_object -> time_create; ?>">
						<i class="fas fa-lock"></i>
					</div>
					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('CREATE_BY'); ?></label>
						<input type="text" disabled name="group_name" value="<?php echo $login_object -> create_by; ?>">
						<i class="fas fa-lock"></i>
					</div>
					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('TIME_UPDATE_AT'); ?></label>
						<input type="text" disabled name="group_name" value="<?php echo $login_object -> time_update; ?>">
						<i class="fas fa-lock"></i>
					</div>
					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('UPDATE_BY'); ?></label>
						<input type="text" disabled name="group_name" value="<?php echo $login_object -> update_by; ?>">
						<i class="fas fa-lock"></i>
					</div>
				</div>
				-->
				<!-- group -->
				<div class="group width-100">

					<div class="group-head width-100"><?php echo CLanguage::instance() -> getString('MOD_LOGINO_OBJECT_INFO'); ?></div>

					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('MOD_LOGINO_OBJECT_NAME'); ?></label>
						<input type="text" disabled name="object_id" value="<?php echo $login_object -> object_id; ?>">
						<i class="fas fa-lock"></i>
					</div>

					<div class="input width-50">
						<label><?php echo CLanguage::instance() -> getString('MOD_LOGINO_OBJECT_DESC'); ?></label>
						<input type="text" name="object_description" maxlength="200" value="<?php echo $login_object -> object_description; ?>">
					</div>
			
					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('MOD_LOGINO_OBJECT_DISABLED'); ?></label>
						<div class="select-wrapper">
						<select name="is_disabled">
							<option value="0" <?php echo ($login_object -> is_disabled == 0 ? 'selected' : ''); ?>><?php echo CLanguage::get() -> string('NO'); ?></option>
							<option value="1" <?php echo ($login_object -> is_disabled == 1 ? 'selected' : ''); ?>><?php echo CLanguage::get() -> string('YES'); ?></option>
						</select>	
						</div>
					</div>

				</div>

				<!-- group -->
				<div class="group width-100">

					<div class="group-head width-100"><?php echo CLanguage::instance() -> getString('MOD_LOGINO_OBJECT_DATABASE'); ?></div>

					<div class="input width-75">
						<label><?php echo CLanguage::instance() -> getString('MOD_LOGINO_OBJECT_DATABASE_SELECT'); ?></label>
						<div class="select-wrapper">
						<select name="object_databases[]" class="dropdown" data-preset="<?php echo implode(',', $login_object -> object_databases); ?>">
							<option></option>
							<?php
							foreach(CFG::GET() -> MYSQL -> DATABASE as $_database)
								echo '<option>'. $_database['name'] .'</option>';
							?>
						</select>		
						</div>
					</div>

					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('MOD_LOGINO_OBJECT_TABLE'); ?></label>
						<div class="select-wrapper">
						<select name="object_table" id="input-object-table">
							<?php
							foreach($authenticationTables as $table)
							{
								echo '<option '. ($login_object -> object_table === $table ? 'selected' : '') .'>'. $table .'</option>';
							}
							?>
						</select>	
						</div>
					</div>
				</div>

				<!-- group -->
				<div class="group width-100">
					<div class="input width-100">
			
					<div class="group-head width-100"><?php echo CLanguage::instance() -> getString('MOD_LOGINO_OBJECT_FIELDS'); ?></div>

					<div id="container-authentication-fields" style="margin-bottom:15px;"></div>
				
					<button class="ui button icon labeled" id="trigger-add-auth-field" type="button"><span><i class="fas fa-plus" data-icon="fa-plus"></i></span>Add authentication field</button>

					</div>
				</div>	

				<!-- group -->
				<div class="group width-100">
					<div class="input width-100">
			
					<div class="group-head width-100"><?php echo CLanguage::instance() -> getString('MOD_LOGINO_OBJECT_SESS_EXTENT'); ?></div>
				
					<div id="container-extend-session-fields" style="margin-bottom:15px;"></div>
									
					<button class="ui button icon labeled" id="trigger-add-extend-session-field" type="button"><span><i class="fas fa-plus" data-icon="fa-plus"></i></span>Add extended session field</button>
		
					</div>
				</div>	

			</div>

			<?php if($enableEdit && $login_object -> is_protected != 1) { ?>

				<div class="result-box" data-error=""></div>

				<!-- Submit button - beware of fieldset name -->

				<div class="submit-container" style="">
					<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-save" data-icon="fa-save"></i></span><?php echo CLanguage::instance() -> getString('BUTTON_SAVE'); ?></button>
					<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-login-objects"><label for="protector-login-objects"></label></div>
				</div>

			<?php } ?>

		</fieldset>



	
		<fieldset class="ui fieldset simply" id="group-data" data-xhr-target="group-data" hidden>
			<div>
				<!-- group -->
				<div class="group width-100">
					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('TIME_CREATE_AT'); ?></label>
						<input type="text" disabled value="<?php echo $login_object -> time_create; ?>">
						<i class="fas fa-lock"></i>
					</div>
					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('CREATE_BY'); ?></label>
						<input type="text" disabled name="group_name" value="<?php echo $login_object -> create_by; ?>">
						<i class="fas fa-lock"></i>
					</div>
					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('TIME_UPDATE_AT'); ?></label>
						<input type="text" disabled name="group_name" value="<?php echo $login_object -> time_update; ?>">
						<i class="fas fa-lock"></i>
					</div>
					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('UPDATE_BY'); ?></label>
						<input type="text" disabled name="group_name" value="<?php echo $login_object -> update_by; ?>">
						<i class="fas fa-lock"></i>
					</div>
				</div>
			</div>
		</fieldset>




			<br><br>

	</div>
</div>



<script>
(function() {
	
	var		authColumns = <?= json_encode($authenticationColumns); ?>;

	function
	onClickAddNewAuthField(fieldSet = null)
	{
//console.log(fieldSet);

		var	oFieldsContainer = document.getElementById('container-authentication-fields');
		var	existingField 	= oFieldsContainer.querySelector(' :last-child');
		var	authTable 		= document.getElementById('input-object-table').value;

		// get next row count
		var	nextFieldCount = 1;
		if(existingField != null)
			nextFieldCount = parseInt(existingField.getAttribute('data-row')) + 1;


		var oFieldWrapper = document.createElement('div');	
			oFieldWrapper.setAttribute('data-row', nextFieldCount);
			oFieldWrapper.style.display = 'flex';
		//	oFieldWrapper.style.fledWrap = 'wrap';
			oFieldWrapper.style.alignItems = 'center';


		// 
		var oAuthFieldContainer = createAuthFieldWrapper('35px');

		var	oButtonRemoveField = document.createElement('button');
			oButtonRemoveField.innerHTML = '<i class="fas fa-trash-alt"></i>';
			oButtonRemoveField.classList.add('ui', 'button', 'icon');
			oButtonRemoveField.style.borderTopRightRadius = '0px';
			oButtonRemoveField.style.borderBottomRightRadius = '0px';
			oButtonRemoveField.onclick = function() {

				var	rowContainer = this.closest('div[data-row]');
					rowContainer.remove();
			};

		oAuthFieldContainer.appendChild(oButtonRemoveField);

		oFieldWrapper.appendChild(oAuthFieldContainer);
	
		addAuthFieldSelectForName(oFieldWrapper, nextFieldCount, authTable, 'object_fields', (typeof fieldSet.name != undefined ? fieldSet.name : null) );
		
		addAuthFieldSelectForDataProc(oFieldWrapper, nextFieldCount, authTable, 'object_fields', (typeof fieldSet.data_prc != undefined ? fieldSet.data_prc : null));

		addAuthFieldSelectForType(oFieldWrapper, nextFieldCount, authTable, 'object_fields', (typeof fieldSet.type != undefined ? fieldSet.type : null));

		addAuthFieldRadioForUsername(oFieldWrapper, nextFieldCount, authTable, (typeof fieldSet.is_username != undefined ? fieldSet.is_username : null));

		oFieldsContainer.appendChild(oFieldWrapper);
	}

	function
	onClickAddNewExtendSessionField(fieldSet = null)
	{
//console.log(fieldSet);

		var	oFieldsContainer = document.getElementById('container-extend-session-fields');
		var	existingField 	= oFieldsContainer.querySelector(' :last-child');
		var	authTable 		= document.getElementById('input-object-table').value;

		// get next row count
		var	nextFieldCount = 1;
		if(existingField != null)
			nextFieldCount = parseInt(existingField.getAttribute('data-row')) + 1;


		var oFieldWrapper = document.createElement('div');	
			oFieldWrapper.setAttribute('data-row', nextFieldCount);
			oFieldWrapper.style.display = 'flex';
		//	oFieldWrapper.style.fledWrap = 'wrap';
			oFieldWrapper.style.alignItems = 'center';


		// 
		var oAuthFieldContainer = createAuthFieldWrapper('35px');

		var	oButtonRemoveField = document.createElement('button');
			oButtonRemoveField.innerHTML = '<i class="fas fa-trash-alt"></i>';
			oButtonRemoveField.classList.add('ui', 'button', 'icon');
			oButtonRemoveField.style.borderTopRightRadius = '0px';
			oButtonRemoveField.style.borderBottomRightRadius = '0px';
			oButtonRemoveField.onclick = function() {

				var	rowContainer = this.closest('div[data-row]');
					rowContainer.remove();
			};

		oAuthFieldContainer.appendChild(oButtonRemoveField);

		oFieldWrapper.appendChild(oAuthFieldContainer);
	
		addAuthFieldSelectForName(oFieldWrapper, nextFieldCount, authTable, 'object_session_ext', (typeof fieldSet.name != undefined ? fieldSet.name : null) );
		
		addAuthFieldSelectForDataProc(oFieldWrapper, nextFieldCount, authTable, 'object_session_ext', (typeof fieldSet.data_prc != undefined ? fieldSet.data_prc : null));

		oFieldsContainer.appendChild(oFieldWrapper);
	}

	function
	createAuthFieldWrapper(fieldItemWidth)
	{
		// create field wrapping container
		var oAuthFieldContainer = document.createElement('div');
			oAuthFieldContainer.classList.add('input');
			oAuthFieldContainer.classList.add('width-25');
			oAuthFieldContainer.style.width = fieldItemWidth;
			oAuthFieldContainer.style.padding = '0px';

		return oAuthFieldContainer;
	}

	function
	addAuthFieldSelectForName(oFieldWrapper, nextFieldCount, authTable, fieldname, preset = null)
	{
		// create field wrapping container
		var oAuthFieldContainer = createAuthFieldWrapper('calc(25% - 35px)');

		// create select wrapper for field name
		var oSelectWrapper = document.createElement('div');
			oSelectWrapper.classList.add('select-wrapper');

		// create select and add options for field name
		var	oFieldColumn = document.createElement('select');
			oFieldColumn.setAttribute('name', fieldname +'['+ nextFieldCount +'][name]');
			oFieldColumn.style.borderRadius = '0px';


		var oOption = document.createElement('option');
		oFieldColumn.appendChild(oOption);

		for(var i = 0; i < authColumns[authTable].length; i++)
		{
			var oOption = document.createElement('option');
				oOption.textContent = authColumns[authTable][i];

			oFieldColumn.appendChild(oOption);
		}

		if(preset != null)
			oFieldColumn.value = preset;
		
		// add select for field name to select wrapper, select wrapper to field container
		oSelectWrapper.appendChild(oFieldColumn);
		oAuthFieldContainer.appendChild(oSelectWrapper);

		oFieldWrapper.appendChild(oAuthFieldContainer);
	}

	function
	addAuthFieldSelectForType(oFieldWrapper, nextFieldCount, authTable, fieldname, preset = null)
	{
		// create field wrapping container
		var oAuthFieldContainer = createAuthFieldWrapper('25%');

		// create select wrapper for field type
		var oSelectWrapper = document.createElement('div');
			oSelectWrapper.classList.add('select-wrapper');

		// create select and add options for field type
		var	oFieldColumn = document.createElement('select');
			oFieldColumn.setAttribute('name', fieldname +'['+ nextFieldCount +'][type]');
			oFieldColumn.style.borderTopLeftRadius = '0px';
			oFieldColumn.style.borderBottomLeftRadius = '0px';

		var oOption1 = document.createElement('option');
			oOption1.textContent = 'text';
		oFieldColumn.appendChild(oOption1);

		var oOption2 = document.createElement('option');
			oOption2.textContent = 'password';
		oFieldColumn.appendChild(oOption2);

		if(preset != null)
			oFieldColumn.value = preset;		

		// add select for field type to select wrapper, select wrapper to field container
		oSelectWrapper.appendChild(oFieldColumn);
		oAuthFieldContainer.appendChild(oSelectWrapper);

		// add field container to fields container
		oFieldWrapper.appendChild(oAuthFieldContainer);		
	}

	function
	addAuthFieldSelectForDataProc(oFieldWrapper, nextFieldCount, authTable, fieldname, preset = null)
	{
		// create field wrapping container
		var oAuthFieldContainer = createAuthFieldWrapper('25%');

		// create select wrapper for field type
		var oSelectWrapper = document.createElement('div');
			oSelectWrapper.classList.add('select-wrapper');

		// create select and add options for field type
		var	oFieldColumn = document.createElement('select');
			oFieldColumn.setAttribute('name', fieldname +'['+ nextFieldCount +'][data_prc]');
			oFieldColumn.style.borderRadius = '0px';

		var oOption1 = document.createElement('option');
			oOption1.textContent = 'crypt';
		oFieldColumn.appendChild(oOption1);

		var oOption2 = document.createElement('option');
			oOption2.textContent = 'hash';
		oFieldColumn.appendChild(oOption2);

		var oOption3 = document.createElement('option');
			oOption3.textContent = 'text';
		oFieldColumn.appendChild(oOption3);

		if(preset != null)
			oFieldColumn.value = preset;		

		// add select for field type to select wrapper, select wrapper to field container
		oSelectWrapper.appendChild(oFieldColumn);
		oAuthFieldContainer.appendChild(oSelectWrapper);

		// add field container to fields container
		oFieldWrapper.appendChild(oAuthFieldContainer);		
	}
	

	function
	addAuthFieldRadioForUsername(oFieldWrapper, nextFieldCount, authTable, preset = null)
	{
		// create field wrapping container
		var oAuthFieldContainer = createAuthFieldWrapper('25%');
			oAuthFieldContainer.style.paddingLeft = '15px';
			oAuthFieldContainer.style.paddingBottom = '4px';

		// create select and add options for field type
		var	oFieldRadio = document.createElement('input');
			oFieldRadio.setAttribute('type', 'radio');
			oFieldRadio.setAttribute('name','object_field_is_username');
			oFieldRadio.setAttribute('value', nextFieldCount);
			oFieldRadio.setAttribute('id','input-radio-is-username-'+ nextFieldCount);

		if(nextFieldCount == 1)
			oFieldRadio.checked = true;

		var	oFieldRadioLabel = document.createElement('label');
			oFieldRadioLabel.textContent = 'Field is username';
			oFieldRadioLabel.setAttribute('for','input-radio-is-username-'+ nextFieldCount);

		if(preset != null && preset == '1')
			oFieldRadio.checked = true;			

		oAuthFieldContainer.appendChild(oFieldRadio);
		oAuthFieldContainer.appendChild(oFieldRadioLabel);

		// add field container to fields container
		oFieldWrapper.appendChild(oAuthFieldContainer);		
	}

	function
	onChangeTableSelect()
	{
		var	authTable	= this.value;
		var	fieldsList 	= document.getElementById('container-authentication-fields').querySelectorAll('.select-fields');

		for(var i = 0; i < fieldsList.length; i++)
		{
			var	activeSelection = fieldsList[i].value;

			fieldsList[i].innerHTML = '';

			var oOption = document.createElement('option');
			fieldsList[i].appendChild(oOption);

			for(var e = 0; e < authColumns[authTable].length; e++)
			{
				var oOption = document.createElement('option');
					oOption.textContent = authColumns[authTable][e];

				fieldsList[i].appendChild(oOption);
			}			

			fieldsList[i].value = activeSelection;
		} 
	}
	
	document.getElementById('trigger-add-auth-field').addEventListener('click', onClickAddNewAuthField);
	document.getElementById('trigger-add-extend-session-field').addEventListener('click', onClickAddNewExtendSessionField);
	document.getElementById('input-object-table').addEventListener('change', onChangeTableSelect);

	<?php
	foreach($login_object -> object_fields as $fieldSet)
	{
		echo 'onClickAddNewAuthField('. json_encode($fieldSet, JSON_FORCE_OBJECT) .');';
	}

	foreach($login_object -> object_session_ext as $fieldSet)
	{
		echo 'onClickAddNewExtendSessionField('. json_encode($fieldSet, JSON_FORCE_OBJECT) .');';
	}
	?>

}());
</script><br><br>
