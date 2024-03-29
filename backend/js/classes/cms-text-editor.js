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
	
		cmstk.callXHR(this.cmdFilelocation, null, this.onXHRInit, cmstk.onXHRError, that);

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

		var fontColor = cmstk.getVisibleBackgroundColor(srcElement);
			fontColor = cmstk.invertRGBA(fontColor);

		var oParent 			= document.createElement("div");
			oParent.classList.add(this.className);
			oParent.classList.add('editor-simple');
			oParent.id 			= srcElement.id || "rich-text-" + nEditorId;

			
			if(srcElement.getAttribute('data-flag-hidden') == '1')
			{
				oParent.style.opacity = '0.5';
				oParent.style.background = 'rgba(194, 214, 214,0.4)';
			}

		var oToolsBar 			= document.createElement("div"); 
			oToolsBar.className = "rte-tools";
			oToolsBar.style.display = "none";
			oToolsBar.style.width = "100%";
			oToolsBar.id 		= "rte-tools-" + nEditorId;

		var	oTextarea			= document.createElement("textarea");
			oTextarea.className = "rte-textarea";
			oTextarea.id 		= "rte-textarea-" + nEditorId;
			oTextarea.innerHTML = srcElement.innerHTML;
			oTextarea.setAttribute('name', this.nameAttribute);
			oTextarea.style.display = "none";

		var	oHiddenVisbile			= document.createElement("input");
			oHiddenVisbile.type		= 	"hidden";
			oHiddenVisbile.setAttribute('name', this.nameAttribute +'-flag-hidden');
			oHiddenVisbile.value = srcElement.getAttribute('data-flag-hidden');

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
			oEditBox.oninput	= function(event) {
				var	textarea = this.parentNode.querySelector('.rte-textarea');
					textarea.innerText = this.innerHTML;
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





			let	rfIcongTag	  = document.createElement("i");

			if(srcElement.getAttribute('data-flag-hidden') == '1')
				rfIcongTag.classList.add('far', 'fa-eye-slash');
			else
				rfIcongTag.classList.add('far', 'fa-eye');

			let rfButtonNode 		  = document.createElement("button");
				rfButtonNode.className = "rte-button";
				rfButtonNode.id 		  = oBtnDefinition.command + nEditorId;
				rfButtonNode.title 	  = oBtnDefinition.text;
				rfButtonNode.style.float = "right";
				rfButtonNode.onclick   = function(event) {
					
				
					let pn = event.target.closest('.editor-simple');


					let hn = pn.querySelector('input[name="simple-text-flag-hidden"]');

					if(hn.value == '1')
					{
						hn.value = '0';
						this.querySelector('i').classList.remove('fa-eye-slash');
						this.querySelector('i').classList.add('fa-eye');
						pn.style.opacity = '1';
						pn.style.background = '';
					}
					else
					{
						hn.value = '1';
						this.querySelector('i').classList.remove('fa-eye');
						this.querySelector('i').classList.add('fa-eye-slash');
						pn.style.opacity = '0.5';
						pn.style.background = 'rgba(194, 214, 214,0.4)';

					}

				
				
												};
			rfButtonNode.setAttribute('type','button');
			rfButtonNode.style.color = fontColor;
			rfButtonNode.appendChild(rfIcongTag);
			oToolsBar.appendChild(rfButtonNode);






		oParent.appendChild(oEditBox);
		oParent.appendChild(oToolsBar);
		oParent.appendChild(oTextarea);
		oParent.appendChild(oHiddenVisbile);

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

