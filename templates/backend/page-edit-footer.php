
<?php

$modules = CModules::instance() -> getModules();

?>

	<script>

		var	CMS = 	{
	 					"SERVER_URL" 		 : "<?php echo CMS_SERVER_URL; ?>",
						"SERVER_URL_BACKEND" : "<?php echo CMS_SERVER_URL_BACKEND; ?>",
						"PAGE_PATH" 		 : "<?php echo $pageRequest -> urlPath; ?>",
						"MODULE_TARGET"		 : (typeof MODULE != "undefined" ? MODULE.TARGET : '')
					};

		var	MODULES = <?php echo json_encode($modules); ?>;

	</script>

	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/toolkit.js"></script>
	
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-text-editor.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-headline-editor.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-code-editor.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-object-tools.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-module-manager.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-ui-select.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/classes/cms-page-edit.js"></script>
	
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js/backend-edit.js"></script>
