
class	cmsModalMediathek extends cmsModal
{
	constructor()
	{
		super();
		this.rootPath = '/';
	}

	open(viewMode, workMode)
	{

console.log('cmsModalMediathek::open');
console.log(viewMode);
console.log(workMode);

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
			mediathek.setEventNameOnSelected(this.eventNameOnSelected);
			mediathek.init(viewMode, workMode, this.rootPath);




	}

	setRootPath(rootPath)
	{
		this.rootPath = rootPath;
	}

	setEventNameOnSelected(eventName)
	{
		this.eventNameOnSelected = eventName;
	}


}

