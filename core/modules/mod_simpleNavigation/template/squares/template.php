
<ul class="simple-sitemap template-squares squares-num-4">

	<?php
	
	foreach($nodeList as $nodeLevel1)
	{
		$mapLevel = null;
		foreach($nodeLevel1 as $node)
		{
			$markNotDisplayed = false;

			if($mapLevel === null)
				$mapLevel = $node -> level + 1 ;

			if($mapLevel !== $node -> level && $node -> listing_type == 'subpages')
				continue;
			
			$node -> page_image_url = MEDIATHEK::getItemUrl($node -> page_image);
			$node -> page_image_url = ($node -> page_image_url !== null ? $node -> page_image_url .'?binary&size=small' : $node -> page_image_url);


			if(	(		$node -> listing_hidden == 1
					&&	$node -> hidden_state === 2 
					&& $node -> page_auth == 0
				)

				||	(	($node -> hidden_state == 5 && $node -> publish_from  < $timestamp)
					&&	($node -> hidden_state == 5 && $node -> publish_until > $timestamp && $node -> publish_until != 0)
					&& $node -> page_auth == 0
					)

				||	$node -> hidden_state === 0 && $node -> page_auth == 0

			); else 
			{
				if(!CMS_BACKEND)
				{
					continue;
				}
				else
				{
					$markNotDisplayed = true;
				}
			}
		

			$lang = ((CFG::GET() -> LANGUAGE -> DEFAULT_IN_URL || $node -> page_language !== CLanguage::getDefault()) ? $node -> page_language .'/' : '');

			if(CMS_BACKEND)
				$url = CMS_SERVER_URL_BACKEND .'pages/view/'. $node -> page_language .'/'. $node -> node_id;
			else
				$url = '/'. $lang . substr($node -> page_path, 1);

			?>

			<li class="<?= ($markNotDisplayed ? 'notDisplayed' : ''); ?>">
				<a href="<?= $url; ?>" title="<?= $node -> page_title; ?>" style=" display:flex; justify-content:center; align-items:center;">

					<div>
						<div class="image" style="background-image:url('<?= $node -> page_image_url; ?>')"></div>
						<div class="title"><?= $node -> page_title; ?></div>
					</div>

				</a>
			</li>

			<?php
		}
	}
	?>

	<li></li>
	<li></li>
	<li></li>
</ul>


<style>
	ul.simple-sitemap.template-squares { list-style:none; display:flex; padding:0px; margin:0px; flex-wrap:wrap; justify-content:space-between; }
	ul.simple-sitemap.template-squares > li { display:block;   padding:0px; margin:0px; flex-shrink:0; }

	ul.simple-sitemap.template-squares > li { margin:0 0px 30px 0px; }
	ul.simple-sitemap.template-squares > li.notDisplayed { opacity: .3;  outline: 1px solid black; filter: grayscale(80%); }

	ul.simple-sitemap.template-squares.squares-num-4 > li { width:calc(25% - 15px); }

	ul.simple-sitemap.template-squares > li > a { display:block; width:100%; height:100%; color:black; }
	ul.simple-sitemap.template-squares > li > a > div { width: 100%; height:100%; position:relative; box-shadow: 0 0 15px -5px rgb(0 0 0 / 30%); border: 1px solid rgba(0,0,0,20%); }
	ul.simple-sitemap.template-squares > li > a .image{ padding-top:75%; width:100%; background-position:center center; background-size:cover; background-repeat:no-repeat;  }
	ul.simple-sitemap.template-squares > li > a .title{ background-color:rgba(255,255,255,0.8); position:absolute; bottom:0; left:0; width:100%; padding:5px 13px;font-size:0.8em; text-shadow: 0 0 2px rgb(255 255 255 / 50%); }

	@media only screen and (max-width: 1000px) {

		ul.simple-sitemap.template-squares.squares-num-4 > li { width:calc(33.33333% - 6px); }
	}

	@media only screen and (max-width: 700px) {

		ul.simple-sitemap.template-squares.squares-num-4 > li { width:calc(50% - 6px); }
	}

	@media only screen and (max-width: 450px) {

		ul.simple-sitemap.template-squares { justify-content:center; }
		ul.simple-sitemap.template-squares.squares-num-4 > li { width:calc(100% - 6px); }
	}

</style>
