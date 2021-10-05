
class cmstkOld
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
	callXHR(requestURL, formData, callbackSuccess, callbackError, xhrCallInstance, xhrAction = '')
	{		
		var xhRequest = new XMLHttpRequest();

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
	ping(requestURL, iTimeout, pingId)
	{
		let	formData = new FormData();
			formData.append('cms-xhrequest', 'lockState');
			formData.append('cms-ping-id', pingId);

		let xhRequest = new XMLHttpRequest();
			xhRequest.open('POST', requestURL);
			xhRequest.responseType = 'json';
			xhRequest.onloadend = function() {

				if(this.status === 200)
				{
					cmstk.pingSuccess(xhRequest.response);

					setTimeout(function() {

						cmstk.ping(requestURL, iTimeout, pingId);

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
	pingLock(lock = true)
	{
		let	submitContainers = document.querySelectorAll('.submit-container');

		for(let i = 0; i < submitContainers.length; i++)		
			submitContainers[i].querySelector('.trigger-submit-protector').disabled = lock;
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

			case 2: // Freigabe, Daten neu laden, Bearbeitung freigeben

					cmstk.pingLock(false);
					resultBox.innerHTML = '';
					resultBox.setAttribute('data-error', '-1');

					document.dataInstance.requestData();
					
					break;

			case 9: // Gesperrt, kein Bearbeitungsrecht
			
					cmstk.pingLock();
					resultBox.innerHTML = template;
					resultBox.setAttribute('data-error', 1);
										
					break;
		}
	}

	static
	getRandomId()
	{
		//return Math.random().toString(36).substring(2, 20) + Math.random().toString(36).substring(2, 20);
		
		var result           = '';
		var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.:~*!?';
		var charactersLength = characters.length;
		for ( var i = 0; i < 40; i++ )
		{
			result += characters.charAt(Math.floor(Math.random() * charactersLength));
		}
		return result;
	}
		
    static
    in_array(needle, haystack, strict = false)
    {
        let haystackSize = haystack.length;
        if(strict)
            for(let i = 0; i < haystackSize; i++)
            if(haystack[i] === needle)
                return true;
        else
            for(let i = 0; i < haystackSize; i++)
            if(haystack[i] == needle)
                return true;
        return false;
    }
}

class cmsTabInstance
{
	static
	getId()
	{
		let lockId = sessionStorage.getItem('lockId');
		if(lockId !== null)
			return lockId;

		lockId = cmstk.getRandomId();
		sessionStorage.setItem('lockId', lockId);
		return lockId;
	}

	static
	register()
	{             
		let tabId =  cmsTabInstance.getId();
		let tabIdRegister = localStorage.getItem('tabIdRegister');
		if(tabIdRegister === null)
			tabIdRegister = [];
		else
			tabIdRegister = tabIdRegister.split('|');
		if(cmsTabInstance.isRegistered(tabId, tabIdRegister))
		{
			tabId = cmstk.getRandomId();
			sessionStorage.setItem('lockId', tabId);
		}
		else
		{
			tabIdRegister.push(tabId);
		}
		tabIdRegister = tabIdRegister.join('|');
		localStorage.setItem('tabIdRegister', tabIdRegister);
	}

	static
	unregister()
	{             
		let tabId =  cmsTabInstance.getId();
		let tabIdRegister = localStorage.getItem('tabIdRegister');
		if(tabIdRegister === null)
			return;
		tabIdRegister = tabIdRegister.split('|');
		let newRegister    = [];
		for(let i = 0; i < tabIdRegister.length; i++)
		{
			if(tabIdRegister[i] !== tabId)
			{
				newRegister.push(tabIdRegister[i]);
			}
		}
		newRegister = newRegister.join('|');
		localStorage.setItem('tabIdRegister', newRegister);
	}

	static
	isRegistered(tabId, tabIdRegister)
	{
		for(let i = 0; i < tabIdRegister.length; i++)
		{
			if(tabIdRegister[i] === tabId)
			{
				return true;
			}
		}
		return false;
	}
}
 
cmsTabInstance.register();
addEventListener("beforeunload", function(){ cmsTabInstance.unregister() }, false);