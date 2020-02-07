class cmsPageEdit
{
	constructor()
	{
	}

	init(buttonId)
	{
		var button = document.getElementById(buttonId);
		if(button != null)
		{
			button.instance = this;
			button.onclick 	= this.submit;
			return true;
		}
		console.log('cmsPageEdit::init() - Button element on page edit mode missing.');
	}
	
	collectFields(element)
	{			
		var fieldsets = element.querySelectorAll('fieldset');
		
		var formData = new FormData();
		for(var o = 0; o < fieldsets.length; o++)
		{
			for(var i = 0; i < fieldsets[o].elements.length; i++)
			{
				var field = fieldsets[o].elements[i];
								
				if(!field.name || field.disabled || field.type === 'file' || field.type === 'reset' || field.type === 'submit' || field.type === 'button' || (field.type === 'checkbox' && !field.checked)) continue;
				formData.append(field.name, field.value);
			}
		}
		
		return formData;		
	}
		
	submit(event)
	{
		var element = event.target;

		var panelContainer	= element.closest('#be-page-panel-content');
		
		var	formData 		= this.instance.collectFields(panelContainer);
			formData.append('cms-xhrequest',panelContainer.getAttribute('data-xhr-target'));

		var	requestTarget	= CMS.SERVER_URL_BACKEND + 'pages/' + panelContainer.getAttribute('data-xhr-overwrite-target');

		var iconElement	= element.querySelector('i');
			iconElement.classList.remove(iconElement.getAttribute('data-icon'));
			iconElement.classList.add('fa-sync-alt');
			iconElement.classList.add('loading');

		let	xhr = new XMLHttpRequest();
			xhr.open('POST', requestTarget, true);
			xhr.onload = function()
			{
				switch(xhr.status)
				{
					case 200:	// OK					
								var jsonObject = JSON.parse(xhr.response); 
								//console.log(jsonObject);
								if(typeof jsonObject.data.redirect != "undefined")
								{
									setTimeout(function(){ window.location.replace(jsonObject.data.redirect); }, 2000);
								}		

								panelContainer.querySelectorAll('.result-box').forEach( function(element, key, nodeList) { 
									element.innerHTML = '';
									element.setAttribute('data-error', '');
								});

								let pageIdError = false;
								for(var key in jsonObject.data) {
									
									var resultBox 		= panelContainer.querySelector('.result-box[data-field="'+ key +'"]');
										resultBox.innerHTML = jsonObject.data[key];
										resultBox.setAttribute('data-error', 1);

									if(key == 'page_id')
										pageIdError = true;
								}

								if(!pageIdError)
								{
									let altPageId = document.getElementById('alt_page_id').value;

									if(altPageId != '')
										window.updateAlternateList(altPageId);
								}
					
								document.getElementById('alt_page_id').value = "";
						
								break;
						
					case 500:	// Error	
								if(typeof resultBox != 'undefined')
								{											
									resultBox.innerHTML = 'Script Error (500)';
									resultBox.setAttribute('data-error',1);
								}
								break;
						
					default:	// UK Error	
								if(typeof resultBox != 'undefined')
								{				
									resultBox.innerHTML = 'Error '+ xhr.status;
									resultBox.setAttribute('data-error',1);
								}
				}

				iconElement.classList.remove('fa-sync-alt');
				iconElement.classList.remove('loading');
				iconElement.classList.add(iconElement.getAttribute('data-icon'));

			};
			xhr.send(formData);				
	}
}