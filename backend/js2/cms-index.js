class cmsIndex
{
	constructor(requestTarget, onSuccessCallback, controllerAction = 'index')
	{
		this.requestTarget 		= requestTarget;
		this.onSuccessCallback 	= onSuccessCallback;
		this.controllerAction 	= controllerAction;
	}

	onRequestSuccess(response, srcInfo)
	{
		if(response.state !== 0)
		{
			console.log(response.msg);
			return;
		}			
		srcInfo.indexInstance.onSuccessCallback(response.data, srcInfo.srcInstance);
	}

	request(srcInstance, formData = null)
	{
		cmstk.callXHR(
			this.requestTarget, 
			formData, 
			this.onRequestSuccess, 
			cmstk.onXHRError, 
			{ indexInstance:this, srcInstance:srcInstance }, 
			this.controllerAction);
	}

}
