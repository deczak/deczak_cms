(function() {
	

	var	pUiSelect = new cmsUiSelect();
		pUiSelect.init();
		pUiSelect.create();



/**
 *	Functions for edit and create forms
 */


	function
	enableFieldsetSubmit(element)
	{ 

		var submitButton = element.closest('.submit-container').querySelector('.trigger-submit-fieldset');
			submitButton.disabled = !element.checked;
	}

	function
	submitFieldset(element)
	{
		var fieldset 	= element.closest('fieldset');
		var resultBox 	= fieldset.querySelector('.result-box');
		var	formData 	= collectFields(fieldset);
			formData.append('cms-xhrequest',fieldset.getAttribute('data-xhr-target'));

		var	requestTarget	= CMS.SERVER_URL_BACKEND + CMS.PAGE_PATH + CMS.MODULE_TARGET;
		var	customTarget 	= fieldset.getAttribute('data-xhr-overwrite-target');
		if(customTarget !== null)
		{
			requestTarget	= CMS.SERVER_URL_BACKEND + CMS.PAGE_PATH + customTarget;
		}

		var iconElement	= element.querySelector('i');
			iconElement.classList.remove(iconElement.getAttribute('data-icon'));
			iconElement.classList.add('fa-sync-alt');
			iconElement.classList.add('loading');

		// Reset Protector for Submit Button

		var submitContainer = element.closest('.submit-container');
			submitContainer.querySelector('.trigger-submit-protector').checked = false;

			element.disabled = true;

		//

		xhr = new XMLHttpRequest();
		xhr.open('POST', requestTarget, true);
		xhr.onload = function()
		{
			switch(xhr.status)
			{
				case 200:	// OK		

							try	{
								var jsonObject = JSON.parse(xhr.response);
							} catch(e) {
								resultBox.innerHTML = 'Invalid json string - parsing failed';
								resultBox.setAttribute('data-error',1);
								break;
							}
						
							if(typeof jsonObject.data.redirect != "undefined")
							{
								setTimeout(function(){ window.location.replace(jsonObject.data.redirect); }, 2000);
							}		

							// Reset failed validation mark

							fieldset.querySelectorAll('.validation-failed').forEach(function(element){
								element.classList.remove('validation-failed');
							});
											
							// mark fields that failed on validation

							if(jsonObject.state == 1)
							{
								for (var key in jsonObject.data)
								{
									//let inputfield = fieldset.querySelector('input[name="'+ jsonObject.data[key] +'"]');
									let inputfield = fieldset.querySelector('[name="'+ jsonObject.data[key] +'"]');
									if(inputfield != null)
									{
										fieldFailed(inputfield);
										continue;
									}
								}
							}

							// display return message

							if(typeof resultBox != 'undefined' && resultBox != null)
							{
								resultBox.innerHTML = jsonObject.msg;
								resultBox.setAttribute('data-error',jsonObject.state);
							}	
									
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

	function
	fieldFailed(inputfield)
	{
		inputfield.classList.add('validation-failed');
	}

	function
	collectFields(fieldset)
	{
		var formData = new FormData();
		for(var i = 0; i < fieldset.elements.length; i++)
		{
			var field = fieldset.elements[i];
			if(!field.name || field.disabled || field.type === 'file' || field.type === 'reset' || field.type === 'submit' || field.type === 'button' || (field.type === 'checkbox' && !field.checked) || (field.type === 'radio' && !field.checked)) continue;

			//	console.log(field.type);
			//	console.log(field.name +' = '+ field.value);

			formData.append(field.name, field.value);
		}
		return formData;
	}

	// EventListener

	document.addEventListener('change', function(event) { var element = event.target; if(element !== null && element.classList.contains('trigger-submit-protector')) enableFieldsetSubmit(element); }, false);
	document.addEventListener('click', function(event) { var element = event.target; if(element !== null && element.classList.contains('trigger-submit-fieldset')) submitFieldset(element); }, false);



	// Modules Index Checkbox Handling
	document.pIndexSelector.bindEvents();

}());

