
class	cmsModalMediathek extends cmsModal
{
	constructor()
	{
		super();
	}

	open(viewMode, workMode, rootPath = '/')
	{


		let content = document.createElement('div');
		
		/*
		
			must react on selectmode

				listen on event
		
		*/


		super.addButton(new cmsModalCtrlButton(cmsModal.BTN_LOCATION.BOTTOM_LEFT, 'Close', null, 'fas fa-times'));
		super.setTitle('Mediathek')
		super.create(content);
		super.open();



		let	mediathek = new cmsMediathek(content);
			mediathek.init(viewMode, workMode, rootPath);




	}


}

