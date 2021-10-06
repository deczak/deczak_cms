
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