class cmsMECP_SimpleGallery
{
	constructor()
	{
		let srcInstance = this;

		window.addEventListener('click', function(event) { srcInstance.onEventClick(event, srcInstance); });
		window.addEventListener('change', function(event) { srcInstance.onEventChange(event, srcInstance); });

		window.addEventListener('event-edit-module-gallery-add-folder', function(event) { srcInstance.onEventAddFolder(event, srcInstance); });

		window.addEventListener('event-edit-module-gallery-add-image-selected', function(event) { srcInstance.onEventAddImageSuccess(event, srcInstance); });
		window.addEventListener('event-edit-module-gallery-add-folder-selected', function(event) { srcInstance.onEventAddFolderSelectSuccess(event, srcInstance); });


		this.manageItemsTemplate  = '';
		this.manageItemsTemplate += '<td style="padding-top:0px; padding-bottom:0px;"><input type="hidden" class="simple-gallery-manage-item-path" name="[INPUT_NAME_ITEM_PATH]" value="[ITEM_PATH]">[ITEM_NAME]</td>';
/*
		this.manageItemsTemplate += '<td style="text-align:center;padding:0px;"> ';
		this.manageItemsTemplate += '<div class="select-wrapper">';
		this.manageItemsTemplate += '<select name="[INPUT_NAME_LISTING_HIDDEN]" style="width:100%; border:0px; box-shadow:none;">';
		this.manageItemsTemplate += '<option value="0" [INPUT_NAME_LISTING_HIDDEN_0]>No</option>';
		this.manageItemsTemplate += '<option value="1" [INPUT_NAME_LISTING_HIDDEN_1]>Yes</option>';
		this.manageItemsTemplate += '</select>';
		this.manageItemsTemplate += '</div>';
		this.manageItemsTemplate += '</td>';
*/
		this.manageItemsTemplate += '<td style="padding-top:0px; padding-bottom:0px;"><input type="hidden" class="simple-gallery-manage-item-listing-type" name="[INPUT_NAME_LISTING_TYPE]" value="[LISTING_TYPE]">[LISTING_TYPE_TEXT]</td>';
		this.manageItemsTemplate += '<td style="text-align:center;padding:0px;">';
		this.manageItemsTemplate += '<button class="ui button trigger-manage-gallery-remove-item" style="width:auto;">';
		this.manageItemsTemplate += '<i class="fas fa-trash-alt"></i>';
		this.manageItemsTemplate += '</button>';
		this.manageItemsTemplate += '</td>';
	}

	onEventClick(event, srcInstance)
	{
		if(typeof event.target === 'undefined')
			return true;

		let mecpContainer = event.target.closest('.simple-gallery-control');
		if(mecpContainer === null)
			return true;

		let mecpDtaClass = mecpContainer.getAttribute('data-target-list');
		let eventTagName = event.target.tagName;

		switch(eventTagName)
		{
			case 'BUTTON':
				
				if(event.target.classList.contains('trigger-manage-gallery'))
				{
					let tableNode = document.querySelector('.simple-gallery-manage-list.'+ mecpDtaClass);
					let attrHidden = tableNode.getAttribute('hidden');

					if(attrHidden === null)
					{
						tableNode.setAttribute('hidden', 'hidden');
						event.target.style.background = '';
					}
					else
					{
						tableNode.removeAttribute('hidden');
						event.target.style.background = 'rgba(0,0,0,0.1)';
					}

					return true;
				}
				
				if(event.target.classList.contains('trigger-manage-gallery-add-image'))
				{
					srcInstance.onEventAddImage(mecpDtaClass);
					return true;
				}	
				
				if(event.target.classList.contains('trigger-manage-gallery-add-folder'))
				{
					srcInstance.onEventAddFolder(mecpDtaClass);
					return true;
				}

				if(event.target.classList.contains('trigger-manage-gallery-remove-item'))
				{
					srcInstance.onEventRemoveItem(event.target.closest('tr'), mecpDtaClass);
					return true;
				}
				break;
		}	
	}

	onEventChange(event, srcInstance)
	{
		if(typeof event.target === 'undefined')
			return true;

		let mecpContainer = event.target.closest('.simple-gallery-control');
		if(mecpContainer === null)
			return true;

		let mecpDtaClass = mecpContainer.getAttribute('data-target-list');
		let eventTagName = event.target.tagName;


		switch(eventTagName)
		{
			case 'SELECT':

				if(event.target.classList.contains('trigger-view-mode'))
				{
				//	let templateId = event.target.getAttribute('data-template-id');
				//	if(templateId !== null)
						this.triggerContentUpdate(mecpContainer);

					//	srcInstance.setViewMode(templateId, mecpDtaClass, mecpContainer);

					return true;
				}
				
				if(event.target.classList.contains('trigger-view-divider'))
				{
					this.triggerContentUpdate(mecpContainer);
					return true;
				}
				break;
		}	
	}
	/*
	setViewMode(viewMode, mecpDtaClass, mecpContainer)
	{
		mecpContainer.querySelector('input[name="simple-gallery-template"]').value = viewMode;
		this.triggerContentUpdate(mecpContainer);
	}
	*/

	onEventAddImage(mecpDtaClass)
	{
		let mediathek = new cmsModalMediathek;
			mediathek.setEventNameOnSelected('event-edit-module-gallery-add-image-selected');
			mediathek.open(cmsMediathek.VIEWMODE_LIST, cmsMediathek.WORKMODE_SELECT, {srcInstance:this, mecpDtaClass:mecpDtaClass});
	}

	onEventAddImageSuccess(event)
	{
		if(event.detail.file === null || event.detail.file.path.length === 0)
			return;

		let snilNode = document.querySelector('.simple-gallery-items-list.'+ event.detail.sourceNode.mecpDtaClass);
		let mecpNode = document.querySelector('.simple-gallery-manage-list.'+ event.detail.sourceNode.mecpDtaClass);

		event.detail.sourceNode.srcInstance.addNavigationItem(
			snilNode,
			mecpNode,
			event.detail.file.path,
			event.detail.file.name,
			0,
			'image'
		);
	}


	onEventAddFolder(mecpDtaClass)
	{
		cmsMediathek.queryDirectories(this.onEventAddFolderSelect, {srcInstance:this, mecpDtaClass:mecpDtaClass});

	}

	onEventAddFolderSelect(response, srcInstance)
	{
		if(response.state !== 0)
		{
			console.log('cmsMediathek.queryDirectories returned invalid value');
			return false;
		}

		let	modalPath = new cmsModalPath;
			modalPath.setEventNameOnSelected('event-edit-module-gallery-add-folder-selected');
			modalPath.open('Gallery Mediathek Directory', response.data, srcInstance, 'fas fa-folder');
	}

	onEventAddFolderSelectSuccess(event)
	{
		let snilNode = document.querySelector('.simple-gallery-items-list.'+ event.detail.sourceNode.mecpDtaClass);
		let mecpNode = document.querySelector('.simple-gallery-manage-list.'+ event.detail.sourceNode.mecpDtaClass);

		event.detail.sourceNode.srcInstance.addNavigationItem(
			snilNode,
			mecpNode,
			event.detail.select.path,
			event.detail.select.name,
			0,
			'folder'
		);



	}

	onEventRemoveItem(itemRowNode, mecpDtaClass)
	{
		let snilNode = document.querySelector('.simple-gallery-items-list.'+ mecpDtaClass);
		let mecpNode = document.querySelector('.simple-gallery-manage-list.'+ mecpDtaClass);

		let itemPath = itemRowNode.querySelector('input.simple-gallery-manage-item-path').value;
		let listingType = itemRowNode.querySelector('input.simple-gallery-manage-item-listing-type').value;

		this.removeNavigationItem(
			snilNode,
			mecpNode,
			itemPath,
			listingType
		);
	}

	removeNavigationItem(snilNode, mecpNode, itemPath, listingType)
	{
		if(typeof snilNode.nodeList === 'undefined')
			return false;

		for(let i = 0; i < snilNode.nodeList.length; i++)
		{
			if(snilNode.nodeList[i].itemPath === itemPath && snilNode.nodeList[i].listingType === listingType)
			{
				snilNode.nodeList.splice(i, 1);
				break;
			}
		}

		this.updateNavigationView(snilNode, mecpNode);
	}

	addNavigationItem(snilNode, mecpNode, itemPath, itemName, listingHidden, listingType, initialCall = false)
	{
		if(typeof snilNode.nodeList === 'undefined')
		{
			snilNode.nodeList = [];	
		}
		else
		{
			if(this.existNavigationItem(snilNode, itemPath, listingType))
				return;		
		}

		snilNode.nodeList.push({
			itemPath:itemPath,
			itemName:itemName,
			listingHidden:listingHidden,
			listingType:listingType
		});

		this.updateNavigationView(snilNode, mecpNode, initialCall);
	}

	existNavigationItem(snilNode, itemPath, listingType)
	{
		if(typeof snilNode.nodeList === 'undefined')
			return false;

		for(let i = 0; i < snilNode.nodeList.length; i++)
		{
			if(snilNode.nodeList[i].itemPath === itemPath && snilNode.nodeList[i].listingType === listingType)
				return true;
		}

		return false;
	}

	updateNavigationView(snilNode, mecpNode, initialCall = false)
	{
		let tableBodyNode = mecpNode.querySelector('table > tbody');
			tableBodyNode.innerHTML = '';

		if(typeof snilNode.nodeList === 'undefined')
			return false;

		for(let i = 0; i < snilNode.nodeList.length; i++)
		{
			let newItem = this.manageItemsTemplate;
				newItem = newItem.replace(/\[ITEM_NAME\]/g, snilNode.nodeList[i].itemName);
				newItem = newItem.replace(/\[LISTING_TYPE\]/g, snilNode.nodeList[i].listingType);
				newItem = newItem.replace(/\[ITEM_PATH\]/g, snilNode.nodeList[i].itemPath);

				newItem = newItem.replace(/\[INPUT_NAME_ITEM_PATH\]/g, 'simple-gallery-item['+ i +'][item-path]');
				newItem = newItem.replace(/\[INPUT_NAME_LISTING_HIDDEN\]/g, 'simple-gallery-item['+ i +'][listing-hidden]');
				newItem = newItem.replace(/\[INPUT_NAME_LISTING_TYPE\]/g, 'simple-gallery-item['+ i +'][listing-type]');

			switch(snilNode.nodeList[i].listingType)
			{
				case 'image':
					newItem = newItem.replace(/\[LISTING_TYPE_TEXT\]/g, 'Image');
					break;
				case 'folder':
					newItem = newItem.replace(/\[LISTING_TYPE_TEXT\]/g, 'Folder');
					break
			}
			
			switch(snilNode.nodeList[i].listingHidden)
			{
				case '0':
					newItem = newItem.replace(/\[INPUT_NAME_LISTING_HIDDEN_0\]/g, 'selected');
					newItem = newItem.replace(/\[INPUT_NAME_LISTING_HIDDEN_1\]/g, '');
					break;
				case '1':
					newItem = newItem.replace(/\[INPUT_NAME_LISTING_HIDDEN_0\]/g, '');
					newItem = newItem.replace(/\[INPUT_NAME_LISTING_HIDDEN_1\]/g, 'selected');
					break
			}

			let rowNode = document.createElement('tr');
				rowNode.innerHTML = newItem;

			tableBodyNode.append(rowNode);
		}	

		if(!initialCall)
			this.triggerContentUpdate(mecpNode);
	}

	triggerContentUpdate(mecpNode)
	{
		mecpNode.closest('.cms-content-object').querySelector('.cms-trigger-object-tool[data-cmd="edit"]').dispatchEvent(new Event('click'));
	}
}

document.MECP_SimpleGallery = new cmsMECP_SimpleGallery;
