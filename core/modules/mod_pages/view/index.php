<?php

	$pAvaiableTemplates	=	new CTemplates(CMS_SERVER_ROOT . DIR_TEMPLATES_PAGE);
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


		<div>

		<div class="collapse">&nbsp;</div>
		<div class="page-name"><?= CLanguage::string('MOD_SITES_OV_TABLE_PAGETITLE'); ?></div>


		</div>
		
		<div>

		<div class="page-template"><?= CLanguage::string('MOD_SITES_OV_TABLE_TEMPLATE'); ?></div>
		<div class="page-option-a"></div>
		<div class="page-option-a"></div>
		<div class="page-option-a"></div>
		<div class="page-option-a"></div>

		<div class="page-update"><?= CLanguage::string('TIME_UPDATE_AT'); ?></div>

		</div>
		
	</div>

	<div id="page-list-overview">

	</div>

</div>


<template id="template-page-item">
	

	<div class="item" data-node-id="%NODE_ID%">

		<div>

		<div class="collapse trigger-collapse" data-num-childs="%NUM_CHILDNODES%"></div>
		<div class="page-name">%PAGE_NAME% <a href="%PAGE_PATH%"><i class="fas fa-external-link-alt" style="font-size:0.9em" title="Public link"></i></a></div>

		</div>
		
		<div>

		<div class="page-template">%PAGE_TEMPLATE%</div>
		<div class="page-option-a"><a href="<?php echo CMS_SERVER_URL_BACKEND . $pageRequest -> urlPath; ?>view/%PAGE_LANGUAGE%/%NODE_ID%?language=<?= CLanguage::getActive(); ?>" target="_blank" class="button icon"><i class="fas fa-pen"></i></a></div>
		<div class="page-option-a"><button class="button icon trigger-add-subpage"><i class="fas fa-plus-square"></i></button></div>
		<div class="page-option-a">%BUTTON_MOVE%</div>
		<div class="page-option-a">%BUTTON_DELETE%</div>
		<div class="page-update">%UPDATE_TIME%</div>

		</div>

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

