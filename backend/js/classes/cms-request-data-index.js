
class	cmsRequestDataIndex
{
	constructor(templateId, timestampFormat, replaceCallback = null)
	{
		this.templateId 		= templateId;
		this.timestampFormat	= timestampFormat;
		this.replaceCallback	= replaceCallback;
		this.xhrAction			= '';
	}
	
	init(xhrAction = '')
	{
		this.xhrAction = xhrAction;
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
		let	template = document.getElementById(this.templateId).innerHTML;
		for(var prop in object)
		{		

				if(typeof object[prop] === 'boolean')
					object[prop] = (object[prop] ? 1 : 0);

			switch(prop)
			{
				case 'time_create':
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

	requestData(systemId = null, onSuccess = null)
	{
		var	that = this;

		var	formData = new FormData;
			formData.append('cms-xhrequest','raw-data');

		if(systemId !== null)
			formData.append('q', 'cms-system-id:'+ systemId);

		var	requestTarget	= CMS.SERVER_URL_BACKEND + CMS.PAGE_PATH;
console.log( this.xhrAction);
		if(onSuccess !== null)
			cmstk.callXHR(requestTarget, formData, onSuccess, cmstk.onXHRError, that, this.xhrAction);
		else
			cmstk.callXHR(requestTarget, formData, that.onXHRSuccess, cmstk.onXHRError, that, this.xhrAction);
	}
}
