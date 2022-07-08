

<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-ui-select.js"></script>

<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/backend.js"></script>

<script>
	CMS.LANGUAGE_DEFAULT_IN_URL = <?= (CFG::GET() -> LANGUAGE -> DEFAULT_IN_URL ? 'true' : 'false') ?>;
	CMS.LANGUAGE_DEFAULT 		= '<?= CLanguage::getDefault(); ?>';
</script>
