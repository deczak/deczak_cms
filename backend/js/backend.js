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
		console.log(element);
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

		xhr = new XMLHttpRequest();
		xhr.open('POST', requestTarget, true);
		xhr.onload = function()
		{
			switch(xhr.status)
			{
				case 200:	// OK					
							var jsonObject = JSON.parse(xhr.response); 
							console.log(jsonObject);
							if(typeof jsonObject.data.redirect != "undefined")
							{
								setTimeout(function(){ window.location.replace(jsonObject.data.redirect); }, 2000);
							}		
												
							if(typeof resultBox != 'undefined')
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
		};
		xhr.send(formData);
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


/**
 *	Functions for overview tables
 */


	function
	updateBatchItemCheckbox(element)
	{
		element.querySelector('.trigger-batch-item-checkbox').checked = !element.querySelector('.trigger-batch-item-checkbox').checked;
		var	allItemCheckboxes 	= document.querySelectorAll('.trigger-batch-item-checkbox');
		var	allSelected 		= true;
		for(var i = 0; i < allItemCheckboxes.length; i++)
		{
			if(!allItemCheckboxes[i].checked) 
			{
				allSelected = false;
				break;
			}
		}
		document.querySelector('.trigger-batch-item-all-checkbox').checked = allSelected;
	}

	function
	updateBatchItemAllCheckbox()
	{
		var	allItemCheckboxes 	= 	document.querySelectorAll('.trigger-batch-item-checkbox');
		for(var i = 0; i < allItemCheckboxes.length; i++)
		{
			allItemCheckboxes[i].checked = this.checked
		}
	}

	// EventListener

	document.querySelector("input.trigger-batch-item-all-checkbox").addEventListener('click', updateBatchItemAllCheckbox);
	document.addEventListener('click', function(event) { var element = event.target.closest('tr'); if(element !== null && element.classList.contains('trigger-batch-item') && !event.target.classList.contains('item-menu')) updateBatchItemCheckbox(element); }, false);

}());

