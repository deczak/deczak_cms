





window.addEventListener('change', function(event)
{

	
	if(typeof event.target === 'undefined' || typeof event.target.name === 'undefined')
		return false;

	//let cco = event.target.closest('.cms-content-object');
	let sic = event.target.closest('.simple-image-controll');


console.log(event.target);
console.log(sic);

//document.getElementById('simple-image-controll').onchange = function(event)
//{


	let inputName = event.target.name;


	let	innerNode = sic.querySelector('.simple-image-div-inner');
	let	imageNode = sic.querySelector('img');
	
	switch(inputName)
	{
		case 'simple-image-height':

			let heightUnit = sic.querySelector('select[name="simple-image-height-unit"]');

			switch(heightUnit.value)
			{
				case '%': 

					event.target.value = (event.target.value < 8   ?   8 : event.target.value);
					event.target.value = (event.target.value > 100 ? 100 : event.target.value);
					break;

				case 'px': 
				
					event.target.value = (event.target.value < 50   ?   50 : event.target.value);
					break;
			}

			sic.style.paddingTop = event.target.value + heightUnit.value;
			break;

		case 'simple-image-height-unit':

			let height = sic.querySelector('input[name="simple-image-height"]');

			switch(event.target.value)
			{
				case '%': 
				
					height.value = (height.value < 8   ?   8 : height.value);
					height.value = (height.value > 100 ? 100 : height.value);
					break;

				case 'px': 
				
					height.value = (height.value < 50   ?   50 : height.value);
					break;
			}

			sic.style.paddingTop = height.value + event.target.value;
			break;

		case 'simple-image-fit':

			imageNode.style.objectFit = event.target.value;
			break;

		case 'simple-image-position-x':
		case 'simple-image-position-x-unit':
		case 'simple-image-position-y':
		case 'simple-image-position-y-unit':

			let posX 	 = sic.querySelector('input[name="simple-image-position-x"]').value;
			let posXUnit = sic.querySelector('select[name="simple-image-position-x-unit"]').value;

			let posY 	 = sic.querySelector('input[name="simple-image-position-y"]').value;
			let posYUnit = sic.querySelector('select[name="simple-image-position-y-unit"]').value;

			imageNode.style.objectPosition = posX + posXUnit +' '+ posY + posYUnit;
			break;
	}
//};
});

window.addEventListener('click', function(event)
{
	if(!event.target.classList.contains('button-select-mediathek-iteem'))
		return;

	let mediathek = new cmsModalMediathek;
		mediathek.setEventNameOnSelected('event-simple-image-selected');
		mediathek.open(cmsMediathek.VIEWMODE_LIST, cmsMediathek.WORKMODE_SELECT, event.target);
});




function simpleImageOnSelected(event, aaaa)
{


	if(event.detail.file === null || event.detail.file.path.length === 0)
		return;

	let	simpeImageNode = event.detail.sourceNode.closest('.simple-image-controll');
	

	let	imageNode = simpeImageNode.querySelector('img');
		imageNode.src = CMS.SERVER_URL + "mediathek/" + event.detail.file.path +"?binary&size=large";

	let	imageIdNode = simpeImageNode.querySelector('input[name="simple-image-id"]');
		imageIdNode.value = event.detail.file.media_id;
}

window.addEventListener('event-simple-image-selected', simpleImageOnSelected);
