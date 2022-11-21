class cmsProgress
{
	constructor(progressNode, settings = null)
	{
		this.progressNode = null;

		if(progressNode === null || !(progressNode instanceof HTMLElement))
			return;

		this.progressNode = progressNode;

		this.progressNode.progress = this;
		this.init(settings);
	}

	init(settings)
	{
		if(this.progressNode === null)
			return;

		this.percentNode = document.createElement('div');
		this.percentNode.style.height = '100%';
		this.percentNode.style.backgroundColor = (settings !== null && typeof settings.progressColor !== 'undefined' ? settings.progressColor : 'aliceblue');
	
		this.setPercent(0);

		let	outerNode = document.createElement('div');
			outerNode.append(this.percentNode);
			outerNode.style.height = '100%';
			outerNode.style.width = '100%';
			outerNode.style.backgroundColor = (settings !== null && typeof settings.backgroundColor !== 'undefined' ? settings.backgroundColor : 'whitesmoke');

		this.progressNode.append(outerNode);
	}

	setPercent(percent)
	{
		if(this.progressNode === null)
			return;

		this.percentNode.style.width = parseInt(percent) +'%';



		let zffP = 255 / 100 * percent;

		let zffPN = 255 - zffP;

		console.log('setPercent', zffP, zffPN);



		/*
		
			rgb		255	0	0		rot

					0	255	0		grün


			hintergrund grau, / whitesmoke

			porzent balkenk mit farberechnung
		
		
		*/
	}
}


