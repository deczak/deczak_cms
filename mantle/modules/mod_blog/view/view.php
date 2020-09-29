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
		
	?>

	<div class="blog-item">

		<span class="categories"><?= implode(' / ', $categories); ?></span>
		<h2><a href="<?= CMS_SERVER_URL . URL_LANG_PRREFIX . substr($node -> page_path, 1); ?>"><?= $headline; ?></a></h2>
		<span class="info"><?= CLanguage::get() -> string('TIME_CREATE_AT'); ?> <?= date("d.m.Y", $node -> create_time); ?></span>

		<p><?= $node -> text -> body; ?></p>
	
		&nbsp;&nbsp;&bull;&nbsp; <a class="darkblue" href="<?= CMS_SERVER_URL . URL_LANG_PRREFIX . substr($node -> page_path, 1); ?>"> <?= CLanguage::get() -> string('READMORE'); ?></a>
		
	</div>

	<?php
}
?>

</div>
