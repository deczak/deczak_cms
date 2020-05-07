<?php

	$tablesColumns = [];
	foreach($tablesList as $tableGroup)
	foreach($tableGroup as $table)
	{
		/*
		$sqlTableRes 	= $sqlConnection -> query("SELECT DISTINCT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table'");
		while($sqlTableItm = $sqlTableRes -> fetch_assoc())
		{
			$tablesColumns[$table][] = $sqlTableItm['COLUMN_NAME'];
		}
		*/
		$tableInfoRes 	= $connection -> query("SELECT DISTINCT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table'", PDO::FETCH_CLASS, 'stdClass');
		$tableInfoList	= $tableInfoRes -> fetchAll();

		foreach($tableInfoList as $tableInfoItm)
		{
			$tablesColumns[$table][] = $tableInfoItm -> COLUMN_NAME;
		}
	

	}	

?>

<div class="be-module-container forms-view">
	<div>
		<div class="inter-menu">
			<h2><?= CLanguage::get() -> string('MENU'); ?></h2>
			<hr>
			<ul>
			<li><a class="darkblue" href="#group-data"><?= CLanguage::get() -> string('MOD_LOGINO_OBJECT_INFO'); ?></a></li>
			</ul>
			<hr>
			<div class="delete-box">	
			</div>
		</div>
	</div>
	<div>

		
		<fieldset class="ui fieldset submit-able" id="group-data" data-xhr-target="group-data">
			<legend><?= CLanguage::get() -> string('MOD_LOGINO_SUB_CREATE_NAME'); ?></legend>
			<div>
				<!-- group -->
				<div class="group width-100">

					<div class="group-head width-100"><?= CLanguage::get() -> string('MOD_LOGINO_OBJECT_INFO'); ?></div>

					<div class="input width-25">
						<label><?= CLanguage::get() -> string('MOD_LOGINO_OBJECT_NAME'); ?></label>
						<input type="text" name="object_id" value="">
					</div>

					<div class="input width-50">
						<label><?= CLanguage::get() -> string('MOD_LOGINO_OBJECT_DESC'); ?></label>
						<input type="text" name="object_description" maxlength="200" value="">
					</div>
			
					<div class="input width-25">
						<label><?= CLanguage::get() -> string('MOD_LOGINO_OBJECT_DISABLED'); ?></label>
						<div class="select-wrapper">
						<select name="is_disabled">
							<option value="0"><?= CLanguage::get() -> string('NO'); ?></option>
							<option value="1"><?= CLanguage::get() -> string('YES'); ?></option>
						</select>	
						</div>
					</div>

				</div>


				<!-- group -->
				<div class="group width-100">

					<div class="group-head width-100"><?= CLanguage::get() -> string('MOD_LOGINO_OBJECT_DATABASE'); ?></div>

					<div class="input width-100">
						<label><?= CLanguage::get() -> string('MOD_LOGINO_OBJECT_DATABASE_SELECT'); ?></label>
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

				</div>

				<!-- group -->
				<div class="group width-100">
					<div class="input width-100">
			
					<div class="group-head width-100"><?= CLanguage::get() -> string('MOD_LOGINO_OBJECT_FIELDS'); ?></div>

					<div id="container-authentication-fields" style="margin-bottom:15px;"></div>
				
					<button class="ui button icon labeled" id="trigger-add-auth-field" type="button"><span><i class="fas fa-plus" data-icon="fa-plus"></i></span>Add authentication field</button>

					<button class="ui button icon labeled" id="trigger-add-assign-field" type="button"><span><i class="fas fa-plus" data-icon="fa-plus"></i></span>Add assignment check field</button>

					</div>
				</div>	






				<!-- group -->
				<div class="group width-100">
					<div class="input width-100">
			
					<div class="group-head width-100"><?= CLanguage::get() -> string('MOD_LOGINO_OBJECT_SESS_EXTENT'); ?></div>
				
					<div id="container-extend-session-fields" style="margin-bottom:15px;"></div>
									
					<button class="ui button icon labeled" id="trigger-add-extend-session-field" type="button"><span><i class="fas fa-plus" data-icon="fa-plus"></i></span>Add extended session field</button>
		
					<button class="ui button icon labeled" id="trigger-add-extend-assign-field" type="button"><span><i class="fas fa-plus" data-icon="fa-plus"></i></span>Add extended session assignment field</button>

					</div>
				</div>	
		

			</div>

			<div class="result-box" data-error=""></div>

			<!-- Submit button - beware of fieldset name -->

			<div class="submit-container">
				<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><span><i class="fas fa-save" data-icon="fa-save"></i></span><?= CLanguage::get() -> string('BUTTON_SAVE'); ?></button>
				<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-group-rights"><label for="protector-group-rights"></label></div>
			</div>

		</fieldset>



		<br><br>

	</div>
</div>



<script>
(function() {
		
	var		tablesList 		= <?= json_encode($tablesList); ?>;
	var		tablesColumns 	= <?= json_encode($tablesColumns); ?>;


	function
	onClickAddNewAuthField(fieldSet = null)
	{

		var	oFieldsContainer = document.getElementById('container-authentication-fields');
		var	existingField 	= oFieldsContainer.lastChild;

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

		if(fieldSet != null && typeof fieldSet.table !== 'undefined')
		{
			authTable = fieldSet.table;
		}
		else
		{
			authTable = tablesList.map(n=>n).shift();
		}

		oAuthFieldContainer.appendChild(oButtonRemoveField);

		oFieldWrapper.appendChild(oAuthFieldContainer);
	
		addAuthFieldSelectForName(oFieldWrapper, nextFieldCount, authTable, 'object_fields', (typeof fieldSet.name !== 'undefined' ? fieldSet.name : null) );
		
		addAuthFieldSelectForTable(oFieldWrapper, nextFieldCount, 'object_fields', authTable);
		
		addAuthFieldSelectForDataProc(oFieldWrapper, nextFieldCount, 'object_fields', (typeof fieldSet.data_prc !== 'undefined' ? fieldSet.data_prc : null));

		addAuthFieldSelectForType(oFieldWrapper, nextFieldCount, 'object_fields', (typeof fieldSet.type !== 'undefined' ? fieldSet.type : null));

		addAuthFieldRadioForUsername(oFieldWrapper, nextFieldCount, (typeof fieldSet.is_username !== 'undefined' ? fieldSet.is_username : null));

		let	pInput	= document.createElement('input');
			pInput.setAttribute('type','hidden');
			pInput.setAttribute('name', 'object_fields['+ nextFieldCount +'][query_type]');
			pInput.setAttribute('value', 'compare');

		oFieldWrapper.appendChild(pInput);

		oFieldsContainer.appendChild(oFieldWrapper);
	}

	function
	onClickAddNewExtendSessionField(fieldSet = null)
	{
		var	oFieldsContainer = document.getElementById('container-extend-session-fields');

		var	existingField 	= oFieldsContainer.lastChild;

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


		if(fieldSet != null && typeof fieldSet.table !== 'undefined')
		{
			authTable = fieldSet.table;
		}
		else
		{
			authTable = tablesList.map(n=>n).shift();
		}



		oAuthFieldContainer.appendChild(oButtonRemoveField);

		oFieldWrapper.appendChild(oAuthFieldContainer);
	
		addAuthFieldSelectForName(oFieldWrapper, nextFieldCount, authTable, 'object_session_ext', (typeof fieldSet.name !== 'undefined' ? fieldSet.name : null) );

		addAuthFieldSelectForTable(oFieldWrapper, nextFieldCount, 'object_session_ext', authTable);
		
		addAuthFieldSelectForDataProc(oFieldWrapper, nextFieldCount, 'object_session_ext', (typeof fieldSet.data_prc !== 'undefined' ? fieldSet.data_prc : null));

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
		var oAuthFieldContainer = createAuthFieldWrapper('calc(20% - 35px)');

		// create select wrapper for field name
		var oSelectWrapper = document.createElement('div');
			oSelectWrapper.classList.add('select-wrapper');

		// create select and add options for field name
		var	oFieldColumn = document.createElement('select');
			oFieldColumn.setAttribute('name', fieldname +'['+ nextFieldCount +'][name]');
			oFieldColumn.style.borderRadius = '0px';
			oFieldColumn.classList.add('select-table-columns');


		var oOption = document.createElement('option');
		oFieldColumn.appendChild(oOption);


		for(var i = 0; i < tablesColumns[authTable].length; i++)
		{
			var oOption = document.createElement('option');
				oOption.textContent = tablesColumns[authTable][i];

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
	addAuthFieldSelectForTable(oFieldWrapper, nextFieldCount, fieldname, preset = null)
	{

		// create field wrapping container
		var oAuthFieldContainer = createAuthFieldWrapper('calc(20% - 35px)');

		// create select wrapper for field name
		var oSelectWrapper = document.createElement('div');
			oSelectWrapper.classList.add('select-wrapper');

		// create select and add options for field name
		var	oFieldColumn = document.createElement('select');
			oFieldColumn.setAttribute('name', fieldname +'['+ nextFieldCount +'][table]');
			oFieldColumn.style.borderRadius = '0px';
			oFieldColumn.onchange = onChangeTableSelect;
			oFieldColumn.tablesColumns = tablesColumns;

		for(var i = 0; i < tablesList.length; i++)
		{
			var oOption = document.createElement('option');
				oOption.textContent = tablesList[i];

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
	addAuthFieldSelectForType(oFieldWrapper, nextFieldCount, fieldname, preset = null)
	{
		// create field wrapping container
		var oAuthFieldContainer = createAuthFieldWrapper('20%');

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
	addAuthFieldSelectForDataProc(oFieldWrapper, nextFieldCount, fieldname, preset = null)
	{
		// create field wrapping container
		var oAuthFieldContainer = createAuthFieldWrapper('20%');

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
	addAuthFieldRadioForUsername(oFieldWrapper, nextFieldCount, preset = null)
	{
		// create field wrapping container
		var oAuthFieldContainer = createAuthFieldWrapper('20%');
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

		var	selectField		= this.closest('div[data-row]').querySelector('select.select-table-columns');

		var	activeSelection = selectField.value;

		selectField.innerHTML = '';

		var oOption = document.createElement('option');
		selectField.appendChild(oOption);

		for(var e = 0; e < this.tablesColumns[authTable].length; e++)
		{
			var oOption = document.createElement('option');
				oOption.textContent = tablesColumns[authTable][e];

			selectField.appendChild(oOption);
		}			

		selectField.value = activeSelection;
	}

	
	document.getElementById('trigger-add-auth-field').addEventListener('click', onClickAddNewAuthField);
	document.getElementById('trigger-add-extend-session-field').addEventListener('click', onClickAddNewExtendSessionField);

}());
</script>


<script>
	var		tablesList 		= <?= json_encode($tablesList); ?>;
	var		tablesColumns 	= <?= json_encode($tablesColumns); ?>;

	//console.log(tablesList);
	//console.log(tablesColumns);

	class	cmsLoginObjectFields
	{	
		constructor(tablesList, tablesColumns, fieldSetsContainerId)
		{
			this.tablesList				= tablesList;
			this.tablesColumns			= tablesColumns;

			this.fieldSetsContainerId	= fieldSetsContainerId;
		}

		onAddAuthCheck(presetFieldSet = null)
		{
			let instance 		  = document.cmsLoginObjectAuthFields;
			let	nextFieldSetIndex = instance.getNextFieldSetIndex();
			let fieldWrapperWidth = '17%';

			let pRemoveButton				= instance.createRemoveButton('35px');
			let pMoveUpButton				= instance.createMoveUpButton('35px');
			let pMoveDownButton				= instance.createMoveDownButton('35px');



			let	pFieldSetType 	= document.createElement('input');
				pFieldSetType.setAttribute('type','hidden');
				pFieldSetType.setAttribute('name','object_fields['+ nextFieldSetIndex +'][query_type]');
				pFieldSetType.setAttribute('data-name-tpl', 'object_fields[%FIELDINDEX%][query_type]');
				pFieldSetType.value = 'compare';




			let pAuthTable 		= instance.createDropdown(nextFieldSetIndex,'object_fields','table', 'authTable','Auth table', tablesList.users, fieldWrapperWidth, instance.onChangeAuthTable);
			let pAuthColumn		= instance.createDropdown(nextFieldSetIndex,'object_fields','name', 'authColumn','Auth columns', [], fieldWrapperWidth, null);
			let pColumnProc		= instance.createDropdown(nextFieldSetIndex,'object_fields','data_prc', 'authProc','Data processing', ['crypt','hash','text'], fieldWrapperWidth, null);
			let pFieldType		= instance.createDropdown(nextFieldSetIndex,'object_fields','type', 'authType','Field Type', ['text','password'], fieldWrapperWidth, null);
			let pRadioButton 	= instance.createRadioButtonUsername(nextFieldSetIndex, 'Field is username', fieldWrapperWidth, (typeof presetFieldSet.is_username !== 'undefined' ? presetFieldSet.is_username : null));

			let pFieldSetContainer = instance.createFieldSetContainer(nextFieldSetIndex);
				pFieldSetContainer.appendChild(pRemoveButton);
				pFieldSetContainer.appendChild(pMoveUpButton);
				pFieldSetContainer.appendChild(pMoveDownButton);

				pFieldSetContainer.appendChild(pAuthTable);
				pFieldSetContainer.appendChild(pAuthColumn);
				pFieldSetContainer.appendChild(pColumnProc);
				pFieldSetContainer.appendChild(pFieldType);
				pFieldSetContainer.appendChild(pRadioButton);

				pFieldSetContainer.appendChild(pFieldSetType);

			let pFieldSetsContainer = document.getElementById(instance.fieldSetsContainerId);
				pFieldSetsContainer.appendChild(pFieldSetContainer);

			if(presetFieldSet !== null)
				instance.presetAuthFieldSet(nextFieldSetIndex, presetFieldSet);

			

			instance.updateMoveButtons(pFieldSetsContainer);


		}

		onAddExtendedSessionInfo(presetFieldSet = null)
		{

			let instance 		  = document.cmsLoginObjectSessionFields;
			let	nextFieldSetIndex = instance.getNextFieldSetIndex();
			let fieldWrapperWidth = '20%';

			let pRemoveButton	= instance.createRemoveButton('35px');


			let	pFieldSetType 	= document.createElement('input');
				pFieldSetType.setAttribute('type','hidden');
				pFieldSetType.setAttribute('name','object_session_ext['+ nextFieldSetIndex +'][query_type]');
				pFieldSetType.setAttribute('data-name-tpl', 'object_session_ext[%FIELDINDEX%][query_type]');
				pFieldSetType.value = 'compare';




			let pAuthTable 		= instance.createDropdown(nextFieldSetIndex,'object_session_ext','table', 'sessionTable','Auth table', tablesList.users, fieldWrapperWidth, instance.onChangeSessionTable);
			let pAuthColumn		= instance.createDropdown(nextFieldSetIndex,'object_session_ext','name', 'sessionColumn','Auth columns', [], fieldWrapperWidth, null);
			let pColumnProc		= instance.createDropdown(nextFieldSetIndex,'object_session_ext','data_prc', 'sessionProc','Data processing', ['crypt','hash','text'], fieldWrapperWidth, null);

			let pFieldSetContainer = instance.createFieldSetContainer(nextFieldSetIndex);
				pFieldSetContainer.appendChild(pRemoveButton);

				pFieldSetContainer.appendChild(pAuthTable);
				pFieldSetContainer.appendChild(pAuthColumn);
				pFieldSetContainer.appendChild(pColumnProc);

				pFieldSetContainer.appendChild(pFieldSetType);

			let pFieldSetsContainer = document.getElementById(instance.fieldSetsContainerId);
				pFieldSetsContainer.appendChild(pFieldSetContainer);

			if(presetFieldSet !== null)
				instance.presetSessionFieldSet(nextFieldSetIndex, presetFieldSet);

		}

		presetAuthFieldSet(fieldSetIndex, presetFieldSet)
		{
			if(presetFieldSet != null && typeof presetFieldSet.table !== 'undefined')
			{
				var authTable = presetFieldSet.table;
			}
			else
			{
				var authTable = tablesList.users.map(n=>n).shift();
			}
						
			document.getElementById('authTable_'+ fieldSetIndex).value = authTable;
			document.getElementById('authTable_'+ fieldSetIndex).dispatchEvent(new Event('change'));
		
			document.getElementById('authColumn_'+ fieldSetIndex).value = (typeof presetFieldSet.name !== 'undefined' ? presetFieldSet.name : null);
			document.getElementById('authProc_'+ fieldSetIndex).value = (typeof presetFieldSet.data_prc !== 'undefined' ? presetFieldSet.data_prc : null);

			let	pTypeField = document.getElementById('authType_'+ fieldSetIndex);
			if(pTypeField !== null)
				pTypeField.value = (typeof presetFieldSet.type !== 'undefined' ? presetFieldSet.type : null);
		}

		presetSessionFieldSet(fieldSetIndex, presetFieldSet)
		{
			if(presetFieldSet != null && typeof presetFieldSet.table !== 'undefined')
			{
				var authTable = presetFieldSet.table;
			}
			else
			{
				var authTable = tablesList.users.map(n=>n).shift();
			}
						
			document.getElementById('sessionTable_'+ fieldSetIndex).value = authTable;
			document.getElementById('sessionTable_'+ fieldSetIndex).dispatchEvent(new Event('change'));
		
			document.getElementById('sessionColumn_'+ fieldSetIndex).value = (typeof presetFieldSet.name !== 'undefined' ? presetFieldSet.name : null);
			document.getElementById('sessionProc_'+ fieldSetIndex).value = (typeof presetFieldSet.data_prc !== 'undefined' ? presetFieldSet.data_prc : null);

			let	pTypeField = document.getElementById('sessionType_'+ fieldSetIndex);
			if(pTypeField !== null)
				pTypeField.value = (typeof presetFieldSet.type !== 'undefined' ? presetFieldSet.type : null);
		}

		presetAssignmentCheckFieldSet(fieldSetIndex, presetFieldSet)
		{
			if(presetFieldSet != null && typeof presetFieldSet.formTable !== 'undefined')
			{
				var assignTable = presetFieldSet.formTable;
				var assignTableRef = presetFieldSet.assignTable;
			}
			else
			{
				var assignTable = tablesList.assignment.map(n=>n).shift();
				var assignTableRef = tablesList.assignment.map(n=>n).shift();
			}
						
			document.getElementById('assignTable_'+ fieldSetIndex).value = assignTable;
			document.getElementById('assignTable_'+ fieldSetIndex).dispatchEvent(new Event('change'));
		
			document.getElementById('assignColumnText_'+ fieldSetIndex).value = (typeof presetFieldSet.formText !== 'undefined' ? presetFieldSet.formText : null);
			document.getElementById('assignColumnValue_'+ fieldSetIndex).value = (typeof presetFieldSet.formValue !== 'undefined' ? presetFieldSet.formValue : null);


			document.getElementById('assignTableRef_'+ fieldSetIndex).value = assignTableRef;
			document.getElementById('assignTableRef_'+ fieldSetIndex).dispatchEvent(new Event('change'));

			document.getElementById('assignTableColumn_'+ fieldSetIndex).value = (typeof presetFieldSet.assignColumn !== 'undefined' ? presetFieldSet.assignColumn : null);
		}

		onAddAssignmentCheck(presetFieldSet = null)
		{
			let instance = document.cmsLoginObjectAuthFields;
			
			let	nextFieldSetIndex = instance.getNextFieldSetIndex();

			let fieldWrapperWidth = '17%';


				let	pFieldSetType 	= document.createElement('input');
				pFieldSetType.setAttribute('type','hidden');
				pFieldSetType.setAttribute('name','object_fields['+ nextFieldSetIndex +'][query_type]');
				pFieldSetType.setAttribute('data-name-tpl', 'object_fields[%FIELDINDEX%][query_type]');
				pFieldSetType.value = 'assign';




			let pRemoveButton				= instance.createRemoveButton('35px');
			let pMoveUpButton				= instance.createMoveUpButton('35px');
			let pMoveDownButton				= instance.createMoveDownButton('35px');
			let pLoginFormDropdown_table 	= instance.createDropdown(nextFieldSetIndex,'object_fields','formTable', 'assignTable','<?= CLanguage::get() -> string('MODULE_LOGOBJ_LFORMTABLE'); ?>', tablesList.assignment, fieldWrapperWidth, instance.onChangeAssignFormTable);
			let pLoginFormDropdown_text		= instance.createDropdown(nextFieldSetIndex,'object_fields','formText', 'assignColumnText','<?= CLanguage::get() -> string('MODULE_LOGOBJ_LFORMTEXT'); ?>', [], fieldWrapperWidth, null);
			let pLoginFormDropdown_value	= instance.createDropdown(nextFieldSetIndex,'object_fields','formValue', 'assignColumnValue','<?= CLanguage::get() -> string('MODULE_LOGOBJ_LFORMVALUE'); ?>', [], fieldWrapperWidth, null);

			let pLoginAssign_table			= instance.createDropdown(nextFieldSetIndex,'object_fields','assignTable', 'assignTableRef','<?= CLanguage::get() -> string('MODULE_LOGOBJ_LASSIGNTABLE'); ?>', tablesList.assignment, fieldWrapperWidth, instance.onChangeAssignCheckTable);
			let pLoginAssign_column			= instance.createDropdown(nextFieldSetIndex,'object_fields','assignColumn', 'assignTableColumn','<?= CLanguage::get() -> string('MODULE_LOGOBJ_LASSIGNCOLUMN'); ?>', [], fieldWrapperWidth);

			let pFieldSetContainer = instance.createFieldSetContainer(nextFieldSetIndex);
				pFieldSetContainer.appendChild(pRemoveButton);
				pFieldSetContainer.appendChild(pMoveUpButton);
				pFieldSetContainer.appendChild(pMoveDownButton);
				pFieldSetContainer.appendChild(pLoginFormDropdown_table);
				pFieldSetContainer.appendChild(pLoginFormDropdown_text);
				pFieldSetContainer.appendChild(pLoginFormDropdown_value);
				pFieldSetContainer.appendChild(pLoginAssign_table);
				pFieldSetContainer.appendChild(pLoginAssign_column);


				pFieldSetContainer.appendChild(pFieldSetType);

			let pFieldSetsContainer = document.getElementById(instance.fieldSetsContainerId);
				pFieldSetsContainer.appendChild(pFieldSetContainer);


			if(presetFieldSet !== null)
				instance.presetAssignmentCheckFieldSet(nextFieldSetIndex, presetFieldSet);



			instance.updateMoveButtons(pFieldSetsContainer);


		}

		onAddAssignmentSession(presetFieldSet = null)
		{

			console.log(presetFieldSet);
			let instance = document.cmsLoginObjectSessionFields;
			
			let	nextFieldSetIndex = instance.getNextFieldSetIndex();

			let fieldWrapperWidth = '20%';

			let	pFieldSetType 	= document.createElement('input');
				pFieldSetType.setAttribute('type','hidden');
				pFieldSetType.setAttribute('name','object_session_ext['+ nextFieldSetIndex +'][query_type]');
				pFieldSetType.setAttribute('data-name-tpl', 'object_session_ext[%FIELDINDEX%][query_type]');
				pFieldSetType.value = 'assign';


			let pRemoveButton				= instance.createRemoveButton('35px');
			let pExtendedSourceTable 		= instance.createDropdown(nextFieldSetIndex,'object_session_ext','checkTable', 'checkTable','<?= CLanguage::get() -> string('MODULE_LOGOBJ_LASSIGNTABLE'); ?>', tablesList.assignment, fieldWrapperWidth, instance.onChangeAssignSessionCheckTable);
			let pExtendedSourceColumn		= instance.createDropdown(nextFieldSetIndex,'object_session_ext','checkColumn','checkColumn','<?= CLanguage::get() -> string('MODULE_LOGOBJ_ASSIGNCOLUMN'); ?>', [], fieldWrapperWidth, null);

			let pExtendedAssignTable		= instance.createDropdown(nextFieldSetIndex,'object_session_ext','infoTable','infoTable','<?= CLanguage::get() -> string('MODULE_LOGOBJ_SOURCETABLE'); ?>', tablesList.assignment, fieldWrapperWidth, instance.onChangeAssignSessionInfoTable);
			let pExtendedAssignColumn		= instance.createDropdown(nextFieldSetIndex,'object_session_ext','infoAssignCol','infoAssignCol','<?= CLanguage::get() -> string('MODULE_LOGOBJ_SOURCECOLUMN2'); ?>', [], fieldWrapperWidth, null);
			let pExtendedAssign2Column		= instance.createDropdown(nextFieldSetIndex,'object_session_ext','infoColumn','infoColumn','<?= CLanguage::get() -> string('MODULE_LOGOBJ_SOURCECOLUMN'); ?>', [], fieldWrapperWidth, null);

			let pFieldSetContainer = instance.createFieldSetContainer(nextFieldSetIndex);
				pFieldSetContainer.appendChild(pRemoveButton);
				pFieldSetContainer.appendChild(pExtendedSourceTable);
				pFieldSetContainer.appendChild(pExtendedSourceColumn);
				pFieldSetContainer.appendChild(pExtendedAssignTable);
				pFieldSetContainer.appendChild(pExtendedAssignColumn);
				pFieldSetContainer.appendChild(pExtendedAssign2Column);

				pFieldSetContainer.appendChild(pFieldSetType);

			let pFieldSetsContainer = document.getElementById(instance.fieldSetsContainerId);
				pFieldSetsContainer.appendChild(pFieldSetContainer);

			if(presetFieldSet !== null)
				instance.presetAssignmentSessionFieldSet(nextFieldSetIndex, presetFieldSet);
		}


		presetAssignmentSessionFieldSet(fieldSetIndex, presetFieldSet)
		{
			if(presetFieldSet != null && typeof presetFieldSet.checkTable !== 'undefined')
			{
				var checkTable = presetFieldSet.checkTable;
				var infoTable = presetFieldSet.infoTable;
			}
			else
			{
				var checkTable = tablesList.assignment.map(n=>n).shift();
				var infoTable = tablesList.assignment.map(n=>n).shift();
			}
						
			document.getElementById('checkTable_'+ fieldSetIndex).value = checkTable;
			document.getElementById('checkTable_'+ fieldSetIndex).dispatchEvent(new Event('change'));

			document.getElementById('infoTable_'+ fieldSetIndex).value = infoTable;
			document.getElementById('infoTable_'+ fieldSetIndex).dispatchEvent(new Event('change'));
		
			document.getElementById('checkColumn_'+ fieldSetIndex).value = (typeof presetFieldSet.checkColumn !== 'undefined' ? presetFieldSet.checkColumn : null);
			document.getElementById('infoColumn_'+ fieldSetIndex).value = (typeof presetFieldSet.infoColumn !== 'undefined' ? presetFieldSet.infoColumn : null);
			document.getElementById('infoAssignCol_'+ fieldSetIndex).value = (typeof presetFieldSet.infoAssignCol !== 'undefined' ? presetFieldSet.infoAssignCol : null);

		}

		onChangeAuthTable()
		{
			let instance = document.cmsLoginObjectSessionFields;
			instance.changeDropdownForColumns('authColumn', this);
		}

		onChangeSessionTable()
		{
			let instance = document.cmsLoginObjectSessionFields;
			instance.changeDropdownForColumns('sessionColumn', this);
		}
		
		onChangeAssignSessionCheckTable(event)
		{
			let instance = document.cmsLoginObjectSessionFields;
			instance.changeDropdownForColumns('checkColumn', this);
		}

		onChangeAssignSessionInfoTable(event)
		{
			let instance = document.cmsLoginObjectSessionFields;
			instance.changeDropdownForColumns('infoColumn', this);
			instance.changeDropdownForColumns('infoAssignCol', this);
		}

		changeDropdownForColumns(elementPrefix, element)
		{
			let instance 			= this;
			let	selectedValue		= element.value;

			let	fieldSetContainer 	= element.closest('div[data-fieldset-index]');
			let	fieldSetIndex		= fieldSetContainer.getAttribute('data-fieldset-index');

			let	dropdown		= document.getElementById(elementPrefix +'_'+ fieldSetIndex);
			let	dropdownValue	= dropdown.value;

			dropdown.innerHTML 	= '';

			for(let i = 0; i < instance.tablesColumns[selectedValue].length; i++)
			{
				let	pOption = document.createElement('option');
					pOption.textContent = instance.tablesColumns[selectedValue][i];

				dropdown.appendChild(pOption);
			}

			dropdown.value = dropdownValue;
		}

		onChangeAssignFormTable(event)
		{

			let instance = document.cmsLoginObjectAuthFields;

			instance.changeDropdownForColumns('assignColumnText', this);
			instance.changeDropdownForColumns('assignColumnValue', this);
		}

		onChangeAssignCheckTable(event)
		{

			let instance = document.cmsLoginObjectAuthFields;

			instance.changeDropdownForColumns('assignTableColumn', this);
		}


		createFieldSetContainer(fieldSetIndex)
		{
			var pFieldSetContainer = document.createElement('div');	
				pFieldSetContainer.setAttribute('data-fieldset-index', fieldSetIndex);
				pFieldSetContainer.style.display 	= 'flex';
				pFieldSetContainer.style.alignItems = 'center';

			return pFieldSetContainer;
		}

		createDropdown(fieldSetIndex, fieldName1, fieldName2, fieldIdPrefix, labelText, optionsList, fieldWrapperWidth = null, onChange = null)
		{

			// create select

			var	pDropdown = document.createElement('select');
				pDropdown.setAttribute('name', fieldName1 +'['+ fieldSetIndex +']['+ fieldName2 +']');
				pDropdown.setAttribute('data-name-tpl', fieldName1 +'[%FIELDINDEX%]['+ fieldName2 +']');
				pDropdown.setAttribute('id', fieldIdPrefix +'_'+ fieldSetIndex);
				pDropdown.style.borderRadius = '0px';
				pDropdown.classList.add('select-table-columns');
				pDropdown.onchange = onChange;

			// create options

			var pOption = document.createElement('option');
				pOption.textContent = '';

			pDropdown.appendChild(pOption);

			for(var i = 0; i < optionsList.length; i++)
			{
				var pOption = document.createElement('option');
					pOption.textContent = optionsList[i];

				pDropdown.appendChild(pOption);
			}

			// create select wrapper

			var pDropdownWrapper = document.createElement('div');
				pDropdownWrapper.classList.add('select-wrapper');
				pDropdownWrapper.appendChild(pDropdown);

			// create label	

			var	pLabel = document.createElement('label');
				pLabel.innerText = labelText;

			// create field wrapper

			let	pFieldWrapper = this.createFieldWrapper(fieldWrapperWidth);
				pFieldWrapper.appendChild(pLabel);
				pFieldWrapper.appendChild(pDropdownWrapper);


			return pFieldWrapper;
		}

		createFieldWrapper(fieldWrapperWidth)
		{
			let pFieldWrapper = document.createElement('div');
				pFieldWrapper.classList.add('input');
				pFieldWrapper.classList.add('width-25');
				pFieldWrapper.style.padding = '0px';

			if(fieldWrapperWidth !== null)
				pFieldWrapper.style.width = fieldWrapperWidth;

			return pFieldWrapper;
		}

		createRemoveButton(fieldWrapperWidth)
		{
			// create label	

			var	pLabel = document.createElement('label');
				pLabel.innerText = ' ';

			// create button

			var	pRemoveButton = document.createElement('button');
				pRemoveButton.innerHTML = '<i class="fas fa-trash-alt"></i>';
				pRemoveButton.classList.add('ui', 'button', 'icon');
				pRemoveButton.style.borderTopRightRadius = '0px';
				pRemoveButton.style.borderBottomRightRadius = '0px';
				pRemoveButton.onclick = function() {

					var	rowContainer = this.closest('div[data-fieldset-index]');
						rowContainer.remove();
				};

			// create field wrapper

			let	pFieldWrapper = this.createFieldWrapper(fieldWrapperWidth);
				pFieldWrapper.appendChild(pLabel);
				pFieldWrapper.appendChild(pRemoveButton);

			return pFieldWrapper;
		}

		createMoveUpButton(fieldWrapperWidth)
		{
			// create label	

			var	pLabel = document.createElement('label');
				pLabel.innerText = ' ';

			// create button

			var	pButton = document.createElement('button');
				pButton.innerHTML = '<i class="fas fa-angle-up"></i>';
				pButton.classList.add('ui', 'button', 'icon', 'button-up');
				pButton.style.borderRadius = '0px';
				pButton.style.borderRight = '0px solid black';
				pButton.onclick = function() {

					let	fieldSetContainer	= this.closest('div[data-fieldset-index]');
					let fieldSetSibling		= fieldSetContainer.previousSibling;

					if(fieldSetSibling == null)
						return false;
					
					let instance 			= document.cmsLoginObjectAuthFields;
					let fieldSetsContainer 	= fieldSetContainer.closest('#'+ instance.fieldSetsContainerId);

					let oldNode 	= fieldSetsContainer.removeChild(fieldSetContainer);
					let newNode 	= fieldSetsContainer.insertBefore(oldNode, fieldSetSibling);

					instance.updateMoveButtons(fieldSetsContainer);
					instance.updateIndexOrder(fieldSetsContainer);

				};

			// create field wrapper

			let	pFieldWrapper = this.createFieldWrapper(fieldWrapperWidth);
				pFieldWrapper.appendChild(pLabel);
				pFieldWrapper.appendChild(pButton);

			return pFieldWrapper;
		}

		updateMoveButtons(fieldSetsContainer)
		{
			let	buttonUps = fieldSetsContainer.querySelectorAll('.button-up > i');

			for(let i = 0; i < buttonUps.length; i++)
			{
				if(i == 0)
					buttonUps[i].style.display = 'none';
				else
					buttonUps[i].style.display = '';
			}

			let	buttonDowns = fieldSetsContainer.querySelectorAll('.button-down > i');

			for(let i = 0; i < buttonDowns.length; i++)
			{
				if(i == buttonDowns.length - 1)
					buttonDowns[i].style.display = 'none';
				else
					buttonDowns[i].style.display = '';
			}
		}

		createMoveDownButton(fieldWrapperWidth)
		{
			// create label	

			var	pLabel = document.createElement('label');
				pLabel.innerText = ' ';

			// create button

			var	pButton = document.createElement('button');
				pButton.innerHTML = '<i class="fas fa-angle-down"></i>';
				pButton.classList.add('ui', 'button', 'icon', 'button-down');
				pButton.style.borderRadius = '0px';
				pButton.style.borderLeft = '0px solid black';
				pButton.onclick = function() {

					let	fieldSetContainer	= this.closest('div[data-fieldset-index]');
					let fieldSetSibling		= fieldSetContainer.nextSibling;

					if(fieldSetSibling == null)
						return false;

					fieldSetSibling		= fieldSetSibling.nextSibling;
							
					let instance 			= document.cmsLoginObjectAuthFields;
					let fieldSetsContainer 	= fieldSetContainer.closest('#'+ instance.fieldSetsContainerId);

					let oldNode 	= fieldSetsContainer.removeChild(fieldSetContainer);
					let newNode 	= fieldSetsContainer.insertBefore(oldNode, fieldSetSibling);


					instance.updateMoveButtons(fieldSetsContainer);
					instance.updateIndexOrder(fieldSetsContainer);

				};

			// create field wrapper

			let	pFieldWrapper = this.createFieldWrapper(fieldWrapperWidth);
				pFieldWrapper.appendChild(pLabel);
				pFieldWrapper.appendChild(pButton);

			return pFieldWrapper;
		}

		updateIndexOrder(fieldSetsContainer)
		{
			let fieldSets = fieldSetsContainer.querySelectorAll('div[data-fieldset-index]');

			for(let i = 0; i < fieldSets.length; i++)
			{
				let newIndex = (i + 1);

				fieldSets[i].setAttribute('data-fieldset-index', newIndex);

				let	tmplInputs = fieldSets[i].querySelectorAll('[data-name-tpl]');

				for(let e = 0; e < tmplInputs.length; e++)
				{
					let	tmplString = tmplInputs[e].getAttribute('data-name-tpl');
						tmplString = tmplString.replace('%FIELDINDEX%', newIndex);

					if(tmplInputs[e].name === 'object_field_is_username')

						tmplInputs[e].setAttribute('value', tmplString);
					else
					{
						let	idSet = tmplInputs[e].id.split('_');
						tmplInputs[e].id = idSet[0] +'_'+ newIndex;
						tmplInputs[e].setAttribute('name', tmplString);
					}
				}
			}
		}

		createRadioButtonUsername(fieldSetIndex, labelText, fieldWrapperWidth = null, optionPreset = null)
		{

			// create label	

			var	pLabel = document.createElement('label');
				pLabel.innerHTML = ' <br><br>';

			// create select and add options for field type
			var	pRadioButton = document.createElement('input');
				pRadioButton.setAttribute('type', 'radio');
				pRadioButton.setAttribute('name','object_field_is_username');
				pRadioButton.setAttribute('value', fieldSetIndex);
				pRadioButton.setAttribute('data-name-tpl', '%FIELDINDEX%');
				pRadioButton.setAttribute('id','input-radio-is-username-'+ fieldSetIndex);

			if(fieldSetIndex == 1)
				pRadioButton.checked = true;

			var	pRadioButtonLabel = document.createElement('label');
				pRadioButtonLabel.textContent = labelText;
				pRadioButtonLabel.setAttribute('for','input-radio-is-username-'+ fieldSetIndex);

			if(optionPreset != null && optionPreset == '1')
				pRadioButton.checked = true;		

			// create field wrapper

			let	pFieldWrapper = this.createFieldWrapper(fieldWrapperWidth);
				pFieldWrapper.appendChild(pLabel);
				pFieldWrapper.appendChild(pRadioButton);
				pFieldWrapper.appendChild(pRadioButtonLabel);

			return pFieldWrapper;
		}

		getNextFieldSetIndex()
		{


			let	lastFieldSet 		= document.getElementById(this.fieldSetsContainerId).lastChild;
			let	nextFieldSetIndex 	= 1;

			if(lastFieldSet != null)
				nextFieldSetIndex = parseInt(lastFieldSet.getAttribute('data-fieldset-index')) + 1;

			return nextFieldSetIndex;
		}
	}

	document.cmsLoginObjectAuthFields 		= new cmsLoginObjectFields(tablesList, tablesColumns, 'container-authentication-fields');
	document.cmsLoginObjectSessionFields 	= new cmsLoginObjectFields(tablesList, tablesColumns, 'container-extend-session-fields');

	document.getElementById('trigger-add-auth-field').onclick 			= document.cmsLoginObjectAuthFields.onAddAuthCheck;
	document.getElementById('trigger-add-extend-session-field').onclick = document.cmsLoginObjectAuthFields.onAddExtendedSessionInfo;
	document.getElementById('trigger-add-assign-field').onclick 		= document.cmsLoginObjectAuthFields.onAddAssignmentCheck;
	document.getElementById('trigger-add-extend-assign-field').onclick 	= document.cmsLoginObjectSessionFields.onAddAssignmentSession;


</script>
