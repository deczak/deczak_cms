
// This class is modified for handling two type of data and additional functions

class	cmsIndexList
{
	constructor()
	{
	}
	
	init(languagesList, activeLanguage)
	{
		this.requestData(activeLanguage);

		this.languagesList = languagesList;
	}

	onXHRSuccess(response, callInstance)
	{

		console.log(response);

		if(response.state != 0)
		{
			return;
		}
		
		let	tableBody 	= document.getElementById('page-list-overview');
			tableBody.innerHTML = '';

		let listNode = document.createElement('ul');

		tableBody.append(listNode);

		callInstance.constructNestedList(response.data, listNode);
	}

	constructNestedList(pageList, destNode)
	{
		for(let i in pageList)
		{
			if(typeof pageList[i] === 'function')
				continue;
				
			let	template = document.getElementById('template-page-item').innerHTML;

			let levelFolderNode = document.createElement('span');
				levelFolderNode.classList.add('level-folder');

			for(let e = 1; e < pageList[i].level; e++)
			{
				levelFolderNode.innerHTML += ' <b>&mdash;</b> ';
			}
			
			if(pageList[i].create_time == '0') pageList[i].create_time = ''; else pageList[i].create_time = cmstk.formatDate(pageList[i].create_time, 'Y-m-d @ H:i:s');
			if(pageList[i].update_time == '0') pageList[i].update_time = ''; else pageList[i].update_time = cmstk.formatDate(pageList[i].update_time, 'Y-m-d @ H:i:s');

			template = template.replaceAll('%NUM_CHILDNODES%', (typeof pageList[i].childnodes !== 'undefined' ? Object.keys(pageList[i].childnodes).length : 0));
			template = template.replaceAll('%PAGE_NAME%', levelFolderNode.outerHTML + pageList[i].page_name);
			template = template.replaceAll('%PAGE_PATH%', CMS.SERVER_URL +''+ ((CMS.LANGUAGE_DEFAULT_IN_URL || pageList[i].page_language !== CMS.LANGUAGE_DEFAULT) ? pageList[i].page_language +'/' : '') + pageList[i].page_path.substr(1));
			template = template.replaceAll('%NODE_ID%', pageList[i].node_id);
			template = template.replaceAll('%PAGE_LANGUAGE%', pageList[i].page_language);
			//template = template.replaceAll('%CREATE_TIME%', pageList[i].create_time);
			template = template.replaceAll('%UPDATE_TIME%', (pageList[i].update_time !== '' ? pageList[i].update_time : pageList[i].create_time));

			if(pageList[i].page_path !== '/')
			{
				template = template.replaceAll('%BUTTON_DELETE%', '<button class="button icon trigger-delete-page"><i class="fas fa-trash-alt"></i></button>');
				template = template.replaceAll('%BUTTON_MOVE%', '<button class="button icon trigger-move-subpage"><i class="fas fa-share" style="font-size:1.2em;"></i></button>');
			}
			else
			{
				template = template.replaceAll('%BUTTON_DELETE%', '&nbsp;');
				template = template.replaceAll('%BUTTON_MOVE%', '&nbsp;');
			}

			let listItemNode = document.createElement('li');
				listItemNode.innerHTML = template;

			if(pageList[i].level === 1)
				listItemNode.classList.add('open');


			if(typeof this.openedNodeIdList[pageList[i].node_id] !== 'undefined' && this.openedNodeIdList[pageList[i].node_id])
				listItemNode.classList.add('open');

			if(typeof pageList[i].childnodes !== 'undefined' && Object.keys(pageList[i].childnodes).length > 0)
			{
				let listNode = document.createElement('ul');

				this.constructNestedList(pageList[i].childnodes, listNode)

				listItemNode.appendChild(listNode);
			}

			destNode.appendChild(listItemNode);
		}
	}

	requestData(language, openedNodeIdList = [])
	{
		var	that = this;

		this.openedNodeIdList = openedNodeIdList;

		

		var	formData = new FormData;
			formData.append('cms-xhrequest','raw-data');

		if(language !== null)
			formData.append('language',language);

		var	requestTarget	= CMS.SERVER_URL_BACKEND + CMS.PAGE_PATH + CMS.MODULE_TARGET;

		cmstk.callXHR(requestTarget, formData, that.onXHRSuccess, cmstk.onXHRError, that, 'index');
	}

}
