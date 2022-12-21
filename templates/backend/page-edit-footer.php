
<?php

if($enableEdit)
	$modules = CModules::instance() -> getModules(true);
else
	$modules = [];

?>

	<script>

		var	CMS = 	{
	 					"SERVER_URL" 		 : "<?php echo CMS_SERVER_URL; ?>",
						"SERVER_URL_BACKEND" : "<?php echo CMS_SERVER_URL_BACKEND; ?>",
						"PAGE_PATH" 		 : "<?php echo $pageRequest -> urlPath; ?>",
						"MODULE_TARGET"		 : (typeof MODULE != "undefined" ? MODULE.TARGET : ''),
						"LANGUAGES"		 	 : <?= json_encode(CLanguage::getLanguages()) ?>
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


	<!-- v2 js, v1 will be removed if v2 finshed, folder gets renamed to js -->

	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js2/cms-toolkit.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js2/cms-crypt.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js2/cms-xhr.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js2/cms-upload.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js2/cms-modal.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js2/cms-modal-mediathek.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js2/cms-modal-confirm.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js2/cms-modal-path.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js2/cms-modal-node.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js2/cms-mediathek.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js2/cms-forms.js"></script>
	<script src="<?php echo CMS_SERVER_URL_BACKEND; ?>js2/cms-index.js"></script>
	
	<script>
	(function() {

		var	pEditorText = new cmsTextEditor();	
			pEditorText.init('editor-simple-text', CMS.SERVER_URL_BACKEND +'json/editor-text.json', 'simple-text');
			pEditorText.create();

		var	pEditorHeadline = new cmsHeadlineEditor();	
			pEditorHeadline.init('editor-simple-headline', CMS.SERVER_URL_BACKEND +'json/editor-headline.json', 'simple-text');
			pEditorHeadline.create();

		var	pEditorCode = new cmsCodeEditor();	
			pEditorCode.init('editor-simple-code', 'simple-text');
			pEditorCode.create();

		<?php if($enableEdit) { ?>

		var	pObjectTools = new cmsObjectTools();
			pObjectTools.init(CMS.SERVER_URL_BACKEND + CMS.PAGE_PATH + CMS.MODULE_TARGET);
			pObjectTools.create();

		var	pModuleManager = new cmsModuleManager();
			pModuleManager.init('cms-edit-content-container', MODULES, {pEditorText, pEditorHeadline, pEditorCode, pObjectTools});
			pModuleManager.create();

		<?php } ?>

		var	pUiSelect = new cmsUiSelect();
			pUiSelect.init();
			pUiSelect.create();

		var	pPageEdit = new cmsPageEdit();
			pPageEdit.init('trigger-submit-site-edit');
		
	}());	
	</script>