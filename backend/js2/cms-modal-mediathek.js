
class	cmsModalMediathek extends cmsModal
{
	constructor()
	{
		super();
		this.rootPath = '';

		this.eventNameOnSelected = null;
	}

	open(viewMode, workMode)
	{
		let srcInstance = this;

		let content = document.createElement('div');
	
		super.addButton(new cmsModalCtrlButton(cmsModal.BTN_LOCATION.BOTTOM_LEFT, 'Close', null, 'fas fa-times'));
		super.setTitle('Mediathek')
		super.create(content, {maxWidth:'1024px'});
		super.open();

		if(this.eventNameOnSelected !== null)
		{
			content.addEventListener(this.eventNameOnSelected, function(event) { srcInstance._onEventItemSelected(event, srcInstance); });
		}

		let	mediathek = new cmsMediathek(content);
			mediathek.setEventNameOnSelected(this.eventNameOnSelected);
			mediathek.init(viewMode, workMode, this.rootPath);

		content.style.overflowY = 'scroll';
		content.style.height = window.innerHeight - (window.innerHeight * 0.2)+'px';
	}

	setRootPath(rootPath)
	{
		this.rootPath = rootPath;
	}

	setEventNameOnSelected(eventName)
	{
		this.eventNameOnSelected = eventName;
	}

	_onEventItemSelected(event, srcInstance)
	{
		

		event.target.dispatchEvent(new CustomEvent('cms-modal-close', { detail: null, bubbles: true }));



	}
}

