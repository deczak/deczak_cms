
class	cmsUiSelect
{
	constructor()
	{
	}

	init()
	{
	}

	create()
	{
		// TODO .. testen ob das mit querySelectorAll funktioniert  .ui div.input > select	
 		var targetElements = Array.prototype.slice.call(document.getElementsByClassName('dropdown'), 0);

		for(var nItem = 0; nItem < targetElements.length; nItem++)
		{
			this.replace(targetElements[nItem]);
		}
	}

	replace(srcElement)
	{
		srcElement.style.cursor ='pointer';

		// bouding div-container
		var	oSrcElePContainer 	= srcElement.parentNode.parentNode;

		// get and remove name attribute
		var	selectNameAttribute	= srcElement.getAttribute('name');
		srcElement.setAttribute('data-name', selectNameAttribute);
		srcElement.removeAttribute('name');

		// write onchange for selection
		srcElement.onchange = this.onChangeSelection;

		// create div for selected items for visual
		var oSelectedViewBox 	= document.createElement('div');
			oSelectedViewBox.classList.add('selected-items');
			oSelectedViewBox.style.display = 'flex';
			oSelectedViewBox.style.flexWrap = 'wrap';

		// append new elements
		oSrcElePContainer.appendChild(oSelectedViewBox);

		// set preset
		var	preset 	= srcElement.getAttribute('data-preset');


		if(preset == null)
			return true;

			preset 	= preset.split(',');

		var	options = srcElement.querySelectorAll('option');

		preset.forEach(function(item) {

			for(var i = 0; i < options.length; i++)
			{
				let	valueAttr	= options[i].getAttribute('value');
				let compareValue = (valueAttr != null ? valueAttr : options[i].innerText);

				if(item === compareValue)
				{
					options[i].hidden = true;

					srcElement.value = item;
					srcElement.onchange();

					break;
				}
			}	
		});
	}

	onChangeSelection()
	{
		var	option		= this.querySelector('option[value="'+this.value+'"]'); 
		var optionValue = (option != null ? option.value : this.value);
		var	optionText 	= (option != null ? option.innerText : this.value);

		if(optionValue == '')
			return false;

		// create element for text
		var selectedText = document.createElement('span');
			selectedText.innerText = optionText;
			selectedText.classList.add('selected-item-text');
			selectedText.style.padding = '4px 8px';

		// create element for remove icon
		var	selectRemBox = document.createElement('span');
			selectRemBox.innerHTML = "&#xf00d;";
			selectRemBox.style.fontFamily = 'icons-solid';
			selectRemBox.style.display = 'inline-block';
			selectRemBox.style.padding = '6px 8px';
			selectRemBox.style.cursor = 'pointer';
			selectRemBox.style.background = 'rgba(0,0,0,0.125)'; 
			selectRemBox.style.borderRadius = '0px 3px 3px 0px';
			selectRemBox.onclick = function() {

				var	selectedItemBox 	= this.parentNode;

				var selectedItemText 	= selectedItemBox.querySelector('.selected-item-text').textContent;
				var	parentContainer 	= selectedItemBox.parentNode.parentNode;
				var	selectOptions 		= parentContainer.querySelectorAll('select > option[hidden]');

				selectOptions.forEach( function(element) {

					if(element.textContent == selectedItemText)
						element.hidden = false;
				});

				selectedItemBox.parentNode.removeChild(selectedItemBox);
			};
			
			selectRemBox.onmouseover = function() { this.style.boxShadow = 'inset 0 0 4px rgba(0,0,0,0.3)'; };
			selectRemBox.onmouseout = function() { this.style.boxShadow = 'initial'; };

		// create input for selected items for submit
		var oSelectedInput 		= document.createElement('input');
			oSelectedInput.setAttribute('type', 'text');
			oSelectedInput.setAttribute('name', this.getAttribute('data-name'));
			oSelectedInput.setAttribute('value', optionValue);
			oSelectedInput.style.display = 'none';			

		// create box for both inner elements and append
		var	selectedItemBox = document.createElement('div');
			selectedItemBox.style.display = 'flex';
			selectedItemBox.style.justifyContent = 'space-between';
			selectedItemBox.style.margin = '6px 6px 0px 0px';
			selectedItemBox.style.fontSize = '0.8em';
			//selectedItemBox.style.border = '1px solid rgba(0,0,0,0.2)';
			selectedItemBox.style.background = 'linear-gradient(to bottom,rgb(233,223,37),rgb(214,187,35))';
			selectedItemBox.style.borderRadius = '3px';
			selectedItemBox.style.boxShadow = '1px 1px 2px 1px rgba(0,0,0,0.3)';

			selectedItemBox.appendChild(oSelectedInput);
			selectedItemBox.appendChild(selectedText);
			selectedItemBox.appendChild(selectRemBox);

		// get element for selected items and append
		var	selectedItemsViewBox = this.parentNode.parentNode.querySelector('.selected-items');

			selectedItemsViewBox.appendChild(selectedItemBox);

		// set selected option hidden and reset value
		var	selectedOption = this.querySelector('option:checked');
			selectedOption.hidden = true;

		this.value = '';
	}
}