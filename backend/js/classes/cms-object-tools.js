
	class	cmsObjectTools
	{
		constructor()
		{
			this.objectClassName 	= 'cms-content-object';					// Class name of wrapping container for every object container
			this.contentClassName 	= 'cms-edit-content-container';			// Class name of wrapping container for objects inside a content container

			this.objectTools 		= { "buttons": [ {"text":"Move up", "cmd":"up", "icon":"fa-caret-up"} , {"text":"Save changes", "cmd":"edit", "icon":"fa-save"} , {"text":"Delete object", "cmd":"delete", "icon":"fa-trash-alt"} , {"text":"Move down", "cmd":"down", "icon":"fa-caret-down"} ] };
		}

		init(pageURL)
		{
			this.pageURL = pageURL;
		}

		create()
		{
			var	objectsList  = Array.prototype.slice.call(document.getElementsByClassName(this.objectClassName), 0);

			for(var nItem = 0; nItem < objectsList.length; nItem++)
			{		
				if(this.toolsExists(objectsList[nItem])) continue;
				this.replace(objectsList[nItem]);
			}
		}

		toolsExists(objectElement)
		{
			var childNodes = objectElement.childNodes;
			for(var i = 0; i < childNodes.length; i++)
			{
				if(childNodes[i].classList != undefined && childNodes[i].classList.contains('cms-object-tools'))
					return true;
			}
			return false;
		}
// in dieser funktion data-rights abrufen und anhand der rechte die tools anzeigen oder nicht, zur zeit sind für frontend module die delete auf edit gelinkt
// andere custom rechte existieren derzeit nicht .. hier todo für js überholung wenn module ihre eigenen funktionen die mit eigenen rechten gedongelt sein könnten
		replace(objectElement)
		{			
			var that 		= this;


			var	objRights 	= objectElement.getAttribute('data-rights');
				objRights	= objRights.split(',');

			if(!objRights.includes('edit'))
				return false;



			var fontColor = cmstk.getVisibleBackgroundColor(objectElement);
				fontColor = cmstk.invertRGBA(fontColor);

			var	eObjectTools = document.createElement('div');
				eObjectTools.classList.add('cms-object-tools');

			for (var oBtnDefinition, oButton, nBtn = 0; nBtn < this.objectTools.buttons.length; nBtn++)
			{
				oBtnDefinition	  = this.objectTools.buttons[nBtn];

				var	oIconTag	  = document.createElement("i");
					oIconTag.classList.add('fas');
					oIconTag.classList.add(oBtnDefinition.icon);

				oButton 		  = document.createElement("button");
				oButton.className = "cms-trigger-object-tool";
				oButton.title 	  = oBtnDefinition.text;
				oButton.onclick   = function() {
					that.executeCommand(this);
				};
				oButton.setAttribute('type','button');
				oButton.setAttribute('data-cmd',oBtnDefinition.cmd);
				oButton.style.color = fontColor;
				oButton.appendChild(oIconTag);

				eObjectTools.appendChild(oButton);
			}
			objectElement.appendChild(eObjectTools);
		}

		executeCommand(buttonElement)
		{
			var	command 		= buttonElement.getAttribute('data-cmd');

			switch(command)
			{
				case 'edit': 	
				case 'delete': 				
								if(command == 'delete' && !confirm('Delete this object?')) break;
				
								this.processCommandState(buttonElement, true);
								
								var formData = this.getFormData(buttonElement);
									formData.append('cms-ctrl-action['+ formData.get('cms-object-id') +']', command);
									formData.append('cms-xhrequest', command);
	
								this.xhrCommand(formData, buttonElement, this['command'+ command[0].toUpperCase() + command.substring(1)]);
								break;

				case 'up':
				case 'down':	var	objectMovement = this['command'+ command[0].toUpperCase() + command.substring(1)](buttonElement);
								if(objectMovement === false)
								{
									break;
								}

								this.submitObjectsOrder(objectMovement);								
								break;
			}
		}

		processCommandState(buttonElement, processing)
		{
			var parentContainer = buttonElement.closest('.'+ this.objectClassName); 
			if(processing)
			{
				buttonElement.childNodes[0].classList.add('loading');
				buttonElement.childNodes[0].classList.add('fa-sync-alt');
				this.enableTools(parentContainer, !processing);
			}
			else
			{
				buttonElement.childNodes[0].classList.remove('loading');
				buttonElement.childNodes[0].classList.remove('fa-sync-alt');
				this.enableTools(parentContainer, !processing);
			}
		}

		enableTools(parentContainer, enable)
		{
			var	tools = parentContainer.querySelectorAll('button');
				tools.forEach(function(buttonElement){
					buttonElement.disabled = !enable;
				});
		}

		xhrCommand(formData, buttonElement, onSuccess)
		{
			var that = this;
			var xhr = new XMLHttpRequest();
			xhr.open('POST', this.pageURL, true);
			xhr.onload = function()
			{
				switch(xhr.status)
				{
					case 200:	// OK					
								var jsonObject = JSON.parse(xhr.response); 
								if(jsonObject.state == 0)
								{
									if(onSuccess != null)
										onSuccess(buttonElement,that);
								}		
								else
								{
									console.log('XHR execution error: '+ jsonObject.msg);
								}				
								break;
						
					case 500:	// Error	
								if(typeof resultBox != 'undefined')
								{
									console.log('XHR script error (500)');
								}
								break;
						
					default:	// UK Error	
								if(typeof resultBox != 'undefined')
								{
									console.log('XHR error '+ xhr.status);
								}
				}

				if(buttonElement != null)
					that.processCommandState(buttonElement, false);
			};
			xhr.send(formData);	
		}

		submitObjectsOrder(contentContainer)
		{
			var nodeID			= document.querySelector('body').getAttribute('data-node-id');

			var formData = this.getObjectsOrderBy(contentContainer);
				formData.append('cms-xhrequest', 'cms-order-by-modules');
				formData.append('cms-order-by-node-id', nodeID);

			this.xhrCommand(formData, null, null);
		}


		commandEdit(buttonElement)
		{
		}

		commandDelete(buttonElement, instance)
		{
			var parentContainer = buttonElement.closest('.'+ instance.objectClassName); 
				parentContainer.remove();
		}

		commandUp(buttonElement)
		{
			var objectContainer 	= buttonElement.closest('.'+ this.objectClassName); 
			var objectSibling 		= objectContainer.previousSibling;

			if(objectSibling == null || objectSibling.classList == undefined || !objectSibling.classList.contains(this.objectClassName))
				return false;

			var contentContainer 	= objectContainer.closest('.'+ this.contentClassName);
			var oldObject 			= contentContainer.removeChild(objectContainer);

			contentContainer.insertBefore(oldObject, objectSibling);
			return contentContainer;
		}

		commandDown(buttonElement)
		{
			var objectContainer 	= buttonElement.closest('.'+ this.objectClassName); 
			var objectSibling 		= objectContainer.nextSibling;

			if(objectSibling == null || objectSibling.classList == undefined ||!objectSibling.classList.contains(this.objectClassName))
				return false;

			var objectSibling 		= objectSibling.nextSibling;

			if(objectSibling == null)
				return false;

			var contentContainer 	= objectContainer.closest('.'+ this.contentClassName);
			var oldObject 			= contentContainer.removeChild(objectContainer);

			contentContainer.insertBefore(oldObject, objectSibling);
			return contentContainer;
		}

		getFormData(buttonElement)
		{
			var	formData 		= new FormData();
			var parentContainer = buttonElement.closest('.'+ this.objectClassName); 
			var	fieldsList		= parentContainer.querySelectorAll('input:not(:disabled), textarea:not(:disabled), select:not(:disabled)');
				fieldsList.forEach(function(fieldElement) {
					// TODO: check if name attribute is valid (exists & contains)
					formData.append(fieldElement.getAttribute('name'), fieldElement.value);
					console.log(fieldElement.getAttribute('name') +' = '+ fieldElement.value);
				});
			return formData;
		}

		getObjectsOrderBy(contentContainer)
		{
			var	formData 		= new FormData();
			var objectsContainer	= contentContainer.querySelectorAll('.'+ this.objectClassName +' > input[name=cms-object-id]') ;

				objectsContainer.forEach(function(fieldElement) {

					console.log(fieldElement.value);
					formData.append('cms-order-by-modules[]', fieldElement.value);
				});

			return formData;
		}
	}