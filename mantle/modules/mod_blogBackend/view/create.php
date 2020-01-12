
<pre>
-> Multiple Kategorie Auswahl
-> Multiple Tags Auswahl
-> Datums Funktion 
</pre>

<div class="be-module-container forms-view">
	<div>
		<div class="inter-menu">
			<h2><?php echo $language -> string('MENU'); ?></h2>
			<hr>
			<ul>
			<li><a class="darkblue" href="#blog-bost"><?php echo $language -> string('MOD_BEBLOG_GRP_BLOPOST'); ?></a></li>
			</ul>
			<hr>
			<div class="delete-box">	
			</div>
		</div>
	</div>
	<div>
		
		<fieldset class="ui fieldset submit-able" id="blog-bost" data-xhr-target="blog-bost">
			<legend><?php echo $language -> string('MOD_BEBLOG_GRP_BLOPOST'); ?></legend>
			<div>

				<!-- group  -->
				<div class="group width-100">

					<div class="group-head width-100"><?php echo $language -> string('MOD_BEBLOG_GRP_DSPLYSTTNG'); ?></div>

					<div class="input width-25">
						<label><?php echo $language -> string('MOD_BEBLOG_DISPLAY'); ?></label>	
						<div class="select-wrapper">
						<select name="display">
							<option value="1"><?php echo CLanguage::instance() -> getString('YES'); ?></option>
							<option value="0"><?php echo CLanguage::instance() -> getString('NO'); ?></option>
						</select>	
						</div>
					</div>

					<div class="input width-25">
						<label><?php echo $language -> string('MOD_BEBLOG_TIMEFROM'); ?></label>
						<input type="text" name="display_time_from" value="">
					</div>

					<div class="input width-25">
						<label><?php echo $language -> string('MOD_BEBLOG_TIMEUNTIL'); ?></label>
						<input type="text" name="display_time_until" value="" maxlength="250">
					</div>

					<div class="input width-25">
						<label><?php echo $language -> string('MOD_BEBLOG_TIMEUNTILLOCKED'); ?></label>
						<input type="text" name="display_time_end_locked" value="" maxlength="250">
					</div>
				</div>

				<!-- group  -->
				<div class="group width-100">

					<div class="group-head width-100"><?php echo $language -> string('MOD_BEBLOG_GRP_POSTHEADLINES'); ?></div>

					<div class="input width-100">
						<label><?php echo $language -> string('MOD_BEBLOG_HEADLINE'); ?></label>
						<input type="text" name="post_headline" value="" maxlength="250">
					</div>

					<div class="input width-100">
						<label><?php echo $language -> string('MOD_BEBLOG_SUBHEADLINE'); ?></label>
						<input type="text" name="post_subheadline" value="" maxlength="250">
					</div>

				</div>
				
				<!-- group  -->
				<div class="group width-100">

					<div class="group-head width-100"><?php echo $language -> string('MOD_BEBLOG_GRP_POSTBODY'); ?></div>

					<div class="input width-100">
						<label><?php echo $language -> string('post_body'); ?></label>
						<textarea name=""></textarea>
					</div>

					<div class="input width-100">
						<label><?php echo $language -> string('post_teaser'); ?></label>
						<textarea name=""></textarea>
					</div>

				</div>


			</div>

			<div class="result-box" data-error=""></div>

			<!-- Submit button - beware of fieldset name -->

			<div class="submit-container">
				<button class="ui button icon labeled trigger-submit-fieldset" type="button" disabled><i class="fas fa-save"></i><?php echo $language -> string('BUTTON_SAVE'); ?></button>
				<div class="protector"><input type="checkbox" class="trigger-submit-protector" id="protector-blog-bost"><label for="protector-blog-bost"></label></div>
			</div>

		</fieldset>

		<br><br>

	</div>
</div>

