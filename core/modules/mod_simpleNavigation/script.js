
class cmsMECP_SimpleNavigation
{
	constructor()
	{
		let srcInstance = this;

		window.addEventListener('click', function(event) { srcInstance.onEventClick(event, srcInstance); });

		window.addEventListener('event-edit-module-navigation-add-page', function(event) { srcInstance.onEventAddPageSuccess(event, srcInstance); });
		window.addEventListener('event-edit-module-navigation-add-subpagesby', function(event) { srcInstance.onEventAddSubpagesBySuccess(event, srcInstance); });

		this.manageItemsTemplate  = '';
		this.manageItemsTemplate += '<td style="padding-top:0px; padding-bottom:0px;"><input type="hidden" class="simple-navigation-manage-item-node-id" name="[INPUT_NAME_NODE_ID]" value="[NODE_ID]">[PAGE_NAME]</td>';
		this.manageItemsTemplate += '<td style="text-align:center;padding:0px;"> ';
		this.manageItemsTemplate += '<div class="select-wrapper">';
		this.manageItemsTemplate += '<select name="[INPUT_NAME_LISTING_HIDDEN]" style="width:100%; border:0px; box-shadow:none;">';
		this.manageItemsTemplate += '<option value="0" [INPUT_NAME_LISTING_HIDDEN_0]>No</option>';
		this.manageItemsTemplate += '<option value="1" [INPUT_NAME_LISTING_HIDDEN_1]>Yes</option>';
		this.manageItemsTemplate += '</select>';
		this.manageItemsTemplate += '</div>';
		this.manageItemsTemplate += '</td>';
		this.manageItemsTemplate += '<td style="padding-top:0px; padding-bottom:0px;"><input type="hidden" class="simple-navigation-manage-item-listing-type" name="[INPUT_NAME_LISTING_TYPE]" value="[LISTING_TYPE]">[LISTING_TYPE_TEXT]</td>';
		this.manageItemsTemplate += '<td style="text-align:center;padding:0px;">';
		this.manageItemsTemplate += '<button class="ui button trigger-manage-pages-remove-item" style="width:auto;">';
		this.manageItemsTemplate += '<i class="fas fa-trash-alt"></i>';
		this.manageItemsTemplate += '</button>';
		this.manageItemsTemplate += '</td>';
	}

	onEventClick(event, srcInstance)
	{
		if(typeof event.target === 'undefined')
			return true;

		let mecpContainer = event.target.closest('.simple-navigation-control');
		if(mecpContainer === null)
			return true;

		let mecpDtaClass = mecpContainer.getAttribute('data-target-list');
		let eventTagName = event.target.tagName;

		switch(eventTagName)
		{
			case 'BUTTON':

				if(event.target.classList.contains('trigger-view-mode'))
				{
					let templateId = event.target.getAttribute('data-template-id');
					if(templateId !== null)
						srcInstance.setViewMode(templateId, mecpDtaClass, mecpContainer);

					return true;
				}
				
				if(event.target.classList.contains('trigger-manage-pages'))
				{
					let tableNode = document.querySelector('.simple-navigation-manage-list.'+ mecpDtaClass);
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
				
				if(event.target.classList.contains('trigger-manage-pages-add-page'))
				{
					srcInstance.onEventAddPage(mecpDtaClass);
					return true;
				}	
				
				if(event.target.classList.contains('trigger-manage-pages-add-subpagesby'))
				{
					srcInstance.onEventAddSubpagesBy(mecpDtaClass);
					return true;
				}

				if(event.target.classList.contains('trigger-manage-pages-remove-item'))
				{
					srcInstance.onEventRemoveItem(event.target.closest('tr'), mecpDtaClass);
					return true;
				}
				break;
		}	
	}

	setViewMode(viewMode, mecpDtaClass, mecpContainer)
	{
		mecpContainer.querySelector('input[name="simple-navigation-template"]').value = viewMode;
		this.triggerContentUpdate(mecpContainer);
	}

	onEventAddPage(mecpDtaClass)
	{
		let	modalNode = new cmsModalNode;
			modalNode.setEventNameOnSelected('event-edit-module-navigation-add-page');
			modalNode.open(
				'Select page as navigation item', 
				{srcInstance:this, mecpDtaClass:mecpDtaClass}, 
				'fas fa-file',
				CMS.LANGUAGES
			);
	}

	onEventAddPageSuccess(event)
	{
		let snilNode = document.querySelector('.simple-navigation-items-list.'+ event.detail.sourceNode.mecpDtaClass);
		let mecpNode = document.querySelector('.simple-navigation-manage-list.'+ event.detail.sourceNode.mecpDtaClass);

		event.detail.sourceNode.srcInstance.addNavigationItem(
			snilNode,
			mecpNode,
			event.detail.select.node_id,
			event.detail.select.name,
			0,
			'page'
		);
	}

	onEventAddSubpagesBy(mecpDtaClass)
	{

		let	modalNode = new cmsModalNode;
			modalNode.setEventNameOnSelected('event-edit-module-navigation-add-subpagesby');
			modalNode.open(
				'Select page for sub pages as navigation item', 
				{srcInstance:this, mecpDtaClass:mecpDtaClass}, 
				'fas fa-file',
				CMS.LANGUAGES
			);
	}

	onEventAddSubpagesBySuccess(event)
	{
		let snilNode = document.querySelector('.simple-navigation-items-list.'+ event.detail.sourceNode.mecpDtaClass);
		let mecpNode = document.querySelector('.simple-navigation-manage-list.'+ event.detail.sourceNode.mecpDtaClass);

		event.detail.sourceNode.srcInstance.addNavigationItem(
			snilNode,
			mecpNode,
			event.detail.select.node_id,
			event.detail.select.name,
			0,
			'subpages'
		);
	}

	onEventRemoveItem(itemRowNode, mecpDtaClass)
	{
		let snilNode = document.querySelector('.simple-navigation-items-list.'+ mecpDtaClass);
		let mecpNode = document.querySelector('.simple-navigation-manage-list.'+ mecpDtaClass);

		let nodeId = itemRowNode.querySelector('input.simple-navigation-manage-item-node-id').value;
		let listingType = itemRowNode.querySelector('input.simple-navigation-manage-item-listing-type').value;

		this.removeNavigationItem(
			snilNode,
			mecpNode,
			nodeId,
			listingType
		);
	}

	removeNavigationItem(snilNode, mecpNode, nodeId, listingType)
	{
		if(typeof snilNode.nodeList === 'undefined')
			return false;

		for(let i = 0; i < snilNode.nodeList.length; i++)
		{
			if(snilNode.nodeList[i].nodeId === nodeId && snilNode.nodeList[i].listingType === listingType)
			{
				snilNode.nodeList.splice(i, 1);
				break;
			}
		}

		this.updateNavigationView(snilNode, mecpNode);
	}

	addNavigationItem(snilNode, mecpNode, nodeId, pageName, listingHidden, listingType, initialCall = false)
	{
		if(typeof snilNode.nodeList === 'undefined')
		{
			snilNode.nodeList = [];	
		}
		else
		{
			if(this.existNavigationItem(snilNode, nodeId, listingType))
				return;		
		}

		snilNode.nodeList.push({
			nodeId:nodeId,
			pageName:pageName,
			listingHidden:listingHidden,
			listingType:listingType
		});

		this.updateNavigationView(snilNode, mecpNode, initialCall);
	}

	existNavigationItem(snilNode, nodeId, listingType)
	{
		if(typeof snilNode.nodeList === 'undefined')
			return false;

		for(let i = 0; i < snilNode.nodeList.length; i++)
		{
			if(snilNode.nodeList[i].nodeId === nodeId && snilNode.nodeList[i].listingType === listingType)
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
				newItem = newItem.replace(/\[PAGE_NAME\]/g, snilNode.nodeList[i].pageName);
				newItem = newItem.replace(/\[LISTING_TYPE\]/g, snilNode.nodeList[i].listingType);
				newItem = newItem.replace(/\[NODE_ID\]/g, snilNode.nodeList[i].nodeId);

				newItem = newItem.replace(/\[INPUT_NAME_NODE_ID\]/g, 'simple-navigation-item['+ i +'][node-id]');
				newItem = newItem.replace(/\[INPUT_NAME_LISTING_HIDDEN\]/g, 'simple-navigation-item['+ i +'][listing-hidden]');
				newItem = newItem.replace(/\[INPUT_NAME_LISTING_TYPE\]/g, 'simple-navigation-item['+ i +'][listing-type]');

			switch(snilNode.nodeList[i].listingType)
			{
				case 'page':
					newItem = newItem.replace(/\[LISTING_TYPE_TEXT\]/g, 'Page');
					break;
				case 'subpages':
					newItem = newItem.replace(/\[LISTING_TYPE_TEXT\]/g, 'Subpages by');
					break
			}
			console.log(snilNode.nodeList[i]);

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

document.MECP_SimpleNavigation = new cmsMECP_SimpleNavigation;
