
// This class is modified for handling two type of data and additional functions

class	cmsIndexList
{
	constructor()
	{
	}
	
	init(languagesList)
	{
		this.requestData(null);

		this.languagesList = languagesList;
	}

	onXHRSuccess(response, callInstance)
	{
		if(response.state != 0)
		{
			return;
		}
		
		let	tableBody 	= document.getElementById('table-body-overview');
			tableBody.innerHTML = '';

		let numObjects 	= Object.keys(response.data).length;

		for(let i = 0; i < numObjects; i++)
		{
			let	template = callInstance.replaceProcess(response.data[i]);

			let tableRow = document.createElement('tr');
				tableRow.classList.add('trigger-batch-item');
				tableRow.innerHTML = template;
				
			if(callInstance.languagesList[response.data[i].page_language].lang_locked)
			{
				tableRow.querySelectorAll('.bach-item-menu .dropdown-content a[data-right="edit"]').forEach(function(element){
					element.remove();
				});
			}
			else
			{
				tableRow.querySelector('.bach-item-menu .dropdown-content a[data-right="view"]').remove();
			}

			tableBody.append(tableRow);
		}
		
		document.pIndexSelector.bindEvents();
	}

	replaceProcess(object)
	{
		if(object.is_frontend == 1)
			object.is_frontend = 'Frontend';
		else
			object.is_frontend = 'Backend';

		let	spacer = (object.page_path !== '/' ? object.level * 20 : 0);
				
		if(object.create_time == '0') object.create_time = ''; else object.create_time = cmstk.formatDate(object.create_time, 'Y-m-d @ H:i:s');
		if(object.update_time == '0') object.update_time = ''; else object.update_time = cmstk.formatDate(object.update_time, 'Y-m-d @ H:i:s');
			
		let	template = document.getElementById('template-table-row-page').innerHTML;
			template = template.replace(/%NODE_ID%/g, object.node_id);
			template = template.replace(/%PAGE_NAME%/g, object.page_name);
			template = template.replace(/%PAGE_LANGUAGE%/g, object.page_language);
			template = template.replace(/%PAGE_PATH%/g, object.page_path);
			template = template.replace(/%SPACER%/g, spacer);
			template = template.replace(/%CREATE_TIME%/g, object.create_time);
			template = template.replace(/%UPDATE_TIME%/g, object.update_time);

		return template;
	}

	requestData(language)
	{
		var	that = this;

		var	formData = new FormData;
			formData.append('cms-xhrequest','raw-data');

		if(language !== null)
			formData.append('language',language);

		var	requestTarget	= CMS.SERVER_URL_BACKEND + CMS.PAGE_PATH + CMS.MODULE_TARGET;

		cmstk.callXHR(requestTarget, formData, that.onXHRSuccess, cmstk.onXHRError, that);
	}


}
