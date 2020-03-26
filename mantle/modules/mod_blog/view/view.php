<div class="blog-container">

<?php

$rootLevel = false;
foreach($sitemap as $sitemapIndex => $node)
{
	if($rootLevel === false)
	{
		$rootLevel = $node -> level + 1;
		unset($sitemap[$sitemapIndex]);
		continue;
	}
	
	if($rootLevel != $node -> level)
	{
		unset($sitemap[$sitemapIndex]);
		continue;
	}

	if(
			(		$node -> hidden_state == 0
			    || 	$node -> hidden_state == 2
			)
		&&	(empty($node -> page_auth) || (!empty($node -> page_auth) && CSession::instance() -> isAuthed($node -> page_auth) === true))
		||	(	($node -> hidden_state == 5 && $node -> publish_from  < $timestamp)
			&&	($node -> hidden_state == 5 && $node -> publish_until > $timestamp && $node -> publish_until != 0)
			)
		||  CMS_BACKEND
		); else unset($sitemap[$sitemapIndex]);

	if(empty($node -> text))
		unset($sitemap[$sitemapIndex]);
}

foreach ($sitemap as $key => $node)
    $createTime[$key] = $node -> create_time;

array_multisort($createTime, SORT_DESC, $sitemap);

#$rootLevel = false;
foreach($sitemap as $node)
{
#	if($rootLevel === false)
#	{
#		$rootLevel = $node -> level + 1;
#		continue;
#	}
#	
#	if($rootLevel != $node -> level)
#	{
#		continue;
#	}
#
#	if(
#			(		$node -> hidden_state == 0
#			    || 	$node -> hidden_state == 2
#			)
#		&&	(empty($node -> page_auth) || (!empty($node -> page_auth) && CSession::instance() -> isAuthed($node -> page_auth) === true))
#		||	(	($node -> hidden_state == 5 && $node -> publish_from  < $timestamp)
#			&&	($node -> hidden_state == 5 && $node -> publish_until > $timestamp && $node -> publish_until != 0)
#			)
#		||  CMS_BACKEND
#		); else continue;
#
#	if(empty($node -> text))
#		continue;

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
