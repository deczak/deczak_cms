<?php

	$ts = ($object -> params -> display_divider ?? '8');

?>


<div class="simple-gallery-images-list" data-tile-size="<?= ($object -> params -> display_divider ?? '8'); ?>" data-tile-format="<?= ($object -> params -> format ?? '8'); ?>" id="simple-gallery-list-<?php echo $object -> object_id; ?>">
	<?php
	do {

		$rp = 0;
		$bp = [];
		$bl = [];


		switch(($object -> params -> display_divider ?? '8'))
		{
			case '2': 
				$imageSize = 'medium';
			case '3': 
				$imageSize = 'small';
			default:
				$imageSize = 'thumb';

		}

		foreach($itemList as $item)
		{
			switch($item -> mime )
			{
				case 'image/jpeg':
				case 'image/png':
				case 'image/png':

					break;

				default:

					continue 2;
			}

			switch($item -> orient )
			{
				case 0:

					$rp2 = 2;
					break;

				case 1:

					$rp2 = 1;
					break;
			}

			if(($rp + $rp2) > $ts)
			{
				if($rp == ($ts - 1) && count($bp) > 0)
				{
					$itemp = array_pop($bp)

					?>
					<a class="orient-<?= $itemp -> orient; ?>" href="<?= CMS_SERVER_URL.DIR_MEDIATHEK . $itemp -> path; ?>?size=xlarge">
						<img src="<?= CMS_SERVER_URL.DIR_MEDIATHEK . $itemp -> path; ?>?binary&size=<?= $imageSize; ?>">
					</a>
					<?php
				}

				$rp = 0;

				switch($item -> orient)
				{
					case 0:

						$bl[] = $item;
						break;

					case 1:

						$bp[] = $item;
						break;
				}

				continue;
			}

			$rp = $rp + $rp2;

			?>
			<a class="orient-<?= $item -> orient; ?>" href="<?= CMS_SERVER_URL.DIR_MEDIATHEK . $item -> path; ?>?size=xlarge">
				<img src="<?= CMS_SERVER_URL.DIR_MEDIATHEK . $item -> path; ?>?binary&size=<?= $imageSize; ?>">
			</a>
			<?php
		}

		$itemList = array_merge($bp, $bl);

		if(empty($itemList))
			break;

	} while (true);
	?>
</div>

<style>


/*

	temporary solution with that media queries


	max-width - 4% [2 + 2] padding / num tiles * 100 / (max-width - 4% padding) = height %

	1400 - 4% / 6 * 100 / (1344) = 16.6%



*/



div.simple-gallery-images-list { display:flex; flex-wrap:wrap; }
div.simple-gallery-images-list > a { flex-shrink:0; display:block; border:1px solid white; padding-top:16.5%; position:relative; }



div.simple-gallery-images-list[data-tile-size="2"] > a { padding-top:49.851%; }
div.simple-gallery-images-list[data-tile-size="2"] > a.orient-0 { width:100%; }
div.simple-gallery-images-list[data-tile-size="2"][data-tile-format="squares"] > a.orient-0,
div.simple-gallery-images-list[data-tile-size="2"] > a.orient-1,
div.simple-gallery-images-list[data-tile-size="2"] > a:last-child { width:50%; }

div.simple-gallery-images-list[data-tile-size="3"] > a { padding-top:33.184%; }
div.simple-gallery-images-list[data-tile-size="3"] > a.orient-0 { width:66.666%; }
div.simple-gallery-images-list[data-tile-size="3"][data-tile-format="squares"] > a.orient-0,
div.simple-gallery-images-list[data-tile-size="3"] > a.orient-1,
div.simple-gallery-images-list[data-tile-size="3"] > a:last-child { width:33.333%; }

div.simple-gallery-images-list[data-tile-size="4"] > a {  padding-top:24.851%;}
div.simple-gallery-images-list[data-tile-size="4"] > a.orient-0 { width:50%; }
div.simple-gallery-images-list[data-tile-size="4"][data-tile-format="squares"] > a.orient-0,
div.simple-gallery-images-list[data-tile-size="4"] > a.orient-1,
div.simple-gallery-images-list[data-tile-size="4"] > a:last-child { width:25%; }

div.simple-gallery-images-list[data-tile-size="5"] > a { padding-top:19.85%; }
div.simple-gallery-images-list[data-tile-size="5"] > a.orient-0 { width:40%; }
div.simple-gallery-images-list[data-tile-size="5"][data-tile-format="squares"] > a.orient-0,
div.simple-gallery-images-list[data-tile-size="5"] > a.orient-1,
div.simple-gallery-images-list[data-tile-size="5"] > a:last-child { width:20%; }

div.simple-gallery-images-list[data-tile-size="6"] > a { padding-top:16.51%; }
div.simple-gallery-images-list[data-tile-size="6"] > a.orient-0 { width:33.33%; }
div.simple-gallery-images-list[data-tile-size="6"][data-tile-format="squares"] > a.orient-0,
div.simple-gallery-images-list[data-tile-size="6"] > a.orient-1,
div.simple-gallery-images-list[data-tile-size="6"] > a:last-child { width:16.66%; }

div.simple-gallery-images-list[data-tile-size="7"] > a { padding-top:14.136%; }
div.simple-gallery-images-list[data-tile-size="7"] > a.orient-0 { width:28.571%; }
div.simple-gallery-images-list[data-tile-size="7"][data-tile-format="squares"] > a.orient-0,
div.simple-gallery-images-list[data-tile-size="7"] > a.orient-1,
div.simple-gallery-images-list[data-tile-size="7"] > a:last-child { width:14.286%; }

div.simple-gallery-images-list[data-tile-size="8"] > a { padding-top:12.351%; }
div.simple-gallery-images-list[data-tile-size="8"] > a.orient-0 { width:25%; }
div.simple-gallery-images-list[data-tile-size="8"][data-tile-format="squares"] > a.orient-0,
div.simple-gallery-images-list[data-tile-size="8"] > a.orient-1,
div.simple-gallery-images-list[data-tile-size="8"] > a:last-child { width:12.5%; }

div.simple-gallery-images-list > a > img { width: 100%; height:100%; object-fit: cover; object-position: 50% 50%; position:absolute; top:0px; left:0px; }

@media only screen and (max-width: 1200px) {

	div.simple-gallery-images-list[data-tile-size="8"] > a { padding-top:14.136%; }
	div.simple-gallery-images-list[data-tile-size="8"] > a.orient-0 { width:28.571%; }
	div.simple-gallery-images-list[data-tile-size="8"][data-tile-format="squares"] > a.orient-0,
	div.simple-gallery-images-list[data-tile-size="8"] > a.orient-1,
	div.simple-gallery-images-list[data-tile-size="8"] > a:last-child { width:14.286%; }

}

@media only screen and (max-width: 1100px) {

	div.simple-gallery-images-list[data-tile-size="7"] > a { padding-top:16.51%; }
	div.simple-gallery-images-list[data-tile-size="7"] > a.orient-0 { width:33.33%; }
	div.simple-gallery-images-list[data-tile-size="7"][data-tile-format="squares"] > a.orient-0,
	div.simple-gallery-images-list[data-tile-size="7"] > a.orient-1,
	div.simple-gallery-images-list[data-tile-size="7"] > a:last-child { width:16.66%; }

	div.simple-gallery-images-list[data-tile-size="8"] > a { padding-top:16.51%; }
	div.simple-gallery-images-list[data-tile-size="8"] > a.orient-0 { width:33.33%; }
	div.simple-gallery-images-list[data-tile-size="8"][data-tile-format="squares"] > a.orient-0,
	div.simple-gallery-images-list[data-tile-size="8"] > a.orient-1,
	div.simple-gallery-images-list[data-tile-size="8"] > a:last-child { width:16.66%; }

}

@media only screen and (max-width: 1100px) {

	div.simple-gallery-images-list[data-tile-size="6"] > a { padding-top:19.85%; }
	div.simple-gallery-images-list[data-tile-size="6"] > a.orient-0 { width:40%; }
	div.simple-gallery-images-list[data-tile-size="6"][data-tile-format="squares"] > a.orient-0,
	div.simple-gallery-images-list[data-tile-size="6"] > a.orient-1,
	div.simple-gallery-images-list[data-tile-size="6"] > a:last-child { width:20%; }

	div.simple-gallery-images-list[data-tile-size="7"] > a { padding-top:19.85%; }
	div.simple-gallery-images-list[data-tile-size="7"] > a.orient-0 { width:40%; }
	div.simple-gallery-images-list[data-tile-size="7"][data-tile-format="squares"] > a.orient-0,
	div.simple-gallery-images-list[data-tile-size="7"] > a.orient-1,
	div.simple-gallery-images-list[data-tile-size="7"] > a:last-child { width:20%; }

	div.simple-gallery-images-list[data-tile-size="8"] > a { padding-top:19.85%; }
	div.simple-gallery-images-list[data-tile-size="8"] > a.orient-0 { width:40%; }
	div.simple-gallery-images-list[data-tile-size="8"][data-tile-format="squares"] > a.orient-0,
	div.simple-gallery-images-list[data-tile-size="8"] > a.orient-1,
	div.simple-gallery-images-list[data-tile-size="8"] > a:last-child { width:20%; }

}

@media only screen and (max-width: 1000px) {

	div.simple-gallery-images-list[data-tile-size="5"] > a {  padding-top:24.851%;}
	div.simple-gallery-images-list[data-tile-size="5"] > a.orient-0 { width:50%; }
	div.simple-gallery-images-list[data-tile-size="5"][data-tile-format="squares"] > a.orient-0,
	div.simple-gallery-images-list[data-tile-size="5"] > a.orient-1,
	div.simple-gallery-images-list[data-tile-size="5"] > a:last-child { width:25%; }

	div.simple-gallery-images-list[data-tile-size="6"] > a {  padding-top:24.851%;}
	div.simple-gallery-images-list[data-tile-size="6"] > a.orient-0 { width:50%; }
	div.simple-gallery-images-list[data-tile-size="6"][data-tile-format="squares"] > a.orient-0,
	div.simple-gallery-images-list[data-tile-size="6"] > a.orient-1,
	div.simple-gallery-images-list[data-tile-size="6"] > a:last-child { width:25%; }

	div.simple-gallery-images-list[data-tile-size="7"] > a {  padding-top:24.851%;}
	div.simple-gallery-images-list[data-tile-size="7"] > a.orient-0 { width:50%; }
	div.simple-gallery-images-list[data-tile-size="7"][data-tile-format="squares"] > a.orient-0,
	div.simple-gallery-images-list[data-tile-size="7"] > a.orient-1,
	div.simple-gallery-images-list[data-tile-size="7"] > a:last-child { width:25%; }

	div.simple-gallery-images-list[data-tile-size="8"] > a {  padding-top:24.851%;}
	div.simple-gallery-images-list[data-tile-size="8"] > a.orient-0 { width:50%; }
	div.simple-gallery-images-list[data-tile-size="8"][data-tile-format="squares"] > a.orient-0,
	div.simple-gallery-images-list[data-tile-size="8"] > a.orient-1,
	div.simple-gallery-images-list[data-tile-size="8"] > a:last-child { width:25%; }
}

@media only screen and (max-width: 950px) {

	div.simple-gallery-images-list[data-tile-size="4"] > a { padding-top:33.184%; }
	div.simple-gallery-images-list[data-tile-size="4"] > a.orient-0 { width:66.666%; }
	div.simple-gallery-images-list[data-tile-size="4"][data-tile-format="squares"] > a.orient-0,
	div.simple-gallery-images-list[data-tile-size="4"] > a.orient-1,
	div.simple-gallery-images-list[data-tile-size="4"] > a:last-child { width:33.333%; }

	div.simple-gallery-images-list[data-tile-size="5"] > a { padding-top:33.184%; }
	div.simple-gallery-images-list[data-tile-size="5"] > a.orient-0 { width:66.666%; }
	div.simple-gallery-images-list[data-tile-size="5"][data-tile-format="squares"] > a.orient-0,
	div.simple-gallery-images-list[data-tile-size="5"] > a.orient-1,
	div.simple-gallery-images-list[data-tile-size="5"] > a:last-child { width:33.333%; }

	div.simple-gallery-images-list[data-tile-size="6"] > a { padding-top:33.184%; }
	div.simple-gallery-images-list[data-tile-size="6"] > a.orient-0 { width:66.666%; }
	div.simple-gallery-images-list[data-tile-size="6"][data-tile-format="squares"] > a.orient-0,
	div.simple-gallery-images-list[data-tile-size="6"] > a.orient-1,
	div.simple-gallery-images-list[data-tile-size="6"] > a:last-child { width:33.333%; }

	div.simple-gallery-images-list[data-tile-size="7"] > a { padding-top:33.184%; }
	div.simple-gallery-images-list[data-tile-size="7"] > a.orient-0 { width:66.666%; }
	div.simple-gallery-images-list[data-tile-size="7"][data-tile-format="squares"] > a.orient-0,
	div.simple-gallery-images-list[data-tile-size="7"] > a.orient-1,
	div.simple-gallery-images-list[data-tile-size="7"] > a:last-child { width:33.333%; }

	div.simple-gallery-images-list[data-tile-size="8"] > a { padding-top:33.184%; }
	div.simple-gallery-images-list[data-tile-size="8"] > a.orient-0 { width:66.666%; }
	div.simple-gallery-images-list[data-tile-size="8"][data-tile-format="squares"] > a.orient-0,
	div.simple-gallery-images-list[data-tile-size="8"] > a.orient-1,
	div.simple-gallery-images-list[data-tile-size="8"] > a:last-child { width:33.333%; }

}

@media only screen and (max-width: 950px) {

	div.simple-gallery-images-list[data-tile-size="3"] > a { padding-top:49.851%; }
	div.simple-gallery-images-list[data-tile-size="3"] > a.orient-0 { width:100%; }
	div.simple-gallery-images-list[data-tile-size="3"][data-tile-format="squares"] > a.orient-0,
	div.simple-gallery-images-list[data-tile-size="3"] > a.orient-1,
	div.simple-gallery-images-list[data-tile-size="3"] > a:last-child { width:50%; }

	div.simple-gallery-images-list[data-tile-size="4"] > a { padding-top:49.851%; }
	div.simple-gallery-images-list[data-tile-size="4"] > a.orient-0 { width:100%; }
	div.simple-gallery-images-list[data-tile-size="4"][data-tile-format="squares"] > a.orient-0,
	div.simple-gallery-images-list[data-tile-size="4"] > a.orient-1,
	div.simple-gallery-images-list[data-tile-size="4"] > a:last-child { width:50%; }

	div.simple-gallery-images-list[data-tile-size="5"] > a { padding-top:49.851%; }
	div.simple-gallery-images-list[data-tile-size="5"] > a.orient-0 { width:100%; }
	div.simple-gallery-images-list[data-tile-size="5"][data-tile-format="squares"] > a.orient-0,
	div.simple-gallery-images-list[data-tile-size="5"] > a.orient-1,
	div.simple-gallery-images-list[data-tile-size="5"] > a:last-child { width:50%; }

	div.simple-gallery-images-list[data-tile-size="6"] > a { padding-top:49.851%; }
	div.simple-gallery-images-list[data-tile-size="6"] > a.orient-0 { width:100%; }
	div.simple-gallery-images-list[data-tile-size="6"][data-tile-format="squares"] > a.orient-0,
	div.simple-gallery-images-list[data-tile-size="6"] > a.orient-1,
	div.simple-gallery-images-list[data-tile-size="6"] > a:last-child { width:50%; }

	div.simple-gallery-images-list[data-tile-size="7"] > a { padding-top:49.851%; }
	div.simple-gallery-images-list[data-tile-size="7"] > a.orient-0 { width:100%; }
	div.simple-gallery-images-list[data-tile-size="7"][data-tile-format="squares"] > a.orient-0,
	div.simple-gallery-images-list[data-tile-size="7"] > a.orient-1,
	div.simple-gallery-images-list[data-tile-size="7"] > a:last-child { width:50%; }

	div.simple-gallery-images-list[data-tile-size="8"] > a { padding-top:49.851%; }
	div.simple-gallery-images-list[data-tile-size="8"] > a.orient-0 { width:100%; }
	div.simple-gallery-images-list[data-tile-size="8"][data-tile-format="squares"] > a.orient-0,
	div.simple-gallery-images-list[data-tile-size="8"] > a.orient-1,
	div.simple-gallery-images-list[data-tile-size="8"] > a:last-child { width:50%; }

}



@media only screen and (max-width: 500px) {

	div.simple-gallery-images-list > a { height: 300px; width:100% !important; }

}



</style>
