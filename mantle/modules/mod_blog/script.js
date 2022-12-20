
class cmsMECP_Blog
{
	constructor()
	{
		let srcInstance = this;

		window.addEventListener('click', function(event) { srcInstance.onEventClick(event, srcInstance); });


		this.manageItemsTemplate  = '';
		this.manageItemsTemplate += '<td style="padding-top:0px; padding-bottom:0px;"><input type="hidden" class="blog-manage-item-node-id" name="[INPUT_NAME_NODE_ID]" value="[NODE_ID]">[PAGE_NAME]</td>';
		this.manageItemsTemplate += '<td style="text-align:center;padding:0px;"> ';
		this.manageItemsTemplate += '<div class="select-wrapper">';
		this.manageItemsTemplate += '<select name="[INPUT_NAME_LISTING_HIDDEN]" style="width:100%; border:0px; box-shadow:none;">';
		this.manageItemsTemplate += '<option value="0" [INPUT_NAME_LISTING_HIDDEN_0]>No</option>';
		this.manageItemsTemplate += '<option value="1" [INPUT_NAME_LISTING_HIDDEN_1]>Yes</option>';
		this.manageItemsTemplate += '</select>';
		this.manageItemsTemplate += '</div>';
		this.manageItemsTemplate += '</td>';
		this.manageItemsTemplate += '<td style="padding-top:0px; padding-bottom:0px;"><input type="hidden" class="blog-manage-item-listing-type" name="[INPUT_NAME_LISTING_TYPE]" value="[LISTING_TYPE]">[LISTING_TYPE_TEXT]</td>';
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

		let mecpContainer = event.target.closest('.blog-control');
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
					let tableNode = document.querySelector('.blog-manage-list.'+ mecpDtaClass);
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
				
				

				break;
		}	
	}

	setViewMode(viewMode, mecpDtaClass, mecpContainer)
	{
		mecpContainer.querySelector('input[name="blog-template"]').value = viewMode;
		this.triggerContentUpdate(mecpContainer);
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

				newItem = newItem.replace(/\[INPUT_NAME_NODE_ID\]/g, 'blog-item['+ i +'][node-id]');
				newItem = newItem.replace(/\[INPUT_NAME_LISTING_HIDDEN\]/g, 'blog-item['+ i +'][listing-hidden]');
				newItem = newItem.replace(/\[INPUT_NAME_LISTING_TYPE\]/g, 'blog-item['+ i +'][listing-type]');

			switch(snilNode.nodeList[i].listingType)
			{
				case 'page':
					newItem = newItem.replace(/\[LISTING_TYPE_TEXT\]/g, 'Page');
					break;
				case 'subpages':
					newItem = newItem.replace(/\[LISTING_TYPE_TEXT\]/g, 'Subpages by');
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

document.MECP_Blog = new cmsMECP_Blog;
