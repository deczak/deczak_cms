
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
			itemsNode.style.width = '100%';

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
					buttonImportNode.onclick = function(event) { srcInstance._onClickButtonUpload(event, srcInstance) };
					buttonImportNode.innerHTML = '<span><i class="fas fa-file-upload"></i></span> Upload';

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
					itemRowHTML += '<td class="filemodify" data-modify="fileedit" title="Edit item"><i class="fas fa-pen"></i></td>';
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
	
	_onClickButtonUpload(event, srcInstance)
	{

		let modalUpload = new cmsModalMediathekUpload();
			modalUpload.open(event, srcInstance);
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
			let modalConfirm = new cmsModalConfirm(
				'Failed',
				response.msg
			);
			return false;
		}

		srcInstance.requestItems();
	}

	_onClickButtonRemoveItemModalConfirmSuccess(event, modalInstance, eventInfo)
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

	_onClickButtonEditItemSuccess(response, eventInfo)
	{
		eventInfo.modalInstance.close();
	}

	_onClickButtonEditItemModalEditSuccess(event, modalInstance, eventInfo)
	{
		eventInfo.modalInstance = modalInstance;

		let fieldList = modalInstance.getFieldList();
		
		let	formData  = new FormData;

			formData.append('media_id', fieldList['modal-media-item-id']);
			formData.append('media_license', fieldList['modal-media-item-license']);
			formData.append('media_license_url', fieldList['modal-media-item-license-url']);
			formData.append('media_gear_camera', fieldList['modal-media-item-gear-camera']);
			formData.append('media_gear_lens', fieldList['modal-media-item-gear-lens']);
			formData.append('media_author', fieldList['modal-media-item-author']);
			formData.append('media_caption', fieldList['modal-media-item-caption']);
			formData.append('media_notice', fieldList['modal-media-item-notice']);
			formData.append('media_title', fieldList['modal-media-item-title']);
	
		let	xhRequest = new cmsXhr;
			xhRequest.request(CMS.SERVER_URL_BACKEND + 'mediathek/', formData, eventInfo.srcInstance._onClickButtonEditItemSuccess, eventInfo, 'edit_item');
	}

	_onClickButtonEditItemRequestItem(response, srcInfo)
	{
		let inputItemId = srcInfo.modalContentNode.querySelector('[name="modal-media-item-id"]');
		if(inputItemId !== null) inputItemId.value = response.media_id;

		let inputItemTitle = srcInfo.modalContentNode.querySelector('[name="modal-media-item-title"]');
		if(inputItemTitle !== null) inputItemTitle.value = response.media_title;

		let inputItemNotice = srcInfo.modalContentNode.querySelector('[name="modal-media-item-notice"]');
		if(inputItemNotice !== null) inputItemNotice.value = response.media_notice;
		
		let inputItemCaption = srcInfo.modalContentNode.querySelector('[name="modal-media-item-caption"]');
		if(inputItemCaption !== null) inputItemCaption.value = response.media_caption;
		
		let inputItemAuthor = srcInfo.modalContentNode.querySelector('[name="modal-media-item-author"]');
		if(inputItemAuthor !== null) inputItemAuthor.value = response.media_author;
		
		let inputItemGearLens = srcInfo.modalContentNode.querySelector('[name="modal-media-item-gear-lens"]');
		if(inputItemGearLens !== null) inputItemGearLens.value = response.media_gear.lens;
		
		let inputItemGearCamera = srcInfo.modalContentNode.querySelector('[name="modal-media-item-gear-camera"]');
		if(inputItemGearCamera !== null) inputItemGearCamera.value = response.media_gear.camera;
		
		let inputItemLicense = srcInfo.modalContentNode.querySelector('[name="modal-media-item-license"]');
		if(inputItemLicense !== null) inputItemLicense.value = response.media_license;
		
		let inputItemLicenseUrl = srcInfo.modalContentNode.querySelector('[name="modal-media-item-license-url"]');
		if(inputItemLicenseUrl !== null) inputItemLicenseUrl.value = response.media_license_url;

		let previewNode = srcInfo.modalContentNode.querySelector('div.preview');
		if(previewNode !== null) {

			switch(response.media_mime)
			{
				case 'image/png': 
				case 'image/webp': 
				case 'image/jpeg':

					let previewInner = document.createElement('div');
						previewInner.classList.add('preview-inner');
						previewInner.style.backgroundImage = 'url("'+ response.media_url +'?binary&size=small")';
				
					previewNode.append(previewInner);
					previewNode.classList.add('grid');

					break;

				default: 
			}
		}
	}

	_onClickButtonEditItem(itemNode, eventNode, srcInstance)
	{
		let contentNode = document.createElement('div');
			contentNode.classList.add('cms-modal-mediathek-edit');


		let contentHTML  = '';


			contentHTML += '<div style="">';

				contentHTML += '<div class="preview">';
				contentHTML += '</div>';

				contentHTML += '<div class="fields">';



					contentHTML += '<div class="input width-100">';

						contentHTML += '<label>Title</label>';
						contentHTML += '<input type="text" name="modal-media-item-title" value="" maxlength="150">';

					contentHTML += '</div>';







					contentHTML += '<div class="input width-100">';

						contentHTML += '<label>Caption</label>';
						contentHTML += '<textarea name="modal-media-item-caption" maxlength="150"></textarea>';

					contentHTML += '</div>';


					contentHTML += '<div class="input width-50">';

						contentHTML += '<label style="">Author</label>';
						contentHTML += '<input type="text" name="modal-media-item-author" value="" maxlength="150">';

					contentHTML += '</div>';




					contentHTML += '<div class="input width-50">';

						contentHTML += '<label>License</label>';
						contentHTML += '<input type="text" name="modal-media-item-license" value="" maxlength="150">';

					contentHTML += '</div>';


					contentHTML += '<div class="input width-100">';

						contentHTML += '<label>License URL</label>';
						contentHTML += '<input type="text" name="modal-media-item-license-url" value="" maxlength="150">';

					contentHTML += '</div>';





					contentHTML += '<div class="input width-50">';

						contentHTML += '<label>Camera</label>';
						contentHTML += '<input type="text" name="modal-media-item-gear-camera" value="">';

					contentHTML += '</div>';


					contentHTML += '<div class="input width-50">';

						contentHTML += '<label>Lens</label>';
						contentHTML += '<input type="text" name="modal-media-item-gear-lens" value="">';

					contentHTML += '</div>';







					contentHTML += '<div class="input width-100">';

						contentHTML += '<label>Notice (not displayed)</label>';
						contentHTML += '<textarea name="modal-media-item-notice" maxlength="150"></textarea>';

					contentHTML += '</div>';




				contentHTML += '</div>';


			contentHTML += '</div>';



			contentHTML += '<input type="hidden" name="modal-media-item-id" value="">';


			
			contentNode.innerHTML = '<fieldset class="ui fieldset simply">'+ contentHTML +'</fieldset>';



		let modal = new cmsModal;
			modal
				.addButton(new cmsModalCtrlButton(cmsModal.BTN_LOCATION.BOTTOM_LEFT, 'Save', srcInstance._onClickButtonEditItemModalEditSuccess, 'fas fa-save', {itemNode:itemNode, eventNode:eventNode, srcInstance:srcInstance}))
				.addButton(new cmsModalCtrlButton(cmsModal.BTN_LOCATION.BOTTOM_LEFT, 'Cancel', null, 'fas fa-times'))
				.setTitle('Mediathek item')
				.create(contentNode, { width:'1100px', maxWidth:'1100px' })
				.open();



		let formData = new FormData;
			formData.append('media_id', itemNode.itemInfo.media_id);


		let index = new cmsIndex(
				CMS.SERVER_URL_BACKEND + 'mediathek/',
				srcInstance._onClickButtonEditItemRequestItem,
				'get_item'
			);
			index.request({srcInstance:srcInstance, modalContentNode:contentNode}, formData);

	}

	_onClickButtonMoveItemSuccess(response, srcInstance)
	{
		if(response.state !== 0)
		{
			let modalConfirm = new cmsModalConfirm(
				'Failed',
				response.msg
			);
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
			let modalConfirm = new cmsModalConfirm(
				'Failed',
				'Mediathek received an error on collect informationen'
			);
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

class cmsModalMediathekUpload extends cmsModal
{

	constructor()
	{
		super();
	}
	
	open(event, srcInstance)
	{



		let instance = this;



		instance.srcInstance = srcInstance;



		instance.modalUploadDropzoneNode = document.createElement('div');
		instance.modalUploadDropzoneNode.classList.add('dropzone');

		instance.modalUploadDropzoneNode.ondrop = function(event) { instance._onDropZoneDrop(event, instance) };
		instance.modalUploadDropzoneNode.ondragover = function(event) { instance._onDropZoneDragOver(event, instance) };
		instance.modalUploadDropzoneNode.ondragenter = function(event) { instance._onDropZoneDragEnter(event, instance) };
		instance.modalUploadDropzoneNode.ondragleave = function(event) { instance._onDropZoneDragLeave(event, instance) };

		instance.modalUploadDropItemsNode = document.createElement('div');
		instance.modalUploadDropItemsNode.classList.add('dropped-items')




		let content = document.createElement('div');
			content.append(
 
				instance.modalUploadDropzoneNode, 
				instance.modalUploadDropItemsNode
				);
	
		super.addButton(new cmsModalCtrlButton(cmsModal.BTN_LOCATION.BOTTOM_LEFT, 'Upload', instance._onEventClick_Upload, 'fas fa-file-upload'));
		super.addButton(new cmsModalCtrlButton(cmsModal.BTN_LOCATION.BOTTOM_LEFT, 'Close', super.close, 'fas fa-times'));
		super.setTitle('Mediathek upload')
		super.create(content, {}, ['modal-mediathek-upload-inner']);
		super.open();



		content.addEventListener('click', function(event) { instance._onEventClickHandler(event, srcInstance); });
	


	
		





	}

	_onEventClickHandler(event, srcInstance)
	{

		//console.log(event.target);
		//console.log(event.target.tagName);
		//console.log(event.target.id);

		let actionClick = event.target.getAttribute('action-click');

		


		//console.log(actionClick);


		if(actionClick === null)
			return;

		event.stopPropagation();

		switch(actionClick)
		{
			case 'dropped-item-remove':

					this._onEventClick_DroppedItemRemove(event);
					break;

		}

	}

	_onEventClick_DroppedItemRemove(event)
	{
		event.target.closest('div.dropped-item').remove();
	}

	_onEventClick_Upload(event, srcInstance)
	{
		let itemsList = srcInstance.nodeModal.querySelectorAll('.dropped-item');

		for(let i = 0; i < itemsList.length; i++)
		{
			if(typeof itemsList[i].fileInfo === 'undefined' || (typeof itemsList[i].fileInfo !== 'undefined' && typeof itemsList[i].fileInfo === null))
				continue;

			let removeButtonNode = itemsList[i].querySelector('[action-click="dropped-item-remove"]');
			if(removeButtonNode !== null)
				removeButtonNode.remove();

			let fieldList = cmsForms.collectFields(itemsList[i].querySelector('fieldset'));

			let progressNode = itemsList[i].querySelector('.progress');

			itemsList[i].progress = new cmsProgress(progressNode, {
				progressColor : 'green',
				backgroundColor : 'red'
			});

            let formData = new FormData();
                formData.append('file', itemsList[i].fileInfo, fieldList.filename);
                formData.append('media-item-author', fieldList['modal-media-item-author']);
                formData.append('media-item-camera', fieldList['modal-media-item-camera']);
                formData.append('media-item-caption', fieldList['modal-media-item-caption']);
                formData.append('media-item-lens', fieldList['modal-media-item-lens']);
                formData.append('media-item-license', fieldList['modal-media-item-license']);
                formData.append('media-item-licenseurl', fieldList['modal-media-item-licenseurl']);
                formData.append('media-item-path', fieldList['modal-media-item-path']);
                formData.append('media-item-title', fieldList['modal-media-item-title']);

			srcInstance._onEventClick_UploadProcess(formData, itemsList[i].progress, itemsList[i]);
		}
	}

	_onEventClick_UploadProcess(formData, progress, droppedItem)
	{


/*

	tracker der dateien die zur verarbeitung anstehen damit bei der letzten datei die mediathek aktualisiert wird


*/



		console.log('cmsModalMediathekUpload::_onEventClick_UploadProcess');


		console.log(progress);

		let requestURL = CMS.SERVER_URL_BACKEND + 'mediathek/';

		let xhRequest = new XMLHttpRequest();
		xhRequest.open('POST', requestURL);
		xhRequest.setRequestHeader("X-Requested-With","XMLHttpRequest");
		xhRequest.setRequestHeader("X-Requested-XHR-Action", 'upload');
		xhRequest.onerror   = function ()
		{
			// Event does not fire on 404 or 500
		};
		xhRequest.onloadend = function(event)
		{


			console.log('onloadend', xhRequest.response, this, event);

			if(this.status === 200)
			{
				//callbackSuccess(xhRequest.response, xhrCallInstance);

				droppedItem.fileInfo = null;



			}
			else
			{
				//callbackError(this, xhrCallInstance);
			}


		};
        xhRequest.upload.addEventListener("progress", (event) => {

            let percentComplete = (event.loaded / event.total) * 100;

			console.log(percentComplete);

			progress.setPercent(percentComplete);


			if(percentComplete == 100)
			{


				// todo  grüner haken in das bild

			}


		});

		xhRequest.send(formData);

	}

	_transformFileEntry(entry, successCallback, srcInstance) {
	
		console.log('_transformFileEntry');

		entry.file((file) => {

			successCallback(file, entry, srcInstance);

		});
	}

	_onDropZoneDropItems(item) {
		
		let srcInstance = this;

		if (item.isFile)
		{
			srcInstance._transformFileEntry(item, srcInstance._onDropZoneDropItemBox, srcInstance)
			srcInstance.modalUploadDropzoneNode.classList.add('lower');
		}
		else if (item.isDirectory)
		{
			let directoryReader = item.createReader();

			directoryReader.readEntries((entries) => {

				entries.forEach((entry) => {

					srcInstance._onDropZoneDropItems(entry);
					
				});

			});
		}
	}

	_onDropZoneDropItemBox(fileInfo, fileEntry, srcInstance)
	{
		switch(fileInfo.type)
		{
			case 'image/jpeg':
			case 'image/png':
			case 'image/webp':

					break;

			default:

					return;
		}
  
		let dropItem = document.createElement('div');
			dropItem.classList.add('dropped-item');
			dropItem.fileInfo = fileInfo;
			dropItem.innerHTML  = `
				<div class="preview">
					<img class="" src="">
					<button class="klaus" type="button" action-click="dropped-item-remove"><i class="far fa-trash-alt"></i></button>
				</div>
				<fieldset class="ui fieldset">

					<div class="fields">
						<div class="input width-100">
							<input type="text" name="modal-media-item-filename" value="`+ fileInfo.name +`" placeholder="Filename" maxlength="150" title="Filename">
						</div>
					</div>

					<div class="fields">
						<div class="input width-100">
							<input type="text" name="modal-media-item-title" value="" placeholder="Title" maxlength="150" title="Title">
						</div>
					</div>

					<div class="fields">
						<div class="input width-100">
							<textarea name="modal-media-item-caption" maxlength="150" placeholder="Caption" title="Caption"></textarea>
						</div>
					</div>

					<div class="fields">
						<div class="input width-100">
							<input type="text" name="modal-media-item-author" value="" placeholder="Author" maxlength="150" title="Author">
						</div>
					</div>

					<div class="fields">
						<div class="input width-100">
							<input type="text" name="modal-media-item-license" value="" placeholder="License" maxlength="150" title="License">
						</div>
					</div>

					<div class="fields">
						<div class="input width-100">
							<input type="text" name="modal-media-item-licenseurl" value="" placeholder="License URL" maxlength="150" title="License URL">
						</div>
					</div>

					<div class="fields">
						<div class="input width-100">
							<input type="text" name="modal-media-item-camera" value="" placeholder="Camera" maxlength="150" title="Camera">
						</div>
					</div>

					<div class="fields">
						<div class="input width-100">
							<input type="text" name="modal-media-item-lens" value="" placeholder="Lens" maxlength="150" title="Lens">
						</div>
					</div>

					<div class="fields">
						<div class="input width-100">
							<input type="text" name="modal-media-item-path" value="" placeholder="Path" maxlength="150" title="Path">
						</div>
					</div>


					<div class="progress"></div>
				
				</fieldset>
			`;

		let itemNode = srcInstance.modalUploadDropItemsNode.appendChild(dropItem);



		console.log('xxxxxxxxxxxxxxxx');
		let activeMediathekPath = srcInstance.srcInstance.activePath;

			activeMediathekPath = activeMediathekPath.replace(/^\/+|\/+$/g, '');


		console.log('activeMediathekPath', activeMediathekPath);

		let filePath = fileEntry.fullPath.split('/').filter(n => n);
		filePath.pop();
		filePath = filePath.join('/');


		if(activeMediathekPath != '')
			filePath = activeMediathekPath + (filePath != '' ? '/' : '') + filePath;
			
		console.log('filePath', filePath);

		if(filePath.length > 0)
			itemNode.querySelector('input[placeholder="Path"]').value = '/'+ filePath +'/';
		else
			itemNode.querySelector('input[placeholder="Path"]').value = '/';
		
		switch(fileInfo.type)
		{
			case 'image/jpeg':
			case 'image/png':
			case 'image/webp':

					let reader  = new FileReader();
					reader.readAsDataURL(fileInfo)
					reader.onload = function(e) {
						itemNode.querySelector('.preview img').src = reader.result;
					};

					break;
					
			default:

		}

		switch(fileInfo.type)
		{
			case 'image/jpeg':

					EXIF.getData(fileInfo, function() {

						let model 		= EXIF.getTag(this, "Model");
						let artist 		= EXIF.getTag(this, "Artist");
						let copyright 	= EXIF.getTag(this, "Copyright");
						let lens		= EXIF.getTag(this, "LensModel");

						if(model !== null && model !== ''&& typeof model !== 'undefined')
							itemNode.querySelector('input[placeholder="Camera"]').value = model;

						if(copyright !== null && copyright !== ''&& typeof copyright !== 'undefined')
							itemNode.querySelector('input[placeholder="License"]').value = copyright;

						if(artist !== null && artist !== '' && typeof artist !== 'undefined')
							itemNode.querySelector('input[placeholder="Author"]').value = String(artist).replaceAll("\r", '').replaceAll("\n", ' ');

						if(lens !== null && lens !== ''&& typeof lens !== 'undefined')
							itemNode.querySelector('input[placeholder="Lens"]').value = lens.replace(/[\u0000-\u001F\u007F-\u009F]/g, "");
					});
					
		}
  
	}

	// Dropzone Events

	_onDropZoneDrop(event, srcInstance)
	{
		event.preventDefault();

		var items = event.dataTransfer.items;

		for(let i=0; i<items.length; i++)
		{
			let item = items[i].webkitGetAsEntry();

			if(item)
			{
				srcInstance._onDropZoneDropItems(item);
			}
		}

		event.target.style.backgroundColor = '';
	}

	_onDropZoneDragOver(event)
	{
		event.preventDefault();
	}

	_onDropZoneDragEnter(event)
	{
		event.target.style.backgroundColor = 'gold';
	}

	_onDropZoneDragLeave(event)
	{
		event.target.style.backgroundColor = '';
	}

}