
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


		this.requestItems();
	}

	requestItems()
	{


		let	formData  = new FormData;



		let	xhRequest = new cmsXhr;
			xhRequest.request(this.requestURL, formData, this.onXHRSuccessRequestItems, this, 'index');


	}


	onXHRSuccessRequestItems(response, srcInstance)
	{


		console.log('onXHRSuccessRequestItems');
		console.log(response);
		console.log(srcInstance);

		srcInstance._generateHTML();

	}

	_generateHTML()
	{

		this.displaContainerNode.innerHTML = "m'kay";

	}

}
