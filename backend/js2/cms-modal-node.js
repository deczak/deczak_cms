class	cmsModalNode extends cmsModal
{
	constructor()
	{
		super();

		this.eventNameOnSelected = null;
	}

	open(modalTitle, sourceNode = null, defaultIcon = null, langList = [], initialLang = null)
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

		srcInstance.parentPath = null;
		srcInstance.pathString = null;
		srcInstance.langList   = langList;
	

		if(initialLang == null)
			initialLang = srcInstance.getDefaultLanguage();

		srcInstance.queryNodeList(initialLang);

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
		{
			event.target.dispatchEvent(new CustomEvent(srcInstance.eventNameOnSelected, { detail: { select: srcInstance.pathInfo, sourceNode: srcInstance.eventSourceNode }, bubbles: true  }));
		}
		else
		{
			event.target.dispatchEvent(new CustomEvent(srcInstance.eventNameOnSelected, { detail: { select: selectedNode.pathInfo, sourceNode: srcInstance.eventSourceNode }, bubbles: true  }));
		}

		event.target.dispatchEvent(new CustomEvent('cms-modal-close', { detail: null, bubbles: true }));
	}

	_generateNodeTable()
	{
		let srcInstance = this;
			srcInstance.content.innerHTML = '';

		// Language panel

		let langNodeHTML = '';

		for(let lang in this.langList)
		{
			langNodeHTML += '<button  class="button-select-language" style="display:inline-block; margin: 0px 5px 9px 0px; padding:3px; width:30px;" data-lang="'+ lang +'">'+ lang.toUpperCase() +'</button>';
		}

		let langNode = document.createElement('div');
			langNode.style.display = 'flex';
			langNode.style.justifyContent = 'flex-start';
			langNode.style.alignItems = 'center';
			langNode.style.fontSize = '0.83em';
			langNode.innerHTML = langNodeHTML;

		srcInstance.content.append(langNode);

		let controlPanelLeftNode = document.createElement('div');
			controlPanelLeftNode.classList.add('left');

		//

		let pathNode = document.createElement('div');
			pathNode.style.fontSize = '0.8em';
			pathNode.style.paddingLeft = '7px';
			pathNode.classList.add('modal-select-current-path-name');

		if(srcInstance.pathString !== null)
		{
			pathNode.innerHTML = srcInstance.pathString;
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
			tableBodyNode.style.userSelect = 'none';

		for(let i in srcInstance.pathList)
		{
			if(typeof srcInstance.pathList[i] === 'function')
				continue;

			let itemRowNode = document.createElement('tr');
				itemRowNode.setAttribute('data-path', srcInstance.pathList[i].path);
				itemRowNode.setAttribute('data-event-click', 'path-select');

				srcInstance.pathList[i].parent = JSON.parse(JSON.stringify(srcInstance.parentPath));

				itemRowNode.pathInfo = JSON.parse(JSON.stringify(srcInstance.pathList[i]));

			let icon = ((typeof srcInstance.pathList[i].icon !== 'undefined' && srcInstance.pathList[i].icon !== null) ? srcInstance.pathList[i].icon : null);
	
				icon = ((icon === null && srcInstance.defaultIcon !== null) ? srcInstance.defaultIcon : icon);
			

			let itemRowHTML = '';
				itemRowHTML += '<td class="fileicon" data-event-click="item-ctr-dir-down"><i class="'+ icon +'"></i></td>';
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
			// except, the TD has his own event-click target

			if(!targetNode.hasAttribute('data-event-click'))
				targetNode = event.target.parentNode;
		}

		if(targetNode.hasAttribute('data-event-click'))
		{
			switch(targetNode.getAttribute('data-event-click'))
			{
				case 'item-ctr-dir-down': // this is on a TD

						targetNode = event.target.parentNode;

						if(Object.keys(targetNode.pathInfo.childs).length !== 0)
						{
							event.stopPropagation();
							srcInstance.parentPath  = JSON.parse(JSON.stringify(srcInstance.pathList));
							srcInstance.pathList    = targetNode.pathInfo.childs;
							srcInstance.pathString  = targetNode.pathInfo.path;
							srcInstance.pathInfo 	= targetNode.pathInfo;

							srcInstance._generateNodeTable();
						}
				
					break;

				case 'path-select':

					switch(event.type)
					{
						case 'click': // Mark as selected

								let targetHasClickedBefore = targetNode.classList.contains('path-selected');
						
								let parentNode = targetNode.parentNode;

								let allItems = parentNode.querySelectorAll('tr');

								for(let i = 0; i < allItems.length; i++)
								{
									allItems[i].classList.remove('path-selected');
								}

								if(!targetHasClickedBefore)
								{
									targetNode.classList.add('path-selected');
								}


							break;

						case 'dblclick': // Open this if it contains childs 
				
							if(Object.keys(targetNode.pathInfo.childs).length !== 0)
							{
								srcInstance.parentPath  = JSON.parse(JSON.stringify(srcInstance.pathList));
								srcInstance.pathList    = targetNode.pathInfo.childs;
								srcInstance.pathString  = targetNode.pathInfo.path;
								srcInstance.pathInfo 	= targetNode.pathInfo;

								srcInstance._generateNodeTable();
							}
					}

					break;

				case 'item-ctr-dir-up':

					for(let i in srcInstance.pathList)
					{
						if(typeof srcInstance.pathList[i] === 'function')
							continue;

						srcInstance.pathList = srcInstance.pathList[i].parent;

						break;
					}

					for(let i in srcInstance.pathList)
					{
						if(typeof srcInstance.pathList[i] === 'function')
							continue;

						if(srcInstance.pathList.level !== '1')
						{
							srcInstance.parentPath = srcInstance.pathList[i].parent;

							let cp = srcInstance.pathString.replace(/^\/|\/$/g, '');
								cp = cp.split('/');
								cp.pop();

							if(cp.length)
								srcInstance.pathString = '/'+ cp.join('/') +'/';
							else
							{
								srcInstance.pathString = null;
								srcInstance.pathInfo = srcInstance.pathRoot
							}
						}
						else
						{
							srcInstance.parentPath = null;
						}

						break;
					}

					srcInstance._generateNodeTable();

					return true;
					break;

			}
		}

		switch(event.target.tagName ?? '')
		{
			case 'BUTTON':


								if(event.target.classList.contains('button-select-language'))
								{

									let lang = targetNode.getAttribute('data-lang');


									srcInstance.queryNodeList(lang);



								}



		}


	}

	setEventNameOnSelected(eventName)
	{
		this.eventNameOnSelected = eventName;
	}

	queryNodeList(language)
	{
		cmsNode.getNodeList(language, this.queryNodeListSuccess, this)
		this.selectedLanguage = language;
	}

	queryNodeListSuccess(response, srcInstance)
	{
		if(response.state !== 0)
		{
			console.log('page controller returned invalid value');
			return false;
		}
		let pathList = [];

		cmsNode.createNestedNodeStructure(response.data, pathList, 1, 2);
		srcInstance.pathList = pathList;
		srcInstance.pathString = null;

		let pathRoot = {
			level:   response.data[0].level,
			name:    response.data[0].page_name,
			path:    response.data[0].page_path,
			icon:    (response.data[0].offspring > 0 ? 'fas fa-folder' : null),
			node_id: response.data[0].node_id,
			page_id: response.data[0].page_id,
			lang:    response.data[0].page_language,
			childs:[]
		};

		srcInstance.pathInfo = pathRoot;
		srcInstance.pathRoot = { ...pathRoot };
		srcInstance._generateNodeTable();
	}

	getDefaultLanguage()
	{
		for(let lang in this.langList)
		{
			if(this.langList[lang].lang_default)
				return lang;
		}
		return 'en';
	}

}
