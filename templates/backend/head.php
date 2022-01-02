
	<link href="<?php echo CMS_SERVER_URL_BACKEND; ?>third-party/fontawesome/css/fontawesome.min.css" rel="stylesheet">
	<link href="<?php echo CMS_SERVER_URL_BACKEND; ?>third-party/fontawesome/css/brands.min.css" rel="stylesheet">
	<link href="<?php echo CMS_SERVER_URL_BACKEND; ?>third-party/fontawesome/css/solid.min.css" rel="stylesheet">
	<link href="<?php echo CMS_SERVER_URL_BACKEND; ?>third-party/fontawesome/css/regular.min.css" rel="stylesheet">

	<link href="<?php echo CMS_SERVER_URL_BACKEND; ?>css/backend-standard.css" rel="stylesheet" title="default" media="screen">
	<link href="<?php echo CMS_SERVER_URL_BACKEND; ?>css/backend-ui-layout.css" rel="stylesheet" title="default" media="screen">
	<link href="<?php echo CMS_SERVER_URL_BACKEND; ?>css/backend-ui-controls.css" rel="stylesheet" title="default" media="screen">

	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-index-selector.js"></script>
	
	<script>

		document.pIndexSelector = new cmsIndexSelector();
		document.pIndexSelector.init('trigger-batch-item-all-checkbox', 'trigger-batch-item-checkbox');

	</script>

	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/toolkit.js"></script>

	<script>

		var	CMS = 	{
						"SERVER_URL" 		 : "<?php echo CMS_SERVER_URL; ?>",
						"SERVER_URL_BACKEND" : "<?php echo CMS_SERVER_URL_BACKEND; ?>",
						"PAGE_PATH" 		 : "<?php echo $pageRequest -> urlPath; ?>",
						"MODULE_TARGET"		 : "",
						"LANGUAGES"		 	 : <?= json_encode(CLanguage::getLanguages()) ?>
					};
		
	</script>

	<!-- v2 js, v1 will be removed if v2 finshed, folder gets renamed to js -->

	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js2/cms-toolkit.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js2/cms-crypt.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js2/cms-xhr.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js2/cms-upload.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js2/cms-mediathek.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js2/cms-modal.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js2/cms-modal-mediathek.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js2/cms-modal-confirm.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js2/cms-modal-path.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js2/cms-modal-node.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js2/cms-forms.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js2/cms-index.js"></script>