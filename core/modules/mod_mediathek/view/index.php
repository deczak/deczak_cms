<div class="be-module-container">

<pre>

Da wir für diverse Bereiche eine gleiche Navigation brauchen um Medien für Module usw. zu 
selecten. Ist es sinnvoll das wir das komplett über JS machen mit allem was dazu benötigt
wird.

Die Sprach Strings müssen in die Hauptdatei rein damit die entsprechend zur Verfügung stehen

->	cms-mediathek.js

	Baut letztendlich alles zusammen, ruft per xhr die Daten ab

	->	cms-xhr.js

	Die einzelnen aktionen können abgeschaltet werden

	Liefert den node im Bedarfsfall

	Managt auch den Upload mit der Upload Steuerung

	->	cms-upload.js

->	cms-modal.js

	Modal Steuerung mit Basis Funktionen

	Bekommt eine Ableitung für die Mediathek

	->	cms-modal-mediathek.js

		Hier muss eine Auswahl der Media möglich sein die dann einen callback mit dem Medien Informationen aufruft


</pre>

<button id="btn-test-1">Modal Basic</button> 
<button id="btn-test-2">Modal Confirm</button> 
<button id="btn-test-2b">Modal Confirmb</button> 
<button id="btn-test-3">Modal Mediathek</button> 

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

