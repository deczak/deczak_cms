
class	cmsMediathek
{
	static VIEWMODE_LIST    = 1;
	static VIEWMODE_SQUARES = 2;

	static WORKMODE_SELECT  = 1;
	static WORKMODE_EDIT    = 2;

	constructor(displaContainerNode)
	{
		this.displaContainerNode = displaContainerNode;
		this.requestURL			 = CMS.SERVER_URL_BACKEND + 'mediathek/';
	}

	/*

		view mode

			list

			squares

		workmode

			select only

			edit

		actions

			navigate through folders

			create folders

			remove folders

			select item (select mode)

			edit item

			remove item

			upload item (cmsUpload)
	*/





	init(viewMode, workMode, rootPath = '/')
	{

		console.log('cmsMediathek::init')
		console.log(viewMode)
		console.log(workMode)

		this.viewMode = viewMode
		this.workMode = workMode

		this.requestItems();
	}

	setEventNameOnSelected(eventName)
	{
		this.eventNameOnSelected = eventName;
	}

	requestItems()
	{


		let	formData  = new FormData;



		let	xhRequest = new cmsXhr;
			xhRequest.request(this.requestURL, formData, this.onXHRSuccessRequestItems, this, 'index');


	}


	onXHRSuccessRequestItems(response, srcInstance)
	{

		if(response.error === 1)
		{
			// TODO
			return;
		}


		console.log('onXHRSuccessRequestItems');
		console.log(response);
		console.log(srcInstance);

		let contentNode = document.createElement('div');
console.log(srcInstance.viewMode);
console.log(srcInstance.VIEWMODE_LIST);
		switch(srcInstance.viewMode)
		{
			case cmsMediathek.VIEWMODE_LIST:

				srcInstance._generateHTML_List(response.data, contentNode);

				break;

			case cmsMediathek.VIEWMODE_SQUARES:

				srcInstance._generateHTML_Squares(response.data, contentNode);

				break;
		}


		console.log(contentNode.innerHTML);

		srcInstance.displaContainerNode.innerHTML = contentNode.innerHTML;



/*

	eventlistener to contentNode for item events

*/


	}


	_generateHTML_Squares(itemsList, contentNode)
	{
		for(let i = 0; i < itemsList.length; i++)
		{

		}
	}

	_generateHTML_List(itemsList, contentNode)
	{
console.log('_generateHTML_List');
		let tableBodyHTML = '';

console.log(itemsList);
console.log(itemsList.length);
		for(let i in itemsList)
		{
			if(typeof itemsList[i] === 'function')
				continue;
				
console.log(itemsList[i]);

			tableBodyHTML += '<tr>';
			tableBodyHTML += '<td>'+ itemsList[i].name +'</td>';
			tableBodyHTML += '<td>'+ itemsList[i].size +'</td>';
			tableBodyHTML += '</tr>';
		}


		let tableHeadHTML  = '';
		 	tableHeadHTML += '<tr>';
		 	tableHeadHTML += '<th>Filename</th>';
		 	tableHeadHTML += '<th>Filesize</th>';
		 	tableHeadHTML += '</tr>';

		contentNode.innerHTML = '<table>'+ tableHeadHTML + tableBodyHTML +'</table>';

	}


}
