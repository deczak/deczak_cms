class	cmsModalPath extends cmsModal
{
	constructor()
	{
		super();

		this.eventNameOnSelected = null;
	}

	open(modalTitle, pathList, sourceNode = null, defaultIcon = null)
	{
		let srcInstance = this;

		srcInstance.content = document.createElement('div');
		srcInstance.defaultIcon = defaultIcon;
		srcInstance.eventSourceNode = sourceNode;

		super.addButton(new cmsModalCtrlButton(cmsModal.BTN_LOCATION.BOTTOM_LEFT, 'Close', null, 'fas fa-times'));
		super.addButton(new cmsModalCtrlButton(cmsModal.BTN_LOCATION.BOTTOM_LEFT, 'Select', function(event) { srcInstance._onButtonSelect(event, srcInstance); }, 'fas fa-check'));
		super.setTitle(modalTitle)
		super.create(srcInstance.content, {maxWidth:'600px'});
		super.open();

		if(this.eventNameOnSelected !== null)
		{
			srcInstance.content.addEventListener(this.eventNameOnSelected, function(event) { srcInstance._onEventItemSelected(event, srcInstance); });
		}

		if(pathList === null || typeof pathList !== 'object')
		{
			srcInstance.content.innerHTML = '';
			let failedNode = document.createElement('p');
				failedNode.innerHTML = '<div style="display:flex; border:1px solid rgb(204, 0, 0); background-color:rgb(2204, 0, 0); color:white;padding:12px; margin:15px 0px;"><i class="fas fa-times-circle" style="font-size:1.3rem;padding-top:0px;min-width: 25px;"></i><div style="padding-left:5px; font-size:0.84em; font-weight:normal; ">pathList is not a valid list</div></div>';
			srcInstance.content.append(failedNode);
			return;
		}

		srcInstance.pathList = pathList;
		srcInstance.parentPath = null;
		srcInstance.pathString = null;
	
		srcInstance._generatePathTable();

		srcInstance.content.addEventListener('click', function(event) { srcInstance._onClick(event, srcInstance); });
		srcInstance.content.addEventListener('dblclick', function(event) { srcInstance._onClick(event, srcInstance); });


		srcInstance.content.style.overflowY = 'scroll';
		srcInstance.content.style.height = window.innerHeight - (window.innerHeight * 0.2)+'px';
		srcInstance.content.style.maxHeight = '500px';
	}

	_onButtonSelect(event, srcInstance)
	{
		let selectedNode = srcInstance.content.querySelector('.cms-modal-path-select-list tr.path-selected');

		if(selectedNode === null)
			return;

		event.target.dispatchEvent(new CustomEvent(srcInstance.eventNameOnSelected, { detail: { select: selectedNode.pathInfo, sourceNode: srcInstance.eventSourceNode }, bubbles: true  }));
		event.target.dispatchEvent(new CustomEvent('cms-modal-close', { detail: null, bubbles: true }));
	}

	_generatePathTable()
	{
		let srcInstance = this;
			srcInstance.content.innerHTML = '';

		let controlPanelLeftNode = document.createElement('div');
			controlPanelLeftNode.classList.add('left');

		//

		let pathNode = document.createElement('div');
			pathNode.style.fontSize = '0.8em';
			pathNode.style.paddingLeft = '7px';
			pathNode.classList.add('modal-select-current-path-name');

		if(srcInstance.pathString !== null)
		{
			pathNode.innerHTML = '/'+ srcInstance.pathString;
		}
		else
		{
			pathNode.innerHTML = '/';
		}

		// Button Folder Up

		let buttonFolderUpNode = document.createElement('button');
			buttonFolderUpNode.classList.add('ui', 'button', 'icon');
			buttonFolderUpNode.type = 'button';

		if(srcInstance.parentPath !== null)
		{
			buttonFolderUpNode.innerHTML = '<i class="fas fa-chevron-up"></i>';
			buttonFolderUpNode.setAttribute('data-event-click', 'item-ctr-dir-up');
		}
		else
		{
			buttonFolderUpNode.innerHTML = '<i class="fas"></i>';
			buttonFolderUpNode.disabled = true;
		}

		controlPanelLeftNode.append(buttonFolderUpNode, pathNode);

		//

		let controlPanelNode = document.createElement('div');
			controlPanelNode.classList.add('controls');
			controlPanelNode.append(controlPanelLeftNode);
			controlPanelNode.style.background = 'rgba(194, 214, 214, 0.4)';

		srcInstance.content.append(controlPanelNode);

		let tableBodyNode = document.createElement('tbody');

		for(let i in srcInstance.pathList)
		{
			if(typeof srcInstance.pathList[i] === 'function')
				continue;

			let itemRowNode = document.createElement('tr');
				itemRowNode.setAttribute('data-path', srcInstance.pathList[i].path);
				itemRowNode.setAttribute('data-event-click', 'path-select');

				srcInstance.pathList[i].parent = JSON.parse(JSON.stringify(srcInstance.parentPath));

				itemRowNode.pathInfo = JSON.parse(JSON.stringify(srcInstance.pathList[i]));

			let icon = (typeof srcInstance.pathList[i].icon !== 'undefined' ? srcInstance.pathList[i].name : null)
				icon = ((icon === null && srcInstance.defaultIcon !== null) ? srcInstance.defaultIcon : '');


			let itemRowHTML = '';
				itemRowHTML += '<td class="fileicon"><i class="'+ icon +'"></i></td>';
				itemRowHTML += '<td class="pathname">'+ srcInstance.pathList[i].name +'</td>';
		
				itemRowNode.innerHTML = itemRowHTML;

				tableBodyNode.append(itemRowNode);
				
		}

		let tableNode = document.createElement('table');
			tableNode.classList.add('cms-modal-path-select-list');
			tableNode.append(tableBodyNode);

		srcInstance.content.append(tableNode);
	}

	_onClick(event, srcInstance)
	{
		let targetNode = event.target;

		if(event.target.tagName == 'TD')
		{
			// check for click on TD to change the targetNode, the info is in the TR
			targetNode = event.target.parentNode;
		}

		if(targetNode.hasAttribute('data-event-click'))
		{
			switch(targetNode.getAttribute('data-event-click'))
			{
				case 'path-select':

					switch(event.type)
					{
						case 'click': // Mark as selected
						

								let parentNode = targetNode.parentNode;

								let allItems = parentNode.querySelectorAll('tr');

								for(let i = 0; i < allItems.length; i++)
								{
									allItems[i].classList.remove('path-selected');
								}

								targetNode.classList.add('path-selected');


							break;

						case 'dblclick': // Open this if it contains childs 
				
							if(Object.keys(targetNode.pathInfo.childs).length !== 0)
							{
								srcInstance.parentPath =  JSON.parse(JSON.stringify(srcInstance.pathList));
								srcInstance.pathList = targetNode.pathInfo.childs;

								srcInstance.pathString = targetNode.pathInfo.path;

								srcInstance._generatePathTable();
							}
					}

					break;

				case 'item-ctr-dir-up':

					for(let i in srcInstance.pathList)
					{
						if(typeof srcInstance.pathList[i] === 'function')
							continue;

						srcInstance.pathList   = srcInstance.pathList[i].parent;

						break;
					}

					for(let i in srcInstance.pathList)
					{
						if(typeof srcInstance.pathList[i] === 'function')
							continue;

						if(srcInstance.pathList.level !== '1')
						{
							srcInstance.parentPath = srcInstance.pathList[i].parent;

							let cp = srcInstance.pathString;
								cp = cp.split('/');
								cp.pop();
							srcInstance.pathString = cp.join('/');
						}
						else
						{
							srcInstance.parentPath = null;
						}

						break;
					}

					srcInstance._generatePathTable();

					return true;
					break;
			}
		}
	}

	setEventNameOnSelected(eventName)
	{
		this.eventNameOnSelected = eventName;
	}

}

