<input type="hidden" name="cms-object-id" value="<?php echo $object -> object_id; ?>">
<?php

$image_url = MEDIATHEK::getItemUrl($object -> params -> id ?? 0);
$image_url = ($image_url !== null ? $image_url .'?binary&size=large' : $image_url);

?>

<div style="position:relative; padding-top:<?= ($object -> params -> height ?? '45') . ($object -> params -> height_unit ?? '%'); ?>;" class="simple-image-controll">

	<div style="position:absolute; top:0; left:0; height:100%; width:100%;" class="simple-image-div-inner">

		<img style="width:100%; height:100%; object-fit:<?= $object -> params -> fit ?? 'cover'; ?>; object-position: <?= ($object -> params -> position_x ?? '50') . ($object -> params -> position_x_unit ?? '%'); ?> <?= ($object -> params -> position_y ?? '50') . ($object -> params -> position_y_unit ?? '%'); ?>;" src="<?= $image_url; ?>">

	</div>

	<div class="ui" style="position:absolute; top:0; left:0; height:100%; width:100%; display:flex; justify-content:center; align-items:center;">

		<div style="display:flex; align-items:center;background-color:grey; color:white;">
			<input type="hidden"  name="simple-image-id" value="<?= $object -> params -> id ?? 38; ?>">
			<button class="ui button icon labeled button-select-mediathek-iteem" style="border-radius:0px;" id="trigger-simple-image-select"><span style="pointer-events:none;"><i class="far fa-image"></i></span>Select Image</button> 
		</div>

		<div style="display:flex; align-items:center;background-color:grey; color:white;">
			<div style="padding: 0 10px; font-size:0.85em;">Height</div>
			<input type="text" name="simple-image-height" style="width:60px; border:0px; border-right:1px solid grey; height:34px; text-align:right;" value="<?= $object -> params -> height ?? 38; ?>">	
			<div class="input">
				<div class="select-wrapper">
					<select name="simple-image-height-unit" style="width:70px; border:0px; border-radius:0;">
						<option <?= (($object -> params -> height_unit ?? '%') === '%' ? 'selected' : ''); ?> value="%">%</option>
						<option <?= (($object -> params -> height_unit ?? '%') === 'px' ? 'selected' : ''); ?> value="px">px</option>
					</select>
				</div>	
			</div>
		</div>

		<div style="display:flex; align-items:center;background-color:grey; color:white;">
			<div style="padding: 0 10px; font-size:0.85em;">Image Fit</div>
			<div class="input">
				<div class="select-wrapper">
					<select name="simple-image-fit" style="width:130px; border:0px; border-radius:0;">
						<option <?= (($object -> params -> fit ?? 'cover') === 'cover' ? 'selected' : ''); ?> value="cover">Cover</option>
						<option <?= (($object -> params -> fit ?? 'cover') === 'contain' ? 'selected' : ''); ?> value="contain">Contain</option>
					</select>
				</div>	
			</div>
		</div>

		<div style="display:flex; align-items:center;background-color:grey; color:white;">
			<div style="padding: 0 10px; font-size:0.85em;">Image Postion</div>
			<div style="padding: 0 10px; font-size:0.85em;">X</div>
			<input type="text" name="simple-image-position-x" style="width:60px; border:0px; border-right:1px solid grey; height:34px; text-align:right;" value="<?= $object -> params -> position_x ?? 50; ?>">	
			<div class="input">
				<div class="select-wrapper">
					<select name="simple-image-position-x-unit" style="width:70px; border:0px; border-radius:0;">
						<option <?= (($object -> params -> position_x_unit ?? '%') === '%' ? 'selected' : ''); ?> value="%">%</option>
						<option <?= (($object -> params -> position_x_unit ?? '%') === 'px' ? 'selected' : ''); ?> value="px">px</option>
					</select>
				</div>	
			</div>
			<div style="padding: 0 10px; font-size:0.85em;">Y</div>
			<input type="text" name="simple-image-position-y" style="width:60px; border:0px; border-right:1px solid grey; height:34px; text-align:right; border-left:1px solid grey" value="<?= $object -> params -> position_y ?? 50; ?>">	
			<div class="input">
				<div class="select-wrapper">
					<select name="simple-image-position-y-unit" style="width:70px; border:0px; border-radius:0;">
						<option <?= (($object -> params -> position_y_unit ?? '%') === '%' ? 'selected' : ''); ?> value="%">%</option>
						<option <?= (($object -> params -> position_y_unit ?? '%') === 'px' ? 'selected' : ''); ?> value="px">px</option>
					</select>
				</div>	
			</div>
		</div>

	</div>

</div>	
