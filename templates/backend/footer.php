
<script>

	var	CMS = 	{
	 				"SERVER_URL" 	: "<?php echo CMS_SERVER_URL; ?>",
	 				"SERVER_URL_BACKEND" : "<?php echo CMS_SERVER_URL_BACKEND; ?>",
	 				"PAGE_PATH" 		 : "<?php echo REQUESTED_PAGE_PATH; ?>",
	 				"MODULE_TARGET"		 : (typeof MODULE != "undefined" ? MODULE.TARGET : '')
				};
		
</script>

<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/backend.js"></script>

