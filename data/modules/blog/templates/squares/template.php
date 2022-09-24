<?php

$rowSizeLimit = 5;
$rowIndex 	  = 0;
$drawList 	  = [];
$subStripList = [
	2 => 100,
	3 => 160,
];

for($i = 0; $i < count($nodeList); $i++)
{
	if(!isset($drawList[$rowIndex]))
	{
		$drawList[$rowIndex] = [
			'X3ItemIndex' 		 => null,		// Item index of size 3 item
			'XAutoItemIndexList' => [],			// Item index list of auto size items
			'rowIndex' 			 => $rowIndex,	// Index of this row
			'rowSizeUsed'		 => 0,			// Total item sizes
			'itemList' 			 => [],			// Item list of assigned tiles
		];
	}

	if(empty($nodeList[$i] -> postSetting))
	{
		$nodeList[$i] -> postSetting = new stdClass;
		$nodeList[$i] -> postSetting -> post_page_color		= 0;
		$nodeList[$i] -> postSetting -> post_text_color		= 0;
		$nodeList[$i] -> postSetting -> post_background_mode	= 0;
		$nodeList[$i] -> postSetting -> post_teasertext_mode	= 0;
		$nodeList[$i] -> postSetting -> post_size_length_min	= 0;
		$nodeList[$i] -> postSetting -> post_size_height		= 1;
	}

	$itemSize = empty($nodeList[$i] -> postSetting -> post_size_length_min) ? 1 : $nodeList[$i] -> postSetting -> post_size_length_min;

	if(($drawList[$rowIndex]['rowSizeUsed'] + $itemSize) > $rowSizeLimit)
	{
		$rowSizeFree = $rowSizeLimit - $drawList[$rowIndex]['rowSizeUsed'];

		// TODO: Hier schauen ob ein auto item vorhanden dessen size geändert werden kann, anschließend rowSizeFree anpassen

		if($rowSizeFree > 0)
		{
			if($rowSizeFree == 2)
				$numOfTiles2Create = random_int(1,2);
			else
				$numOfTiles2Create = 1;

			$itemListNum = count($drawList[$rowIndex]['itemList']);

			for($o = 0; $o < $numOfTiles2Create; $o++)
			{
				$placeholderItemIndex = random_int(0,$itemListNum + 1);

				$placeholder = new stdClass;
				$placeholder -> postSetting = new stdClass;
				$placeholder -> postSetting -> post_page_color			= '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
				$placeholder -> postSetting -> post_text_color			= 'transparent';
				$placeholder -> postSetting -> post_background_mode	= 0;
				$placeholder -> postSetting -> post_teasertext_mode	= 0;
				$placeholder -> postSetting -> post_size_length_min	= 1;
				$placeholder -> postSetting -> post_size_height		= 1;

				if($numOfTiles2Create == 1)
				{
					$placeholder -> postSetting -> post_size_length_min	= $rowSizeFree;
				}

				if($placeholderItemIndex >= $itemListNum)
				{
					$drawList[$rowIndex]['itemList'][] = [
						'itemSize' => $placeholder -> postSetting -> post_size_length_min,
						'item' 	   => $placeholder,
						'pholder'  => true,
					];
				}
				else
				{
					array_splice( $drawList[$rowIndex]['itemList'], $placeholderItemIndex, 0, [[
						'itemSize' => $placeholder -> postSetting -> post_size_length_min,
						'item' 	   => $placeholder,
						'pholder'  => true,
					]]);
				}
			}
		}

		$i--;
		$rowIndex++;
	}
	else
	{
		$drawList[$rowIndex]['rowSizeUsed'] = $drawList[$rowIndex]['rowSizeUsed'] + $itemSize;

		$itemIndex = count($drawList[$rowIndex]['itemList']);

		if($itemSize === 3)
			$drawList[$rowIndex]['X3ItemIndex'] = $itemIndex;

		$drawList[$rowIndex]['itemList'][] = [
			'itemSize' => $itemSize,
			'item' 	   => $nodeList[$i],
		];

		if($nodeList[$i] -> postSetting -> post_size_length_min === 0)
			$drawList[$rowIndex]['XAutoItemIndexList'][] = $itemIndex;
	}
}
?>

<div class="blog-container blog-tiles">
		
	<?php
	for($rowIndex = 0; $rowIndex < count($drawList); $rowIndex++)
	{
		for($itemIndex = 0; $itemIndex < count($drawList[$rowIndex]['itemList']); $itemIndex++)
		{
			$placeholder = &$drawList[$rowIndex]['itemList'][$itemIndex]['pholder'] ?? false;
			$itemSize 	 = &$drawList[$rowIndex]['itemList'][$itemIndex]['itemSize'];
			$item 		 = &$drawList[$rowIndex]['itemList'][$itemIndex]['item'];

			$categories = [];
			if($item -> postSetting -> post_display_category ?? 0)
			{
				foreach($item -> categories as $nodeCategory)
				{
					$categories[] = $nodeCategory -> category_name;
				}
			}
			?>
			<div class="blog-tiles-item" tile-size="<?= $itemSize; ?>">

				<?php

				$tileBackgroundStyleSet = [];
				$tileTextStyleSet = [];

				switch($item -> postSetting -> post_background_mode)
				{
					case 0:

						$tileBackgroundStyleSet[] = 'background-color:'. $item -> postSetting -> post_page_color;
						break;
						
					case 1:

						$tileBackgroundStyleSet[] = 'background-image:url(\''. $item -> page_image_url_m .'\')';
						break;
				}

				$tileTextStyleSet[] = 'color:'. $item -> postSetting -> post_text_color;

				?>
				
				<?php
				if(!$placeholder) 
				{
					if(CMS_BACKEND)
						echo '<a href="'. CMS_SERVER_URL_BACKEND .'pages/view/'. $item -> page_language .'/'. $item -> node_id .'" title="'. $item -> page_title .'">';
					else
						echo '<a href="'. CMS_SERVER_URL . URL_LANG_PRREFIX . substr($item -> page_path, 1) .'" title="'. $item -> page_title .'" '. ($item -> menu_follow == 0 ? 'rel="nofollow"' : '') .'>';
				}
				?>

				<div class="blog-tiles-item-content">

					<span class="item-content-background <?= ($item -> postSetting -> post_background_mode ? 'background-image-mode' : '') ?> <?= ($placeholder ? 'background-placeholder' : '') ?>" style="<?= implode('; ', $tileBackgroundStyleSet); ?>"></span>

					<div class="item-content-text" style="<?= implode('; ', $tileTextStyleSet); ?>">

						<div class="item-content-text-wrapper <?= ($item -> postSetting -> post_background_mode ? 'text-background-color' : '') ?>">


							<span class="item-content-categories"><?= implode(' / ', $categories); ?></span>

							<span class="item-content-title">

								<?= strip_tags($item -> headline -> body ?? ''); ?>

							</span>

							<?php

							$teserText = '';

							if($itemSize > 1)
							{
								switch($item -> postSetting -> post_teasertext_mode)
								{	
									case 1:

										$teserText = $item -> page_description;
										break;

									case 2:

										$teserText = $item -> text -> body; 
										break;				
								}

								if(!empty($teserText))
								{
									echo '<span class="item-content-teaser">';

									$teserText = substr($teserText, 0, $subStripList[$itemSize] ?? 200); 
									$teserText = substr($teserText, 0, strrpos($teserText, ' ')); 

									echo $teserText;

									if(strlen($item -> text -> body) > strlen($teserText))
										echo '&nbsp...';

									echo '</span>';
								}
							}

							?> 

						</div>

					</div>

				</div>

				<?php
				if(!$placeholder) 
				{
					echo '</a>';
				}
				?>

			</div>
			<?php
		}
	}
	?>

</div>

<div style="clear:both;"></div>

<style>

	div.blog-container.blog-tiles {

		font-size:1rem;
		margin: 0 -5px 0 -5px;
	}

	@media (max-width: 1400px) {
		div.blog-container.blog-tiles {

			font-size:1.1vw;
		}
	}

	div.blog-container.blog-tiles > div.blog-tiles-item { 

		float:left;
	}

	div.blog-container.blog-tiles > div.blog-tiles-item[tile-size="1"] { 

		width:19.959%;
	}

	div.blog-container.blog-tiles > div.blog-tiles-item[tile-size="2"] { 

		width:40%;
	}

	div.blog-container.blog-tiles > div.blog-tiles-item[tile-size="3"] { 

		width:60%;
	}
	
	div.blog-container.blog-tiles > div.blog-tiles-item[tile-size="1"] div.blog-tiles-item-content,
	div.blog-container.blog-tiles > div.blog-tiles-item[tile-size="2"] div.blog-tiles-item-content,
	div.blog-container.blog-tiles > div.blog-tiles-item[tile-size="3"] div.blog-tiles-item-content { 

		height:258px;
	}

	@media (max-width: 1400px) {

		div.blog-container.blog-tiles > div.blog-tiles-item[tile-size="1"] div.blog-tiles-item-content { 

			padding-top:96.62%;
		}

		div.blog-container.blog-tiles > div.blog-tiles-item[tile-size="2"] div.blog-tiles-item-content { 

			padding-top:48.21%;
		}


		div.blog-container.blog-tiles > div.blog-tiles-item[tile-size="3"] div.blog-tiles-item-content { 

			padding-top:32.14%;
		}
	}

	div.blog-container.blog-tiles > div.blog-tiles-item div.blog-tiles-item-content { 

		margin:5px;
		display:flex;
		flex-direction:column;
		justify-content:center;
		text-shadow: 0 0px 4px rgb(255 255 255);
		box-shadow: 0 0 5px 2px rgb(0 0 0 / 10%);
		position:relative;
		overflow:hidden;		
		transition:all 0.5s;
	}

	div.blog-container.blog-tiles > div.blog-tiles-item div.blog-tiles-item-content span.item-content-background { 

		display:block;
		position:absolute;
		top:0px; 
		left:0px;
		width:100%;
		height:100%;
		background-repeat:no-repeat;
		background-position:center center;
		background-size:cover;
  		opacity:0.6;
		transform: scale(1.02) rotate(0.1deg);
		transition:all 0.5s;
	}

	div.blog-container.blog-tiles > div.blog-tiles-item div.blog-tiles-item-content span.item-content-background.background-image-mode { 

  		filter: saturate(0.3);
  		opacity:0.4;
	}

	div.blog-container.blog-tiles > div.blog-tiles-item div.blog-tiles-item-content span.item-content-background:hover { 

  		filter: saturate(1);
		cursor:pointer;
		transition:all 0.5s;
  		opacity:1;
	}

	div.blog-container.blog-tiles > div.blog-tiles-item div.blog-tiles-item-content span.item-content-background.background-placeholder { 

  		filter: unset;
  		opacity:1.0;
		cursor:inherit;
	}

	div.blog-container.blog-tiles > div.blog-tiles-item div.blog-tiles-item-content span.item-content-background.background-image-mode:hover { 

		transform: scale(1.1) rotate(2deg);
	}

	div.blog-container.blog-tiles > div.blog-tiles-item div.blog-tiles-item-content div.item-content-text {

		display:flex;
		flex-direction:column;
		justify-content:center;
		z-index: 1;
		pointer-events:none;
		position:absolute;
		top: 0px;
    	left: 0px;
    	width: 100%;
    	height: 100%;
	}
	
	div.blog-container.blog-tiles > div.blog-tiles-item div.blog-tiles-item-content div.item-content-text-wrapper {

		padding:15px 30px;
		transition:all 0.5s;
	}
	
	div.blog-container.blog-tiles > div.blog-tiles-item div.blog-tiles-item-content:hover div.item-content-text .text-background-color {

		background:rgba(255,255,255,.6);
	}
		
	div.blog-container.blog-tiles > div.blog-tiles-item div.blog-tiles-item-content span.item-content-categories {

		display:block;
		font-size:0.5em;
		font-weight:600;
		text-transform:uppercase;
		letter-spacing:1px;
	}
	div.blog-container.blog-tiles > div.blog-tiles-item div.blog-tiles-item-content span.item-content-title {

		display:block;
		font-size:1.45em;
		font-weight:300;
	}

	div.blog-container.blog-tiles > div.blog-tiles-item div.blog-tiles-item-content span.item-content-teaser {

		display:block;
		font-size:0.9em;
		margin-top:5px;
	}

</style>
