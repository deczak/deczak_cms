
<form class="form-search-sidebar" id="trigger-form-submit-<?= $object -> object_id; ?>" action="<?= CMS_SERVER_URL.URL_LANG_PRREFIX.substr($parentNode -> page_path,1); ?>search/" method="get">
	<div>
		<input class="ui" type="text" name="q" value="" placeholder="<?= $language -> string('MOD_SEARCH_PLACEHOLDER'); ?>">
		<button type="submit" id="trigger-search-button-<?= $object -> object_id; ?>"><i class="fas fa-search"></i> </button>
	</div>
</form>

<style>
form.form-search-sidebar > div { position:relative; }
form.form-search-sidebar > div > input { width:100%; outline:none !important; padding: 4px 6px; }
form.form-search-sidebar > div > button { position:absolute; top:50%; transform:translateY(-50%); right:5px; background:none; border:0px; }
</style>

<script>
document.getElementById('trigger-form-submit-<?= $object -> object_id; ?>').onsubmit = function(event) {

	event.preventDefault();
	event.stopPropagation();

	let inputSearchString = this.querySelector('input');
	if(inputSearchString == null)
		return;

	inputSearchString.value = inputSearchString.value.replace(/(<([^>]+)>)/gi, '');
	inputSearchString.value = inputSearchString.value.replace(/(\s)/gi, '+');
	
	window.location.href = this.getAttribute('action') + inputSearchString.value;
};
</script>
