<div class="be-module-container">
\o/
<!--
<button id="btn-test-1">Modal Basic</button> 
<button id="btn-test-2">Modal Confirm</button> 
<button id="btn-test-2b">Modal Confirmb</button> 
<button id="btn-test-2c">Modal Confirm c</button> 
<button id="btn-test-3">Modal Mediathek</button> 
-->

</div>

<script>

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

</script>

