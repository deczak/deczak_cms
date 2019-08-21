
	<script>

		var	CMS = 	{
	 					"SERVER_URL" 		 : "<?php echo CMS_SERVER_URL; ?>",
						"SERVER_URL_BACKEND" : "<?php echo CMS_SERVER_URL_BACKEND; ?>",
						"PAGE_PATH" 		 : "<?php echo REQUESTED_PAGE_PATH; ?>",
						"MODULE_TARGET"		 : (typeof MODULE != "undefined" ? MODULE.TARGET : '')
					};

		var	MODULES = <?php echo file_get_contents( CMS_SERVER_ROOT.DIR_DATA .'active-modules.json'); ?>;

	</script>

	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/toolkit.js"></script>
	
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-text-editor.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-headline-editor.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-object-tools.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-module-manager.js"></script>

	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/backend-edit.js"></script>
