
class TK
{
	static
	getNodeIndex(childNode)
	{
		var	i = 0;
		while((childNode = childNode.previousElementSibling) != null) ++i;
		return i;
	}

	static
	getChildNodeByIndex(index, parentNode)
	{
		if(typeof parentNode[index] !== undefined)
			return parentNode[index];
		return null;
	}
	
	static
	getVisibleBackgroundColor(node)
	{
		var transparent = 'rgba(0, 0, 0, 0)';

		if(!node)
			return transparent;

		var bg = getComputedStyle(node).backgroundColor;

		if(bg === transparent)	
			return TK.getVisibleBackgroundColor(node.parentElement);
		else
			return bg;   
	}
	
	static
	invertRGBA(cssRGBA)
	{
		if(cssRGBA === 'rgba(0, 0, 0, 0)')
		{
			cssRGBA = 'rgba(255, 255, 255, 1)';
		}
		

		cssRGBA = cssRGBA.replace(/[^\d,]/g, '').split(',');
		cssRGBA.forEach(function(item, index) {
			
			if(index == 3) return true;
			cssRGBA[index] = 255 - cssRGBA[index];
		});
		
		return 'rgba('+ cssRGBA.join(', ') +')';
	}

	static
	ucfirst(string)
	{
		return string.charAt(0).toUpperCase() + string.slice(1)
	}

	static
	callXHR(requestURL, formData, callbackSuccess, callbackError, xhrCallInstance)
	{		
		var xhRequest = new XMLHttpRequest();

		xhRequest.open('POST', requestURL);
		xhRequest.responseType = 'json';
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
	
	static
 	onXHRError(xhrInstance, xhrCallInstance)
	{
		console.log('XHR error '+ xhrInstance.status +' when accessing '+ xhrInstance.responseURL);
	}

	static
	formatDate(unixTimestamp, format)
	{
		let date 	= new Date(unixTimestamp * 1000);

		var result	= format;

		// Day

		result	= result.replace(/d/g, date.getDate().toString().padStart(2, '0'));

		//	Month

		let	month 	= date.getMonth() + 1;
		result	= result.replace(/m/g, month.toString().padStart(2, '0'));

		// Year

		result	= result.replace(/Y/g, date.getFullYear().toString());

		// Time

		result	= result.replace(/H/g, date.getHours().toString().padStart(2, '0'));
		result	= result.replace(/i/g, date.getMinutes().toString().padStart(2, '0'));
		result	= result.replace(/s/g, date.getSeconds().toString().padStart(2, '0'));

		return result;
	}


}