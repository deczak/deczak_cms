
<div class="be-module-container">

	<table class="table-overview">
		<thead>
			<tr>
				<td class="batch-selection-item"></td>
				<td class="column-icon"></td>
				<td><?php echo $language -> string('M_BEMOULE_NAME'); ?></td>
				<td><?php echo $language -> string('M_BEMOULE_DESC'); ?></td>
				<td><?php echo $language -> string('M_BEMOULE_SECTION'); ?></td>
				<td class="column-state"><?php echo $language -> string('M_BEMOULE_STATE'); ?></td>
				<td class="column-button"></td>
				<td class="bach-item-menu"></td>
			</tr>
		</thead>
		<tbody id="table-body-overview"><!-- javascript injection --></tbody>
		<tfoot>
			<tr>
				<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-all-checkbox" id="item-all"><label for="item-all"></label></td>
				<td colspan="6"><?php echo $language -> string('SELECT_ALL'); ?></td>
				<td></td>
			</tr>			
		</tfoot>
	</table>

	<div class="ui">
		<div id="result-box-install" class="ui result-box"></div>
	</div>

	<br>


	<div class="ui"><div class="result-box big" data-error="2">
		<b><?= $language -> string('NOTE'); ?>:</b> &nbsp;<?= $language -> string('M_BEMOULE_MSG_INSTALLNOTICE'); ?>		
	</div></div>

	<br><br>

</div>

<style>

	div.be-module-container table.table-overview .column-icon { width:40px; }
	div.be-module-container table.table-overview .column-state { width:65px; text-align:center; }
	div.be-module-container table.table-overview .column-button { text-align:center; }

</style>

<template id="template-table-row">

	<td class="batch-selection-item"><input type="checkbox" class="trigger-batch-item-checkbox" name="group-id[]" value="%module_id%" id="item-%module_id%"><label for="item-%module_id%"></label></td>
	<td class=""><span style="font-family:icons-solid;">%module_icon%</span></td>
	<td>%module_name%</td>
	<td>%module_desc%</td>
	<td>%is_frontend% / %module_type%</td>
	<td><div class="color-indicator positive" data-state="%is_active%"></div></td>
	<td class="column-button"></td>
	<td class="bach-item-menu"><span>&equiv;</span><div class="dropdown-content"><div></div><a href="<?php echo CMS_SERVER_URL_BACKEND . $pageRequest -> urlPath; ?>module/%module_id%"><?php echo $language -> string('BUTTON_EDIT'); ?></a></div></td>
	
</template>

<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-index.js"></script>
<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-request-data-item.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function(){


/*
	onXHRSuccess(response, callInstance)
	{
		if(response.state != 0)
			return;
			
		let	tableBody 	= document.getElementById('table-body-overview');
			tableBody.innerHTML = '';
			
		let numObjects 	= Object.keys(response.data).length;

		for(let i = 0; i < numObjects; i++)
		{
			let	template = callInstance.replaceProcess(response.data[i]);

			let tableRow = document.createElement('tr');
				tableRow.classList.add('trigger-batch-item');
				tableRow.innerHTML = template;

			tableBody.append(tableRow);
		}
	}

	replaceProcess(object)
	{
		let	template = document.getElementById(this.templateId).innerHTML;
		for(var prop in object)
		{		

				if(typeof object[prop] === 'boolean')
					object[prop] = (object[prop] ? 1 : 0);

			switch(prop)
			{
				case 'update_time':
				case 'create_time':
				case 'time_login':

						if(object[prop] != 0)
							object[prop] = cmstk.formatDate(object[prop], this.timestampFormat);
						else
							object[prop] = '';
			}

			let regex = new RegExp('%'+ prop +'%',"g");
			template = template.replace(regex, object[prop]);
		}

		if(this.replaceCallback != null)
			template = this.replaceCallback(template, object);

		return template;
	}
*/

	function
	onXHRSuccess(response, callInstance)
	{

		if(response.state != 0)
		{
			return;
		}
		
		let	tableBody 	= document.getElementById('table-body-overview');
			tableBody.innerHTML = '';

		//

		let numObjects 	= Object.keys(response.data.installed).length;

		for(let i = 0; i < numObjects; i++)
		{
			let	template = replaceProcessInstalled(response.data.installed[i]);

			let tableRow = document.createElement('tr');
				tableRow.classList.add('trigger-batch-item');
				tableRow.innerHTML = template;

			tableBody.append(tableRow);
		}

		//

		numObjects 	= Object.keys(response.data.available).length;

		for(let i = 0; i < numObjects; i++)
		{
			let	template = replaceProcessAvailable(response.data.available[i]);

			let tableRow = document.createElement('tr');
				tableRow.classList.add('trigger-batch-item');
				tableRow.classList.add('disable-submenu');
				tableRow.innerHTML = template;

			let	buttonContainer = tableRow.querySelector('.column-button');

			let	buttonInstall = document.createElement('button');
				buttonInstall.classList.add('ui', 'button', 'labeled', 'icon', 'trigger-install-module');
				buttonInstall.setAttribute('data-module', response.data.available[i].module_location);
				buttonInstall.setAttribute('data-type', response.data.available[i].module_type);
				buttonInstall.innerHTML = '<span><i class="fas fa-box" data-icon="fa-box"></i></span>Install';
				buttonInstall.style.width = "125px";
				buttonInstall.style.textAlign = "left";
				buttonInstall.onclick = function() { onInstall(this, callInstance); }

			buttonContainer.append(buttonInstall);
			
			tableBody.append(tableRow);
		}
	}

	function
	replaceProcessInstalled(object)
	{
	
		object.is_frontend = (object.is_frontend == 1 ? 'Frontend' : 'Backend');
			

		let	template = document.getElementById('template-table-row').innerHTML;

		for(var prop in object)
		{		

				if(typeof object[prop] === 'boolean')
					object[prop] = (object[prop] ? 1 : 0);

			switch(prop)
			{
				case 'update_time':
				case 'create_time':
				case 'time_login':

						if(object[prop] != 0)
							object[prop] = cmstk.formatDate(object[prop], '<?= CFG::GET() -> BACKEND -> TIME_FORMAT; ?>');
						else
							object[prop] = '';

						break;

				case 'module_type':

						object[prop] = cmstk.ucfirst(object[prop]);

						break;
			}

			let regex = new RegExp('%'+ prop +'%',"g");
			template = template.replace(regex, object[prop]);
		}

		return template;
	}

	function
	replaceProcessAvailable(object)
	{
		object.is_frontend = (object.is_frontend == 1 ? 'Frontend' : 'Backend');
			
		let	template = document.getElementById('template-table-row').innerHTML;

		for(var prop in object)
		{		

				if(typeof object[prop] === 'boolean')
					object[prop] = (object[prop] ? 1 : 0);

			switch(prop)
			{
				case 'update_time':
				case 'create_time':
				case 'time_login':

						if(object[prop] != 0)
							object[prop] = cmstk.formatDate(object[prop], '<?= CFG::GET() -> BACKEND -> TIME_FORMAT; ?>');
						else
							object[prop] = '';

						break;

				case 'module_type':

						object[prop] = cmstk.ucfirst(object[prop]);

						break;
			}

			let regex = new RegExp('%'+ prop +'%',"g");
			template = template.replace(regex, object[prop]);
		}


		return template;
	}

	function
	onInstall(button, instance)
	{
		var that = this;

		var	module = button.getAttribute('data-module');
		var	type = button.getAttribute('data-type');

		var	formData = new FormData;
			formData.append('cms-xhrequest','install');
			formData.append('module',module);
			formData.append('type',type);

		var	requestTarget	= CMS.SERVER_URL_BACKEND + CMS.PAGE_PATH + CMS.MODULE_TARGET;

		cmstk.callXHR(requestTarget, formData, onXHRInstallSuccess, cmstk.onXHRError, that);
	}

	function
	onXHRInstallSuccess(response, instance)
	{
		let resultBox = document.getElementById('result-box-install');
			resultBox.innerText = '';

		if(response.state != 0)
		{
			resultBox.innerText = response.msg;
			resultBox.setAttribute('data-error', response.state);
			return;
		}
		
		instance.requestData();
	}

// TODO :: 
/*

	es gibt zwei listen, installiert, verfügbar, letztere brauchen einen install bautton

	die klasse kann aber nur mit einer liste umgehen

*/


	let	indexList = new cmsRequestDataIndex('template-table-row', '<?= CFG::GET() -> BACKEND -> TIME_FORMAT; ?>');
		indexList.requestData(null, onXHRSuccess);
		
});	
</script>