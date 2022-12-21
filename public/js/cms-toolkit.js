
class cms_tk
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

}
