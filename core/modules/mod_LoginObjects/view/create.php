<?php

	// this will be replaced later by another source, possible $authenticationTables gets removed and replaced by new one with direct use
	$authenticationTables[] = 'tb_users';
	$authenticationTables[] = 'tb_users_backend';

	// get primary sql connection
	$_sqlInstance = CSQLConnect::instance() -> getConnection( CFG::GET() -> MYSQL -> PRIMARY_DATABASE );

	// get columns from authentication tables
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
			</div>
		</div>
	</div>
	<div>

		
		<fieldset class="ui fieldset submit-able" id="group-data" data-xhr-target="group-data">
			<legend><?php echo CLanguage::instance() -> getString('MOD_LOGINO_SUB_CREATE_NAME'); ?></legend>
			<div>
				<!-- group -->
				<div class="group width-100">

					<div class="group-head width-100"><?php echo CLanguage::instance() -> getString('MOD_LOGINO_OBJECT_INFO'); ?></div>

					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('MOD_LOGINO_OBJECT_NAME'); ?></label>
						<input type="text" name="object_id" value="">
					</div>

					<div class="input width-50">
						<label><?php echo CLanguage::instance() -> getString('MOD_LOGINO_OBJECT_DESC'); ?></label>
						<input type="text" name="object_description" maxlength="200" value="">
					</div>
			
					<div class="input width-25">
						<label><?php echo CLanguage::instance() -> getString('MOD_LOGINO_OBJECT_DISABLED'); ?></label>
						<div class="select-wrapper">
						<select name="is_disabled">
							<option value="0"><?php echo CLanguage::instance() -> getString('NO'); ?></option>
							<option value="1"><?php echo CLanguage::instance() -> getString('YES'); ?></option>
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
						<select name="object_databases[]" class="dropdown">
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
								echo '<option>'. $table .'</option>';
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

			<div class="result-box" data-error=""></div>

			<!-- Submit button - beware of fieldset name -->

			<div class="submit-container">
				<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-save" data-icon="fa-save"></i></span><?php echo CLanguage::instance() -> getString('BUTTON_SAVE'); ?></button>
				<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-group-rights"><label for="protector-group-rights"></label></div>
			</div>

		</fieldset>



		<br><br>

	</div>
</div>



<script>
(function() {
	
	var		authColumns = <?= json_encode($authenticationColumns); ?>;

	function
	onClickAddNewAuthField()
	{
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
	
		addAuthFieldSelectForName(oFieldWrapper, nextFieldCount, authTable, 'object_fields');
		
		addAuthFieldSelectForDataProc(oFieldWrapper, nextFieldCount, authTable, 'object_fields');

		addAuthFieldSelectForType(oFieldWrapper, nextFieldCount, authTable, 'object_fields');


		addAuthFieldRadioForUsername(oFieldWrapper, nextFieldCount, authTable);

		oFieldsContainer.appendChild(oFieldWrapper);
	}

	function
	onClickAddNewExtendSessionField()
	{
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
	
		addAuthFieldSelectForName(oFieldWrapper, nextFieldCount, authTable, 'object_session_ext');
		
		addAuthFieldSelectForDataProc(oFieldWrapper, nextFieldCount, authTable, 'object_session_ext');

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
	addAuthFieldSelectForName(oFieldWrapper, nextFieldCount, authTable, fieldname)
	{
		// create field wrapping container
		var oAuthFieldContainer = createAuthFieldWrapper('calc(25% - 35px)');

		// create select wrapper for field name
		var oSelectWrapper = document.createElement('div');
			oSelectWrapper.classList.add('select-wrapper');

		// create select and add options for field name
		var	oFieldColumn = document.createElement('select');
			oFieldColumn.setAttribute('name', fieldname +'['+ nextFieldCount +'][name]');
			oFieldColumn.classList.add('select-fields');
			oFieldColumn.style.borderRadius = '0px';

		var oOption = document.createElement('option');
		oFieldColumn.appendChild(oOption);

		for(var i = 0; i < authColumns[authTable].length; i++)
		{
			var oOption = document.createElement('option');
				oOption.textContent = authColumns[authTable][i];

			oFieldColumn.appendChild(oOption);
		}

		// add select for field name to select wrapper, select wrapper to field container
		oSelectWrapper.appendChild(oFieldColumn);
		oAuthFieldContainer.appendChild(oSelectWrapper);

		oFieldWrapper.appendChild(oAuthFieldContainer);
	}

	function
	addAuthFieldSelectForType(oFieldWrapper, nextFieldCount, authTable, fieldname)
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

		// add select for field type to select wrapper, select wrapper to field container
		oSelectWrapper.appendChild(oFieldColumn);
		oAuthFieldContainer.appendChild(oSelectWrapper);

		// add field container to fields container
		oFieldWrapper.appendChild(oAuthFieldContainer);		
	}

	function
	addAuthFieldSelectForDataProc(oFieldWrapper, nextFieldCount, authTable, fieldname)
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

		// add select for field type to select wrapper, select wrapper to field container
		oSelectWrapper.appendChild(oFieldColumn);
		oAuthFieldContainer.appendChild(oSelectWrapper);

		// add field container to fields container
		oFieldWrapper.appendChild(oAuthFieldContainer);		
	}
	
	function
	addAuthFieldRadioForUsername(oFieldWrapper, nextFieldCount, authTable)
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

}());
</script>


