<?php

	$page 		= $sitemap[0];
	$categories = [];

	foreach($page -> categories as $category)
	{
		$categories[] = $category -> category_name;
	}

?>

<div class="blog-item">

	<span class="categories"><?= implode(' / ', $categories); ?></span>
	<h1><?= $page -> page_title; ?></h1>
	<span class="info"><?= CLanguage::get() -> string('TIME_CREATE_AT'); ?> <?= date("d.m.Y", $page -> create_time); ?></span>

</div>
