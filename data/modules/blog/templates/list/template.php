<?php

/*

[0] => dta_post Object
	(
		[post_id] => 12
		[node_id] => 101
		[post_page_color] => 0
		[post_text_color] => 0
		[post_background_mode] => 0
		[post_teasertext_mode] => 1
		[post_size_length_min] => 0
		[post_size_height] => 1
	)
*/
?>

<div class="blog-container">

<?php

if(empty($nodeList))
{

	?><div style="text-align:center;"><?= CLanguage::string('POSTS_NOT_FOUND'); ?></div><?php
}
else
{
	foreach($nodeList as $node)
	{
		$categories = [];
		foreach($node -> categories as $nodeCategory)
		{
			$categories[] = $nodeCategory -> category_name;
		}

		$headline = trim(strip_tags($node -> headline -> body));
		$headline = (empty($headline) ? $node -> page_title : $headline);

		if(property_exists($node, 'page_url'))
			$pagePath = substr($node -> page_url, 1);
		else
			$pagePath = substr($node -> page_path, 1);

		?>

		<div class="blog-item">

			<span class="categories"><?= implode(' / ', $categories); ?></span>
			<h2><a href="<?= CMS_SERVER_URL . URL_LANG_PRREFIX . $pagePath; ?>"><?= $headline; ?></a></h2>

			<p><?= $node -> text -> body; ?></p>
		
			&nbsp;&nbsp;&bull;&nbsp; <a class="darkblue" href="<?= CMS_SERVER_URL . URL_LANG_PRREFIX . $pagePath; ?>"> <?= CLanguage::string('READMORE'); ?></a>
			
		</div>

		<?php
	}
}
?>

</div>
