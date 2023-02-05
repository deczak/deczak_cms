
class cmstk
{
	/**
	 * Detect if node top is in viewport
	 * 
	 * @param node Dom Node of Element
	 * @param reactBeforeY Y pos before detection success
	 * @return bool
	 */

	static detectNodeInViewport(node, reactBeforeY = 0)
	{
		let boundingRect = node.getBoundingClientRect();
		
		if((window.scrollY + window.innerHeight) >= (boundingRect.top + window.scrollY - reactBeforeY))
			return true;

		return false
	}
	
	/**
	 * Detect if node bottom is in viewport
	 * 
	 * @param node Dom Node of Element
	 * @param reactBeforeY Y pos before detection success
	 * @return bool
	 */

	static detectNodeBottomInViewport(node, reactBeforeY = 0)
	{
		let boundingRect = node.getBoundingClientRect();
	
		if((window.scrollY + window.innerHeight) >= (boundingRect.bottom + window.scrollY - reactBeforeY))
			return true;

		return false
	}
	
	/**
	 * Format bytes as human-readable text.
	 * 
	 * @param bytes Number of bytes.
	 * @param si True to use metric (SI) units, aka powers of 1000. False to use binary (IEC), aka powers of 1024.
	 * @param dp Number of decimal places to display.
	 * 
	 * @return Formatted string.
	 */
	static formatFilesize(bytes, si = true, dp = 1)
	{
		const thresh = si ? 1000 : 1024;

		if(Math.abs(bytes) < thresh)
			return bytes + ' B';
		
		const units = si 
			? ['kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'] 
			: ['KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
		let u = -1;
		const r = 10**dp;

		do
		{
			bytes /= thresh;
			++u;
		}
		while (Math.round(Math.abs(bytes) * r) / r >= thresh && u < units.length - 1);

		return bytes.toFixed(dp) + ' ' + units[u];
	}

	/**
	 * Generate 40 chars long random string
	 * 
	 * @return string
	 */
	static getRandomId()
	{		
		let result           = '';
		let characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.:~*!?';
		let charactersLength = characters.length;
		for(let i = 0; i < 40; i++ )
		{
			result += characters.charAt(Math.floor(Math.random() * charactersLength));
		}
		return result;
	}

	/**
	 * Generate random integer between min and max
	 * 
	 * @param min Min integer.
	 * @param max Max integer.
	 * @return integer
	 */
	static getRandomInteger(min, max)
	{
		return Math.floor(Math.random() * ((max+1) - min)) + min;
	}

	/**
	 *	Strips all unwandet characters out 
	 */
	static validateFilename(str)
	{
		let toValidate = String(str);
		toValidate = toValidate.replace(/[\s]/g, '-');
		toValidate = toValidate.replaceAll('ä', 'ae');
		toValidate = toValidate.replaceAll('ö', 'oe');
		toValidate = toValidate.replaceAll('ü', 'ue');
		toValidate = toValidate.replaceAll('Ä', 'Ae');
		toValidate = toValidate.replaceAll('Ö', 'Oe');
		toValidate = toValidate.replaceAll('Ü', 'Ue');
		toValidate = toValidate.replaceAll('ß', 'ss');
		toValidate = toValidate.replace(/[^a-zA-Z.\-_\[\]\d]/g, '');
		return toValidate;
	}

	/**
	 *	Strips all unwandet characters out 
	 */
	static validateFilepath(str)
	{
		let toValidate = String(str);
		toValidate = toValidate.replace(/[\s]/g, '-');
		toValidate = toValidate.replace(/\/\//g, '/');
		toValidate = toValidate.replaceAll('ä', 'ae');
		toValidate = toValidate.replaceAll('ö', 'oe');
		toValidate = toValidate.replaceAll('ü', 'ue');
		toValidate = toValidate.replaceAll('Ä', 'Ae');
		toValidate = toValidate.replaceAll('Ö', 'Oe');
		toValidate = toValidate.replaceAll('Ü', 'Ue');
		toValidate = toValidate.replaceAll('ß', 'ss');
		toValidate = toValidate.replace(/[^a-zA-Z.\-_\[\]/\d]/g, '');
		return toValidate;
	}

	// 

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
		xhRequest.setRequestHeader("X-Requested-With","XMLHttpRequest");
		xhRequest.setRequestHeader("X-Requested-XHR-Action", 'ping');
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

class cmsNode
{
	static getNodeList(language, onSuccessCallback, srcInstance)
	{
		let formData 		= new FormData();
			formData.append('language', language ?? 'en');
			formData.append('listtype', 'simple');

		let	requestTarget	= CMS.SERVER_URL_BACKEND +'pages';
	
		cmstk.callXHR(requestTarget, formData, onSuccessCallback, cmstk.onXHRError, srcInstance, 'index');
	}

	/**
	 * Create a multidimensional array from a nested set node list
	 * 
	 * @param list _srcList Source node list as array or object with properties
	 * @param array _dstList Destination structured node list
	 * @param int _loop Array index to start
	 * @param int _level Node Level
	 * @return int Last processed array index, -1 if params are not valid types
	 */
	static createNestedNodeStructure(_srcList, _dstList, _loop, _level = 1)
	{
		if(!Array.isArray(_dstList) || !Number.isInteger(_loop) || !Number.isInteger(_level))
			return -1;
		let remLoop = 0;
		for(let i in _srcList)
		{
			remLoop = parseInt(i);
			if(parseInt(i) < _loop || typeof _srcList[i] === 'function')
				continue;
			if(_srcList[i].level < _level)
				break;
		
			if(_srcList[i].level > _level)
			{
				_loop = cmsNode.createNestedNodeStructure(_srcList, _dstList[_dstList.length - 1].childs, parseInt(i), _srcList[i].level);
				remLoop = _loop;
				if(remLoop == Object.keys(_srcList).length - 1)
					break;
				continue;
			}
			let item = {
						level:   _srcList[i].level,
						name:    _srcList[i].page_name,
						path:    _srcList[i].page_path,
						icon:    (_srcList[i].offspring > 0 ? 'fas fa-folder' : null),
						node_id: _srcList[i].node_id,
						page_id: _srcList[i].page_id,
						lang:    _srcList[i].page_language,
						childs:[]
					};
					
			_dstList.push(item);
		}
		return remLoop;
	}

}
