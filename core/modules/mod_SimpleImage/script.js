class cmsMECP_SimpleImage
{
	constructor()
	{
		let srcInstance = this;

		window.addEventListener('click', function(event) { srcInstance.onEventClick(event, srcInstance); });
		window.addEventListener('change', function(event) { srcInstance.onEventChange(event, srcInstance); });

		window.addEventListener('event-edit-module-image-select', function(event) { srcInstance.onEventImageSelect(event, srcInstance); });
		window.addEventListener('event-edit-module-image-select-success', function(event) { srcInstance.onEventImageSelectSuccess(event, srcInstance); });
	}

	onEventClick(event, srcInstance)
	{

		console.log(event);
		if(typeof event.target === 'undefined')
			return true;

		let mecpContainer = event.target.closest('.simple-image-control');
		if(mecpContainer === null)
			return true;

		let mecpDtaClass = mecpContainer.getAttribute('data-target-list');
		let eventTagName = event.target.tagName;

		switch(eventTagName)
		{
			case 'BUTTON':

				if(event.target.classList.contains('trigger-simple-image-select-modal'))
				{
					srcInstance.onEventImageSelect(mecpDtaClass, mecpContainer);

					return true;
				}
	
				break;
		}	
	}

	onEventChange(event, srcInstance)
	{
		if(typeof event.target === 'undefined')
			return true;

		let mecpContainer = event.target.closest('.simple-image-control');
		if(mecpContainer === null)
			return true;

		let mecpDtaClass = mecpContainer.getAttribute('data-target-list');
		let inputName = event.target.name;

		let snilNode = document.querySelector('.simple-image-container.'+ mecpDtaClass);

		let	imageNode = snilNode.querySelector('img');

		switch(inputName)
		{
			case 'simple-image-height':

				let heightUnit = mecpContainer.querySelector('select[name="simple-image-height-unit"]');

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

				snilNode.style.paddingTop = event.target.value + heightUnit.value;
				
				break;

			case 'simple-image-height-unit':

				let height = mecpContainer.querySelector('input[name="simple-image-height"]');

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

				snilNode.style.paddingTop = height.value + event.target.value;
				break;

			case 'simple-image-fit':

				imageNode.style.objectFit = event.target.value;
				break;

			case 'simple-image-position-x':
			case 'simple-image-position-x-unit':
			case 'simple-image-position-y':
			case 'simple-image-position-y-unit':

				let posX 	 = mecpContainer.querySelector('input[name="simple-image-position-x"]').value;
				let posXUnit = mecpContainer.querySelector('select[name="simple-image-position-x-unit"]').value;

				let posY 	 = mecpContainer.querySelector('input[name="simple-image-position-y"]').value;
				let posYUnit = mecpContainer.querySelector('select[name="simple-image-position-y-unit"]').value;

				imageNode.style.objectPosition = posX + posXUnit +' '+ posY + posYUnit;
				break;
		}	

		srcInstance.triggerContentUpdate(snilNode);
	}

	onEventImageSelect(mecpDtaClass)
	{
		let mediathek = new cmsModalMediathek;
			mediathek.setEventNameOnSelected('event-edit-module-image-select-success');
			mediathek.open(cmsMediathek.VIEWMODE_LIST, cmsMediathek.WORKMODE_SELECT, {srcInstance:this, mecpDtaClass:mecpDtaClass});
	}

	onEventImageSelectSuccess(event)
	{
		let snilNode = document.querySelector('.simple-image-container.'+ event.detail.sourceNode.mecpDtaClass);

		if(event.detail.file === null || event.detail.file.path.length === 0)
			return;

		let	simpeImageNode = snilNode;

		let	imageNode = simpeImageNode.querySelector('img');
			imageNode.src = CMS.SERVER_URL + "mediathek/" + event.detail.file.path +"?binary&size=large";

		let	imageIdNode = simpeImageNode.querySelector('input[name="simple-image-id"]');
			imageIdNode.value = event.detail.file.media_id;

		event.detail.sourceNode.srcInstance.triggerContentUpdate(snilNode);
	}
	
	triggerContentUpdate(mecpNode)
	{
		mecpNode.closest('.cms-content-object').querySelector('.cms-trigger-object-tool[data-cmd="edit"]').dispatchEvent(new Event('click'));
	}
}

document.MECP_SimpleImage = new cmsMECP_SimpleImage;
