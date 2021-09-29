
class	cmsModalConfirm extends cmsModal
{
	constructor(modalTitle, modalText, modalButtonsList = null)
	{
		super();
		super.setTitle(modalTitle);
		if(modalButtonsList !== null)
		{
			for(let i = 0; i < modalButtonsList.length; i++)
			{
				super.addButton(modalButtonsList[i]);
			}
		}
		else
		{
			super.addButton(
				new cmsModalCtrlButton(
					cmsModal.BTN_LOCATION.BOTTOM_LEFT,
					'OK',
					null,
					'fas fa-check'
				)
			);
		}

		let nodeContent = document.createElement('p');
			nodeContent.innerHTML = modalText;
		super.create(nodeContent);
		super.open();
	}

}


class	cmsModalConfirmDelete extends cmsModal
{
	constructor(modalTitle, modalText, modalButtonsList = null)
	{
		super();
		super.setTitle(modalTitle);
		if(modalButtonsList !== null)
		{
			for(let i = 0; i < modalButtonsList.length; i++)
			{
				super.addButton(modalButtonsList[i]);
			}
		}
		else
		{
			super.addButton(
				new cmsModalCtrlButton(
					cmsModal.BTN_LOCATION.BOTTOM_LEFT,
					'Delete',
					null,
					'fas fa-trash-alt'
				)
			);
			
			super.addButton(
				new cmsModalCtrlButton(
					cmsModal.BTN_LOCATION.BOTTOM_LEFT,
					'Cancel',
					null,
					'fas fa-times'
				)
			);
		}

		let nodeContent = document.createElement('p');
			nodeContent.innerHTML = modalText;
		super.create(nodeContent);
		super.open();
	}

}
