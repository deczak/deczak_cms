<input type="hidden" name="cms-object-id" value="<?php echo $object -> object_id; ?>">

<?php
$image_url = MEDIATHEK::getItemUrl($object -> params -> id ?? 0);
$image_url = ($image_url !== null ? $image_url .'?binary&size=large' : $image_url);
?>

<div class="page-edit-module-controls-panel simple-image-control" data-target-list="simple-image-container-<?php echo $object -> object_id; ?>">

	<div class="left">

		<div class="module-header" style="white-space:nowrap; padding:0 8px; font-weight:700; font-size:1.1em;">
			<i class="fas fa-image"></i>&nbsp;&nbsp;&nbsp;Image
		</div> 

	</div>

	<div class="right">

		<div style="display:flex; align-items:center; ">
			<button class="ui button icon labeled trigger-simple-image-select-modal" style="border-radius:0px; height:29px;" id="trigger-simple-image-select"><span style="pointer-events:none;"><i class="far fa-image"></i></span>Select Image</button> 
		</div>


		<div style="display:flex; align-items:center;">
			<label>Image Height</label>	
			<input type="text" name="simple-image-height" style="width:55px; height:29px; text-align:right; border-right:0px;" value="<?= $object -> params -> height ?? 38; ?>">	
			<div class="input">
				<div class="select-wrapper">
					<select name="simple-image-height-unit" style="width:45px; border-radius:0; padding: 5px;">
						<option <?= (($object -> params -> height_unit ?? '%') === '%' ? 'selected' : ''); ?> value="%">%</option>
						<option <?= (($object -> params -> height_unit ?? '%') === 'px' ? 'selected' : ''); ?> value="px">px</option>
					</select>
				</div>	
			</div>
		</div>

		<div style="display:flex; align-items:center;">
			<label>Image Fit</label>	
			<div class="input">
				<div class="select-wrapper">
					<select name="simple-image-fit" style="width:130px; border-radius:0; padding: 5px;">
						<option <?= (($object -> params -> fit ?? 'cover') === 'cover' ? 'selected' : ''); ?> value="cover">Cover</option>
						<option <?= (($object -> params -> fit ?? 'cover') === 'contain' ? 'selected' : ''); ?> value="contain">Contain</option>
						<option <?= (($object -> params -> fit ?? 'cover') === 'none' ? 'selected' : ''); ?> value="none">None</option>
					</select>
				</div>	
			</div>
		</div>

		<div style="display:flex; align-items:center;">
			<label>Image Postion</label>	
			<label>X</label>	
			<input type="text" name="simple-image-position-x" style="width:55px; height:29px; text-align:right; border-right:0px;" value="<?= $object -> params -> position_x ?? 50; ?>">	
			<div class="input">
				<div class="select-wrapper">
					<select name="simple-image-position-x-unit" style="width:45px; border-radius:0; padding: 5px;">
						<option <?= (($object -> params -> position_x_unit ?? '%') === '%' ? 'selected' : ''); ?> value="%">%</option>
						<option <?= (($object -> params -> position_x_unit ?? '%') === 'px' ? 'selected' : ''); ?> value="px">px</option>
					</select>
				</div>	
			</div>
			<label>Y</label>	
			<input type="text" name="simple-image-position-y" style="width:55px; height:29px; text-align:right; border-right:0px;" value="<?= $object -> params -> position_y ?? 50; ?>">	
			<div class="input">
				<div class="select-wrapper">
					<select name="simple-image-position-y-unit" style="width:45px; border-radius:0; padding: 5px;">
						<option <?= (($object -> params -> position_y_unit ?? '%') === '%' ? 'selected' : ''); ?> value="%">%</option>
						<option <?= (($object -> params -> position_y_unit ?? '%') === 'px' ? 'selected' : ''); ?> value="px">px</option>
					</select>
				</div>	
			</div>
		</div>
	
	</div>

</div>

<div style="position:relative; padding-top:<?= ($object -> params -> height ?? '45') . ($object -> params -> height_unit ?? '%'); ?>;" class="simple-image-container simple-image-container-<?php echo $object -> object_id; ?>">

	<input type="hidden"  name="simple-image-id" value="<?= $object -> params -> id ?? 0; ?>">
	<img style="width:100%; height:100%; top:0px; left:0px; position:absolute; object-fit:<?= $object -> params -> fit ?? 'cover'; ?>; object-position: <?= ($object -> params -> position_x ?? '50') . ($object -> params -> position_x_unit ?? '%'); ?> <?= ($object -> params -> position_y ?? '50') . ($object -> params -> position_y_unit ?? '%'); ?>;" src="<?= $image_url; ?>">

</div>	
