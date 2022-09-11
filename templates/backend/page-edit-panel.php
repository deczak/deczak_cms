<?php

	require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelLoginObjects.php';
	require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelCategories.php';
	require_once CMS_SERVER_ROOT.DIR_CORE.DIR_MODELS.'modelTags.php';

	$pAvaiableTemplates	=	new CTemplates(CMS_SERVER_ROOT . DIR_TEMPLATES);
	$avaiableTemplates 	= 	$pAvaiableTemplates -> searchTemplates(true);

	$pDatabase = CDatabase::instance() -> getConnection(CFG::GET() -> MYSQL -> PRIMARY_DATABASE);

	##	get edit right ... language > page-module > frontend-module

	$enableEdit = $pageRequest -> enablePageEdit;	// Page-Module right
	$enableEdit = ((!empty($pageRequest -> languageInfo) && !$pageRequest -> languageInfo -> lang_locked) ? $enableEdit : false);	// Language lock flag

?>

<div id="be-page-panel">

	<input type="checkbox" id="trigger-page-panel-slider" style="display:none !important; opacity:0 !important;" value="">
	<label for="trigger-page-panel-slider" id="be-page-panel-slider">&nbsp;</label>
		
	<div id="be-page-panel-content" data-xhr-target="update-site" data-xhr-overwrite-target="edit/<?php echo $pageRequest -> page_language; ?>/<?php echo $pageRequest -> node_id; ?>">
		
		<div class="backend-title-container">
			<a href="<?php echo CMS_SERVER_URL_BACKEND; ?>"><b>BACKYARD</b> // SYSTEM</a>		
			<a class="yellow" style="display:block; margin-top:5px; font-size:0.95em;" href="<?php echo CMS_SERVER_URL_BACKEND . $_pageRequest['origin_index']; ?>"><?= CLanguage::string('BEPE_PANEL_BACKLINK'); ?></a>			
		</div>
		
		<div style="height:calc(100% - 142px); overflow-y:auto; overflow-x:hidden; background-color:rgba(74,74,74,1);">
				
			<!-- page information -->

			<fieldset class="ui fieldset submit-able">
				<legend><?= CLanguage::string('BEPE_PANEL_GROUP_INFORMATION'); ?></legend>
				<div>
					<table id="table-page-informationen">
						<tbody>
							<tr>
								<td><?= CLanguage::string('BEPE_PANEL_NODEID'); ?></td>
								<td><?php echo $pageRequest -> node_id; ?></td>
							</tr>
							<tr>
								<td><?= CLanguage::string('BEPE_PANEL_PAGEID'); ?></td>
								<td><?php echo $pageRequest -> page_id; ?></td>
							</tr>
							<tr>
								<td><?= CLanguage::string('BEPE_PANEL_LANGUAGE'); ?></td>
								<td><?php echo $pageRequest -> page_language; ?></td>
							</tr>
						</tbody>
					</table>
				</div>



			</fieldset>		
					
			<!-- page name & description -->

			<fieldset class="ui fieldset submit-able">
				<legend><?= CLanguage::string('BEPE_PANEL_GROUP_NAME'); ?></legend>
				<div>

					<div class="input width-100">
						<label><?= CLanguage::string('BEPE_PANEL_PAGENAME'); ?></label>
						<input type="text" name="page_name" value="<?php echo $pageRequest -> page_name; ?>">
					</div>

					<div class="result-box" data-field="page_name" data-error=""></div>

					<div class="input width-100">
						<label><?= CLanguage::string('BEPE_PANEL_PAGETITLE'); ?></label>
					<input type="text" name="page_title" value="<?php echo $pageRequest -> page_title; ?>">
					</div>

					<div class="result-box" data-field="page_title" data-error=""></div>

					<div class="input width-100">
						<label><?= CLanguage::string('BEPE_PANEL_PAGEDESCRIPTION'); ?></label>
						<textarea name="page_description"><?php echo $pageRequest -> page_description; ?></textarea>
					</div>

					<div class="result-box" data-field="page_description" data-error=""></div>

					<div class="input width-100">
						<label><?= CLanguage::string('BEPE_PANEL_CRUMBNAME'); ?></label>
						<input type="text" name="crumb_name" value="<?php echo $pageRequest -> crumb_name; ?>">
					</div>

					<div class="result-box" data-field="crumb_name" data-error=""></div>

				</div>
			</fieldset>		

			<!-- page template select -->

			<fieldset class="ui fieldset submit-able">
				<legend><?= CLanguage::string('BEPE_PANEL_GROUP_TEMPLATE'); ?></legend>
				<div style="padding-top:10px;">
					<div class="input width-100">
						<div class="select-wrapper">
						<select name="page_template">
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

				</div>
			</fieldset>	


			<!-- page image -->

			<fieldset class="ui fieldset submit-able">
				<legend><?= CLanguage::string('BEPE_PANEL_GROUP_IMAGE'); ?></legend>
				<div style="padding-top:10px;">

					<div class="input width-100" id="panel-site-image-selector">

						<div>

							<div class="imagebox-def-bg"><div class="imagebox-preview" style="background-image:url('<?= $pageRequest -> page_image_url; ?>')"></div></div>


							<input type="hidden"  id="panel-site-image-id" name="page_image" value="<?php echo $pageRequest -> page_image; ?>">

							<div  class="button-panel">
								<button class="ui button icon labeled button-select-mediathek-item" id="trigger-panel-siteimage-select"><span><i class="far fa-image"></i></span>Select Image</button> 
								<button class="ui button icon button-remove-mediathek-item" id="trigger-panel-siteimage-remove"><i class="far fa-trash-alt"></i></button> 
							</div>

						</div>


						<script>

							document.getElementById('trigger-panel-siteimage-select').onclick = function()
							{

								let mediathek = new cmsModalMediathek;
									mediathek.setEventNameOnSelected('test-mediathek-on-selected');
									mediathek.open(cmsMediathek.VIEWMODE_LIST, cmsMediathek.WORKMODE_SELECT);

							};

							document.getElementById('trigger-panel-siteimage-remove').onclick = function()
							{

								let previewBox = document.getElementById('panel-site-image-selector').querySelector('.imagebox-preview');
									previewBox.style.backgroundImage = "";

								document.getElementById('panel-site-image-id').value = 0;

							};

							function testIt(event)
							{
								if(event.detail.file === null || event.detail.file.path.length === 0)
									return;
								

								let previewBox = document.getElementById('panel-site-image-selector').querySelector('.imagebox-preview');
									previewBox.style.backgroundImage = "url('"+ CMS.SERVER_URL + "mediathek/" + event.detail.file.path +"?binary&size=small')";
								
								document.getElementById('panel-site-image-id').value = event.detail.file.media_id;
							}

							window.addEventListener('test-mediathek-on-selected', testIt);

						</script>

						<style>

							#panel-site-image-selector .button-panel {

								display:flex;

								}

							#panel-site-image-selector .imagebox-def-bg {

								background: #445963;
								background-image: 
									linear-gradient(transparent 11px, rgba(220,220,200,.8) 12px, transparent 12px),
									linear-gradient(90deg, transparent 11px, rgba(220,220,200,.8) 12px, transparent 12px);
								background-size: 100% 12px, 12px 100%;
								width:100%;
								height:170px;
								margin:0px;
								border: 1px solid rgba(220,220,200,.8);
								
								}

							#panel-site-image-selector .button-select-mediathek-item {

								width:100%; 
								border: 0px; 
								border-top-left-radius:0px !important; 
								border-top-right-radius:0px !important; 
								border-bottom-right-radius:0px !important;

								}

							#panel-site-image-selector .button-remove-mediathek-item {

								border: 0px; 
								width:40px; 
								background:red; 
								flex-shrink:0; 
								color:white;
								border-top-left-radius:0px !important; 
								border-top-right-radius:0px !important; 
								border-bottom-left-radius:0px !important;

								}

							#panel-site-image-selector .imagebox-preview {

								width:100%;
								height:100%;
								background-position:center center;
								background-size:contain;
    							background-repeat:no-repeat;

								}

						</style>


					</div>
						

				</div>
			</fieldset>	



			<!-- visibilty settings -->

			<fieldset class="ui fieldset submit-able">
				<legend><?= CLanguage::string('BEPE_PANEL_GROUP_VISIBILTY'); ?></legend>
				<div style="padding-top:10px;">
					<div class="input width-100">
						<label><?= CLanguage::string('BEPE_PANEL_ACCESSLEVEL'); ?></label>
						<div class="select-wrapper">
						<?php
						if($pageRequest -> page_path == '/')
							echo '<input type="hidden" name="hidden_state" value="0">';
						?>
						<select name="hidden_state" id="select-visibleState" <?= ($pageRequest -> page_path == '/' ? 'disabled' : ''); ?>>
							<option value="0" <?= ($pageRequest -> hidden_state == 0 ? 'selected' : ''); ?>><?= CLanguage::string('BEPE_PANEL_HIDDEN_0_VISIBLE'); ?></option>
							<option value="5" <?= ($pageRequest -> hidden_state == 5 ? 'selected' : ''); ?>><?= CLanguage::string('BEPE_PANEL_HIDDEN_5_VISIBLEFROM'); ?></option>
							<option value="1" <?= ($pageRequest -> hidden_state == 1 ? 'selected' : ''); ?>><?= CLanguage::string('BEPE_PANEL_HIDDEN_1_LOCKED'); ?></option>
							<option value="4" <?= ($pageRequest -> hidden_state == 4 ? 'selected' : ''); ?>><?= CLanguage::string('BEPE_PANEL_HIDDEN_4_LOCKED'); ?></option>
							<option value="2" <?= ($pageRequest -> hidden_state == 2 ? 'selected' : ''); ?>><?= CLanguage::string('BEPE_PANEL_HIDDEN_2_HIDDEN'); ?></option>
							<!--<option value="3" <?= ($pageRequest -> hidden_state == 3 ? 'selected' : ''); ?>><?= CLanguage::string('BEPE_PANEL_HIDDEN_3_REGISTERED'); ?></option>-->
						</select>
						</div>
						<div class="result-box" data-field="hidden_state" data-error=""></div>
					</div>


			

				<div id="publish_settings" <?= ($pageRequest -> hidden_state != 5 ? 'style="display:none;"' : ''); ?>>

					<?php
						$pageRequest -> publish_from = ($pageRequest -> publish_from != 0 ? date('Y-m-d', $pageRequest -> publish_from) : '');
						$pageRequest -> publish_until = ($pageRequest -> publish_until != 0 ? date('Y-m-d', $pageRequest -> publish_until) : '');
					?>
		

					<div class="input width-100">
						<label><?= CLanguage::string('BEPE_PANEL_PUBLISHFROM'); ?></label>
						<input type="text" class="datepicker-from" name="publish_from" id="" value="<?php echo $pageRequest -> publish_from; ?>">
					</div>

					<div class="result-box" data-field="publish_from" data-error=""></div>

					<div class="input width-100">
						<label><?= CLanguage::string('BEPE_PANEL_PUBLISHUNTIL'); ?></label>
						<input type="text" class="datepicker-until" name="publish_until" id="" value="<?php echo $pageRequest -> publish_until; ?>">
					</div>

					<div class="result-box" data-field="publish_until" data-error=""></div>

					<div class="input width-100">
						<label><?= CLanguage::string('BEPE_PANEL_PUBLISHUNTIL_STATE'); ?></label>
						<div class="select-wrapper">
						<select name="publish_expired" <?= ($pageRequest -> page_path == '/' ? 'disabled' : ''); ?>>
							<option value="0" <?= ($pageRequest -> publish_expired == 0 ? 'selected' : ''); ?>><?= CLanguage::string('BEPE_PANEL_PUBLISHUNTIL_0_HIDDEN'); ?></option>
							<option value="1" <?= ($pageRequest -> publish_expired == 1 ? 'selected' : ''); ?>><?= CLanguage::string('BEPE_PANEL_PUBLISHUNTIL_1_LOCKED'); ?></option>
						</select>
						</div>
					</div>

					<div class="result-box" data-field="publish_expired" data-error=""></div>

					<script>
					document.getElementById('select-visibleState').onchange = function() {
						var	publishBox = document.getElementById('publish_settings');

						if(this.value == 5)
						{
							publishBox.style.display = 'block';

							publishBox.querySelectorAll('select').forEach(function(element, key){
								element.disabled = false;
							});

							publishBox.querySelectorAll('input').forEach(function(element, key){
								element.disabled = false;
							});
						}
						else
						{
							publishBox.style.display = 'none';

							publishBox.querySelectorAll('select').forEach(function(element, key){
								element.disabled = true;
							});

							publishBox.querySelectorAll('input').forEach(function(element, key){
								element.disabled = true;
							});
						}
					};

					flatpickr('.datepicker-from',{
						dateFormat: 'Y-m-d'
					});
					flatpickr('.datepicker-until',{
						dateFormat: 'Y-m-d'
					});
					</script>

					<style>
					.flatpickr-calendar select,
					.flatpickr-calendar input { padding:0px 6px !important; box-shadow:none !important; font-size:14px !important; height: 24px !important; }
					.flatpickr-calendar select{ -moz-appearance: initial !important; appearance: initial !important; }
					.flatpickr-calendar input { -moz-appearance: textfield !important; appearance: textfield !important; }
					.flatpickr-calendar .flatpickr-months,
					.flatpickr-calendar .flatpickr-weekdays { background:rgba(233,223,37,0.8); }
					.flatpickr-calendar .flatpickr-months { border-top-left-radius:5px; border-top-right-radius:5px; }
					.flatpickr-calendar { border: 1px solid grey; }
					.flatpickr-calendar .flatpickr-day { border-radius:3px !important; }
					</style>

				</div>






					<div class="input width-100">
						<label><?= CLanguage::string('BEPE_PANEL_AUTHOBJECT'); ?></label>
						<div class="select-wrapper">
						<?php
						if($pageRequest -> page_path == '/')
							echo '<input type="hidden" name="page_auth" value="0">';
						?>
						<select name="page_auth" id="select-authobject" <?= ($pageRequest -> page_path == '/' ? 'disabled' : ''); ?>>
							<option value="0" <?= ($pageRequest -> page_auth == 0 ? 'selected' : ''); ?>><?= CLanguage::string('BEPE_PANEL_AUTHOBJECT_0_NONE'); ?></option>
							<?php
							$modelLoginObjects	 = new modelLoginObjects();
							$modelLoginObjects	-> load($pDatabase);
							$loginObjectsList 	 = $modelLoginObjects -> getResult();
							foreach($loginObjectsList as $loginObject)
								echo '<option value="'. $loginObject-> object_id .'" '. ($pageRequest -> page_auth == $loginObject-> object_id ? 'selected' : '') .'>'. $loginObject-> object_id .'</option>';
							?>
						</select>
						</div>
						<div class="result-box" data-field="page_auth" data-error=""></div>
					</div>


					<div class="input width-100">

					<input type="checkbox" name="apply_childs_auth" value="1" id="apply_childs_auth">
					<label for="apply_childs_auth"><?= CLanguage::string('BEPE_PANEL_AUTHAPPLYCHILDS'); ?></label>	



			</fieldset>				
		
			<!-- search machines settings -->

			<fieldset class="ui fieldset submit-able">
				<legend><?= CLanguage::string('BEPE_PANEL_GROUP_SEARCHMACHINE'); ?></legend>
				<div style="padding-top:10px;">
					<div class="input width-100">
						<div class="select-wrapper">
						<select name="crawler_index">
							<option value="1" <?= ($pageRequest -> crawler_index == 1 ? 'selected' : ''); ?>><?= CLanguage::string('BEPE_PANEL_CRAWLER_1_INDEX'); ?></option>
							<option value="0" <?= ($pageRequest -> crawler_index == 0 ? 'selected' : ''); ?>><?= CLanguage::string('BEPE_PANEL_CRAWLER_0_NOINDEX'); ?></option>
						</select>
						</div>
						<div class="result-box" data-field="crawler_index" data-error=""></div>
					</div>
					<div class="input width-100">
						<div class="select-wrapper">
						<select name="crawler_follow">
							<option value="1" <?= ($pageRequest -> crawler_follow == 1 ? 'selected' : ''); ?>><?= CLanguage::string('BEPE_PANEL_CRAWLER_1_FOLLOW'); ?></option>
							<option value="0" <?= ($pageRequest -> crawler_follow == 0 ? 'selected' : ''); ?>><?= CLanguage::string('BEPE_PANEL_CRAWLER_0_NOFOLLOW'); ?></option>
						</select>
						</div>
						<div class="result-box" data-field="crawler_follow" data-error=""></div>
					</div>
					<div class="input width-100">
						<div class="select-wrapper">
						<select name="menu_follow">
							<option value="1" <?= ($pageRequest -> menu_follow == 1 ? 'selected' : ''); ?>><?= CLanguage::string('BEPE_PANEL_CRAWLER_1_MENUFOLLOW'); ?></option>
							<option value="0" <?= ($pageRequest -> menu_follow == 0 ? 'selected' : ''); ?>><?= CLanguage::string('BEPE_PANEL_CRAWLER_0_MENUNOFOLLOW'); ?></option>
						</select>
						</div>
						<div class="result-box" data-field="menu_follow" data-error=""></div>
					</div>
				</div>
			</fieldset>		

			<!-- categories & tags -->

			<fieldset class="ui fieldset submit-able">
				<legend><?= CLanguage::string('BEPE_PANEL_GROUP_TAGSCATS'); ?></legend>
				<div style="padding-top:10px;">

					<?php
					$selectedCategories = [];
					foreach($pageRequest -> page_categories as $category)
						$selectedCategories[] = $category['id'];
					?>

					<div class="input width-100">
						<label><?php echo CLanguage::string('BEPE_PANEL_CATEGORIES'); ?></label>
						<div class="select-wrapper">
						<select name="page_categories[]" class="dropdown" data-preset="<?= implode(',', $selectedCategories); ?>">
							<option value=""></option>
							<?php
							$modelCategories	 = new modelCategories();
							$modelCategories	-> load($pDatabase);
							$categoriesList 	 = $modelCategories -> getResult();
							foreach($categoriesList as $category)
								echo '<option value="'. $category -> category_id .'">'. $category -> category_name .'</option>';
							?>
						</select>		
						</div>
					</div>

					<?php
					$selectedTags = [];
					foreach($pageRequest -> page_tags as $tag)
						$selectedTags[] = $tag['id'];
					?>

					<div class="input width-100">
						<label><?php echo CLanguage::string('BEPE_PANEL_TAGS'); ?></label>
						<div class="select-wrapper">
						<select name="page_tags[]" class="dropdown" data-preset="<?= implode(',', $selectedTags); ?>">
							<option value=""></option>
							<?php
							$modelTags	 = new modelTags();
							$modelTags	-> load($pDatabase);
							$tagsList 	 = $modelTags -> getResult();
							foreach($tagsList as $tag)
								echo '<option value="'. $tag -> tag_id .'">'. $tag -> tag_name .'</option>';
							?>
						</select>		
						</div>
					</div>

				</div>
			</fieldset>	

			<?php

			// Injected modules settings
				
			cmsSystemModules::instance() -> call(cmsSystemModules::SECTION_TOOLBAR);

			?>

			<!-- extended settings -->

			<fieldset class="ui fieldset submit-able">
				<legend><?= CLanguage::string('BEPE_PANEL_GROUP_EXTSETTINGS'); ?></legend>
				<div style="padding-top:10px;">
					<div class="input width-100">
						<label><?= CLanguage::string('BEPE_PANEL_BROWSERCACHING'); ?></label>
						<div class="select-wrapper">
						<select name="cache_disabled">
							<option value="0" <?= ($pageRequest -> cache_disabled == 0 ? 'selected' : ''); ?>><?= CLanguage::string('BEPE_PANEL_CACHING_0_ENABLED'); ?></option>
							<option value="1" <?= ($pageRequest -> cache_disabled == 1 ? 'selected' : ''); ?>><?= CLanguage::string('BEPE_PANEL_CACHING_1_DISABLED'); ?></option>
						</select>
						</div>
						<div class="result-box" data-field="cache_disabled" data-error=""></div>
					</div>
				</div>
			</fieldset>	

			<!-- alternate languages -->

			<fieldset class="ui fieldset submit-able">
				<legend><?= CLanguage::string('BEPE_PANEL_GROUP_ALTLANG'); ?></legend>


				<?php if($pageRequest -> page_id != 1) { ?>

					<div style="padding-top:10px;" id="panel-alternate-language">

						<div class="input width-100">
							<label><?= CLanguage::string('BEPE_PANEL_ALTLANGNODEID'); ?></label>
							<input type="text" name="page_id" id="alt_page_id" value="">
						</div>

						<div class="result-box" data-field="page_id" data-error=""></div>






					</div>


	



					<hr>

				<?php } else { echo '<div style="padding-top:10px;"></div>'; } ?>

				<div class="input width-100" id="alterante-container" style="display:block;"></div>

				<script>

					function
					updateAlternateList(pageId)
					{
						var formData 		= new FormData();
							formData.append('cms-xhrequest', 'raw-alternate');
							formData.append('page_id', pageId);

						var	requestTarget	= CMS.SERVER_URL_BACKEND + 'pages/';

						cmstk.callXHR(requestTarget, formData, onSuccessRetrieveAlternates, cmstk.onXHRError, this, 'indexAlt');
					}

					function
					onSuccessRetrieveAlternates(response, instance)
					{
						if(response.state != 0)
						{
							return;
						}

						let nodeId = <?= $pageRequest -> node_id; ?>;

						let altList = document.getElementById('alterante-container');
							altList.innerHTML = '';

						let numObjects 	= Object.keys(response.data).length;

						let numValid = 0;

						for(let i = 0; i < numObjects; i++)
						{
							if(nodeId === response.data[i].node_id)
								continue;

							let	link = document.createElement('a');
								link.setAttribute('href', CMS.SERVER_URL_BACKEND + 'pages/view/' + response.data[i].page_language + '/' + response.data[i].node_id);
								link.classList.add('yellow');
								link.style.fontSize = '0.9em';
								link.style.display = 'block';
								link.innerHTML = '<span style="display:inline-block; width:20px;">' + response.data[i].page_language.toUpperCase() + '</span> | ' + response.data[i].page_name;

								altList.append(link);

							numValid++;
						}

						if(numValid == 0)
							altList.innerHTML = '<span style="font-size:0.75em;"><?= CLanguage::string('BEPE_PANEL_NOALTLANGEXIST'); ?></span>';
					}

					document.addEventListener("DOMContentLoaded", function() {
						window.updateAlternateList('<?= $pageRequest -> page_id; ?>');
					});	
					
				</script>
				
			</fieldset>	

			<!-- redirect settings -->

			<fieldset class="ui fieldset submit-able">
				<legend><?= CLanguage::string('BEPE_PANEL_GROUP_REDIRECT'); ?></legend>

					<div style="padding-top:10px;" id="panel-redirect-selection">

						<div class="input width-100 append-button-panel">
							<label><?= CLanguage::string('BEPE_PANEL_REDIRECTNODEID'); ?></label>

							<div class="input-inner">

								<input type="hidden" name="page_redirect" id="page_redirect_node_id" value="<?= $pageRequest -> page_redirect; ?>">
								
								<input type="text" id="page_redirect_node_name" readonly value="<?= (!empty($pageRequest -> page_redirect) ? '['. $pageRequest -> page_redirect .']' : '') ?>">

								<div class="button-panel">
									<button class="ui button icon button-select button-select-redirect-node"><span><i class="fas fa-ellipsis-h"></i></span></button> 
									<button class="ui button icon button-remove button-remove-redirect-node"><i class="far fa-trash-alt"></i></button> 
								</div>

							</div>

						</div>





						<style>

							#panel-alternate-language .button-panel { display:flex; }
							#panel-alternate-language .button-panel span,
							#panel-alternate-language .button-panel i { pointer-events:none; }

							#panel-alternate-language .button-select { border:0px; flex-shrink:0; border-radius:0px !important; }
							#panel-alternate-language .button-remove { border:0px; background:red; flex-shrink:0; color:white; border-top-left-radius: 0px !important; border-bottom-left-radius:0px !important; }

							#panel-redirect-selection .button-panel { display:flex; }
							#panel-redirect-selection .button-panel span,
							#panel-redirect-selection .button-panel i { pointer-events:none; }

							#panel-redirect-selection .button-select { border:0px; flex-shrink:0; border-radius:0px !important; }
							#panel-redirect-selection .button-remove { border:0px; background:red; flex-shrink:0; color:white; border-top-left-radius: 0px !important; border-bottom-left-radius:0px !important; }


						</style>



						<div class="result-box" data-field="page_redirect" data-error=""></div>

					</div>



					
				
			</fieldset>	

		</div>

		<div id="be-page-panel-submit">

			<?php
			if($enableEdit)
				echo '<button class="ui button labeled icon trigger-submit-site-edit" id="trigger-submit-site-edit" type="button"><span><i class="fas fa-save" data-icon="fa-save"></i></span>'.  CLanguage::string('BUTTON_SAVE') .'</button>';
			?>

		</div>	

	</div>

</div>	


<script> // Select Nodes for redirect
	
	// Redirect Node

	function openModalSelectRedirectNodeSuccess(event)
	{
		document.getElementById('page_redirect_node_id').value   = event.detail.select.node_id
		document.getElementById('page_redirect_node_name').value = '['+ event.detail.select.node_id +'] '+ event.detail.select.name;
	}

	function openModalSelectRedirectNode()
	{

		let	modalNode = new cmsModalNode;
			modalNode.setEventNameOnSelected('event-page-panel-redirect-node-select-selected');
			modalNode.open(
				'Select page as redirect target', 
				this, 
				'fas fa-file',
				<?= json_encode(CLanguage::getLanguages()) ?>
			);
	}

	function openModalRemoveRedirectNode()
	{
		document.getElementById('page_redirect_node_id').value   = '';
		document.getElementById('page_redirect_node_name').value = '';		
	}

	// Listener

	window.addEventListener('click', function(event)
	{
		if(typeof event.target === 'undefined')
			return false;

		let eventTagName = event.target.tagName;

		switch(eventTagName)
		{
			case 'BUTTON':

				if(event.target.classList.contains('button-select-redirect-node'))
				{
					openModalSelectRedirectNode();
					return true;
				}

				if(event.target.classList.contains('button-remove-redirect-node'))
				{
					openModalRemoveRedirectNode();
					return true;
				}

				break;
		}
	});

	window.addEventListener('event-page-panel-redirect-node-select-selected', openModalSelectRedirectNodeSuccess);

</script>
