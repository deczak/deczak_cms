class	cmsModuleManager
{	
	constructor()
	{
	}
	
	init(contentContainerClassName, activeModules, parentInstances)
	{
		this.className 			= contentContainerClassName;
		this.activeModules		= activeModules;
		this.parentInstances	= parentInstances;
	}
	
	create()
	{
		if(Object.keys(this.activeModules).length == 0)
			return false;

		var targetElements = Array.prototype.slice.call(document.getElementsByClassName(this.className), 0);

		for(var nItem = 0; nItem < targetElements.length; nItem++)
		{

			
			this.insert(targetElements[nItem], 0);
			this.insert(targetElements[nItem], null);
		}		
	}
	
	insert(dstContainer, position)
	{		
		var oParent = document.createElement("div");
			oParent.classList.add('cms-edit-new-module-container');
		
		var	oButtonOpen = document.createElement("button");
			oButtonOpen.classList.add('cms-trigger-open-new-module-window');
			oButtonOpen.setAttribute('type','button');
			oButtonOpen.innerHTML = '&#xf067';
			oButtonOpen.onclick = this.onOpenModuleManager.bind(oButtonOpen);
		
		var	oTabsContainer = document.createElement("div");
			oTabsContainer.classList.add('cms-edit-new-module-items-container');
		
		var	oTabsList = document.createElement("ul");
			oTabsList.classList.add('cms-trigger-tab-selection');
				
		var	moduleGroups = this.getModuleGroups();
		
		for (var tab = 0; tab < moduleGroups.length; tab++)
		{
			var	oTabItem = document.createElement("li");
				oTabItem.setAttribute('data-page-dst', moduleGroups[tab])
				oTabItem.innerHTML = moduleGroups[tab];
				oTabItem.onclick = this.onTabSelection.bind(oTabItem);
			
			if(tab == 0)
				oTabItem.classList.add('active');
						
			oTabsList.appendChild(oTabItem);			
		}
				
		oTabsContainer.appendChild(oTabsList);
					
		for (var group = 0; group < moduleGroups.length; group++)
		{	
			var oTab = document.createElement("div");
				oTab.classList.add('cms-edit-new-module-items');
				oTab.classList.add('cms-tab-page');
				oTab.setAttribute('data-page-name', moduleGroups[group]);
			
			var	oModulesList = document.createElement("ul");
			
			for (var module = 0; module < Object.keys(this.activeModules).length; module++)
			{	
				if(this.activeModules[module].module_group !== moduleGroups[group])
					continue;

				var	oModuleItem = document.createElement("li");
					oModuleItem.classList.add('cms-trigger-add-module');
					oModuleItem.setAttribute('data-module-id', this.activeModules[module].module_id);
					oModuleItem.innerHTML = '<span>'+ this.activeModules[module].module_icon +'</span>'+ this.activeModules[module].module_name;
					oModuleItem.onclick = this.onInsertModule.bind(oModuleItem, this.parentInstances);
					oModuleItem.instance = this;
				
				oModulesList.appendChild(oModuleItem)
			}
			
			oTab.appendChild(oModulesList);

			oTabsContainer.appendChild(oTab);
		}

		oParent.appendChild(oButtonOpen);
		oParent.appendChild(oTabsContainer);

		var	childNodes = dstContainer.childNodes;
		if(position === 0 && childNodes.length > 0)
			dstContainer.insertBefore(oParent,childNodes[0]);
		else
			dstContainer.appendChild(oParent);
	}	

	onOpenModuleManager()
	{
		var	tabContainer		= this.parentNode.querySelector('.cms-edit-new-module-items-container');
		var tabContainerState 	= tabContainer.style.display;
		var	allTabContainers	= document.querySelectorAll('.cms-edit-new-module-items-container');

		var i;
		for(i = 0; i < allTabContainers.length; i++)
			allTabContainers[i].style.display = "none";

		if(tabContainerState !== "block")			
			tabContainer.style.display = "block";	
	}
	
	onInsertModule(parentInstances)
	{
		var moduleContainer	= this.closest('.cms-edit-new-module-container'); 
		var moduleID 		= this.getAttribute('data-module-id'); 
		var orderBy 		= cmstk.getNodeIndex(moduleContainer); 
		var nodeID			= document.querySelector('body').getAttribute('data-node-id');
		var contentContainer= this.closest('.cms-edit-content-container'); 
		var	contentId		= contentContainer.getAttribute('data-view');

		var	formData 		= new FormData();
			formData.append('cms-xhrequest','cms-insert-module');
			formData.append('cms-insert-module', moduleID);
			formData.append('cms-insert-after', orderBy);
			formData.append('cms-insert-node-id', nodeID);
			formData.append('cms-insert-content-id', contentId);
	
		var	requestTarget	= CMS.SERVER_URL_BACKEND + CMS.PAGE_PATH + CMS.MODULE_TARGET;

		var	that = this;
		
		var xhr = new XMLHttpRequest();
		xhr.open('POST', requestTarget, true);
		xhr.responseType = 'json';
		xhr.setRequestHeader("X-Requested-With","XMLHttpRequest");
		xhr.setRequestHeader("X-Requested-XHR-Action", 'cms-insert-module');
		xhr.onload = function()
		{
			switch(xhr.status)
			{
				case 200:	// OK					
							var jsonObject = xhr.response; 
							
							if(typeof jsonObject.data.redirect != "undefined")
							{
								setTimeout(function(){ window.location.replace(jsonObject.data.redirect); }, 2000);
							}		
												
							if(typeof resultBox != 'undefined')
							{
								resultBox.innerHTML = jsonObject.msg;
								resultBox.setAttribute('data-error',jsonObject.state);
							}	

							if(jsonObject.state == 0 || jsonObject.error == 0)
							{

								// some rights are multiple times in activeModules
								var	userRights = '';
								for (var module = 0; module < Object.keys(that.instance.activeModules).length; module++)
								{	
									if(parseInt(moduleID) === that.instance.activeModules[module].module_id)
									{
										userRights = that.instance.activeModules[module].user_rights.join(',');
										break;
									}
								}




								var newObject;								
									newObject = document.createElement( 'div' );
									newObject.classList.add('cms-content-object');
									newObject.setAttribute('data-rights',userRights);
									newObject.innerHTML = jsonObject.data.html;

								var contentObjects = contentContainer.querySelectorAll('.cms-content-object');

								var targetElement = cmstk.getChildNodeByIndex(orderBy, contentObjects);

								if(targetElement != null)
								{	//	Add the new element before
									targetElement.insertAdjacentElement('beforebegin', newObject);
								}
								else
								{	//	Add the new elment after last
									var targetElement = cmstk.getChildNodeByIndex((orderBy - 2), contentObjects);

									if(targetElement !== undefined)
										targetElement.insertAdjacentElement('afterend', newObject);
									else
										contentContainer.insertBefore(newObject, contentContainer.childNodes[1]);
								}

								parentInstances.pEditorText.createOnDestNode(newObject);
								parentInstances.pEditorHeadline.createOnDestNode(newObject);
								parentInstances.pEditorCode.createOnDestNode(newObject);
								parentInstances.pObjectTools.create();
								parentInstances.pObjectTools.submitObjectsOrder(contentContainer);
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
		};
		xhr.send(formData);		
	}

	onTabSelection()
	{
		var	tabControl 		= this.parentNode;
		var	tabChilds		= tabControl.querySelectorAll('li');
		var pageTarget 		= this.getAttribute('data-page-dst'); 
		var parentContainer = tabControl.parentNode;
		var tabPages 		= parentContainer.querySelectorAll('.cms-tab-page');

		var i;
		for(i = 0; i < tabChilds.length; i++)
		{
			if(tabChilds[i].getAttribute('data-page-dst') == pageTarget)
				tabChilds[i].classList.add('active');
			else
				tabChilds[i].classList.remove('active');
		} 

		for(i = 0; i < tabPages.length; i++)
		{
			if(tabPages[i].getAttribute('data-page-name') == pageTarget)
				tabPages[i].style.display = "block";
			else
				tabPages[i].style.display = "none";
		} 		
	}

	getModuleGroups()
	{
		var	moduleGroups = [];
		for (var i = 0; i < Object.keys(this.activeModules).length; i++)
		{
			if(!this.activeModules[i].is_frontend)
				continue;
			if(moduleGroups.indexOf(this.activeModules[i].module_group) != -1)
				continue;

			moduleGroups.push(this.activeModules[i].module_group);
		}
		return moduleGroups;
	}	
}
	