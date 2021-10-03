
class	cmsMediathek
{


	constructor(displaContainerId)
	{
		this.displaContainerId 	 = displaContainerId;
		this.displaContainerNode = document.getElementById(displaContainerId);
		this.requestURL			 = CMS.SERVER_URL_BACKEND + 'mediathek/';
	}

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
