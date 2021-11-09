<input type="hidden" name="cms-object-id" value="<?php echo $object -> object_id; ?>">

<div class="page-edit-module-controls-panel simple-gallery-control" data-target-list="simple-gallery-list-<?php echo $object -> object_id; ?>">

	<div class="left">

		<button class="ui button icon labeled button-import simple-gallery-select-directory" type="button">
			<span>
				<i class="far fa-images"></i>
			</span> 
			Mediathek Directory
		</button>

		<!--

		<button class="ui button icon" type="button" disabled="">
			<i class="fas"></i>
		</button>
		<label>View mode</label>
		<button class="ui button icon button-view-list" type="button">
			<i class="fas fa-bars"></i>
		</button>
		<button class="ui button icon button-view-square" type="button">
			<i class="fas fa-th"></i>
		</button>
		-->

	</div>

	<div class="right">

		<input type="hidden" name="simple-gallery-path" value="<?= $object -> body; ?>">

	</div>

</div>

<?php
$destList = [];
MEDIATHEK::getItemsList($object -> body.'/', $destList, true);
?>

<div class="simple-gallery-images-list" data-tile-size="6" id="simple-gallery-list-<?php echo $object -> object_id; ?>">
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
div.simple-gallery-images-list > a { flex-shrink:0; display:block; border:1px solid white; height: 175px; }

div.simple-gallery-images-list[data-tile-size="4"] > a { height: 300px;}
div.simple-gallery-images-list[data-tile-size="4"] > a.orient-0 { width:50%; }
div.simple-gallery-images-list[data-tile-size="4"] > a.orient-1,
div.simple-gallery-images-list[data-tile-size="4"] > a:last-child { width:25%; }

div.simple-gallery-images-list[data-tile-size="6"] > a { height: 250px;}
div.simple-gallery-images-list[data-tile-size="6"] > a.orient-0 { width:33.33%; }
div.simple-gallery-images-list[data-tile-size="6"] > a.orient-1,
div.simple-gallery-images-list[data-tile-size="6"] > a:last-child { width:16.66%; }

div.simple-gallery-images-list[data-tile-size="8"] > a { height: 175px;}
div.simple-gallery-images-list[data-tile-size="8"] > a.orient-0 { width:25%; }
div.simple-gallery-images-list[data-tile-size="8"] > a.orient-1,
div.simple-gallery-images-list[data-tile-size="8"] > a:last-child { width:12.5%; }

div.simple-gallery-images-list > a > img { width: 100%; height:100%; object-fit: cover; object-position: 50% 50%; }


.page-edit-module-controls-panel { display:flex; justify-content:space-between; align-items:center; width:calc(100% - 1px); background:rgba(194, 214, 214, 0.4); font-weight: 300; font-size: 0.84em; }
.page-edit-module-controls-panel > div { display:flex; align-items:center; }
.page-edit-module-controls-panel > div.right { text-align:right; }
.page-edit-module-controls-panel button { border-radius:0px; border:0px; background:transparent; font-family: 'Open Sans',sans-serif; }
.page-edit-module-controls-panel button:not([disabled]):hover { background:rgba(0,0,0,0.1); }
.page-edit-module-controls-panel label { padding:0 12px; font-size:0.87em; }
.page-edit-module-controls-panel button i,
.page-edit-module-controls-panel button span { pointer-events:none; }

</style>
