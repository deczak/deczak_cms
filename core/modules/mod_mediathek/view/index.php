<div class="be-module-container" id="container-mediathek" style="padding:0 2%;"></div>

not yet finished

<br><br>


<button id="btn-test-1">Select Modal Test</button> 

<script>

	let	mediathek = new cmsMediathek('container-mediathek');
		mediathek.init();




	document.getElementById('btn-test-1').onclick = function()
	{
		let mediathek = new cmsModalMediathek;
			mediathek.create()
					 .open();
	};


</script>

