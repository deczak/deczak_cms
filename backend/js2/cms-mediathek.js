
class	cmsMediathek
{
	constructor(displaContainerId)
	{
		this.displaContainerId 	 = displaContainerId;
		this.displaContainerNode = document.getElementById(displaContainerId);
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





	init()
	{
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
