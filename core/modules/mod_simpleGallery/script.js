
function openModalSelectMediathekDirectory(response, srcInstance)
{
	if(response.state !== 0)
	{
		console.log('cmsMediathek.queryDirectories returned invalid value');
		return false;
	}

	let	modalPath = new cmsModalPath;
		modalPath.setEventNameOnSelected('event-simple-gallery-modal-select-selected');
		modalPath.open('Gallery Mediathek Directory', response.data, srcInstance, 'fas fa-folder');
}

window.addEventListener('click', function(event)
{
	if(typeof event.target === 'undefined')
		return false;

	let eventTagName = event.target.tagName;

	switch(eventTagName)
	{
		case 'BUTTON':

			if(event.target.classList.contains('simple-gallery-select-directory'))
			{
				cmsMediathek.queryDirectories(openModalSelectMediathekDirectory, event.target);
				return true;
			}

			break;
	}
});


function onXHRSuccessSimpleGalleryQueryItems(response, srcInstance)
{


	if(response.state !== 0)
		return


	let parentNode = srcInstance.closest('.simple-gallery-control');

	let	sgilId = parentNode.getAttribute('data-target-list');

	let sgilNOde = document.getElementById(sgilId);
		sgilNOde.innerHTML = '';

	let processList = []

	for(let i in response.data)
	{
		if(typeof response.data[i] === 'function')
			continue;


		console.log(response.data[i]);

		switch(response.data[i].mime )
		{
			case 'image/jpeg':
			case 'image/png':
			case 'image/png':

				processList.push(response.data[i]);
		}
	}



	console.log('onXHRSuccessSimpleGalleryQueryItems');
	console.log(processList);




	do
	{



		let rp = 0;
		let bp = [];
		let bl = [];


		let rp2 = 0;


		for(let i = 0; i < processList.length; i++)
		{
			
			switch(processList[i].orient )
			{
				case 0:

					rp2 = 2;
					break;

				case 1:

					rp2 = 1;
					break;
			}



			if((rp + rp2) > 8)
			{

				if(rp == 7 && bp.length > 0)
				{
					let itemp = bp.pop();




					let apNode = document.createElement('a');
						apNode.classList.add('orient-'+ itemp.orient);
						apNode.innerHTML = '<img src="'+ CMS.SERVER_URL +'mediathek/'+ itemp.path +'?binary&size=thumb">';

					sgilNOde.append(apNode);

				}

				rp = 0;

				switch(processList[i].orient)
				{
					case 0:

						bl.push(processList[i]);
						break;

					case 1:

						bp.push(processList[i]);
						break;
				}

				continue;
			}

			rp = rp + rp2;


			let aNode = document.createElement('a');
				aNode.classList.add('orient-'+ processList[i].orient);
				aNode.innerHTML = '<img src="'+ CMS.SERVER_URL +'mediathek/'+ processList[i].path +'?binary&size=thumb">';



			sgilNOde.append(aNode);

		




		}	



		processList = bp.concat(bl);



		if(processList.length === 0)
			break;

	}
	while(true)

}


function selectTestEvent(event)
{
	let parentNode = event.detail.sourceNode.closest('.simple-gallery-control');

	let	mediathekPathNode = parentNode.querySelector('[name="simple-gallery-path"]');
		mediathekPathNode.value = event.detail.select.path;

	cmsMediathek.queryDiretoryItems(event.detail.select.path, onXHRSuccessSimpleGalleryQueryItems, event.detail.sourceNode);
}

window.addEventListener('event-simple-gallery-modal-select-selected', selectTestEvent);
