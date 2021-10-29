<?php

$image_url = MEDIATHEK::getItemUrl($object -> params -> id ?? 0);
$image_url = ($image_url !== null ? $image_url .'?binary&size=large' : $image_url);

?>

<div class="simple-image" style="position:relative; padding-top:<?= $object -> params -> height . $object -> params -> height_unit; ?>">

	<div style="position:absolute; top:0; left:0; height:100%; width:100%;">

		<img style="width:100%; height:100%; object-fit:<?= $object -> params -> fit; ?>; object-position: <?= $object -> params -> position_x . $object -> params -> position_x_unit; ?> <?= $object -> params -> position_y . $object -> params -> position_y_unit; ?>;" src="<?= $image_url; ?>">

	</div>

</div>	
