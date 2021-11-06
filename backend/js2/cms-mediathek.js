
class	cmsMediathek
{
	static VIEWMODE_LIST    = 1;
	static VIEWMODE_SQUARES = 2;

	static WORKMODE_SELECT  = 1;
	static WORKMODE_EDIT    = 2;

	constructor(displayContainerNode)
	{
		this.displayContainerNode = displayContainerNode;
	}

	init(viewMode, workMode, rootPath = '')
	{
		this.viewMode 	  = viewMode;
		this.workMode 	  = workMode;
		this.rootPath	  = rootPath;
		this.activePath   = rootPath;


		if(typeof document.cmsMediathek === 'undefined')
		{
			document.cmsMediathek = {};
			document.cmsMediathek.viewMode = viewMode
		}
		


		this.requestItems();
	}

	setEventNameOnSelected(eventName, sourceNode = null)
	{
		this.eventNameOnSelected = eventName;
		this.eventSourceNode     = sourceNode;
	}

	requestItems()
	{
		let	formData  = new FormData;
			formData.append('path', this.activePath);

		let	xhRequest = new cmsXhr;
			xhRequest.request(CMS.SERVER_URL_BACKEND + 'mediathek/', formData, this.onXHRSuccessRequestItems, this, 'index');
	}

	onXHRSuccessRequestItems(response, srcInstance)
	{
		if(response.error === 1)
		{
			// TODO
			return;
		}

		let itemsNode = document.createElement('div');

		switch(document.cmsMediathek.viewMode)
		{
			case cmsMediathek.VIEWMODE_LIST:

				srcInstance._generateHTML_List(response.data, itemsNode);
				break;

			case cmsMediathek.VIEWMODE_SQUARES:

				srcInstance._generateHTML_Squares(response.data, itemsNode);
				break;
		}

		let mediathekNode = document.createElement('div');
			mediathekNode.classList.add('mediathek-main');

		let controlPanelLeftNode = document.createElement('div');
			controlPanelLeftNode.classList.add('left');

		let controlPanelRightNode = document.createElement('div');
			controlPanelRightNode.classList.add('right');


		switch(srcInstance.workMode)
		{
			case cmsMediathek.WORKMODE_EDIT:

				let buttonImportNode = document.createElement('button');
					buttonImportNode.classList.add('ui', 'button', 'icon', 'labeled', 'button-import');
					buttonImportNode.type = 'button';
					buttonImportNode.onclick = function(event) { srcInstance._onClickButtonImport(event, srcInstance) };
					buttonImportNode.innerHTML = '<span><i class="fas fa-file-import"></i></span> Mediathek Import';

				controlPanelRightNode.append(buttonImportNode);


				break;

			case cmsMediathek.WORKMODE_SELECT:

				break;


		}

		// Button Folder Up



				let buttonFolderUpNode = document.createElement('button');
					buttonFolderUpNode.classList.add('ui', 'button', 'icon');
					buttonFolderUpNode.type = 'button';

		if(srcInstance.activePath !== '')
		{
					buttonFolderUpNode.innerHTML = '<i class="fas fa-chevron-up"></i>';
					buttonFolderUpNode.setAttribute('data-event-click', 'item-ctr-dir-up');
		}
		else
		{
			buttonFolderUpNode.innerHTML = '<i class="fas"></i>';
			buttonFolderUpNode.disabled = true;
		}

		// Button View mode

				var textViewModeNode = document.createElement('label');
					textViewModeNode.append(document.createTextNode('View mode'));


				let buttonViewSquareNode = document.createElement('button');
					buttonViewSquareNode.classList.add('ui', 'button', 'icon', 'button-view-square');
					buttonViewSquareNode.onclick = function(event) { srcInstance._onClickButtonViewSquare(event, srcInstance) };
					buttonViewSquareNode.innerHTML = '<i class="fas fa-th"></i>';
					buttonViewSquareNode.type = 'button';


				let buttonViewListNode = document.createElement('button');
					buttonViewListNode.classList.add('ui', 'button', 'icon', 'button-view-list');
					buttonViewListNode.onclick = function(event) { srcInstance._onClickButtonViewList(event, srcInstance) };
					buttonViewListNode.innerHTML = '<i class="fas fa-bars"></i>';
					buttonViewListNode.type = 'button';


				controlPanelLeftNode.append(buttonFolderUpNode, textViewModeNode, buttonViewListNode, buttonViewSquareNode);


		//

		let controlPanelNode = document.createElement('div');
			controlPanelNode.classList.add('controls');
			controlPanelNode.append(controlPanelLeftNode, controlPanelRightNode);
			controlPanelNode.style.background = 'rgba(194, 214, 214, 0.4)';

		mediathekNode.append(controlPanelNode);
		mediathekNode.append(itemsNode);

		switch(srcInstance.workMode)
		{
			case cmsMediathek.WORKMODE_SELECT:

				mediathekNode.classList.add('mediathek-work-select');
				break;

			case cmsMediathek.WORKMODE_EDIT:

				mediathekNode.classList.add('mediathek-work-edit');
				break;
		}

		mediathekNode.addEventListener('click', function(event) { srcInstance._onClick(event, srcInstance); });

		srcInstance.displayContainerNode.innerHTML = '';
		srcInstance.displayContainerNode.append(mediathekNode);
	}

	_generateHTML_Squares(itemsList, contentNode)
	{
		let parentNode = document.createElement('div');
			parentNode.classList.add('mediathek', 'mediathek-square');

		console.log('_generateHTML_Squares');
		console.log(itemsList);

		for(let i in itemsList)
		{
			if(typeof itemsList[i] === 'function')
				continue;
				
			let squareNode = document.createElement('div');
				squareNode.classList.add('item-'+ (itemsList[i].extension == 'dir' ? 'dir' : 'file'));
				squareNode.setAttribute('data-event-click', 'item-'+ (itemsList[i].extension == 'dir' ? 'dir' : 'file'));
				squareNode.setAttribute('data-item-path', itemsList[i].path);
				squareNode.itemInfo = JSON.parse(JSON.stringify(itemsList[i]));
				
			switch(itemsList[i].extension)
			{
				case 'png':	
				case 'gif':	
				case 'webp':	
				case 'jpeg':	
				case 'jpg':	

					squareNode.style.backgroundImage = "url('"+ CMS.SERVER_URL +"mediathek/"+ itemsList[i].path +"?binary&size=thumb')";
					break;

				case 'dir':	

					squareNode.innerHTML = '<div><i class="fas fa-folder"></i><span class="name">'+ itemsList[i].name  +'</span></div>';
					break;
			}

			parentNode.append(squareNode);
		}

		contentNode.append(parentNode);
	}

	_generateHTML_List(itemsList, contentNode)
	{
		let tableBodyNode = document.createElement('tbody');

		for(let i in itemsList)
		{
			if(typeof itemsList[i] === 'function')
				continue;

			let itemRowNode = document.createElement('tr');
				itemRowNode.classList.add('item-'+ (itemsList[i].extension == 'dir' ? 'dir' : 'file'));
				itemRowNode.setAttribute('data-event-click', 'item-'+ (itemsList[i].extension == 'dir' ? 'dir' : 'file'));
				itemRowNode.setAttribute('data-item-path', itemsList[i].path);
				itemRowNode.itemInfo = JSON.parse(JSON.stringify(itemsList[i]));

			let itemIcon = 'fas fa-file';
			switch(itemsList[i].extension)
			{
				case 'dir':	itemIcon = 'fas fa-folder'; break;
				case 'png':	
				case 'gif':	
				case 'webp':	
				case 'jpeg':	
				case 'jpg':	itemIcon = 'fas fa-file-image'; break;
				case 'pdf':	itemIcon = 'fas fa-file-pdf'; break;
				case 'zip':	itemIcon = 'fas fa-file-archive'; break;
			}

		let itemRowHTML = '';
			itemRowHTML += '<td class="fileicon"><i class="'+ itemIcon +'"></i></td>';
			itemRowHTML += '<td class="filename">'+ itemsList[i].name +'</td>';
			itemRowHTML += '<td class="filetype">'+ itemsList[i].extension +'</td>';
			itemRowHTML += '<td class="filesize">'+ (itemsList[i].size ?  cmstk.formatFilesize(itemsList[i].size) : '') +'</td>';
	
			itemRowNode.innerHTML = itemRowHTML;

			tableBodyNode.append(itemRowNode);
		}

		let tableHeadNode = document.createElement('thead');

		let tableHeadHTML  = '';
		 	tableHeadHTML += '<tr>';
		 	tableHeadHTML += '<th class="fileicon"></th>';
		 	tableHeadHTML += '<th class="filename">Filename</th>';
		 	tableHeadHTML += '<th class="filetype">File type</th>';
		 	tableHeadHTML += '<th class="filesize">File size</th>';
		 	tableHeadHTML += '</tr>';
			tableHeadNode.innerHTML = tableHeadHTML;

		let tableNode = document.createElement('table');
			tableNode.classList.add('mediathek', 'mediathek-list');
			tableNode.append(tableHeadNode, tableBodyNode);

		contentNode.append(tableNode);
	}


	_onClick(event, srcInstance)
	{
		console.log(event.target);

		let targetNode = event.target;

		if(document.cmsMediathek.viewMode == cmsMediathek.VIEWMODE_LIST && (event.target.tagName == 'TD'))
		{
			// In list mode, check for click on TD to change the targetNode, the info is in the TR

			targetNode = event.target.parentNode;
		}

		console.log(targetNode);

		// check event type for targetNode, is he eligible for click event

		if(targetNode.hasAttribute('data-event-click'))
		{
			// react on click key word
			switch(targetNode.getAttribute('data-event-click'))
			{
				case 'item-dir':

						// click on dir item always open this dir
						
						srcInstance.activePath = targetNode.getAttribute('data-item-path') + '';
						srcInstance.requestItems();

					break;

				case 'item-file':

					targetNode.dispatchEvent(new CustomEvent(srcInstance.eventNameOnSelected, { detail: { file: targetNode.itemInfo, sourceNode: srcInstance.eventSourceNode }, bubbles: true  }));

					break;

				case 'item-ctr-dir-up':

						// click on dir move up

						if(srcInstance.activePath == '')
							break;
			
						srcInstance.activePath = srcInstance.activePath.substring(0, srcInstance.activePath.lastIndexOf('/')) + '';
						srcInstance.requestItems();

					break;
			}
		}
	}

	_onClickButtonImport(event, srcInstance)
	{
		let	formData  = new FormData;

		let	xhRequest = new cmsXhr;
			xhRequest.request(CMS.SERVER_URL_BACKEND + 'mediathek/', formData, srcInstance.onXHRSuccessImport, srcInstance, 'import');
	}

	_onClickButtonViewSquare(event, srcInstance)
	{
		document.cmsMediathek.viewMode = cmsMediathek.VIEWMODE_SQUARES;
		srcInstance.requestItems();
	}

	_onClickButtonViewList(event, srcInstance)
	{
		document.cmsMediathek.viewMode = cmsMediathek.VIEWMODE_LIST;
		srcInstance.requestItems();
	}

	onXHRSuccessImport(response, srcInstance)
	{
		srcInstance.requestItems();
	}



	static queryDirectories(onSuccessCallback, srcInstance)
	{
		let	formData  = new FormData;

		let	xhRequest = new cmsXhr;
			xhRequest.request(CMS.SERVER_URL_BACKEND + 'mediathek/', formData, onSuccessCallback, srcInstance, 'directory_list');
	}



}
