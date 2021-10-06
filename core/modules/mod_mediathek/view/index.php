<div class="be-module-container" id="container-mediathek" style="padding:0 2%;"></div>

<br><br>

<script>

	let	mediathek = new cmsMediathek(document.getElementById('container-mediathek'));
		mediathek.setEventNameOnSelected('test-mediathek-on-selected');
		mediathek.init(cmsMediathek.VIEWMODE_LIST, cmsMediathek.WORKMODE_SELECT);

</script>
