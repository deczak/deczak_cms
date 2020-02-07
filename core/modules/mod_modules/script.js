
// This class is modified for handling two type of data and additional functions

class	cmsIndexList
{
	constructor()
	{
	}
	
	init()
	{
		this.requestData();
	}

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
			let	template = callInstance.replaceProcessInstalled(response.data.installed[i]);

			let tableRow = document.createElement('tr');
				tableRow.classList.add('trigger-batch-item');
				tableRow.innerHTML = template;

			tableBody.append(tableRow);
		}

		//

		numObjects 	= Object.keys(response.data.available).length;

		for(let i = 0; i < numObjects; i++)
		{
			let	template = callInstance.replaceProcessAvailable(response.data.available[i]);

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
				buttonInstall.onclick = function() { callInstance.onInstall(this, callInstance); }

			buttonContainer.append(buttonInstall);
			
			tableBody.append(tableRow);
		}
	}

	replaceProcessInstalled(object)
	{
		if(object.is_frontend == 1)
			object.is_frontend = 'Frontend';
		else
			object.is_frontend = 'Backend';
			
		let	template = document.getElementById('template-table-row-modules').innerHTML;
			template = template.replace(/%MODULE_ICON%/g, object.module_icon);
			template = template.replace(/%MODULE_NAME%/g, object.module_name);
			template = template.replace(/%MODULE_ID%/g, object.module_id);
			template = template.replace(/%IS_FRONTEND%/g, object.is_frontend);
			template = template.replace(/%IS_ACTIVE%/g, object.is_active);
			template = template.replace(/%MODULE_TYPE%/g, cmstk.ucfirst(object.module_type));

		return template;
	}

	replaceProcessAvailable(object)
	{
		if(object.module_frontend == 1)
			object.module_frontend = 'Frontend';
		else
			object.module_frontend = 'Backend';
			
		let	template = document.getElementById('template-table-row-modules').innerHTML;
			template = template.replace(/%MODULE_ICON%/g, object.module_icon);
			template = template.replace(/%MODULE_NAME%/g, object.module_name);
			template = template.replace(/%MODULE_ID%/g, "");
			template = template.replace(/%IS_FRONTEND%/g, object.module_frontend);
			template = template.replace(/%IS_ACTIVE%/g, "");
			template = template.replace(/%MODULE_TYPE%/g, cmstk.ucfirst(object.module_type));

		return template;
	}

	requestData()
	{
		var	that = this;

		var	formData = new FormData;
			formData.append('cms-xhrequest','raw-data');

		var	requestTarget	= CMS.SERVER_URL_BACKEND + CMS.PAGE_PATH + CMS.MODULE_TARGET;

		cmstk.callXHR(requestTarget, formData, that.onXHRSuccess, cmstk.onXHRError, that);
	}

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

		cmstk.callXHR(requestTarget, formData, instance.onXHRInstallSuccess, cmstk.onXHRError, that);
	}

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
}
