<?php

	$pAvaiableTemplates	=	new CTemplates(CMS_SERVER_ROOT . DIR_TEMPLATES);
	$avaiableTemplates 	= 	$pAvaiableTemplates -> searchTemplates(true);

?>

<div class="be-module-container">

	<div class="language-select-container">
		<?php
		foreach(CLanguage::getLanguages() as $language)
		{
			if(!$language -> lang_frontend)
				continue;

			echo '<a class="trigger-language-select" data-language="'. $language -> lang_key .'"><span>'.  strtoupper($language -> lang_key) .'</span>'. $language -> lang_name .'</a>';
		}
		?>
	</div>


	<div id="page-header-overview">

		<div class="collapse">&nbsp;</div>

		<div class="page-name"><?= CLanguage::string('MOD_SITES_OV_TABLE_PAGETITLE'); ?></div>

		<div class="page-template"><?= CLanguage::string('MOD_SITES_OV_TABLE_TEMPLATE'); ?></div>
		<div class="page-option-a"></div>
		<div class="page-option-a"></div>
		<div class="page-option-a"></div>
		<div class="page-option-a"></div>

		<div class="page-update"><?= CLanguage::string('TIME_UPDATE_AT'); ?></div>

	</div>

	<div id="page-list-overview">

	</div>

</div>

<style>

	#page-header-overview,
	#page-list-overview	li > div.item { display:flex; width:100%;  }

	#page-list-overview	ul,
	#page-list-overview	li { list-style:none; margin:0px; padding:0px; }
	#page-list-overview	li { display:none;}
	#page-list-overview > ul > li,
	#page-list-overview	li.open > ul > li { display:block; }

	#page-list-overview	div.item .collapse { position:relative; margin-top:2px; height: 17px;  margin-right:6px; margin-left:3px; width:17px; cursor:pointer; }
	#page-list-overview	div.item .collapse:before { content: '+'; border:1px solid gray; padding:3px; background-color:white; width: 9px; height:9px; line-height: 9px;  position: absolute; text-align:center; }
	#page-list-overview	li.open > div.item .collapse:before { content: '-'; line-height: 8px; }
	#page-list-overview div.item .collapse[data-num-childs="0"],
	#page-list-overview > ul > li > div.item .collapse { opacity:0; pointer-events:none; }
	/*#page-list-overview ul > li { background-color:rgba(64, 128, 191, 10%); }*/

	#page-list-overview	div.item { padding: 4px 0px; font-size:0.86em; }

	#page-header-overview div.collapse { width:26px; }
	#page-header-overview div { font-size: 0.65em; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; padding: 4px 0px; }
	#page-header-overview { border-bottom: 1px solid rgb(80,80,80); }

	#page-list-overview	div.page-name,
	#page-header-overview div.page-name {  width: 25%; }

	#page-list-overview	div.page-template,
	#page-header-overview div.page-template {  width: 130px; }

	#page-list-overview	div.page-update,
	#page-header-overview div.page-update {  width: 150px; margin-left:10px; }

	#page-list-overview	div.page-option-a,
	#page-header-overview div.page-option-a { width:28px; text-align:center; }
	#page-list-overview	 a,
	#page-list-overview	 button { padding:2px 4px; border:0px; background:none; color: rgba(0, 0, 0, 15%); text-shadow:none; }
	#page-list-overview	 a:hover,
	#page-list-overview	 button:hover { color: rgba(0, 0, 0, 100%); }
	#page-list-overview	 a i,
	#page-list-overview	 button i { pointer-events:none; }

	#page-list-overview	div.item:hover { background-color:rgba(214,187,35,25%); }

	.language-select-container { display:flex; padding:10px 20px; margin-bottom:25px; }
	.language-select-container .trigger-language-select { border:1px solid grey; padding:3px 6px; padding-left:34px; position:relative; margin-right:10px; font-size:0.8em; }
	.language-select-container .trigger-language-select span { background:grey; color:white; position:absolute; display:block; left:0px; top:0px; height:100%; padding:3px 6px; font-weight:500; }

</style>

<template id="template-page-item">
	

	<div class="item" data-node-id="%NODE_ID%">
		<div class="collapse trigger-collapse" data-num-childs="%NUM_CHILDNODES%"></div>
		<div class="page-name">%PAGE_NAME% <a href="%PAGE_PATH%"><i class="fas fa-external-link-alt" style="font-size:0.9em"></i></a></div>
		<div class="page-template">%PAGE_TEMPLATE%</div>
		<div class="page-option-a"><a href="<?php echo CMS_SERVER_URL_BACKEND . $pageRequest -> urlPath; ?>view/%PAGE_LANGUAGE%/%NODE_ID%?language=<?= CLanguage::getActive(); ?>" target="_blank" class="button icon"><i class="fas fa-pen"></i></a></div>
		<div class="page-option-a"><button class="button icon trigger-add-subpage"><i class="fas fa-plus-square"></i></button></div>
		<div class="page-option-a">%BUTTON_MOVE%</div>
		<div class="page-option-a">%BUTTON_DELETE%</div>
		<div class="page-update">%UPDATE_TIME%</div>
	</div>

	
</template>

<script>

	let indexList 		 = null;
	let	activeLanguage 	 = '';
	let	languagesList    = <?= json_encode(CLanguage::getLanguages()); ?>;
	let openedNodeIdList = [];

	document.addEventListener("DOMContentLoaded", function() {

		for(let lang in languagesList)
		{
			activeLanguage = lang;
			break;
		}

		indexList = new cmsIndexList();
		indexList.init(languagesList, activeLanguage);
	});	

	document.addEventListener('click', function(event) {

		let element = event.target; 

		if(	element !== null && element.classList.contains('trigger-language-select')) onLanguageSelect(element, event);

		if(	element !== null && element.classList.contains('trigger-collapse'))	onToggleCollapse(element, event);

		if(	element !== null && element.classList.contains('trigger-delete-page')) onPageDelete(element, event);

		if(	element !== null && element.classList.contains('trigger-add-subpage')) onPageAdd(element, event);

		if(	element !== null && element.classList.contains('trigger-move-subpage')) onPageSubMove(element, event);
			
	}, false);


	function
	onPageSubMoveOKSuccess(xhrResponse, srcInfo)
	{

		console.log('onPageSubMoveOKSuccess');
		console.log(xhrResponse);

		if(xhrResponse.state !== 0)
			return;

		openedNodeIdList[ srcInfo.select.node_id ] = true;
		indexList.requestData(activeLanguage, openedNodeIdList);
	}

	function
	onPageSubMoveOK(event)
	{
		$xhrAction = 'movesub';

		let requestURL = CMS.SERVER_URL_BACKEND + CMS.PAGE_PATH + CMS.MODULE_TARGET + $xhrAction + '/' + event.detail.sourceNode.nodeId;

		let	formData  = new FormData;
			formData.append('new-parent-node-id', event.detail.select.node_id);

			cmsXhr.request(requestURL, formData, onPageSubMoveOKSuccess, event.detail, $xhrAction);
	}

	window.addEventListener('event-modal-select-new-parent-page', function(event) { onPageSubMoveOK(event); });

	function
	onPageSubMove(buttonNode, buttonEvent)
	{
		let nodeId = buttonNode.closest('div.item').getAttribute('data-node-id');
		
		let	modalNode = new cmsModalNode;
			modalNode.setEventNameOnSelected('event-modal-select-new-parent-page');
			modalNode.open(
				'<?= CLanguage::string('MOD_SITES_PAGEMOVE'); ?>', 
				{nodeId:nodeId, buttonNode:buttonNode}, 
				'fas fa-file',
				CMS.LANGUAGES
			);
	}

	function
	onPageAddOKSuccess(xhrResponse, srcInfo)
	{
		if(xhrResponse.state !== 0)
			return;

		openedNodeIdList[ srcInfo.nodeId ] = true;
		indexList.requestData(activeLanguage, openedNodeIdList);
	}

	function
	onPageAddOK(buttonEvent, modalInstance, srcInfo)
	{
		let fieldList = modalInstance.getFieldList();

		$xhrAction = 'create';

		let requestURL = CMS.SERVER_URL_BACKEND + CMS.PAGE_PATH + CMS.MODULE_TARGET + $xhrAction + '/' + activeLanguage + '/' + fieldList['modal-page-node-id'];

		let	formData  = new FormData;
			formData.append('page_name', fieldList['modal-page-name']);
			formData.append('page_description', fieldList['modal-page-description']);
			formData.append('page_template', fieldList['modal-page-template']);

			cmsXhr.request(requestURL, formData, onPageAddOKSuccess, srcInfo, $xhrAction);

		modalInstance.close(buttonEvent);
	}

	function
	onPageAdd(buttonNode, buttonEvent)
	{
		let nodeId = buttonNode.closest('div.item').getAttribute('data-node-id');

		let contentHTML = '';
		
			contentHTML += '<fieldset class="ui fieldset simply" style="background: unset; padding: 0px; margin-bottom: unset;">';
			contentHTML += '<input type="hidden" name="modal-page-node-id" value="'+ nodeId +'">';
			contentHTML += '<div>';
			contentHTML += '<div class="fields" style="width: 100%; display: flex; flex-wrap: wrap;">';
			contentHTML += '<div class="input width-100"><label><?= CLanguage::string('MOD_SITES_OV_TABLE_PAGENAME'); ?></label><input type="text" name="modal-page-name" value="" maxlength="100"></div>';
			contentHTML += '<div class="input width-100"><label><?= CLanguage::string('MOD_SITES_PAGEDESC'); ?></label><textarea name="modal-page-description" maxlength="160"></textarea></div>';
			contentHTML += `
			
					<div class="input width-100">
					<label><?= CLanguage::string('BEPE_PANEL_GROUP_TEMPLATE'); ?></label>
						<div class="select-wrapper">
						<select name="modal-page-template">
							<?php 
							foreach($pAvaiableTemplates -> getTemplates() as $_tmplIndex => $_tmplData)
							{
								echo '<option value="'. $_tmplIndex .'" '. ($_tmplIndex === $pageRequest -> page_template ? 'selected' : '') .'>'. $_tmplData -> template_name .'</option>';
							}
							?>
						</select>
						</div>
						<div class="result-box" data-field="page_template" data-error=""></div>
					</div>		
			`;
			contentHTML += '</div>';
			contentHTML += '</div>';
			contentHTML += '</fieldset>';

		let content = document.createElement('div');
			content.innerHTML = contentHTML;

		let modalA = new cmsModal;
			modalA	.addButton(new cmsModalCtrlButton(cmsModal.BTN_LOCATION.BOTTOM_LEFT, 'OK', onPageAddOK, 'fas fa-check', {srcButtonNode:buttonNode, nodeId:nodeId}))
					.addButton(new cmsModalCtrlButton(cmsModal.BTN_LOCATION.BOTTOM_LEFT, '<?= CLanguage::string('BUTTON_CANCEL'); ?>', null, 'fas fa-times'))
					.setTitle('<?= CLanguage::string('MOD_SITES_PAGECREATE'); ?>')
					.create(content)
					.open();
	}

	function
	onPageDeleteOKSuccess(xhrResponse, srcInfo)
	{
		if(xhrResponse.state !== 0)
			return;

		let listItemNode = srcInfo.srcButtonNode.closest('li');

		switch(srcInfo.xhrAction)
		{
			case 'delete':

				indexList.requestData(activeLanguage, openedNodeIdList);
				break

			case 'deletetree':

				listItemNode.remove();
				break
		}
	}

	function
	onPageDeleteOK(buttonEvent, modalInstance, srcInfo)
	{
		let fieldList = modalInstance.getFieldList();

		$xhrAction = '';

		switch(fieldList['modal-page-delete-type'])
		{
			case 'pagetree':

				$xhrAction = 'deletetree';
				break;

			case 'page':
			default:
			
				$xhrAction = 'delete';
				break;
		}

		srcInfo.xhrAction = $xhrAction;

		let requestURL = CMS.SERVER_URL_BACKEND + CMS.PAGE_PATH + CMS.MODULE_TARGET + $xhrAction + '/' + fieldList['modal-page-node-id'];

		let	formData  = new FormData;

			cmsXhr.request(requestURL, formData, onPageDeleteOKSuccess, srcInfo, $xhrAction);

		modalInstance.close(buttonEvent);
	}

	function
	onPageDelete(buttonNode, buttonEvent)
	{
		let nodeId = buttonNode.closest('div.item').getAttribute('data-node-id');

		let contentHTML = '';
			contentHTML += '<?= CLanguage::string('MOD_SITES_PAGEDELETECONFIRM'); ?>';
			contentHTML += '<fieldset style="margin-left:10px;margin-top:10px;">';
			contentHTML += '<input type="hidden" name="modal-page-node-id" value="'+ nodeId +'">';
			contentHTML += '<div style="margin-bottom: 4px;"><input type="radio" name="modal-page-delete-type" value="page" checked id="modal-page-delete-page"><label for="modal-page-delete-page"><?= CLanguage::string('MOD_SITES_DELETEONLYTHIS'); ?></label></div>';
			contentHTML += '<div style="margin-bottom: 4px;"><input type="radio" name="modal-page-delete-type" value="pagetree" id="modal-page-delete-pagetree"><label for="modal-page-delete-pagetree"><?= CLanguage::string('MOD_SITES_DELETEALL'); ?></label></div>';
			contentHTML += '</fieldset>';

		let content = document.createElement('div');
			content.innerHTML = contentHTML;

		let modalA = new cmsModal;
			modalA	.addButton(new cmsModalCtrlButton(cmsModal.BTN_LOCATION.BOTTOM_LEFT, '<?= CLanguage::string('BUTTON_DELETE'); ?>', onPageDeleteOK, 'fas fa-trash-alt', {srcButtonNode:buttonNode}))
					.addButton(new cmsModalCtrlButton(cmsModal.BTN_LOCATION.BOTTOM_LEFT, '<?= CLanguage::string('BUTTON_CANCEL'); ?>', null, 'fas fa-times'))
					.setTitle('<?= CLanguage::string('MOD_SITES_PAGEDELETE'); ?>', cmsModal.TITLE_STATE.RED)
					.create(content)
					.open();
	}

	function
	onToggleCollapse(element)
	{

		let liNode = element.closest('li');
		liNode.classList.toggle('open');

		let nodeId = liNode.querySelector('div.item').getAttribute('data-node-id');

		openedNodeIdList[ nodeId ] = liNode.classList.contains('open');

	}

	function
	onLanguageSelect(element,event)
	{
		event.stopPropagation();
		event.preventDefault();

		activeLanguage = element.getAttribute('data-language');
		indexList.requestData(activeLanguage, openedNodeIdList);

		return false;
	}
	
</script>

