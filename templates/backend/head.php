
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
						"MODULE_TARGET"		 : ""
					};
		
	</script>
