
<script>

	var	CMS = 	{
	 				"SERVER_URL" 		 : "<?php echo CMS_SERVER_URL; ?>",
	 				"SERVER_URL_BACKEND" : "<?php echo CMS_SERVER_URL_BACKEND; ?>",
	 				"PAGE_PATH" 		 : "<?php echo $pageRequest -> urlPath; ?>",
	 				"MODULE_TARGET"		 : (typeof MODULE != "undefined" ? MODULE.TARGET : '')
				};
		
</script>

<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/toolkit.js"></script>

<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-ui-select.js"></script>

<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/backend.js"></script>
