<?php

/*
	max size der kachel



    $postSettings -> post_id] => 12
    $postSettings -> node_id] => 101
    $postSettings -> post_page_color] => #000000
    $postSettings -> post_text_color] => #cc00c5
    $postSettings -> post_background_mode] => 1
    $postSettings -> post_teasertext_mode] => 1
    $postSettings -> post_size_length_min] => 0
    $postSettings -> post_size_height] => 1

			Wir müssen hier die Daten laden für die felder auf grundlage der seite die bearbeitet werden soll.
		


*/
?>

<fieldset class="ui fieldset submit-able">
	<legend><?= CLanguage::string('M_BLOG_BEPE_GROUP'); ?></legend>
	<div style="padding-top:10px;">
	
		<div class="input width-100">
			<label><?= CLanguage::string('M_BLOG_BEPE_BLOG_POST_BG_MODE'); ?></label>
			<div class="select-wrapper">
			<select name="modBlog_background_mode">
				<option value="0" <?= ($postSettings -> post_background_mode == 0 ? 'selected' : ''); ?>><?= CLanguage::string('M_BLOG_BEPE_BLOG_POST_BG_MODE_CLR'); ?></option>
				<option value="1" <?= ($postSettings -> post_background_mode == 1 ? 'selected' : ''); ?>><?= CLanguage::string('M_BLOG_BEPE_BLOG_POST_BG_MODE_IMG'); ?></option>
			</select>
			</div>
			<div class="result-box" data-field="modBlog_background_mode" data-error=""></div>
		</div>

		<div class="input width-100">
			<label><?= CLanguage::string('M_BLOG_BEPE_BLOG_POST_BG_MODE_CLR'); ?></label>
			<input type="color" name="modBlog_page_color" value="<?= $postSettings -> post_page_color ?? ''.sprintf("#%02x%02x%02x", mt_rand(120, 220), mt_rand(120, 220), mt_rand(120, 220)); ?>" style="padding: 0px 1px;">
		</div>

		<div class="input width-100">
			<label><?= CLanguage::string('M_BLOG_BEPE_BLOG_POST_TXT_MODE_CLR'); ?></label>
			<input type="color" name="modBlog_text_color" value="#000000" style="padding: 0px 1px;">
		</div>

		<div class="input width-100">
			<label><?= CLanguage::string('M_BLOG_BEPE_BLOG_TEXT_MODE'); ?></label>
			<div class="select-wrapper">
			<select name="modBlog_teasertext_mode">
				<option value="1" <?= ($postSettings -> post_teasertext_mode == 1 ? 'selected' : ''); ?>><?= CLanguage::string('M_BLOG_BEPE_BLOG_TEXT_MODE_META'); ?></option>
				<option value="2" <?= ($postSettings -> post_teasertext_mode == 2 ? 'selected' : ''); ?>><?= CLanguage::string('M_BLOG_BEPE_BLOG_TEXT_MODE_HEAD'); ?></option>
				<option value="0" <?= ($postSettings -> post_teasertext_mode == 0 ? 'selected' : ''); ?>><?= CLanguage::string('M_BLOG_BEPE_BLOG_TEXT_MODE_NOPE'); ?></option>
			</select>
			</div>
			<div class="result-box" data-field="modBlog_teasertext_mode" data-error=""></div>
		</div>

		<div class="input width-100">
			<label><?= CLanguage::string('M_BLOG_BEPE_BLOG_POST_SIZE_LENGTH_MIN'); ?></label>
			<div class="select-wrapper">
			<select name="modBlog_post_size_length_min">
				<option value="0" <?= ($postSettings -> post_size_length_min == 0 ? 'selected' : ''); ?>><?= CLanguage::string('M_BLOG_BEPE_BLOG_POST_SIZE_LENGTH_AUTO'); ?></option>
				<option value="1" <?= ($postSettings -> post_size_length_min == 1 ? 'selected' : ''); ?>>1</option>
				<option value="2" <?= ($postSettings -> post_size_length_min == 2 ? 'selected' : ''); ?>>2</option>
				<option value="3" <?= ($postSettings -> post_size_length_min == 3 ? 'selected' : ''); ?>>3</option>
			</select>
			</div>
			<div class="result-box" data-field="modBlog_post_size_length_min" data-error=""></div>
		</div>

		<div class="input width-100">
			<label><?= CLanguage::string('M_BLOG_BEPE_BLOG_POST_SIZE_HEIGHT'); ?></label>
			<div class="select-wrapper">
			<select name="modBlog_post_size_height">
				<option value="1" <?= ($postSettings -> post_size_height == 1 ? 'selected' : ''); ?>>1</option>
				<option value="2" <?= ($postSettings -> post_size_height == 2 ? 'selected' : ''); ?>>2</option>
			</select>
			</div>
			<div class="result-box" data-field="modBlog_post_size_height" data-error=""></div>
		</div>


		<div class="input width-100">
			<input type="hidden" name="modBlog_post_display_categorie" value="0">
			<input type="checkbox" name="modBlog_post_display_categorie" value="1" id="modBlog_post_display_categorie" <?= (($postSettings -> post_display_category ?? 0) == 1 ? 'checked' : ''); ?>>
			<label for="modBlog_post_display_categorie"><?= CLanguage::string('M_BLOG_BEPE_BLOG_POST_DISPLAY_CAT'); ?></label>	
		</div>

	</div>
</fieldset>	
