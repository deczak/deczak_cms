<?php
	$destList = [];
	MEDIATHEK::getItemsList($object -> body.'/', $destList, true);
?>

<div class="simple-gallery-images-list" id="simple-gallery-list-<?php echo $object -> object_id; ?>">
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

			if(($rp + $rp2) > 8)
			{
				if($rp == 7 && count($bp) > 0)
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
div.simple-gallery-images-list > a { flex-shrink:0; display:block; outline:1px solid white; transition: transform .1s; height: 175px;}
div.simple-gallery-images-list > a:hover { /*transform: scale(1.1);*/ }
div.simple-gallery-images-list > a.orient-0 { width:25%; }
div.simple-gallery-images-list > a.orient-1 { width:12.5%; }
div.simple-gallery-images-list > a:last-child { width:12.5%; }
div.simple-gallery-images-list > a > img { width: 100%; height:100%; object-fit: cover; object-position: 50% 50%; }
</style>
