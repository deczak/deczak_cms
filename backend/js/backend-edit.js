(function() {

	var	pEditorText = new cmsTextEditor();	
		pEditorText.init('editor-simple-text', CMS.SERVER_URL_BACKEND +'json/editor-text.json', 'simple-text');
		pEditorText.create();

	var	pEditorHeadline = new cmsHeadlineEditor();	
		pEditorHeadline.init('editor-simple-headline', CMS.SERVER_URL_BACKEND +'json/editor-headline.json', 'simple-text');
		pEditorHeadline.create();

	var	pObjectTools = new cmsObjectTools();
		pObjectTools.init(CMS.SERVER_URL_BACKEND + CMS.PAGE_PATH + CMS.MODULE_TARGET);
		pObjectTools.create();

	var	pEditorCode = new cmsCodeEditor();	
		pEditorCode.init('editor-simple-code', 'simple-text');
		pEditorCode.create();

	var	pModuleManager = new cmsModuleManager();
		pModuleManager.init('cms-edit-content-container', MODULES, {pEditorText, pEditorHeadline, pEditorCode, pObjectTools});
		pModuleManager.create();

	var	pUiSelect = new cmsUiSelect();
		pUiSelect.init();
		pUiSelect.create();

	var	pPageEdit = new cmsPageEdit();
		pPageEdit.init('trigger-submit-site-edit');
	
}());	