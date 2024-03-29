
class	cmsModal
{
	static BTN_LOCATION = { TOP_RIGHT : 1, BOTTOM_LEFT : 2, BOTTOM_RIGHT : 3 };
	static TITLE_STATE = { DEFAULT : 1, RED : 2 };
	
	constructor(params = null)
	{
		this.buttonsList = [];
		this.nodeModal	 = null;
		this.modalTitle  = '';
		this.modalTitleState  = cmsModal.TITLE_STATE.DEFAULT;
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
	setTitle(title, titleState = cmsModal.TITLE_STATE.DEFAULT)
	{
		this.modalTitle = title;
		this.modalTitleState = titleState;
		return this;
	}

	/**
	 * 	Creates the required nodes for the modal
	 */
	create(nodeContent, extInnerStyles = null, extInnerClasses = null)
	{		
		let srcInstance = this;

		this.nodeModal = document.createElement('div');
		this.nodeModal.classList.add('cms-modal', 'cms-modal-id-');

		let	nodeModalChild = document.createElement('div');
		nodeModalChild.classList.add('cms-modal-inner');
		if(extInnerClasses !== null)
			nodeModalChild.classList.add(...extInnerClasses);

		if(extInnerStyles !== null)
		{
			for(let styleKey in extInnerStyles)
			{
				nodeModalChild.style[styleKey] = extInnerStyles[styleKey];
			}
		}

		if(this.modalTitle.trim().length != 0)
		{
			let nodeTitle = document.createElement('h4');
			nodeTitle.innerHTML = this.modalTitle.trim();
			nodeTitle.setAttribute('data-state', this.modalTitleState);
			nodeModalChild.append(nodeTitle);

		}

		nodeContent.classList.add('cms-modal-content-userdefined')

		nodeModalChild.append(nodeContent);


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
			{
				nodeButton.onclick = function(event) { srcInstance.buttonsList[i].buttonCallback(event, srcInstance, srcInstance.buttonsList[i].dataInfo) };
			}
			else
			{
				nodeButton.onclick = this.close;
			}

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

		this.nodeModal.addEventListener('cms-modal-close', function(event) { srcInstance.close(event, srcInstance); });

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
	close(event = null)
	{

		if(event === null)
			event = { target:this.nodeModal };

		event.target.closest('div.cms-modal').remove();
		document.querySelector('body').classList.remove('noScroll');
	}

	getFieldList()
	{
		let fieldsetNode = this.nodeModal.querySelector('fieldset');
		if(fieldsetNode === null)
			return [];
		return cmsForms.collectFields(fieldsetNode);
	}
}

class	cmsModalCtrlButton
{
	constructor(buttonPos, buttonText, buttonCallback = null, buttonIcon = null, dataInfo = null)
	{
		this.buttonPos 		= buttonPos;
		this.buttonText 	= buttonText;
		this.buttonIcon 	= buttonIcon;
		this.buttonCallback = buttonCallback;
		this.dataInfo 		= dataInfo;
	}
}


/*



<button id="btn-test-1">Modal Basic</button> 
<button id="btn-test-2">Modal Confirm</button> 
<button id="btn-test-2b">Modal Confirmb</button> 
<button id="btn-test-2c">Modal Confirm c</button> 
<button id="btn-test-3">Modal Mediathek</button> 



document.getElementById('btn-test-1').onclick = function()
{

	let content = document.createElement('div');
		content.innerHTML = '<p>This ist just a test!</p>';


	let modalA = new cmsModal;
		modalA	.addButton(new cmsModalCtrlButton(cmsModal.BTN_LOCATION.BOTTOM_LEFT, 'Share it on 500px!', null, 'fab fa-500px'))
				.addButton(new cmsModalCtrlButton(cmsModal.BTN_LOCATION.BOTTOM_LEFT, 'Mail yourself!', null, 'fas fa-at'))
				.setTitle('A Modal for own defined content')
				.create(content)
				.open();
};

document.getElementById('btn-test-2').onclick = function()
{
	let modalA = new cmsModalConfirm(
		'confirm title',
		'confirm text',
		[
			new cmsModalCtrlButton(cmsModal.BTN_LOCATION.BOTTOM_RIGHT, 'OK'),
			new cmsModalCtrlButton(cmsModal.BTN_LOCATION.BOTTOM_RIGHT, 'Cancel'),
			new cmsModalCtrlButton(cmsModal.BTN_LOCATION.BOTTOM_RIGHT, 'Delete', null,' fas fa-trash-alt')
		]
	);

};

document.getElementById('btn-test-2b').onclick = function()
{
	let modalA = new cmsModalConfirm(
		'confirm title',
		'confirm text'
	);

};


document.getElementById('btn-test-2c').onclick = function()
{
	console.log('check');
	let modalA = new cmsModalConfirmDelete(
		'confirm title asdf',
		'confirm textqwer'
	);

};






document.getElementById('btn-test-3').onclick = function()
{
	let modalA = new cmsModalMediathek;

	modalA.open();
};

function heClickedZeButton()
{
	alert("m'kay");
}


*/