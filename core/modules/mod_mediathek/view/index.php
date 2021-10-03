<div class="be-module-container" id="container-mediathek" style="padding:0 2%;"></div>

not yet finished

<br><br>


<button id="btn-test-1">Select Modal Test</button> 

<script>

	let	mediathek = new cmsMediathek(document.getElementById('container-mediathek'));
		mediathek.init();




	document.getElementById('btn-test-1').onclick = function()
	{

		/*
			must submit an info what happens after selection
		*/


		let mediathek = new cmsModalMediathek;
			mediathek.open(cmsMediathek.VIEWMODE_LIST, cmsMediathek.WORKMODE_SELECT);
	};


</script>

