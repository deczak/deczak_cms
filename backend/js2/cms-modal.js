
class	cmsModal
{
	static BTN_LOCATION = { TOP_RIGHT : 1, BOTTOM_LEFT : 2, BOTTOM_RIGHT : 3 };
	
	constructor(params = null)
	{
		this.buttonsList = [];
		this.nodeModal	 = null;
		this.modalTitle  = '';
	}

	/**
	 * 	Add a button-object to the modal
	 */
	addButton(buttonObject)
	{
		this.buttonsList.push(buttonObject);
		return this;
	}

	/**
	 * 	Set a title for the Modal
	 */
	setTitle(title)
	{
		this.modalTitle = title;
		return this;
	}

	/**
	 * 	Creates the required nodes for the modal
	 */
	create(nodeContent)
	{		
		this.nodeModal = document.createElement('div');
		this.nodeModal.classList.add('cms-modal', 'cms-modal-id-');

		let	nodeModalChild = document.createElement('div');
		nodeModalChild.classList.add('cms-modal-inner');

		if(this.modalTitle.trim().length != 0)
		{
			let nodeTitle = document.createElement('h4');
			nodeTitle.innerHTML = this.modalTitle.trim();
			nodeModalChild.append(nodeTitle);

			let titleHline = document.createElement('hr');
			nodeModalChild.append(titleHline);
		}

		nodeModalChild.append(nodeContent);

		let hline = document.createElement('hr');
		nodeModalChild.append(hline);

		let bottomButtonBox = document.createElement('div');
		bottomButtonBox.classList.add('cms-modal-buttons-bottom');

		let bottomButtonBoxRight = document.createElement('div');

		let bottomButtonBoxLeft = document.createElement('div');

		for(let i = 0; i < this.buttonsList.length; i++)
		{
			let nodeButton = document.createElement('button');
			nodeButton.type = 'button'
			nodeButton.classList.add('ui', 'button');
			nodeButton.innerHTML = ''
			if(this.buttonsList[i].buttonIcon !== null)
			{
				nodeButton.classList.add('icon', 'labeled');
				nodeButton.innerHTML += '<span><i class="'+ this.buttonsList[i].buttonIcon +'"> </i></span>';
			}
			nodeButton.innerHTML += this.buttonsList[i].buttonText;

			if(this.buttonsList[i].buttonCallback !== null)
				nodeButton.onclick = this.buttonsList[i].buttonCallback;
			else
				nodeButton.onclick = this.close;

			switch(this.buttonsList[i].buttonPos)
			{
				case cmsModal.BTN_LOCATION.BOTTOM_LEFT:

					bottomButtonBoxLeft.append(nodeButton);
					break;

				case cmsModal.BTN_LOCATION.BOTTOM_RIGHT:

					bottomButtonBoxRight.append(nodeButton);
					break;

				case cmsModal.BTN_LOCATION.TOP_RIGHT:
					break;
			}
		}

		bottomButtonBox.append(bottomButtonBoxLeft, bottomButtonBoxRight);

		nodeModalChild.append(bottomButtonBox);

		this.nodeModal.appendChild(nodeModalChild);

		return this;
	}

	/**
	 * 	Open the created modal
	 */
	open()
	{
		if(this.buttonsList.length === 0)
		{
			this.addButton(
				new cmsModalCtrlButton(
					cmsModal.BTN_LOCATION.BOTTOM_LEFT,
					'OK',
					null,
					'fas fa-check'
				)
			);
		}

		if(this.nodeModal === null)
			this.create();

		let bodyNode = document.querySelector('body');
			bodyNode.appendChild(this.nodeModal);
			bodyNode.classList.add('noScroll');

	}

	/**
	 * 	Close the created modal
	 */
	close(event)
	{
		event.target.closest('div.cms-modal').remove();
			document.querySelector('body').classList.remove('noScroll');
	}
}

class	cmsModalCtrlButton
{
	constructor(buttonPos, buttonText, buttonCallback = null, buttonIcon = null)
	{
		this.buttonPos 		= buttonPos;
		this.buttonText 	= buttonText;
		this.buttonIcon 	= buttonIcon;
		this.buttonCallback = buttonCallback;
	}
}
