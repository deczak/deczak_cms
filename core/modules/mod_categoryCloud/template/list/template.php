<ul>
<?php foreach($termList as $term)
{
	if($term -> category_hidden)
		continue;

	echo '<li><a href="'. CMS_SERVER_URL . URL_LANG_PRREFIX . substr($parentNode  -> page_path, 1) .'category/'. $term -> category_url .'" class="darkblue">'. $term -> category_name .'</a></li>';
}
?>
</ul>