
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
		
		let	template = document.getElementById('template-table-row-languages').innerHTML;
			template = template.replace(/%LANG_KEY%/g, object.lang_key);
			template = template.replace(/%LANG_NAME%/g, object.lang_name);
			template = template.replace(/%LANG_LANG_NAME_NATIVE%/g, object.lang_name_native);
			template = template.replace(/%LANG_HIDDEN%/g, object.lang_hidden ? 1 : 0);
			template = template.replace(/%LANG_LOCKED%/g, object.lang_locked ? 1 : 0);
			template = template.replace(/%LANG_DEFAULT%/g, object.lang_default ? 1 : 0);
			template = template.replace(/%LANG_FRONTEND%/g, object.lang_frontend ? 1 : 0);
			template = template.replace(/%LANG_BACKEND%/g, object.lang_backend ? 1 : 0);

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
}
