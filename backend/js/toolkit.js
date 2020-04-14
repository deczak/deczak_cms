
class cmstk
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
			return cmstk.getVisibleBackgroundColor(node.parentElement);
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

	static 
	ping(requestURL, iTimeout)
	{
		
		let	formData = new FormData();
			formData.append('cms-xhrequest', 'lockState');

		let xhRequest = new XMLHttpRequest();
			xhRequest.open('POST', requestURL);
			xhRequest.responseType = 'json';
			xhRequest.onloadend = function() {

				if(this.status === 200)
				{
					
					cmstk.pingSuccess(xhRequest.response);

					if(xhRequest.response.data.lockedState == 2)
						return;
					
					setTimeout(function() {

						cmstk.ping(requestURL, iTimeout);

					}, iTimeout);

				}
				else
				{
					console.log('ping error');
				}
			};
			xhRequest.send(formData);
	}	

	static
	pingLock()
	{
		let	submitContainers = document.querySelectorAll('.submit-container');

		for(let i = 0; i < submitContainers.length; i++)		
			submitContainers[i].querySelector('.trigger-submit-protector').disabled = true;
	}

	static
	pingSuccess(response)
	{
		let	resultBox 	= document.getElementById('ping-lock-result');

		let	template 	= '<div style="display:flex;"><div style="width:25px;font-size:1.4em;flex-shrink:0;"><i class="fas fa-shield-alt"></i></div><div>'+ response.data.lockedMessage +'</div></div>';

		switch(response.data.lockedState)
		{
			case 0:	// Freigabe

					resultBox.innerHTML = '';
					resultBox.setAttribute('data-error','-1');

					break;

			case 1: // Gesperrt

					cmstk.pingLock();
					resultBox.innerHTML = template;
					resultBox.setAttribute('data-error', 1);
					
					break;		

			case 2: // Freigabe, aber nicht reserviert zur Bearbeitung

					cmstk.pingLock();
					resultBox.innerHTML = template;
					resultBox.setAttribute('data-error', 2);
					
					break;

			case 9: // Gesperrt, kein Bearbeitungsrecht
			
					cmstk.pingLock();
					resultBox.innerHTML = template;
					resultBox.setAttribute('data-error', 1);
										
					break;
		}
	}


}