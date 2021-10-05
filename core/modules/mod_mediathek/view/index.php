<div class="be-module-container" id="container-mediathek" style="padding:0 2%;"></div>

<br><br><br>
<br><br>


<button id="btn-test-1">Select Modal Test</button> 

<script>

	let	mediathek = new cmsMediathek(document.getElementById('container-mediathek'));
		mediathek.setEventNameOnSelected('test-mediathek-on-selected');
		mediathek.init(cmsMediathek.VIEWMODE_LIST, cmsMediathek.WORKMODE_SELECT);




	document.getElementById('btn-test-1').onclick = function()
	{

		/*
			must submit an info what happens after selection
		*/


		let mediathek = new cmsModalMediathek;
			mediathek.setEventNameOnSelected('test-mediathek-on-selected');
			mediathek.open(cmsMediathek.VIEWMODE_LIST, cmsMediathek.WORKMODE_SELECT);
	};



function testIt(event)
{
	console.log('testIt');
	console.log(event);
	console.log(event.detail);
	console.log(this);
}


  window.addEventListener('test-mediathek-on-selected', testIt);

</script>

