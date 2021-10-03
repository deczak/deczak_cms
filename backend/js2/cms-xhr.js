
class	cmsXhr
{
	constructor()
	{
	}

	
	request(requestURL, formData, callbackSuccess, xhrCallInstance, xhrAction, callbackError = null)
	{		
		let that = this;

		if(callbackError === null)
			callbackError = that.onXHRError;

		let xhRequest = new XMLHttpRequest();
		xhRequest.open('POST', requestURL);
		xhRequest.responseType = 'json';
		xhRequest.setRequestHeader("X-Requested-With","XMLHttpRequest");
		xhRequest.setRequestHeader("X-Requested-XHR-Action", xhrAction);
		xhRequest.onerror   = function ()
		{
			// Event does not fire on 404 or 500
		};
		xhRequest.onloadend = function()
		{
			if(this.status === 200)
			{
				callbackSuccess(xhRequest.response, xhrCallInstance);
			}
			else
			{
				callbackError(this, xhrCallInstance);
			}
		};
		xhRequest.send(formData);
	}
	
	
 	onXHRError(xhrInstance, xhrCallInstance)
	{
		console.log('XHR error '+ xhrInstance.status +' when accessing '+ xhrInstance.responseURL);
	}



}
