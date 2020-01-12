(function() {

	var	pEditorText = new cmsTextEditor();	
		pEditorText.init('editor-simple-text', CMS.SERVER_URL_BACKEND +'json/editor-text.json', 'simple-text');
		pEditorText.create();

	var	pEditorHeadline = new cmsHeadlineEditor();	
		pEditorHeadline.init('editor-simple-headline', CMS.SERVER_URL_BACKEND +'json/editor-headline.json', 'simple-text');
		pEditorHeadline.create();

	var	pObjectTools = new cmsObjectTools();
		pObjectTools.init(CMS.SERVER_URL_BACKEND + CMS.PAGE_PATH + CMS.MODULE_TARGET);
		pObjectTools.create();

	var	pEditorCode = new cmsCodeEditor();	
		pEditorCode.init('editor-simple-code', 'simple-text');
		pEditorCode.create();

	var	pModuleManager = new cmsModuleManager();
		pModuleManager.init('cms-edit-content-container', MODULES, {pEditorText, pEditorHeadline, pEditorCode, pObjectTools});
		pModuleManager.create();



	
/**
 *	Functions for edit sites
 */
	
	
	function
	collectAllFields(element)
	{			
		
		
		var fieldsets = element.querySelectorAll('fieldset');
		
		var formData = new FormData();
		for(var o = 0; o < fieldsets.length; o++)
		{
		
		//	console.log(fieldsets[o]);

			for(var i = 0; i < fieldsets[o].elements.length; i++)
			{
				var field = fieldsets[o].elements[i];
				
				
				if(!field.name || field.disabled || field.type === 'file' || field.type === 'reset' || field.type === 'submit' || field.type === 'button' || (field.type === 'checkbox' && !field.checked)) continue;
				formData.append(field.name, field.value);
				
				console.log(field.name +' = '+ field.value)
			}
		}
		
		return formData;		
	}
	
	
	function
	submitAllFieldset(element)
	{
		
		var panelContainer	= element.closest('#be-page-panel-content');
		
		var	formData 		= collectAllFields(panelContainer);
			formData.append('cms-xhrequest',panelContainer.getAttribute('data-xhr-target'));

		var	requestTarget	= CMS.SERVER_URL_BACKEND + 'pages/' + panelContainer.getAttribute('data-xhr-overwrite-target');

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
												
						//	if(typeof resultBox != 'undefined')
						//	{
						//		resultBox.innerHTML = jsonObject.msg;
						//		resultBox.setAttribute('data-error', 1);
						//	}	



/*
						let numFields 	= Object.keys(response.data).length;

						for(let i = 0; i < numObjects; i++)
						{


						}
*/


panelContainer.querySelectorAll('.result-box').forEach( function(element, key, nodeList) { 

	element.innerHTML = '';
	element.setAttribute('data-error', '');

  });

$pageIdError = false;
for(var key in jsonObject.data) {
    

	
		var resultBox 		= panelContainer.querySelector('.result-box[data-field="'+ key +'"]');

								resultBox.innerHTML = jsonObject.data[key];
								resultBox.setAttribute('data-error', 1);

	if(key == 'page_id')
	$pageIdError = true;

}

if(!$pageIdError)
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
		};
		xhr.send(formData);				
		
	}
	
	// EventListener

	document.addEventListener('click', function(event) { var element = event.target; if(element !== null && element.classList.contains('trigger-submit-site-edit')) submitAllFieldset(element); }, false);
	
}());	