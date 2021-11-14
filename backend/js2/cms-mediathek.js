
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

		window.addEventListener('event-mediathek-file-move-select-forder-selected', this._onClickButtonMoveItemModalPathSuccess);

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

					squareNode.classList.add('item-image');
					squareNode.style.backgroundImage = "url('"+ CMS.SERVER_URL +"mediathek/"+ itemsList[i].path +"?binary&size=thumb')";
					squareNode.innerHTML = '<div class="name">'+ itemsList[i].name  +'</div>';

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



		console.log('_generateHTML_List');
		console.log(itemsList);


		let tableBodyNode = document.createElement('tbody');

		for(let i in itemsList)
		{
			if(typeof itemsList[i] === 'function')
				continue;

			let itemRowNode = document.createElement('tr');
				itemRowNode.classList.add('item-'+ (itemsList[i].type == 'DIR' ? 'dir' : 'file'));
				itemRowNode.setAttribute('data-event-click', 'item-'+ (itemsList[i].type == 'DIR' ? 'dir' : 'file'));
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
			itemRowHTML += '<td class="filetime">'+ itemsList[i].time  +'</td>';

			if(this.workMode === cmsMediathek.WORKMODE_EDIT)
			{

				if(itemsList[i].type !== 'DIR')
				{
					itemRowHTML += '<td class="filemodify" data-modify="fileedit" title="Edit item"><i class="fas fa-pen-square"></i></td>';
					itemRowHTML += '<td class="filemodify" data-modify="filemove" title="Move item"><i class="fas fa-share"></i></td>';
				}
				else
				{
					itemRowHTML += '<td class="filemodify"></td>';
					itemRowHTML += '<td class="filemodify"></td>';
				}

				itemRowHTML += '<td class="filemodify" data-modify="fileremove" title="Delete item"><i class="fas fa-trash-alt"></i></td>';
			}

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
		 	tableHeadHTML += '<th class="filetime">Modified Date</th>';

			if(this.workMode === cmsMediathek.WORKMODE_EDIT)
			{
				tableHeadHTML += '<th class="filemodify"></th>';
				tableHeadHTML += '<th class="filemodify"></th>';
				tableHeadHTML += '<th class="filemodify"></th>';
			}

		 	tableHeadHTML += '</tr>';
			tableHeadNode.innerHTML = tableHeadHTML;

		let tableNode = document.createElement('table');
			tableNode.classList.add('mediathek', 'mediathek-list');
			tableNode.append(tableHeadNode, tableBodyNode);

		contentNode.append(tableNode);
	}


	_onClick(event, srcInstance)
	{
		let targetNode = event.target;

		if(document.cmsMediathek.viewMode == cmsMediathek.VIEWMODE_LIST && (event.target.tagName == 'TD'))
		{
			// In list mode, check for click on TD to change the targetNode, the info is in the TR

			targetNode = event.target.parentNode;
		}

		if(event.target.classList.contains('filemodify') && srcInstance.workMode === cmsMediathek.WORKMODE_EDIT)
		{
			let modifyMode = event.target.getAttribute('data-modify');

			switch(modifyMode)
			{
				case 'fileremove':

					srcInstance._onClickButtonRemoveItem(targetNode, event.target, srcInstance);
					break;

				case 'fileedit':

					srcInstance._onClickButtonEditItem(targetNode, event.target, srcInstance);
					break;

				case 'filemove':

					srcInstance._onClickButtonMoveItem(targetNode, event.target, srcInstance);
					break;
			}

			return false;
		}

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







	_onClickButtonRemoveItemSuccess(response, srcInstance)
	{
		if(response.state !== 0)
		{
			console.log('Mediathek returns error on remove file: '+ response.msg);
			return false;
		}

		srcInstance.requestItems();
	}




	_onClickButtonRemoveItemModalConfirmSuccess(modalInstance, eventInfo)
	{
		modalInstance.close();
		
		let	formData  = new FormData;
			formData.append('mediathek-remove-item-src', eventInfo.itemNode.itemInfo.path);

		let	xhRequest = new cmsXhr;
			xhRequest.request(CMS.SERVER_URL_BACKEND + 'mediathek/', formData, eventInfo.srcInstance._onClickButtonRemoveItemSuccess, eventInfo.srcInstance, 'remove_item');
	}





	_onClickButtonRemoveItem(itemNode, eventNode, srcInstance)
	{
		let modalA = new cmsModalConfirm(
			'Delete Mediathek item(s)',
			'Do you really want delete this mediathek item(s)?',
			[
				new cmsModalCtrlButton(cmsModal.BTN_LOCATION.BOTTOM_RIGHT, 'Cancel', null, 'fas fa-times'),
				new cmsModalCtrlButton(cmsModal.BTN_LOCATION.BOTTOM_RIGHT, 'Delete', srcInstance._onClickButtonRemoveItemModalConfirmSuccess,' fas fa-trash-alt', {itemNode:itemNode, eventNode:eventNode, srcInstance:srcInstance})
			], 
		);
	}









	_onClickButtonEditItem(itemNode, eventNode, srcInstance)
	{
		/*	
		  	itemNode	TR or DIV of whole item, contains a itemInfo property with info
			eventNode	Node of event target, TD or DIV
		*/
		alert ('on my todo');


		/*
			open modal with inputs
		*/
	}








	_onClickButtonMoveItemSuccess(response, srcInstance)
	{
		if(response.state !== 0)
		{
			console.log('Mediathek returns error on move file: '+ response.msg);
			return false;
		}

		srcInstance.requestItems();
	}

	_onClickButtonMoveItemModalPathSuccess(event)
	{
		let	formData  = new FormData;
			formData.append('mediathek-move-item-src', event.detail.sourceNode.itemNode.itemInfo.path);
			formData.append('mediathek-move-item-dst', event.detail.select.path);

		let	xhRequest = new cmsXhr;
			xhRequest.request(CMS.SERVER_URL_BACKEND + 'mediathek/', formData, event.detail.sourceNode.srcInstance._onClickButtonMoveItemSuccess, event.detail.sourceNode.srcInstance, 'move_item');
	}

	_onClickButtonMoveItemModalPath(response, srcInstance)
	{
		if(response.state !== 0)
		{
			console.log('cmsMediathek.queryDirectories returned invalid value');
			return false;
		}

		let	modalPath = new cmsModalPath;
			modalPath.setEventNameOnSelected('event-mediathek-file-move-select-forder-selected');
			modalPath.open('Select destination folder', response.data, srcInstance, 'fas fa-folder');
	}

	_onClickButtonMoveItem(itemNode, eventNode, srcInstance)
	{
		cmsMediathek.queryDirectories(srcInstance._onClickButtonMoveItemModalPath, {itemNode:itemNode, eventNode:eventNode, srcInstance:srcInstance});
	}








	static queryDirectories(onSuccessCallback, srcInstance)
	{
		let	formData  = new FormData;

		let	xhRequest = new cmsXhr;
			xhRequest.request(CMS.SERVER_URL_BACKEND + 'mediathek/', formData, onSuccessCallback, srcInstance, 'directory_list');
	}

	static queryDiretoryItems(mediathekPath, onSuccessCallback, srcInstance)
	{
		let	formData  = new FormData;
			formData.append('simple-gallery-path', mediathekPath);

		let	xhRequest = new cmsXhr;
			xhRequest.request(CMS.SERVER_URL_BACKEND + 'mediathek/', formData, onSuccessCallback, srcInstance, 'directory_items');
	}
}
