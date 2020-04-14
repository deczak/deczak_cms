<ul class="simple-sitemap template-squares squares-num-4">

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

	?>


	<li><a href="<?= $node -> page_path; ?>" style=" display:flex; justify-content:center; align-items:center;">
	

	<?= $node -> page_title; ?>
	
	</a></li>

	<?php

	



}
?>

</ul>

<style>
	ul.simple-sitemap.template-squares { list-style:none; display:flex; padding:0px; margin:0px; }
	ul.simple-sitemap.template-squares > li { display:block;  height:100px; border:1px solid grey; padding:0px; margin:0px; transition: all 0.2s;}
	ul.simple-sitemap.template-squares > li { margin:0 10px 20px 10px; }
	ul.simple-sitemap.template-squares > li:first-child { margin:0 10px 20px 0; }
	ul.simple-sitemap.template-squares > li > a { display:block; width:100%; height:100%; color:black; }

	ul.simple-sitemap.template-squares > li:hover { box-shadow: 0px 2px 5px 0px rgba(0,0,0,0.4); margin-top:-2px !important; }

	ul.simple-sitemap.template-squares.squares-num-4 > li { width:calc(25% - 15px); }
	ul.simple-sitemap.template-squares.squares-num-4 > li:nth-child(4n) { margin:0 0 20px 10px; }
	ul.simple-sitemap.template-squares.squares-num-4 > li:nth-child(5n) { margin:0 10px 20px 0; }

</style>
