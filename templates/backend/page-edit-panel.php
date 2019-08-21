<?php


	
		$pAvaiableTemplates	=	new CTemplates(CMS_SERVER_ROOT . DIR_TEMPLATES);
		$avaiableTemplates 	= 	$pAvaiableTemplates -> searchTemplates(true);


?>

<div id="be-page-panel" >
	
	<input type="checkbox" id="trigger-page-panel-slider" style="display:none !important; opacity:0 !important;" value="">
	<label for="trigger-page-panel-slider" id="be-page-panel-slider">&nbsp;</label>
		
	<div id="be-page-panel-content" data-xhr-target="update-site">
		
		<div>

		<div class="backend-title-container">
			<a href="http://gateway.intranet/deczak_v3_test/backend/"><b>BACKYARD</b> // SYSTEM</a>		
			<a class="yellow" style="display:block; margin-top:5px; font-size:0.95em;" href="<?php echo CMS_SERVER_URL_BACKEND . $_pageRequest['origin_index']; ?>">Back to Overview</a>			
		</div>
		




		
		<fieldset class="ui fieldset submit-able">
			<legend>Page information</legend>

				<div>

					<table id="table-page-informationen">
						<tbody>
							<tr>
								<td>Node ID</td>
								<td><?php echo $page -> node_id; ?></td>
							</tr>
							<tr>
								<td>Language</td>
								<td><?php echo $page -> page_language; ?></td>
							</tr>
							<tr>
								<td></td>
								<td></td>
							</tr>
						</tbody>
					</table>

			</div>
		</fieldset>		
		




		
		<fieldset class="ui fieldset submit-able">
			<legend>Name & description</legend>

				<div>


					<div class="input width-100">
						<label>Page name</label>
						<input type="text" name="page_name" value="<?php echo $page -> page_name; ?>">
					</div>

					<div class="input width-100">
						<label>Page title</label>
					<input type="text" name="page_title" value="<?php echo $page -> page_title; ?>">
					</div>

					<div class="input width-100">
						<label>Page description</label>
						<textarea name="page_description"><?php echo $page -> page_description; ?></textarea>
					</div>


			</div>

		</fieldset>		



		<fieldset class="ui fieldset submit-able">
			<legend>Template</legend>

			<div style="padding-top:10px;">


					<div class="input width-100">
						<select name="page_template">
							<?php 
							foreach($pAvaiableTemplates -> getTemplates() as $_tmplIndex => $_tmplData)
							{
								echo '<option value="'. $_tmplIndex .'" '. ($_tmplIndex === $page -> page_template ? 'selected' : '') .'>'. $_tmplData -> template_name .'</option>';
							}
							?>
						</select>
					</div>


			</div>

		</fieldset>	




			<fieldset>
			<legend>Visibility & Restrictions</legend>

			<div>

<br><br><br>


			</div>

		</fieldset>			

	
		
		</div>
		
	
		<div id="submit-site-edit-container">

			<button class="trigger-submit-site-edit" type="button"><i class="fas fa-save"></i>&nbsp;&nbsp;&nbsp;Save changes</button>

		</div>	
	
		
		
		
		
	</div>
		
</div>	
	