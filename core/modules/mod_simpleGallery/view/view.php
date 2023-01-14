
<div class="simple-gallery-images-list loading-indicator-space" id="simple-galleryimage-list-<?= $object -> object_id; ?>" data-tile-format="<?= ($object -> params -> format ?? '8'); ?>" id="simple-gallery-list-<?php echo $object -> object_id; ?>">

	<div class="loading-indicator" style="clear:both;text-align: center; padding-top:20px; position:absolute; top:100%; left:0px; width:100%;">
		<div class="lds-dual-ring"></div>
	</div>

<div style="clear:both;"><!-- float cleaner ... do not add linebreak or js is broken --></div></div>

<script>

class cmsSimpleGalleryController
{
	constructor()
	{
		this.drawRow 			= 1;		// Active row, just for debug
		this.drawRowXMaxSize	= 1500;
		this.drawImageYSize 	= <?= ($object -> params -> thumb_height ?? '300'); ?>;		// Image Y size regular
		this.drawImageYBuffer 	= 35;		// Image Y size lowering to fit them in row
		this.drawImageYMax 	    = 350;		// Image Y size max on row size corretion
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

			if(cmstk.detectNodeBottomInViewport(containerNode, 200))
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

		cmsXhr.request((typeof CMS.SERVER_URL_BACKEND !== 'undefined' ? CMS.SERVER_URL_BACKEND : CMS.SERVER_URL) + CMS.PAGE_PATH, formData, (response, srcInfo) => {
			
			if(response.state) 
			{
				console.log('xhr Request Failed: '+ response.msg);
				return false;
			}

			srcInfo.srcInstance.requestItemsSuccess(srcInfo.outputNode, Object.values(response.data))

		}, {srcInstance:this, outputNode: outputNode}, 'getItems', outputNode.simpleGallery.objectId, cmsXhr.onXHRError);
	}

	requestItemsSuccess(outputNode, itemList)
	{
		let processedItems = { num: 0};

		let drawList = this.prepareDrawList(outputNode, itemList, processedItems);

		this.drawList(outputNode, drawList);

		outputNode.simpleGallery.requestOffset = outputNode.simpleGallery.requestOffset + processedItems.num;
		outputNode.simpleGallery.lockRequest = false;

		if(cmstk.detectNodeBottomInViewport(outputNode, 200))
			this.requestItems(outputNode);

		if(itemList.length < outputNode.simpleGallery.requestLimit || itemList.length == 0)
		{
			outputNode.simpleGallery.stopRequest = true;
			outputNode.classList.remove('loading-indicator-space');
		}
	}

	rowSizeCorrection(itemsList)
	{
		let percentUsed = 0;

		for(let i = 0; i < itemsList.length; i++)
		{
			percentUsed += itemsList[i].sizeX;
		}

		let percentUnsed = 100 - percentUsed;
		let percentUnusedPerItem = percentUnsed / itemsList.length;
		let drawImageYSize = this.drawRowXMaxSize / ((this.drawRowXMaxSize / 100 * percentUsed) / this.drawImageYSize);

		if(drawImageYSize > this.drawImageYMax)
			drawImageYSize = this.drawImageYMax;

		for(let i = 0; i < itemsList.length; i++)
		{
			let scaleFaktor     = itemsList[i].sizeX / itemsList[i].sizeY
			itemsList[i].sizeX += percentUnusedPerItem;
			itemsList[i].sizeY  = drawImageYSize / this.drawRowXMaxSize * 100;;
		}
		return itemsList;
	}

	prepareDrawList(outputNode, itemList, processedItems)
	{
		let drawItems = [];

		let drawItemsBuffer  	= [];
		let drawItemsBuffer_B 	= [];
		let drawRowXUsedSize 	= 0;		
		let drawRowXUsedSize_B 	= 0;	

		let outputNodeRect = outputNode.getBoundingClientRect();

		let drawImageYSize		= this.drawImageYSize;
			drawImageYSize		= drawImageYSize / this.drawRowXMaxSize * outputNodeRect.width;

		let image = Object.keys(itemList);

		for(let i = 0; i < image.length; i++)
		{
			let scaleFaktor 	= itemList[image[i]].props[1] / drawImageYSize
			let drawImageXSize 	= itemList[image[i]].props[0] / scaleFaktor;
			let percentXSize	= drawImageXSize * 100 / this.drawRowXMaxSize;
			let percentYPadding = drawImageYSize / this.drawRowXMaxSize * 100;

			let scaleFaktor______B = itemList[image[i]].props[1] / (drawImageYSize - this.drawImageYBuffer)
			let drawImageXSize___B = itemList[image[i]].props[0] / scaleFaktor______B;
			let percentXSize_____B = drawImageXSize___B * 100 / this.drawRowXMaxSize;
			let percentYPadding__B = (drawImageYSize - this.drawImageYBuffer) / this.drawRowXMaxSize * 100;

			drawRowXUsedSize  	+= drawImageXSize;
			drawRowXUsedSize_B 	+= drawImageXSize___B;

			if((drawRowXUsedSize) > this.drawRowXMaxSize)
			{
				if((drawRowXUsedSize_B) > this.drawRowXMaxSize)
				{
					drawItemsBuffer = this.rowSizeCorrection(drawItemsBuffer);

					processedItems.num += drawItemsBuffer.length;

					drawItems = drawItems.concat(drawItemsBuffer);

					drawRowXUsedSize = 0;
					drawRowXUsedSize_B = 0;
					drawItemsBuffer = [];
					drawItemsBuffer_B = [];
					this.drawRow++;
					i--;
					continue;
				}
				else
				{
					drawItemsBuffer_B.push({
						image:itemList[image[i]],
						sizeX: percentXSize_____B,
						sizeY: percentYPadding__B
					});

					drawItemsBuffer_B = this.rowSizeCorrection(drawItemsBuffer_B);

					processedItems.num += drawItemsBuffer_B.length;

					drawItems = drawItems.concat(drawItemsBuffer_B);

					drawRowXUsedSize = 0;
					drawRowXUsedSize_B = 0;
					drawItemsBuffer = [];
					drawItemsBuffer_B = [];
					
					this.drawRow++;
				
					continue;
				}
			}

			drawItemsBuffer.push({
				image: itemList[image[i]],
				sizeX: percentXSize,
				sizeY: percentYPadding
			});

			drawItemsBuffer_B.push({
				image:itemList[image[i]],
				sizeX: percentXSize_____B,
				sizeY: percentYPadding__B
			});
		}

		if(image.length < outputNode.simpleGallery.requestLimit)
		{
			// Process rest of data

			this.drawRow++;

			// drawItemsBuffer = this.rowSizeCorrection(drawItemsBuffer);
			processedItems.num += drawItemsBuffer.length;
			drawItems = drawItems.concat(drawItemsBuffer);
		}

		return drawItems;
	}

	drawList(outputNode, itemList)
	{
		let imageSize = 'thumb';

		for(let i = 0; i < itemList.length; i++)
		{
			let aNode = document.createElement('a');
				aNode.href = '<?= CMS_SERVER_URL.DIR_MEDIATHEK; ?>'+ itemList[i].image.path +'/?size=xlarge'
				aNode.innerHTML = '<img src="<?= CMS_SERVER_URL.DIR_MEDIATHEK; ?>'+ itemList[i].image.path +'/?binary&size='+ imageSize +'">'

				aNode.style.width 		= itemList[i].sizeX +'%';
				aNode.style.paddingTop  = itemList[i].sizeY +'%';
				
			outputNode.insertBefore(aNode, outputNode.lastChild);
		}
	}
}

document.cmsSimpleGalleryController = new cmsSimpleGalleryController();

document.addEventListener('DOMContentLoaded', () => {

	document.cmsSimpleGalleryController.create('simple-galleryimage-list-<?= $object -> object_id; ?>', <?= $object -> object_id; ?>, <?= json_encode($itemList); ?>);

});

</script>

<style>

	div.simple-gallery-images-list.loading-indicator-space { margin-bottom:40px; }
	div.simple-gallery-images-list:not(.loading-indicator-space) .loading-indicator { display:none;  }
	div.simple-gallery-images-list > a { float:left; position:relative; }
	div.simple-gallery-images-list > a > img { width: 100%; height:100%; object-fit: cover; object-position: 50% 50%; position:absolute; top:0px; left:0px; border:solid transparent 3px; }

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
