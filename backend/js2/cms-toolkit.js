
class cmstk extends cmstkOld
{

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


	static validateFilename(str)
	{
		let toValidate = String(str);
		toValidate = toValidate.replace(/[\s]/g, '-');
		toValidate = toValidate.replace(/[^a-zA-Z.\-_\[\]\d]/g, '');
		return toValidate;
	}

	static validateFilepath(str)
	{
		let toValidate = String(str);
		toValidate = toValidate.replace(/[\s]/g, '-');
		toValidate = toValidate.replace(/\/\//g, '/');
		toValidate = toValidate.replace(/[^a-zA-Z.\-_\[\]/\d]/g, '');
		return toValidate;
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
						icon:    (_srcList[i].offspring > 0 ? 'fas fa-copy' : null),
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
