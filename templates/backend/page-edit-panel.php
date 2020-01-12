<?php

	$pAvaiableTemplates	=	new CTemplates(CMS_SERVER_ROOT . DIR_TEMPLATES);
	$avaiableTemplates 	= 	$pAvaiableTemplates -> searchTemplates(true);

?>

<div id="be-page-panel">

	
	<input type="checkbox" id="trigger-page-panel-slider" style="display:none !important; opacity:0 !important;" value="">
	<label for="trigger-page-panel-slider" id="be-page-panel-slider">&nbsp;</label>
		
	<div id="be-page-panel-content" data-xhr-target="update-site" data-xhr-overwrite-target="edit/<?php echo $pageRequest -> page_language; ?>/<?php echo $pageRequest -> node_id; ?>">
		
	<div class="backend-title-container">
			<a href="<?php echo CMS_SERVER_URL_BACKEND; ?>"><b>BACKYARD</b> // SYSTEM</a>		
			<a class="yellow" style="display:block; margin-top:5px; font-size:0.95em;" href="<?php echo CMS_SERVER_URL_BACKEND . $_pageRequest['origin_index']; ?>"><?= CLanguage::GET() -> STRING('BEPE_PANEL_BACKLINK'); ?></a>			
		</div>
		

		<div style="height:calc(100% - 142px); overflow-y:auto; overflow-x:hidden; background-color:rgba(74,74,74,1);">

	

		
		<fieldset class="ui fieldset submit-able">
			<legend><?= CLanguage::GET() -> STRING('BEPE_PANEL_GROUP_INFORMATION'); ?></legend>
			<div>
				<table id="table-page-informationen">
					<tbody>
						<tr>
							<td><?= CLanguage::GET() -> STRING('BEPE_PANEL_NODEID'); ?></td>
							<td><?php echo $pageRequest -> node_id; ?></td>
						</tr>
						<tr>
							<td><?= CLanguage::GET() -> STRING('BEPE_PANEL_PAGEID'); ?></td>
							<td><?php echo $pageRequest -> page_id; ?></td>
						</tr>
						<tr>
							<td><?= CLanguage::GET() -> STRING('BEPE_PANEL_LANGUAGE'); ?></td>
							<td><?php echo $pageRequest -> page_language; ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</fieldset>		
		


 
		
		<fieldset class="ui fieldset submit-able">
			<legend><?= CLanguage::GET() -> STRING('BEPE_PANEL_GROUP_NAME'); ?></legend>
			<div>

				<div class="input width-100">
					<label><?= CLanguage::GET() -> STRING('BEPE_PANEL_PAGENAME'); ?></label>
					<input type="text" name="page_name" value="<?php echo $pageRequest -> page_name; ?>">
				</div>

				<div class="result-box" data-field="page_name" data-error=""></div>

				<div class="input width-100">
					<label><?= CLanguage::GET() -> STRING('BEPE_PANEL_PAGETITLE'); ?></label>
				<input type="text" name="page_title" value="<?php echo $pageRequest -> page_title; ?>">
				</div>

				<div class="result-box" data-field="page_title" data-error=""></div>

				<div class="input width-100">
					<label><?= CLanguage::GET() -> STRING('BEPE_PANEL_PAGEDESCRIPTION'); ?></label>
					<textarea name="page_description"><?php echo $pageRequest -> page_description; ?></textarea>
				</div>

				<div class="result-box" data-field="page_description" data-error=""></div>

			</div>
		</fieldset>		



		<fieldset class="ui fieldset submit-able">
			<legend><?= CLanguage::GET() -> STRING('BEPE_PANEL_GROUP_TEMPLATE'); ?></legend>
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

		<fieldset class="ui fieldset submit-able">
			<legend><?= CLanguage::GET() -> STRING('BEPE_PANEL_GROUP_VISIBILTY'); ?></legend>
			<div style="padding-top:10px;">
				<div class="input width-100">
					<div class="select-wrapper">
					<select name="hidden_state" <?= ($pageRequest -> page_path == '/' ? 'disabled' : ''); ?>>
						<option value="0" <?= ($pageRequest -> hidden_state == 0 ? 'selected' : ''); ?>><?= CLanguage::GET() -> STRING('BEPE_PANEL_HIDDEN_0_VISIBLE'); ?></option>
						<option value="1" <?= ($pageRequest -> hidden_state == 1 ? 'selected' : ''); ?>><?= CLanguage::GET() -> STRING('BEPE_PANEL_HIDDEN_1_LOCKED'); ?></option>
						<option value="4" <?= ($pageRequest -> hidden_state == 4 ? 'selected' : ''); ?>><?= CLanguage::GET() -> STRING('BEPE_PANEL_HIDDEN_4_LOCKED'); ?></option>
						<option value="2" <?= ($pageRequest -> hidden_state == 2 ? 'selected' : ''); ?>><?= CLanguage::GET() -> STRING('BEPE_PANEL_HIDDEN_2_HIDDEN'); ?></option>
						<option value="3" <?= ($pageRequest -> hidden_state == 3 ? 'selected' : ''); ?>><?= CLanguage::GET() -> STRING('BEPE_PANEL_HIDDEN_3_REGISTERED'); ?></option>
					</select>
					</div>
					<div class="result-box" data-field="hidden_state" data-error=""></div>
				</div>
			</div>
		</fieldset>				
	
		<fieldset class="ui fieldset submit-able">
			<legend><?= CLanguage::GET() -> STRING('BEPE_PANEL_GROUP_SEARCHMACHINE'); ?></legend>
			<div style="padding-top:10px;">
				<div class="input width-100">
					<div class="select-wrapper">
					<select name="crawler_index">
						<option value="1" <?= ($pageRequest -> crawler_index == 1 ? 'selected' : ''); ?>><?= CLanguage::GET() -> STRING('BEPE_PANEL_CRAWLER_1_INDEX'); ?></option>
						<option value="0" <?= ($pageRequest -> crawler_index == 0 ? 'selected' : ''); ?>><?= CLanguage::GET() -> STRING('BEPE_PANEL_CRAWLER_0_NOINDEX'); ?></option>
					</select>
					</div>
					<div class="result-box" data-field="crawler_index" data-error=""></div>
				</div>
				<div class="input width-100">
					<div class="select-wrapper">
					<select name="crawler_follow">
						<option value="1" <?= ($pageRequest -> crawler_follow == 1 ? 'selected' : ''); ?>><?= CLanguage::GET() -> STRING('BEPE_PANEL_CRAWLER_1_FOLLOW'); ?></option>
						<option value="0" <?= ($pageRequest -> crawler_follow == 0 ? 'selected' : ''); ?>><?= CLanguage::GET() -> STRING('BEPE_PANEL_CRAWLER_0_NOFOLLOW'); ?></option>
					</select>
					</div>
					<div class="result-box" data-field="crawler_follow" data-error=""></div>
				</div>
				<div class="input width-100">
					<div class="select-wrapper">
					<select name="menu_follow">
						<option value="1" <?= ($pageRequest -> menu_follow == 1 ? 'selected' : ''); ?>><?= CLanguage::GET() -> STRING('BEPE_PANEL_CRAWLER_1_MENUFOLLOW'); ?></option>
						<option value="0" <?= ($pageRequest -> menu_follow == 0 ? 'selected' : ''); ?>><?= CLanguage::GET() -> STRING('BEPE_PANEL_CRAWLER_0_MENUNOFOLLOW'); ?></option>
					</select>
					</div>
					<div class="result-box" data-field="menu_follow" data-error=""></div>
				</div>
			</div>
		</fieldset>		

		<fieldset class="ui fieldset submit-able">
			<legend><?= CLanguage::GET() -> STRING('BEPE_PANEL_GROUP_EXTSETTINGS'); ?></legend>
			<div style="padding-top:10px;">
				<div class="input width-100">
					<label><?= CLanguage::GET() -> STRING('BEPE_PANEL_BROWSERCACHING'); ?></label>
					<div class="select-wrapper">
					<select name="cache_disabled">
						<option value="0" <?= ($pageRequest -> cache_disabled == 0 ? 'selected' : ''); ?>><?= CLanguage::GET() -> STRING('BEPE_PANEL_CACHING_0_ENABLED'); ?></option>
						<option value="1" <?= ($pageRequest -> cache_disabled == 1 ? 'selected' : ''); ?>><?= CLanguage::GET() -> STRING('BEPE_PANEL_CACHING_1_DISABLED'); ?></option>
					</select>
					</div>
					<div class="result-box" data-field="cache_disabled" data-error=""></div>
				</div>
			</div>
		</fieldset>	


		<fieldset class="ui fieldset submit-able">
			<legend><?= CLanguage::GET() -> STRING('BEPE_PANEL_GROUP_ALTLANG'); ?></legend>

			<div style="padding-top:10px;">

				<div class="input width-100">
					<label><?= CLanguage::GET() -> STRING('BEPE_PANEL_ALTLANGNODEID'); ?></label>
					<input type="text" name="page_id" id="alt_page_id" value="">
				</div>

				<div class="result-box" data-field="page_id" data-error=""></div>

			</div>
				<hr>

				<div class="input width-100" id="alterante-container" style="display:block;"></div>


				<script>

					function
					updateAlternateList(pageId)
					{
						var formData 		= new FormData();
							formData.append('cms-xhrequest', 'raw-alternate');
							formData.append('page_id', pageId);

						var	requestTarget	= CMS.SERVER_URL_BACKEND + 'pages/';

						TK.callXHR(requestTarget, formData, onSuccessRetrieveAlternates, TK.onXHRError, this);
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
								link.innerText = response.data[i].page_language.toUpperCase() + ' | ' + response.data[i].page_name;

								altList.append(link);


							numValid++;

						}

						if(numValid == 0)

							altList.innerHTML = '<span style="font-size:0.75em;"><?= CLanguage::GET() -> STRING('BEPE_PANEL_NOALTLANGEXIST'); ?></span>';


					}

					document.addEventListener("DOMContentLoaded", function(){

				
					
						window.updateAlternateList('<?= $pageRequest -> page_id; ?>');

					});	
					
				</script>



		</fieldset>	

		
		</div>
		
	
		<div id="be-page-panel-submit">

			<button class="ui button labeled icon trigger-submit-site-edit" id="trigger-submit-site-edit" type="button"><i class="fas fa-save"></i><?= CLanguage::GET() -> STRING('BUTTON_SAVE'); ?></button>

		</div>	
	
		
		
		
		
	</div>
		
</div>	


<script>


</script>