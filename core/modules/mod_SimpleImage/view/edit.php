<input type="hidden" name="cms-object-id" value="<?php echo $object -> object_id; ?>">
<?php

$image_url = MEDIATHEK::getItemUrl($object -> params -> id ?? 0);
$image_url = ($image_url !== null ? $image_url .'?binary&size=large' : $image_url);

?>

<div style="position:relative; padding-top:<?= ($object -> params -> height ?? '45') . ($object -> params -> height_unit ?? '%'); ?>;" id="simple-image-controll-<?php echo $object -> object_id; ?>">

	<div style="position:absolute; top:0; left:0; height:100%; width:100%;" class="simple-image-div-inner">

		<img style="width:100%; height:100%; object-fit:<?= $object -> params -> fit ?? 'cover'; ?>; object-position: <?= ($object -> params -> position_x ?? '50') . ($object -> params -> position_x_unit ?? '%'); ?> <?= ($object -> params -> position_y ?? '50') . ($object -> params -> position_y_unit ?? '%'); ?>;" src="<?= $image_url; ?>">

	</div>

	<div class="ui" style="position:absolute; top:0; left:0; height:100%; width:100%; display:flex; justify-content:center; align-items:center;">

		<div style="display:flex; align-items:center;background-color:grey; color:white;">
			<input type="hidden"  name="simple-image-id" value="<?= $object -> params -> id ?? 38; ?>">
			<button class="ui button icon labeled button-select-mediathek-item" style="border-radius:0px;" id="trigger-simple-image-select-<?php echo $object -> object_id; ?>"><span><i class="far fa-image"></i></span>Select Image</button> 
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

<span style="font-size:0.65em;"><i>after insert this module, reload, otherwise image select does not work</i></span>

<script>

	document.getElementById('simple-image-controll-<?php echo $object -> object_id; ?>').onchange = function(event)
	{
		if(typeof event.target === 'undefined' || typeof event.target.name === 'undefined')
			return false;

		let inputName = event.target.name;
		let	innerNode = this.querySelector('.simple-image-div-inner');
		let	imageNode = this.querySelector('img');
		
		switch(inputName)
		{
			case 'simple-image-height':

				let heightUnit = this.querySelector('select[name="simple-image-height-unit"]');

				switch(heightUnit.value)
				{
					case '%': 

						event.target.value = (event.target.value < 8   ?   8 : event.target.value);
						event.target.value = (event.target.value > 100 ? 100 : event.target.value);
						break;

					case 'px': 
					
						event.target.value = (event.target.value < 50   ?   50 : event.target.value);
						break;
				}

				this.style.paddingTop = event.target.value + heightUnit.value;
				break;

			case 'simple-image-height-unit':

				let height = this.querySelector('input[name="simple-image-height"]');

				switch(event.target.value)
				{
					case '%': 
					
						height.value = (height.value < 8   ?   8 : height.value);
						height.value = (height.value > 100 ? 100 : height.value);
						break;

					case 'px': 
					
						height.value = (height.value < 50   ?   50 : height.value);
						break;
				}

				this.style.paddingTop = height.value + event.target.value;
				break;

			case 'simple-image-fit':

				imageNode.style.objectFit = event.target.value;
				break;

			case 'simple-image-position-x':
			case 'simple-image-position-x-unit':
			case 'simple-image-position-y':
			case 'simple-image-position-y-unit':

				let posX 	 = this.querySelector('input[name="simple-image-position-x"]').value;
				let posXUnit = this.querySelector('select[name="simple-image-position-x-unit"]').value;

				let posY 	 = this.querySelector('input[name="simple-image-position-y"]').value;
				let posYUnit = this.querySelector('select[name="simple-image-position-y-unit"]').value;

				imageNode.style.objectPosition = posX + posXUnit +' '+ posY + posYUnit;
				break;
		}
	};

	document.getElementById('trigger-simple-image-select-<?php echo $object -> object_id; ?>').onclick = function()
	{
		let mediathek = new cmsModalMediathek;
			mediathek.setEventNameOnSelected('event-simple-image-selected-<?php echo $object -> object_id; ?>');
			mediathek.open(cmsMediathek.VIEWMODE_LIST, cmsMediathek.WORKMODE_SELECT);
	};

	function simpleImageOnSelected<?php echo $object -> object_id; ?>(event)
	{
		if(event.detail === null || event.detail.path.length === 0)
			return;

		let simpleImageCtrlNode = document.getElementById('simple-image-controll-<?php echo $object -> object_id; ?>');

		let	imageNode = simpleImageCtrlNode.querySelector('img');
			imageNode.src = CMS.SERVER_URL + "mediathek/" + event.detail.path +"?binary&size=large";

		let	imageIdNode = simpleImageCtrlNode.querySelector('input[name="simple-image-id"]');
			imageIdNode.value = event.detail.media_id;
	}

	window.addEventListener('event-simple-image-selected-<?php echo $object -> object_id; ?>', simpleImageOnSelected<?php echo $object -> object_id; ?>);

</script>