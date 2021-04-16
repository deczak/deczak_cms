<ul>
<?php foreach($termList as $term)
{
	if($term -> tag_hidden)
		continue;

	echo '<li><a href="'. CMS_SERVER_URL . URL_LANG_PRREFIX . substr($parentNode  -> page_path, 1) .'tag/'. $term -> tag_url .'" class="darkblue">'. $term -> tag_name .'</a></li>';
}
?>
</ul>