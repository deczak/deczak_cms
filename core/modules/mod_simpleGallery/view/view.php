
<div class="simple-gallery-images-list loading-indicator-space" id="simple-galleryimage-list-<?= $object -> object_id; ?>" data-tile-size="<?= ($object -> params -> display_divider ?? '8'); ?>" data-tile-format="<?= ($object -> params -> format ?? '8'); ?>" id="simple-gallery-list-<?php echo $object -> object_id; ?>">


	<div class="loading-indicator" style="clear:both;text-align: center; padding-top:20px; position:absolute; top:100%; left:0px; width:100%;">
		<div class="lds-dual-ring"></div>
	</div>

</div>

<script>

class cmsSimpleGalleryController
{
	constructor()
	{
	}

	create(nodeId, objectId, initialItemList)
	{
		let containerNode = document.getElementById(nodeId);

		containerNode.simpleGallery = {};
		containerNode.simpleGallery.requestLimit	= initialItemList.length;
		containerNode.simpleGallery.requestOffset	= 0;
		containerNode.simpleGallery.stopRequest		= false;
		containerNode.simpleGallery.lockRequest		= false;
		containerNode.simpleGallery.objectId		= objectId;

		let srcInstance = this;

		srcInstance.requestItemsSuccess(containerNode, initialItemList);

		addEventListener("scroll", (event) => {

			if(cms_tk.detectNodeBottomInViewport(containerNode, 200))
				srcInstance.requestItems(containerNode);

		});
	}

	requestItems(outputNode)
	{
		if(outputNode.simpleGallery.lockRequest)
			return;

		if(outputNode.simpleGallery.stopRequest)
			return;

		outputNode.simpleGallery.lockRequest = true;

		let formData = new FormData;
			formData.set('requestLimit', outputNode.simpleGallery.requestLimit);
			formData.set('requestOffset', outputNode.simpleGallery.requestOffset);

		cms_xhr.request(CMS.SERVER_URL + CMS.PAGE_PATH, formData, (response, srcInfo) => {
			
			if(response.state) 
			{
				console.log('xhr Request Failed: '+ response.msg);
				return false;
			}

			srcInfo.srcInstance.requestItemsSuccess(srcInfo.outputNode, Object.values(response.data))

		}, {srcInstance:this, outputNode: outputNode}, 'getItems', outputNode.simpleGallery.objectId, cms_xhr.onXHRError);
	}

	requestItemsSuccess(outputNode, itemList)
	{
		this.drawList(outputNode, itemList);

		outputNode.simpleGallery.requestOffset = outputNode.simpleGallery.requestOffset + itemList.length;
		outputNode.simpleGallery.lockRequest = false;

		if(cms_tk.detectNodeBottomInViewport(outputNode, 200))
			this.requestItems(outputNode);


		if(itemList.length < outputNode.simpleGallery.requestLimit)
		{
			outputNode.simpleGallery.stopRequest = true;

			outputNode.classList.remove('loading-indicator-space');
		}



	}

	drawList(outputNode, itemList)
	{
		let ts = <?= ($object -> params -> display_divider ?? '8'); ?>;

		loopDo:
		do {

			let rp = 0;
			let bp = [];
			let bl = [];
			let rp2= 0;

			let imageSize = 'thumb';

			switch((<?= $object -> params -> display_divider ?? '8'; ?>))
			{
				case '2': 
					imageSize = 'medium'; break;
				case '3': 
					imageSize = 'small'; break;

			}

			for(let item of itemList)
			{
				switch(item.mime )
				{
					case 'image/jpeg':
					case 'image/png':
					case 'image/png':

						break;

					default:

						continue loopDo;
				}

				switch(item.orient)
				{
					case 0:

						rp2 = 2;
						break;

					case 1:

						rp2 = 1;
						break;
				}

				if((rp + rp2) > ts)
				{
					if(rp == (ts - 1) && bp.length > 0)
					{
						let itemp = bp.pop();

						let aNode = document.createElement('a');
							aNode.classList.add('orient-'+ itemp.orient);
							aNode.href = '<?= CMS_SERVER_URL.DIR_MEDIATHEK; ?>'+ itemp.path +'/?size=xlarge'
							aNode.innerHTML = '<img src="<?= CMS_SERVER_URL.DIR_MEDIATHEK; ?>'+ itemp.path +'/?binary&size='+ imageSize +'">'

						outputNode.appendChild(aNode);
					}

					rp = 0;

					switch(item.orient)
					{
						case 0:

							bl.push(item);
							break;

						case 1:

							bp.push(item);
							break;
					}

					continue;
				}

				rp = rp + rp2;

				let aNode = document.createElement('a');
					aNode.classList.add('orient-'+ item.orient);
					aNode.href = '<?= CMS_SERVER_URL.DIR_MEDIATHEK; ?>'+ item.path +'/?size=xlarge'
					aNode.innerHTML = '<img src="<?= CMS_SERVER_URL.DIR_MEDIATHEK; ?>'+ item.path +'/?binary&size='+ imageSize +'">'

				outputNode.appendChild(aNode);			
			}

			itemList = bp.concat(bl);

			if(itemList.length == 0)
				break;

		} while (true);
	}
}

document.cmsSimpleGalleryController = new cmsSimpleGalleryController();

document.addEventListener('DOMContentLoaded', () => {

	document.cmsSimpleGalleryController.create('simple-galleryimage-list-<?= $object -> object_id; ?>', <?= $object -> object_id; ?>, <?= json_encode($itemList); ?>);

});

</script>

<style>


/*

	temporary solution with that media queries


	max-width - 4% [2 + 2] padding / num tiles * 100 / (max-width - 4% padding) = height %

	1400 - 4% / 6 * 100 / (1344) = 16.6%



*/



div.simple-gallery-images-list { display:flex; flex-wrap:wrap; position: relative; margin-bottom:40px; }

div.simple-gallery-images-list.loading-indicator-space { margin-bottom:40px; }
div.simple-gallery-images-list:not(.loading-indicator-space) .loading-indicator { display:none;  }

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

<style>

	.lds-dual-ring {
		display: inline-block;
		width: 60px;
		height: 60px;
	}

	.lds-dual-ring:after {
		content: " ";
		display: block;
		width: 44px;
		height: 44px;
		margin: 4px;
		border-radius: 50%;
		border: 6px solid gold;
		border-color: gold transparent gold transparent;
		animation: lds-dual-ring 1.2s linear infinite;
	}

	@keyframes lds-dual-ring {
		0%   { transform: rotate(0deg); }
		100% { transform: rotate(360deg); }
	}

</style>
