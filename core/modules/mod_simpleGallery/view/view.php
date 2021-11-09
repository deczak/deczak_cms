<?php
	$destList = [];
	MEDIATHEK::getItemsList($object -> body.'/', $destList, true);


	$ts = 4;

?>

<div class="simple-gallery-images-list" data-tile-size="<?= $ts; ?>"  id="simple-gallery-list-<?php echo $object -> object_id; ?>">
	<?php
	do {

		$rp = 0;
		$bp = [];
		$bl = [];

		foreach($destList as $item)
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
						<img src="<?= CMS_SERVER_URL.DIR_MEDIATHEK . $itemp -> path; ?>?binary&size=thumb">
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
				<img src="<?= CMS_SERVER_URL.DIR_MEDIATHEK . $item -> path; ?>?binary&size=thumb">
			</a>
			<?php
		}

		$destList = array_merge($bp, $bl);

		if(empty($destList))
			break;

	} while (true);
	?>
</div>

<style>
div.simple-gallery-images-list { display:flex; flex-wrap:wrap; }
div.simple-gallery-images-list > a { flex-shrink:0; display:block; border:1px solid white; height: 175px;}

div.simple-gallery-images-list[data-tile-size="4"] > a { height: 300px;}
div.simple-gallery-images-list[data-tile-size="4"] > a.orient-0 { width:50%; }
div.simple-gallery-images-list[data-tile-size="4"] > a.orient-1 { width:25%; }
div.simple-gallery-images-list[data-tile-size="4"] > a:last-child { width:25%; }

div.simple-gallery-images-list[data-tile-size="6"] > a { height: 250px;}
div.simple-gallery-images-list[data-tile-size="6"] > a.orient-0 { width:33.33%; }
div.simple-gallery-images-list[data-tile-size="6"] > a.orient-1 { width:16.66%; }
div.simple-gallery-images-list[data-tile-size="6"] > a:last-child { width:16.66%; }

div.simple-gallery-images-list[data-tile-size="8"] > a { height: 175px;}
div.simple-gallery-images-list[data-tile-size="8"] > a.orient-0 { width:25%; }
div.simple-gallery-images-list[data-tile-size="8"] > a.orient-1 { width:12.5%; }
div.simple-gallery-images-list[data-tile-size="8"] > a:last-child { width:12.5%; }

div.simple-gallery-images-list > a > img { width: 100%; height:100%; object-fit: cover; object-position: 50% 50%; }
</style>
