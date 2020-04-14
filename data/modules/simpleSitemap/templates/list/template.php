<ul class="simple-sitemap template-list">

<?php
foreach($sitemap as $node)
{
	

	if(	(		$object -> params -> display_hidden == 1
			&&	$node -> hidden_state === 2 
			&& $node -> page_auth == 0
		)

		||	(	($node -> hidden_state == 5 && $node -> publish_from  < $timestamp)
			&&	($node -> hidden_state == 5 && $node -> publish_until > $timestamp && $node -> publish_until != 0)
			&& $node -> page_auth == 0
			)

		||	$node -> hidden_state === 0 && $node -> page_auth == 0

	  ); else continue;


	echo '<li><a href="'. $node -> page_path .'" class="darkblue">'. $node -> page_title .'</a></li>';


}
?>


</ul>