
class cmsRequestDataItem extends cmsRequestDataIndex
{
	constructor(templateId, timestampFormat, systemId, replaceCallback = null)
	{
		super(templateId, timestampFormat);

		this.systemId = systemId;
		this.replaceCallback = replaceCallback;
	}

	requestData(onSuccess = null)
	{
		console.log(this.systemId);
		super.requestData(this.systemId, this.onXHRSuccess);
	}

	onXHRSuccess(response, callInstance)
	{
		if(Object.keys(response.data).length != 1)
			return false;

		for(let prop in response.data[0])
		{
			if(typeof response.data[0][prop] === 'object')
			{
				let nodes = document.querySelectorAll('[data-input-checkbox="'+ prop +'"]');
		
				for(let nodeIndex = 0; nodeIndex < nodes.length; nodeIndex++)
				{
					nodes[nodeIndex].checked = false;
					let value = 	nodes[nodeIndex].getAttribute('value');
					for(let objectProp in response.data[0][prop])
					{
						if(String(response.data[0][prop][objectProp]) === String(value))
						{
							nodes[nodeIndex].checked = true;
							break;
						}							
					}
				}
			}
			else
			{
				let node = document.querySelector('[name="'+ prop +'"]');

				if(node == null)
					continue;

				if(typeof response.data[0][prop] === 'boolean')
					response.data[0][prop] = (response.data[0][prop] ? 1 : 0);

				switch(node.nodeName)
				{
					case 'SELECT':

							node.value = response.data[0][prop];
							break;

					case 'INPUT':

							node.value = response.data[0][prop];
							node.setAttribute('value', response.data[0][prop]);

							break;
				}
			}

			if(callInstance.replaceCallback != null)
				callInstance.replaceCallback(prop, response.data[0][prop]);
				
		}
	}
}
