<div class="blog-container">

<?php
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
		<span class="info"><i class="far fa-clock"></i>&nbsp;<?= date("d.", $node -> create_time); ?> <?= CLanguage::get() -> string(date("F", $node -> create_time)); ?> <?= date("Y", $node -> create_time); ?></span>

		<p><?= $node -> text -> body; ?></p>
	
		&nbsp;&nbsp;&bull;&nbsp; <a class="darkblue" href="<?= CMS_SERVER_URL . URL_LANG_PRREFIX . $pagePath; ?>"> <?= CLanguage::get() -> string('READMORE'); ?></a>
		
	</div>

	<?php
}
?>

</div>
