class	cmsTextEditor
{
	constructor()
	{
		this.aEditors 		= [];
		this.nReady 		= 0;

		this.sModeLabel 	= "Source";
		this.rId 			= /\d+$/;

		this.customCommands = {
			"createLink": function (oDoc, that) {
				var sLnk = prompt("Write the URL here", "http:\/\/");
				if (sLnk && sLnk !== "http://"){ that.formatContent(oDoc, "createlink", sLnk); }
			}
		};
	}

	init(className, cmdFilelocation, nameAttribute)
	{
		var that = this

		this.className 		= className;
		this.cmdFilelocation= cmdFilelocation
		this.nameAttribute	= nameAttribute;
	
		TK.callXHR(this.cmdFilelocation, null, this.onXHRInit, TK.onXHRError, that);

		document.addEventListener('mousedown', function(event) { that.hideToolbars(event.target); }, false);
	}

	onXHRInit(xhrResponse, xhrCallInstance)
	{
		xhrCallInstance.oTools = xhrResponse;
		xhrCallInstance.create(2);		
	}

	create(nFlag = 1)
	{
		this.nReady |= nFlag;
		if (this.nReady !== 3) { return; }

 		var targetElements = Array.prototype.slice.call(document.getElementsByClassName(this.className), 0);

		for(var nItem = 0; nItem < targetElements.length; nItem++)
		{
			if(this.editorExists(targetElements[nItem])) continue;
			this.replace(targetElements[nItem]);
		}
		
		document.execCommand("defaultParagraphSeparator", false, "p");
	}

	createOnDestNode(node)
	{
		var	targetElement = node.querySelector('.'+ this.className);
		if(targetElement != null)		
			this.replace(targetElement);
	}

	editorExists(targetElement)
	{
		var childNodes = targetElement.childNodes
		if(childNodes.length == 3 && childNodes[0].classList !== undefined && childNodes[0].classList.contains('rte-editbox'))
			return true;
		return false;
	}

	replace(srcElement)
	{
		var that 		= this;
		var	nEditorId 	= this.aEditors.length;

		var fontColor = TK.getVisibleBackgroundColor(srcElement);
			fontColor = TK.invertRGBA(fontColor);

		var oParent 			= document.createElement("div");
			oParent.classList.add(this.className);
			oParent.classList.add('editor-simple');
			oParent.id 			= srcElement.id || "rich-text-" + nEditorId;

		var oToolsBar 			= document.createElement("div"); 
			oToolsBar.className = "rte-tools";
			oToolsBar.style.display = "none";
			oToolsBar.id 		= "rte-tools-" + nEditorId;

		var	oTextarea			= document.createElement("textarea");
			oTextarea.className = "rte-textarea";
			oTextarea.id 		= "rte-textarea-" + nEditorId;
			oTextarea.innerHTML = srcElement.innerHTML;
			oTextarea.setAttribute('name', this.nameAttribute);
			oTextarea.style.display = "none";

		var oEditBox 			= document.createElement("div");
			oEditBox.className 	= "rte-editbox";
			oEditBox.id 		= "rte-editbox-" + nEditorId;
			oEditBox.setAttribute('placeholder', 'Enter text here');
			oEditBox.contentEditable = true;
			oEditBox.innerHTML 	= srcElement.innerHTML;
			oEditBox.onfocus 	= function() {
				var toolBar = this.nextSibling;
					toolBar.style.display = "block";
			};
			oEditBox.onpaste	= function(event) {
				event.stopPropagation();
				event.preventDefault();
				var plainText = (event.clipboardData || window.clipboardData).getData('text/plain');
				that.formatContent(this, "insertHTML", plainText);
			};
			oEditBox.oninput	= function() {
				var	textarea = this.parentNode.querySelector('.rte-textarea');
					textarea.innerHTML = this.innerHTML;
			};

		this.aEditors.push(oEditBox);

		for (var oBtnDefinition, oButton, nBtn = 0; nBtn < this.oTools.buttons.length; nBtn++)
		{
			oBtnDefinition	  = this.oTools.buttons[nBtn];

			var	oIconTag	  = document.createElement("i");
				oIconTag.classList.add('fas');
				oIconTag.classList.add(oBtnDefinition.image);

			oButton 		  = document.createElement("button");
			oButton.className = "rte-button";
			oButton.id 		  = oBtnDefinition.command + nEditorId;
			oButton.title 	  = oBtnDefinition.text;
			oButton.onclick   = function() {
				var sBtnGroup = that.rId.exec(this.id)[0], sCmd = this.id.slice(0, - sBtnGroup.length);
				that.customCommands.hasOwnProperty(sCmd) ? that.customCommands[sCmd](that.aEditors[sBtnGroup],that) : that.formatContent(that.aEditors[sBtnGroup], sCmd, this.alt || false);
			};
			oButton.setAttribute('type','button');
			oButton.style.color = fontColor;
			oButton.appendChild(oIconTag);
			oToolsBar.appendChild(oButton);
		}

		oParent.appendChild(oEditBox);
		oParent.appendChild(oToolsBar);
		oParent.appendChild(oTextarea);

		srcElement.parentNode.replaceChild(oParent, srcElement);
	}

	formatContent(oDoc, sCmd, sValue)
	{
		document.execCommand(sCmd, false, sValue);
		oDoc.focus();
	}

	hideToolbars(eventTarget)
	{
		if(eventTarget === null)
			return;

		var	activeToolBar 	= null;
		var	toolBarID		= null;
		var eventParent 	= eventTarget.closest('.'+ this.className);

		if(eventParent != null)
		{
			activeToolBar 	= eventParent.querySelector('.rte-tools');
			toolBarID 		= activeToolBar.id;
		}
	
		var toolBars = document.querySelectorAll('.rte-tools');
			toolBars.forEach(function(element) {
				if(toolBarID != null && toolBarID === element.id) return;
				element.style.display = 'none';
			});
	}
	
	update(updateTextarea, editor)
	{
		if(!editor.classList.contains(this.className)) return;

		var	editBox		= editor.querySelector('.rte-editbox');
		var	textarea	= editor.querySelector('.rte-textarea');
		
		if(editBox == null || textarea == null)
			return;

		if(updateTextarea)
			textarea.innerHTML = editBox.innerHTML;	
		else
			editBox.innerHTML = editBox.innerHTML;
	}

	updateAll(updateTextarea)
	{
		var	that	= this
		var	editors = document.querySelectorAll('.'+ this.className);
			editors.forEach(function(editor) {
				that.update(updateTextarea, editor);
			});

	}
}

