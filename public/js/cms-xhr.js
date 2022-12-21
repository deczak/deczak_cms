
class	cms_xhr
{
	static request(requestURL, formData, callbackSuccess, xhrCallInstance, xhrAction, xhrObjectId, callbackError = null)
	{		
		let that = this;

		if(callbackError === null)
			callbackError = that.onXHRError;

		let xhRequest = new XMLHttpRequest();
		xhRequest.open('POST', requestURL);
		xhRequest.responseType = 'json';
		xhRequest.setRequestHeader("X-Requested-With","XMLHttpRequest");
		xhRequest.setRequestHeader("X-Requested-XHR-Action", xhrAction);
		xhRequest.setRequestHeader("X-Requested-XHR-Object", xhrObjectId);
		xhRequest.onerror   = function ()
		{
			// Event does not fire on 404 or 500
		};
		xhRequest.onloadend = function()
		{
			if(xhRequest.status === 200)
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

	static uploadRequest(requestURL, formData, srcInstance, callbackSuccess, progress, uploadItemNode)
	{
		let xhRequest = new XMLHttpRequest();
		xhRequest.open('POST', requestURL);
		xhRequest.setRequestHeader("X-Requested-With","XMLHttpRequest");
		xhRequest.setRequestHeader("X-Requested-XHR-Action", 'upload');
		xhRequest.onerror = () =>	{
			// Event does not fire on 404 or 500
			console.log('cmsXhr.uploadRequest error', requestURL, callbackSuccess);
		};
		xhRequest.onloadend = (event) => {
			if(callbackSuccess !== null)
				callbackSuccess(xhRequest.status, xhRequest.response, srcInstance, uploadItemNode)
		};
        xhRequest.upload.addEventListener("progress", (event) => {
			if(progress !== null)
			{
				let percentComplete = (event.loaded / event.total) * 100;
				progress.setPercent(percentComplete);
			}
		});

		xhRequest.send(formData);
	}
		
 	static onXHRError(xhrInstance, xhrCallInstance)
	{
		console.log('XHR error '+ xhrInstance.status +' when accessing '+ xhrInstance.responseURL);
	}
}
