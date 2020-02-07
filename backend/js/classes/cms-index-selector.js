
class cmsIndexSelector
{
	constructor()
	{
		var selectAllClass		= '';
		var selectItemClass		= '';
	}

	init(selectAllClass, selectItemClass)
	{
		this.selectAllClass 	= selectAllClass;
		this.selectItemClass 	= selectItemClass;

		this.bindEvents();
	}

	bindEvents()
	{
		if(this.selectAllClass != '' && this.selectItemClass != ''); 
			else return;

		let that = this;

		let	selectAll = document.querySelector('.'+ this.selectAllClass);
		if(selectAll != null)
		{
			if(typeof selectAll.instance == 'undefined')
			{
				selectAll.instance = that;
				selectAll.onchange = that.updateSelections;

			}
		}

		let	selectItems = document.querySelectorAll('.'+ this.selectItemClass);
		if(selectItems != null)
		{
			selectItems.forEach(function(element) {
			if(typeof element.instance == 'undefined')
			{
				element.instance = that;
				element.onchange = that.updateSelections;
			}
			});
		}
	}

	updateSelections(event)
	{
		let	element		= event.target;
		let	pInstance	= element.instance;
		let	table 		= element.closest('table');

		// Check what checkbox type called this event

		if(element.classList.contains(pInstance.selectAllClass))
		{
			// Select All

			let	allItemCheckboxes 	= 	table.querySelectorAll('.'+ pInstance.selectItemClass);
			for(let i = 0; i < allItemCheckboxes.length; i++)
			{
				allItemCheckboxes[i].checked = element.checked
			}

		} else if(element.classList.contains(pInstance.selectItemClass))
		{
			// Select Item

			let	allItemCheckboxes 	= table.querySelectorAll('.'+ pInstance.selectItemClass);
			let	allSelected 		= true;
			for(let i = 0; i < allItemCheckboxes.length; i++)
			{
				if(!allItemCheckboxes[i].checked) 
				{
					allSelected = false;
					break;
				}
			}
			document.querySelector('.'+ pInstance.selectAllClass).checked = allSelected;
		}
	}
}