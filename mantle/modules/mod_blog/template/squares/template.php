
<div class="blog-container blog-tiles" id="blog-tiles-container"></div>

<div id="blog-tiles-container-loading-indicator" style="clear:both;text-align: center; padding-top:20px;">
	<div class="lds-dual-ring"></div>
</div>

<script>
class blogSquaresController
{
	constructor(objectId, nodeList)
	{
 		let blogNode = document.getElementById('blog-tiles-container');
		let blogNodeRect = blogNode.getBoundingClientRect()

		if(blogNodeRect.width < 500)
		{
			this.rowSizeLimit 		= 2;
		}
		else if(blogNodeRect.width < 800)
		{
			this.rowSizeLimit 		= 3;
		}
		else if(blogNodeRect.width < 1100)
		{
			this.rowSizeLimit 		= 4;
		}
		else
		{
			this.rowSizeLimit 		= 5;
		}

		this.rowIndex 	  		= 0;
		this.subStripList 		= [];
		this.subStripList[2]	= 100;
		this.subStripList[3]	= 160;
		
		this.requestLimit		= nodeList.length;
		this.requestOffset		= 0;

		this.stopRequest		= false;
		this.lockRequest		= false;

		this.objectId			= objectId;

		this.requestItemsSuccess(nodeList, this);

		let srcInstance = this;

		addEventListener("scroll", (event) => {

			if(cmstk.detectNodeInViewport(document.getElementById('blog-tiles-container-loading-indicator'), 200))
				srcInstance.requestItems();

		});

	}

	requestItems()
	{
		if(this.lockRequest)
			return;

		if(this.stopRequest)
			return;

		this.lockRequest = true;

		let formData = new FormData;
			formData.set('requestLimit', this.requestLimit);
			formData.set('requestOffset', this.requestOffset);

		cmsXhr.request((typeof CMS.SERVER_URL_BACKEND !== 'undefined' ? CMS.SERVER_URL_BACKEND : CMS.SERVER_URL) + CMS.PAGE_PATH, formData, (response, srcInstance) => {
			
			if(response.state) 
			{
				console.log('xhr Request Failed: '+ response.msg);
				return false;
			}

			srcInstance.requestItemsSuccess(Object.values(response.data), srcInstance)


		}, this, 'getBlogItems', this.objectId, cmsXhr.onXHRError);

	}

	requestItemsSuccess(nodeList, srcInstance)
	{
		if(!nodeList.length)
		{

			srcInstance.stopRequest = true;
			document.getElementById('blog-tiles-container-loading-indicator').innerHTML = '';
			return;
		}

		let drawList = srcInstance.prepareDrawListItems(nodeList);
		srcInstance.drawList(drawList);
		srcInstance.lockRequest = false;

		if(cmstk.detectNodeInViewport(document.getElementById('blog-tiles-container-loading-indicator'), 400))
			srcInstance.requestItems();
	}

	prepareDrawListItems(nodeList)
	{

		let drawList = [];

		for(let i = 0; i < nodeList.length; i++)
		{
			if(typeof drawList[this.rowIndex] === 'undefined')
			{
				drawList[this.rowIndex] = {}
				drawList[this.rowIndex].X3ItemIndex 		= null			// Item index of size 3 item
				drawList[this.rowIndex].XAutoItemIndexList 	= []			// Item index list of auto size items
				drawList[this.rowIndex].rowIndex 			= this.rowIndex	// Index of this row
				drawList[this.rowIndex].rowSizeUsed			= 0				// Total item sizes
				drawList[this.rowIndex].itemList 			= []			// Item list of assigned tiles
				
			}

			if(typeof nodeList[i].postSetting === 'undefined' || nodeList[i].postSetting === null)
			{
				nodeList[i].postSetting = {
				post_page_color			: 0,
				post_text_color			: 0,
				post_background_mode	: 0,
				post_teasertext_mode	: 0,
				post_size_length_min	: 1,
				post_size_height		: 1
				};
			}

			let itemSize = nodeList[i].postSetting.post_size_length_min == 0 ? 1 : nodeList[i].postSetting.post_size_length_min;

			if(this.rowSizeLimit < itemSize)
				itemSize = this.rowSizeLimit;

			if(this.rowSizeLimit == 2 &&  itemSize == 1)
				itemSize = 2;

			if((drawList[this.rowIndex].rowSizeUsed + itemSize) > this.rowSizeLimit)
			{
				let rowSizeFree = this.rowSizeLimit - drawList[this.rowIndex].rowSizeUsed;

				// TODO: Hier schauen ob ein auto item vorhanden dessen size geändert werden kann, anschließend rowSizeFree anpassen

				if(rowSizeFree > 0)
				{
					let numOfTiles2Create = 1;

					if(rowSizeFree == 2)
						numOfTiles2Create = cmstk.getRandomInteger(1,2);

					let itemListNum = drawList[this.rowIndex].itemList.length;

					for(let o = 0; o < numOfTiles2Create; o++)
					{
						let placeholderItemIndex = cmstk.getRandomInteger(0, itemListNum + 1);

						let randomColor = cmstk.getRandomInteger(0, 0xFFFFFF).toString(16);
							randomColor = randomColor.padStart(6, '0');

						let placeholder = {};
						placeholder.postSetting = {};
						placeholder.postSetting.post_page_color			= '#' + randomColor;
						placeholder.postSetting.post_text_color			= 'transparent';
						placeholder.postSetting.post_background_mode	= 0;
						placeholder.postSetting.post_teasertext_mode	= 0;
						placeholder.postSetting.post_size_length_min	= 1;
						placeholder.postSetting.post_size_height		= 1;

						if(numOfTiles2Create == 1)
						{
							placeholder.postSetting.post_size_length_min	= rowSizeFree;
						}

						if(placeholderItemIndex >= itemListNum)
						{
							drawList[this.rowIndex].itemList.push({
								itemSize : placeholder.postSetting.post_size_length_min,
								item 	 : placeholder,
								pholder  : true
							});
						}
						else
						{
							drawList[this.rowIndex].itemList.splice(placeholderItemIndex, 0, {
								itemSize : placeholder.postSetting.post_size_length_min,
								item 	 : placeholder,
								item 	 : placeholder,
								pholder  : true
							});
						}
					}
				}

				i--;
				this.rowIndex++;
			}
			else
			{
				drawList[this.rowIndex].rowSizeUsed = drawList[this.rowIndex].rowSizeUsed + itemSize;

				let itemIndex = drawList[this.rowIndex].itemList.length;

				if(itemSize === 3)
					drawList[this.rowIndex].X3ItemIndex = itemIndex;

				drawList[this.rowIndex].itemList.push({
					itemSize : itemSize,
					item 	   : nodeList[i]
				});

				if(nodeList[i].postSetting.post_size_length_min === 0)
					drawList[this.rowIndex].XAutoItemIndexList.push(itemIndex);

			}
		}

		let numItemsToDraw = 0;

		if(nodeList.length === this.requestLimit)
		{
			for(let i = 0; i < drawList.length; i++)
			{
				if(!drawList[i])
					continue;

				let rowSizeFillState = 0;

				let rowNumItemsToDraw = 0;

				for(let i2 = 0; i2 < drawList[i].itemList.length; i2++)
				{
					rowSizeFillState = rowSizeFillState + drawList[i].itemList[i2].itemSize;

					if(typeof drawList[i].itemList[i2].pholder !== 'undefined' && drawList[i].itemList[i2].pholder) ;else 
					{
						rowNumItemsToDraw++;
					}
				}

				if(rowSizeFillState < this.rowSizeLimit)
				{
					drawList[i] = null;
					continue;
				}

				numItemsToDraw = numItemsToDraw + rowNumItemsToDraw;
			}
		}
		else
		{
			this.stopRequest = true;
			document.getElementById('blog-tiles-container-loading-indicator').innerHTML = '';
		}

		this.requestOffset = this.requestOffset + numItemsToDraw;

		return drawList;
	}

	drawList(drawList)
	{
 		let blogNode = document.getElementById('blog-tiles-container');

		for(let rowIndex = 0; rowIndex < drawList.length; rowIndex++)
		{
			if(!drawList[rowIndex])
				continue;

			for(let itemIndex = 0; itemIndex < drawList[rowIndex]['itemList'].length; itemIndex++)
			{
				let placeholder = drawList[rowIndex]['itemList'][itemIndex]['pholder'] ?? false;
				let itemSize 	 = drawList[rowIndex]['itemList'][itemIndex]['itemSize'];
				let item 		 = drawList[rowIndex]['itemList'][itemIndex]['item'];

				if(typeof item === 'undefined')
					continue; 
				let categories = [];

				if(item.postSetting.post_display_category ?? 0)
				{
					for(let catIndex = 0; catIndex < item.categories.length; catIndex++)
					{
						categories.push(item.categories[catIndex].category_name);
					}
				}


				let itemNode = document.createElement('div');
					itemNode.classList.add('blog-tiles-item');
					itemNode.setAttribute('tile-size', itemSize)
					itemNode.setAttribute('row-size', this.rowSizeLimit)



				let tileBackgroundStyleSet = [];
				let tileTextStyleSet = [];

				switch(item.postSetting.post_background_mode)
				{
					case 0:

						tileBackgroundStyleSet.push('background-color:'+ item.postSetting.post_page_color);
						break;
						
					case 1:

						tileBackgroundStyleSet.push('background-image:url(\''+ item.page_image_url_m +'\')');
						break;
				}

				tileTextStyleSet.push('color:'+ item.postSetting.post_text_color);

				let linkNode = null;

				if(!placeholder) 
				{ 
					<?php
						if(CMS_BACKEND)
							echo "let pageUrl = '". CMS_SERVER_URL_BACKEND ."pages/view/'+ item.page_language +'/'+ item.node_id";
						else
							echo 'let pageUrl = CMS.SERVER_URL + item.url;';
					?>

					if(typeof item.page_redirect !== 'undefined' && item.page_redirect !== null)
						pageUrl = item.page_redirect;


					linkNode = document.createElement('a');
					linkNode.href = pageUrl;
					linkNode.title = item.page_title;

					if(typeof item.page_redirect !== 'undefined')
						linkNode.target = 'about:blank';

					if(!item.menu_follow)
						linkNode.setAttribute('rel', 'nofollow');
				}
			
				let itemContent = '';
					itemContent += '<div class="blog-tiles-item-content">';
					itemContent += '<span class="item-content-background '+ (item.postSetting.post_background_mode ? 'background-image-mode' : '') +' '+ (placeholder ? 'background-placeholder' : '') +'" style="'+ tileBackgroundStyleSet.join(';') +'"></span>';
					itemContent += '<div class="item-content-text" style="'+ tileTextStyleSet.join(';') +'">';
					itemContent += '<div class="item-content-text-wrapper '+ (item.postSetting.post_background_mode ? 'text-background-color' : '') +'">';
					itemContent += '<span class="item-content-categories">'+ categories.join(' / ') +'</span>';

				if(typeof item.headline !== 'undefined' && item.headline !== null)
					itemContent += '<span class="item-content-title">'+ (item.headline.body.replace(/<[^>]*>?/gm, '')?? '') +'</span>';

				let teaserText = '';



				if(item.text === null)
					item.text = {body:''}

				if(itemSize > 1)
				{
					switch(item.postSetting.post_teasertext_mode)
					{	
						case 1:

							teaserText = item.page_description;
							break;

						case 2:

							teaserText = item.text.body; 
							break;				
					}

					if(teaserText != '')
					{
						teaserText = teaserText.substr(0, this.subStripList[itemSize] ?? 200); 
						teaserText = teaserText.substr(0, teaserText.lastIndexOf(' '))

						itemContent += '<span class="item-content-teaser">'+ teaserText + (teaserText.length && item.text.body.length > teaserText.length ? '&nbsp;...' : '') +'</span>';
					}
				}

				itemContent += '</div>';
				itemContent += '</div>';
				itemContent += '</div>';
					
				if(linkNode !== null)
				{
					linkNode.innerHTML = itemContent;
					itemNode.append(linkNode);
				}
				else
				{
					itemNode.innerHTML = itemContent;
				}
						
				blogNode.append(itemNode);
			}
		}
	}

}

document.addEventListener('DOMContentLoaded', () => {
document.blogSquaresController = new blogSquaresController(<?= $objectId; ?>, <?= json_encode($nodeList); ?>);
});

</script>

<style>

	div.blog-container.blog-tiles {

		font-size:1rem;
		margin: 0 -5px 0 -5px;
	}


	div.blog-container.blog-tiles > div.blog-tiles-item { 

		float:left;
	}


	div.blog-container.blog-tiles > div.blog-tiles-item[row-size="5"] { 
		font-size:1rem;
	}

	@media (max-width: 1400px) {

		div.blog-container.blog-tiles > div.blog-tiles-item[row-size="5"] { 
			font-size:1.1vw;
		}

	}

	div.blog-container.blog-tiles > div.blog-tiles-item[row-size="4"] { 
		font-size:1.6vw;
	}

	div.blog-container.blog-tiles > div.blog-tiles-item[row-size="3"] { 
		font-size:1.8vw;
	}

	div.blog-container.blog-tiles > div.blog-tiles-item[row-size="2"] { 
		font-size:4.0vw;
	}


	div.blog-container.blog-tiles > div.blog-tiles-item[tile-size="1"][row-size="5"] { 

		width:20%;
	}

	div.blog-container.blog-tiles > div.blog-tiles-item[tile-size="1"][row-size="4"] { 

		width:25%;
	}

	div.blog-container.blog-tiles > div.blog-tiles-item[tile-size="1"][row-size="3"] { 

		width:33.33%;
	}

	div.blog-container.blog-tiles > div.blog-tiles-item[tile-size="1"][row-size="2"] { 

		width:50%;
	}

	div.blog-container.blog-tiles > div.blog-tiles-item[tile-size="2"][row-size="5"] { 
		width:40%;
	}

	div.blog-container.blog-tiles > div.blog-tiles-item[tile-size="2"][row-size="4"] { 
		width:50%;
	}

	div.blog-container.blog-tiles > div.blog-tiles-item[tile-size="2"][row-size="3"] { 
		width:66.66%;
	}

	div.blog-container.blog-tiles > div.blog-tiles-item[tile-size="2"][row-size="2"] { 
		width:100%;
	}

	div.blog-container.blog-tiles > div.blog-tiles-item[tile-size="3"][row-size="5"] { 
		width:60%;
	}
	
	div.blog-container.blog-tiles > div.blog-tiles-item[tile-size="3"][row-size="4"] { 
		width:75%;
	}
	
	div.blog-container.blog-tiles > div.blog-tiles-item[tile-size="3"][row-size="3"] { 
		width:100%;
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
		/*text-shadow: 0 0px 4px rgb(255 255 255);*/
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

  		filter: saturate(0.2);
  		opacity:0.2;
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
		font-size:0.54em;
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
